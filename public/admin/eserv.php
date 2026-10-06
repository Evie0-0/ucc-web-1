<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Serv\EservService;
use Repo\EservRepository;
use Core\User;
use Core\Database;
use Core\Token;

$pageTitle = 'E-Services';

User::requireAuthentication();

$serv = new EservService();
$repo = new EservRepository(Database::connect());
$services = $repo->fetchSearchedServices('');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Token::verify($_POST['csrf_key'] ?? null)) {
        header('Location: /404.php');
        exit('Invalid csrf token.');
    }

    $action = $_POST['action'];

    switch ($action) {
        case 'save':
            $serv->save();
            break;
        case 'remove':
            $serv->remove();
            break;
        default:
            jsonResponse(400, 'Invalid request.');
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['request'])) {
        $request = $_GET['request'];

        switch ($request) {
            case 'search':
                $serv->search();
                break;
            case 'edit':
                $serv->edit();
                break;
            default:
                jsonResponse(400, 'Invalid request.');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>UCC Admin | E-Services</title>
	<link rel="icon" type="image/png" href="/admin/assets/images/ucc-LOGO.png">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Libre+Baskerville:wght@700&display=swap" rel="stylesheet">
	<script src="https://unpkg.com/lucide@latest"></script>
	<link rel="stylesheet" href="/admin/assets/css/style.css">
	<link rel="stylesheet" href="/admin/assets/css/eserv.css">
</head>
<style>
/* Change this, this is only for making images small temprorarily */ 
img {
    width: 50px;
    height: 50px'
}
</style>
<body>
	<div class="dashboard-layout">
        <?php require_once __DIR__ . '/template/sidebar.php' ?>
		<main class="main-content">
            <?php require_once __DIR__ . '/template/header.php' ?>
			<section class="services-admin">
				<div class="services-toolbar">
					<div class="services-search">
						<i data-lucide="search"></i>
                        <input type="text" id="serviceSearch" placeholder="Search e-services...">
					</div>
                    <button type="button" id="applySearch">Search</button>
					<button class="service-add-btn" type="button" id="addServiceBtn">
						<i data-lucide="plus"></i>
						Add E-Service
					</button>
				</div>

                <div class="services-grid" id="servicesGrid">
                    <?php require __DIR__ . '/template/eserv-table.php'; ?>
                </div>
			</section>
		</main>
	</div>

	<div class="service-modal" id="serviceModal" aria-hidden="true">
		<div class="service-modal-box">
			<div class="service-modal-header">
				<div>
					<h3 id="modalTitle">Add E-Service</h3>
					<p>Enter the information for this e-service.</p>
				</div>
				<button class="modal-close" type="button" id="modalClose">
					<i data-lucide="x"></i>
				</button>
			</div>

            <form method="post" enctype="multipart/form-data" id="serviceForm">
                <input type="hidden" name="csrf_key" value="<?= e(Token::generate()) ?>">
                <input type="hidden" name="service_id" value="0" id="serviceId">
                <input type="hidden" name="remove_image" value="0" id="removeImage">

                <div class="image-upload-area">
                    <div class="upload-placeholder" id="uploadPlaceholder">
                        <i data-lucide="image-plus"></i>
                        <strong>Logo</strong>
                        <span>Upload an image</span>
                    </div>
                    <img id="imagePreview" class="image-preview" alt="Logo preview">
                    <input type="file" name="logo" id="logoImage" accept="image/jpeg,image/png,image/webp" hidden>
                    <button type="button" class="post-btn post-btn-ghost" id="uploadImageBtn">
                        <i data-lucide="upload"></i> Upload Image
                    </button>
                    <button type="button" id="removeImageBtn" hidden>Remove Image</button>
                </div>

				<div class="form-group">
					<label for="serviceName">Service Name</label>
					<input type="text" name="name" id="serviceName" required>
				</div>

				<div class="form-group">
					<label for="serviceCategory">Category</label>
					<input type="text" name="category" id="serviceCategory" required>
				</div>

				<div class="form-group">
					<label for="serviceDescription">Description</label>
					<textarea id="serviceDescription" name="description" rows="4" required></textarea>
				</div>

				<div class="form-group">
					<label for="serviceUrl">Website URL</label>
					<input type="url" name="url" id="serviceUrl" placeholder="https://example.com" required>
				</div>

				<div class="modal-actions">
					<button type="button" class="modal-cancel" id="modalCancel">Cancel</button>
					<button type="submit" name="action" value="save" class="modal-save">Save Service</button>
				</div>
			</form>
		</div>
	</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script> 
<script>
let formDirty = false;
</script>
<script>
lucide.createIcons();

/*
		document.querySelectorAll(".menu-title").forEach(button => {
			button.addEventListener("click", () => {
				button.parentElement.classList.toggle("open");
			});
		});

		const searchInput = document.getElementById("serviceSearch");
		const servicesGrid = document.getElementById("servicesGrid");
		const noServices = document.getElementById("noServices");

		searchInput.addEventListener("input", () => {
			const query = searchInput.value.toLowerCase().trim();
			const cards = servicesGrid.querySelectorAll(".service-card");
			let visible = 0;

			cards.forEach(card => {
				const text = card.textContent.toLowerCase();
				const match = text.includes(query);

				card.style.display = match ? "" : "none";

				if (match) {
					visible++;
				}
			});

			noServices.style.display = visible === 0 ? "flex" : "none";
		});

		const modal = document.getElementById("serviceModal");
		const modalTitle = document.getElementById("modalTitle");
		const serviceForm = document.getElementById("serviceForm");
		const modalClose = document.getElementById("modalClose");
		const modalCancel = document.getElementById("modalCancel");
		const addServiceBtn = document.getElementById("addServiceBtn");

		let editingCard = null;

		function openModal(mode, card = null) {
			editingCard = card;

			modalTitle.textContent = mode === "edit" ? "Edit E-Service" : "Add E-Service";

			if (card) {
				document.getElementById("serviceName").value = card.querySelector("h3").textContent.trim();
				document.getElementById("serviceCategory").value = card.querySelector(".service-card-content span").textContent.trim();
				document.getElementById("serviceDescription").value = card.querySelector(".service-card-content p").textContent.trim();
				document.getElementById("serviceUrl").value = card.querySelector(".service-card-footer a").getAttribute("href") || "";
			} else {
				serviceForm.reset();
			}

			modal.classList.add("show");
			modal.setAttribute("aria-hidden", "false");
			document.body.style.overflow = "hidden";
		}

		function closeModal() {
			modal.classList.remove("show");
			modal.setAttribute("aria-hidden", "true");
			document.body.style.overflow = "";
			editingCard = null;
		}

		addServiceBtn.addEventListener("click", () => openModal("add"));
		modalClose.addEventListener("click", closeModal);
		modalCancel.addEventListener("click", closeModal);

		modal.addEventListener("click", event => {
			if (event.target === modal) {
				closeModal();
			}
		});

		document.addEventListener("keydown", event => {
			if (event.key === "Escape" && modal.classList.contains("show")) {
				closeModal();
			}
		});

		serviceForm.addEventListener("submit", event => {
			event.preventDefault();

			const name = document.getElementById("serviceName").value.trim();
			const category = document.getElementById("serviceCategory").value.trim();
			const description = document.getElementById("serviceDescription").value.trim();
			const url = document.getElementById("serviceUrl").value.trim();

			if (editingCard) {
				editingCard.querySelector("h3").textContent = name;
				editingCard.querySelector(".service-card-content span").textContent = category;
				editingCard.querySelector(".service-card-content p").textContent = description;
				editingCard.querySelector(".service-card-footer a").href = url;
			} else {
				const card = document.createElement("article");
				card.className = "service-card";

				card.innerHTML = `
					<div class="service-card-top">
						<div class="service-icon">
							<i data-lucide="globe"></i>
						</div>
						<div class="service-actions">
							<button class="service-action edit-action" type="button" title="Edit">
								<i data-lucide="pencil"></i>
							</button>
							<button class="service-action delete-action" type="button" title="Delete">
								<i data-lucide="trash-2"></i>
							</button>
						</div>
					</div>
					<div class="service-card-content">
						<h3>${name}</h3>
						<span>${category}</span>
						<p>${description}</p>
					</div>
					<div class="service-card-footer">
						<a href="${url}" target="_blank">Visit Website <i data-lucide="arrow-up-right"></i></a>
					</div>
				`;

				servicesGrid.appendChild(card);
				lucide.createIcons();
			}

			closeModal();
		});

		servicesGrid.addEventListener("click", event => {
			const editButton = event.target.closest(".edit-action");
			const deleteButton = event.target.closest(".delete-action");

			if (editButton) {
				const card = editButton.closest(".service-card");
				openModal("edit", card);
			}

			if (deleteButton) {
				const card = deleteButton.closest(".service-card");
				card.remove();

				const remainingCards = servicesGrid.querySelectorAll(".service-card");

				if (remainingCards.length === 0) {
					noServices.style.display = "flex";
				}
			}
        });
 */

// Opens modal
const addServiceBtn = document.getElementById("addServiceBtn");
const modal = document.getElementById("serviceModal");

addServiceBtn.addEventListener("click", () => {
    modal.classList.add("show");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
});

// Upload image preview and remove image preview
const uploadImageBtn = document.getElementById('uploadImageBtn');
const logoImage = document.getElementById('logoImage');
const imagePreview = document.getElementById('imagePreview');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const removeImageBtn = document.getElementById('removeImageBtn');
const removeImage = document.getElementById('removeImage');

function updateRemoveImageBtn() {
    removeImageBtn.hidden = !imagePreview.getAttribute('src');
}

updateRemoveImageBtn();

uploadImageBtn.addEventListener('click', () => {
    logoImage.click();
});

logoImage.addEventListener('change', () => {
    const file = logoImage.files[0];

    if (!file) {
        return;
    }

    formDirty = true;
    imagePreview.src = URL.createObjectURL(file);
    imagePreview.style.display = 'block';
    uploadPlaceholder.style.display = 'none';
    removeImage.value = '0';

    updateRemoveImageBtn();
});

removeImageBtn.addEventListener('click', () => {
    logoImage.value = '';
    imagePreview.removeAttribute('src');
    imagePreview.style.display = 'none';
    uploadPlaceholder.style.display = 'flex';
    removeImage.value = '1';
    formDirty = true;

    updateRemoveImageBtn();
});

// Dirty checker
const serviceForm = document.getElementById('serviceForm');

serviceForm.addEventListener('input', () => {
    formDirty = true;
});

serviceForm.addEventListener('change', () => {
    formDirty = true;
});

// Close modal / discard editing
function closeServiceModal() {
    if (!formDirty) {
        window.location.reload();
        return;
    }

    Swal.fire({
        title: 'Discard changes?',
        text: 'Your current work will be lost.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Discard',
        cancelButtonText: 'Keep editing'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.reload();
        }
    });
}

document.getElementById('modalClose').addEventListener('click', closeServiceModal);
document.getElementById('modalCancel').addEventListener('click', closeServiceModal);

// Search
const serviceSearch = document.getElementById('serviceSearch');
const applySearch = document.getElementById('applySearch');
const serviceIdEditing = document.getElementById('serviceId');
const servicesGrid = document.getElementById('servicesGrid');

async function loadServices() {

    const params = new URLSearchParams({
        request: 'search',
        search: serviceSearch.value.trim(),
        editing_id: serviceIdEditing.value
    });

    try {
        const response = await fetch(`eserv.php?${params}`);

        if (!response.ok) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load e-services.'
            });

            return;
        }

        const result = await response.json();

        servicesGrid.innerHTML = result.html;

        lucide.createIcons();

    } catch (error) {
        console.error(error);

        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Unable to communicate with the server.'
        });
    }
}

applySearch.addEventListener('click', loadServices);

// Edit
servicesGrid.addEventListener('click', async (event) => {
    const editButton = event.target.closest('.edit-action');
    const removeButton = event.target.closest('.delete-action');

    if (removeButton) {
        event.preventDefault();

        const result = await Swal.fire({
            title: 'Remove E-Service?',
            text: 'This e-service will no longer appear here.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) {
            return;
        }

        const form = removeButton.closest('form');
        const serviceId = form.querySelector('input[name="service_id"]').value;
        const csrfToken = form.querySelector('input[name="csrf_key"]').value;

        try {
            const response = await fetch('eserv.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'remove',
                    service_id: serviceId,
                    csrf_key: csrfToken
                })
            });

            const result = await response.json();

            if (!response.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message
                });

                return;
            }

            await Swal.fire({
                icon: 'success',
                title: 'Success',
                text: result.message
            });

            loadServices();
        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Unable to communicate with the server.'
            });
        }

        return;
    }


    if (editButton) {
        const serviceId = editButton.dataset.serviceId;

        try {
            const params = new URLSearchParams({
                request: 'edit',
                service_id: serviceId
            });

            const response = await fetch(`eserv.php?${params}`);
            const result = await response.json();

            if (!response.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message
                });

                return;
            }

            const service = result.service;
            document.getElementById('modalTitle').textContent = 'Edit E-Service';
            document.getElementById('serviceId').value = service.id;
            document.getElementById('serviceName').value = service.name;
            document.getElementById('serviceCategory').value = service.category;
            document.getElementById('serviceDescription').value = service.description;
            document.getElementById('serviceUrl').value = service.url;

            if (service.logo) {
                imagePreview.src = '/admin/storage/uploads/' + service.logo;
                imagePreview.style.display = 'block';
                uploadPlaceholder.style.display = 'none';
            } else {
                imagePreview.removeAttribute('src');
                imagePreview.style.display = 'none';
                uploadPlaceholder.style.display = 'flex';
            }

            removeImage.value = '0';

            updateRemoveImageBtn();

            formDirty = false;
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            loadServices();

        } catch (error) {

            console.error(error);

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Unable to communicate with the server.'
            });
        }

        return;
    }

});

// Service form
serviceForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: new FormData(serviceForm, event.submitter)  
        }); 

        const result = await response.json();

        if (!response.ok) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message
            }); 

            return;
        }

        await Swal.fire({
            icon: 'success',
            title: 'Success',
            text: result.message
        });

        window.location.reload();

    } catch (error) {
        console.error(error);

        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Unable to communicate with the server.'
        });
    }
});
</script>
</body>
</html>
