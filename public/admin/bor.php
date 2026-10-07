<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Serv\BORService;
use Repo\BORRepository;
use Core\User;
use Core\Database;
use Core\Token;
$pageTitle = 'Board of Regents';
User::requireAuthentication();

$serv = new BORService();
$repo = new BORRepository(Database::connect());
$bors = $repo->fetchFilteredBors('', 'all');

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
            case 'filter':
                $serv->filter();
                break;
            case 'view':
                $serv->view();
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
    <title>UCC Admin | <?= e($pageTitle ?? '') ?></title>
    <link rel="icon" type="image/png" href="/admin/assets/images/ucc-LOGO.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Libre+Baskerville:wght@700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/admin/assets/css/style.css">
    <link rel="stylesheet" href="/admin/assets/css/bor.css">
</head>
<body>
<div class="dashboard-layout">
    <?php require_once __DIR__ . '/template/sidebar.php' ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/template/header.php' ?>
        <section class="bor-page">
            <div class="page-toolbar">
                <div class="toolbar-copy">
                    <p>Manage the members and official information of the University of Caloocan City Board of Regents.</p>
                </div>
                <button class="primary-btn" id="addBorBtn" type="button">
                    <i data-lucide="plus"></i>
                    Add Regent
                </button>
            </div>
            <div class="bor-card">
                <div class="table-toolbar">
                    <div class="search-box">
                        <i data-lucide="search"></i>
                        <input id="borSearch" type="search" placeholder="Search regent..." autocomplete="off">
                    </div>
                    <div class="bor-filter">
                        <select id="borStatusFilter">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="hidden">Hidden</option>
                        </select>
                    </div>
                    <button type="button" id="applyBorFilter">Search</button>
                    <div class="result-count" id="borCount">
                        <?= count($bors) ?> members
                    </div>
                </div>
                <div id="borTableContainer">
                    <?php require __DIR__ . '/template/bor-table.php'; ?>
                </div>
            </div>
        </section>
        <div class="modal-overlay" id="borModal" hidden>
            <div class="official-modal" role="dialog" aria-modal="true" aria-labelledby="borModalTitle">
                <div class="modal-header">
                    <div>
                        <span class="modal-eyebrow">Board of Regents</span>
                        <h2 id="borModalTitle">Add Regent</h2>
                    </div>
                    <button class="modal-close" id="closeBorModal" type="button" aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <form method="post" id="borForm" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_key" value="<?= e(Token::generate()) ?>">
                    <input type="hidden" name="bor_id" value="0" id="borId">
                    <input type="hidden" name="remove_image" value="0" id="removeImage">

                    <div class="form-grid">
                        <div class="image-upload-area">
                            <div class="upload-placeholder" id="uploadPlaceholder">
                                <i data-lucide="image-plus"></i>
                                <strong>Photo</strong>
                                <span>Upload an image</span>
                            </div>
                            <img id="imagePreview" class="image-preview" alt="Photo preview">
                            <input type="file" name="image" id="featuredImage" accept="image/jpeg,image/png,image/webp" hidden>
                            <button type="button" class="post-btn post-btn-ghost" id="uploadImageBtn">
                                <i data-lucide="upload"></i> Upload Image
                            </button>
                            <button type="button" id="removeImageBtn" hidden>Remove Image</button>
                        </div>
                        <label>
                            <span>Full Name</span>
                            <input id="borName" name="name" type="text" required>
                        </label>
                        <label class="full">
                            <span>Position</span>
                            <input name="position" id="borPosition" type="text" required>
                        </label>
                        <label class="full">
                            <span>Biography / Short Description</span>
                            <textarea name="bio" id="borBio" rows="4" placeholder="Enter biography or additional information"></textarea>
                        </label>
                        <label>
                            <span>Status</span>
                            <select name="status" id="borStatus">
                                <option value="active">Active</option>
                                <option value="hidden">Hidden</option>
                            </select>
                        </label>
                    </div>
                    <div class="form-actions">
                        <button class="secondary-btn" id="cancelBorBtn" type="button">
                            Cancel
                        </button>
                        <button class="primary-btn" type="submit" name="action" value="save">
                            <i data-lucide="save"></i>
                            Save Regent
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-overlay" id="borViewModal" hidden>
            <div class="official-modal view-modal" role="dialog" aria-modal="true" aria-labelledby="borViewTitle">
                <div class="modal-header">
                    <div>
                        <span class="modal-eyebrow">Regent Details</span>
                        <h2 id="borViewTitle">Board Member</h2>
                    </div>
                    <button class="modal-close" id="closeBorView" type="button" aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="details-layout">
                    <div class="details-photo" id="borViewPhoto"></div>
                    <div class="details-copy">
                        <h3 id="borViewName"></h3>
                        <p class="details-position" id="borViewPosition"></p>
                        <span class="status-badge" id="borViewStatus">Active</span>
                        <div class="details-divider"></div>
                        <p class="details-label">Biography / Short Description</p>
                        <p class="details-bio" id="borViewBio"></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
lucide.createIcons();
document.querySelectorAll(".menu-title").forEach(button => {
	button.addEventListener("click", () => {
		button.parentElement.classList.toggle("open");
	});
});

// Dirty checker
let formDirty = false;

const borForm = document.getElementById('borForm');

borForm.addEventListener('input', () => {
    formDirty = true;
});

borForm.addEventListener('change', () => {
    formDirty = true;
});

// Open modal
function openBorModal() {
    formDirty = false;
    removeImage.value = '0';
    document.getElementById('borModal').hidden = false;
}

document.getElementById('addBorBtn').addEventListener('click', openBorModal);

// Close modal
function closeBorModal() {
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

document.getElementById('closeBorModal').addEventListener('click', closeBorModal);
document.getElementById('cancelBorBtn').addEventListener('click', closeBorModal);

// Upload image and remove image
const featuredImage = document.getElementById('featuredImage');
const imagePreview = document.getElementById('imagePreview');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const uploadImageBtn = document.getElementById('uploadImageBtn');
const removeImageBtn = document.getElementById('removeImageBtn');
const removeImage = document.getElementById('removeImage');

// Open file picker
uploadImageBtn.addEventListener('click', () => {
    featuredImage.click();
});

// Select image
featuredImage.addEventListener('change', () => {
    const file = featuredImage.files[0];

    if (!file) {
        return;
    }

    imagePreview.src = URL.createObjectURL(file);
    imagePreview.hidden = false;
    uploadPlaceholder.hidden = true;
    removeImageBtn.hidden = false;
    removeImage.value = '0';
});

// Remove image
removeImageBtn.addEventListener('click', () => {
    featuredImage.value = '';
    imagePreview.src = '';
    imagePreview.hidden = true;
    uploadPlaceholder.hidden = false;
    removeImageBtn.hidden = true;
    removeImage.value = '1';
});

// Filter & search
const borSearch = document.getElementById('borSearch');
const borStatusFilter = document.getElementById('borStatusFilter');
const applyBorFilter = document.getElementById('applyBorFilter');
const borTableContainer = document.getElementById('borTableContainer');
const borCount = document.getElementById('borCount');

async function loadBors() {
    const params = new URLSearchParams({
        request: 'filter',
        search: borSearch.value.trim(),
        filter: borStatusFilter.value,
    });

    try {
        const response = await fetch(`bor.php?${params}`);

        if (!response.ok) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load regents.'
            });

            return;
        }

        const result = await response.json();
        borTableContainer.innerHTML = result.html;
        borCount.textContent = `${result.count} ${result.count === 1 ? 'member' : 'members'}`;

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

applyBorFilter.addEventListener('click', loadBors);

// View BOR
const borViewModal = document.getElementById('borViewModal');
const closeBorView = document.getElementById('closeBorView');
const borViewPhoto = document.getElementById('borViewPhoto');
const borViewName = document.getElementById('borViewName');
const borViewPosition = document.getElementById('borViewPosition');
const borViewStatus = document.getElementById('borViewStatus');
const borViewBio = document.getElementById('borViewBio');

async function viewBor(borId) {
    const params = new URLSearchParams({
        request: 'view',
        bor_id: borId
    });

    try {
        const response = await fetch(`bor.php?${params}`);

        const result = await response.json();

        if (!response.ok) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message
            });

            return;
        }

        borViewName.textContent = result.name;
        borViewPosition.textContent = result.position;
        borViewBio.textContent = result.bio || '—';
        borViewStatus.textContent = result.status.charAt(0).toUpperCase() + result.status.slice(1);
        borViewStatus.className = `status-badge ${result.status === 'active' ? 'active' : 'hidden-status'}`;

        if (result.image) {
            borViewPhoto.innerHTML = `<img src="/admin/storage/uploads/${result.image}" alt="${result.name}">`;
        } else {
            borViewPhoto.innerHTML = `<span>${result.name.substring(0, 2).toUpperCase()}</span>`;
        }

        borViewModal.hidden = false;

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

// List actions
document.addEventListener('click', async (event) => {
    const viewButton = event.target.closest('[data-action="view"]');
    const editButton = event.target.closest('[data-action="edit"]');
    const deleteButton = event.target.closest('.delete');

    if (viewButton) {
        const borId = viewButton.dataset.id;
        viewBor(borId);
        return;
    }

    if (deleteButton) {
        event.preventDefault();

        const result = await Swal.fire({
            title: 'Remove regent?',
            text: 'This regent will be removed from the list.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) {
            return;
        }

        const form = deleteButton.closest('form');
        const borId = form.querySelector('input[name="bor_id"]').value;
        const csrfToken = form.querySelector('input[name="csrf_key"]').value;

        try {
            const response = await fetch('bor.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'remove',
                    bor_id: borId,
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

            loadBors();

        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Unable to communicate with the server.'
            });
        }
    }

    if (editButton) {
        const borId = editButton.dataset.id;

        try {
            const params = new URLSearchParams({
                request: 'edit',
                bor_id: borId
            });

            const response = await fetch(`bor.php?${params}`);
            const result = await response.json();

            if (!response.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Cannot find regent to edit.'
                });

                return;
            }

            const bor = result.bor;
            document.getElementById('borModalTitle').textContent = 'Edit Regent';
            document.getElementById('borName').value = bor.name;
            document.getElementById('borPosition').value = bor.position;
            document.getElementById('borBio').value = bor.bio ?? '';
            document.getElementById('borStatus').value = bor.status;
            document.getElementById('borId').value = bor.id;

            if (bor.image) {
                imagePreview.src = '/admin/storage/uploads/' + bor.image;
                imagePreview.hidden = false;
                uploadPlaceholder.hidden = true;
            } else {
                imagePreview.removeAttribute('src');
                imagePreview.hidden = true;
                uploadPlaceholder.hidden = false;
            }

            removeImage.value = '0';
            removeImageBtn.hidden = !bor.image;
            formDirty = false;
            borModal.hidden = false;

            loadBors();

        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Unable to communicate with the server.'
            });
        }
    }
});

// Close view modal
closeBorView.addEventListener('click', () => {
    borViewModal.hidden = true;
});

// BOR form
borForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: new FormData(borForm, event.submitter)
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