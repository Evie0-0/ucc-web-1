<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Serv\ExoffService;
use Repo\ExoffRepository;
use Core\User;
use Core\Database;
use Core\Token;

$pageTitle = 'Executive Officials';

User::requireAuthentication();

$serv = new ExoffService();
$repo = new ExoffRepository(Database::connect());
$exoffs = $repo->fetchFilteredExoffs('', 'all');

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
     <link rel="stylesheet" href="/admin/assets/css/exoff.css">
	<link rel="stylesheet" href="/admin/assets/css/style.css">
</head>
<body>
	<div class="dashboard-layout">
        <?php require_once __DIR__ . '/template/sidebar.php' ?>
		<main class="main-content">
            <?php require_once __DIR__ . '/template/header.php' ?>
            <section class="officials-page">
                <div class="page-toolbar">
                    <div class="toolbar-copy">
                        <p>Manage executive official profiles displayed on the public website.</p>
                    </div>
                    <button class="primary-btn" id="addOfficialBtn" type="button">
                        <i data-lucide="plus"></i>
                        Add Official
                    </button>
                </div>
                <div class="officials-card">
                    <div class="table-toolbar">
                        <div class="search-box">
                            <i data-lucide="search"></i>
                            <input id="officialSearch" type="search" placeholder="Search official..." autocomplete="off">
                        </div>
                        <select id="officialFilter">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="hidden">Hidden</option>
                        </select>
                        <button type="button" id="applyExoffFilter">Search</button>
                        <div class="result-count" id="exoffCount">
                            <?= count($exoffs) ?> officials
                        </div>
                    </div>
                    <div id="officialsTableContainer">
                        <?php require __DIR__ . '/template/exoff-table.php'; ?>
                    </div>
                </div>
            </section>
            <div class="modal-overlay" id="viewModal" hidden>
                <div class="official-modal view-modal" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
                    <div class="modal-header">
                        <div>
                            <span class="modal-eyebrow">Official Details</span>
                            <h2 id="viewModalTitle">Executive Official</h2>
                        </div>
                        <button class="modal-close" id="closeViewBtn" type="button" aria-label="Close">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    <div class="details-layout">
                        <div class="details-photo" id="viewPhoto"></div>
                        <div class="details-copy">
                            <h3 id="viewName"></h3>
                            <p class="details-position" id="viewPosition"></p>
                            <span class="status-badge" id="viewStatus"></span>
                            <div class="details-divider"></div>
                            <p class="details-label">Biography / Short Description</p>
                            <p class="details-bio" id="viewBio"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-overlay" id="officialModal" hidden>
                <div class="official-modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
                    <div class="modal-header">
                        <div>
                            <span class="modal-eyebrow">Executive Official</span>
                            <h2 id="modalTitle">Add Official</h2>
                        </div>
                        <button class="modal-close" id="closeModalBtn" type="button" aria-label="Close">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    <form method="post" id="officialForm" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_key" value="<?= e(Token::generate()) ?>">
                        <input type="hidden" name="exoff_id" value="0" id="exoffId">
                        <input type="hidden" name="remove_image" value="0" id="removeImage">

                        <div class="form-grid">
                            <div class="image-upload-area">
                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <i data-lucide="image-plus"></i>
                                    <strong>Photo</strong>
                                    <span>Upload an image</span>
                                </div>
                                <img id="imagePreview" class="image-preview" alt="Photo preview">
                                <input type="file" name="image" id="featuredImage" accept="image/jpeg,image/png,image/webp"hidden>
                                <button type="button" class="post-btn post-btn-ghost" id="uploadImageBtn">
                                    <i data-lucide="upload"></i>
                                    Upload Image
                                </button>
                                <button type="button" id="removeImageBtn" hidden>
                                    Remove Image
                                </button>
                            </div>
                            <label>
                                <span>Full Name</span>
                                <input id="name" name="name" type="text" required>
                            </label>
                            <label class="full">
                                <span>Position</span>
                                <input id="position" name="position" type="text" required>
                            </label>
                            <label class="full">
                                <span>Biography / Short Description</span>
                                <textarea id="bio" name="bio" rows="4" placeholder="Enter biography or additional role information"></textarea>
                            </label>
                            <label>
                                <span>Status</span>
                                <select id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="hidden">Hidden</option>
                                </select>
                            </label>
                        </div>
                        <div class="form-actions">
                            <button class="secondary-btn" id="cancelBtn" type="button">Cancel</button>
                            <button class="primary-btn" type="submit" name="action" value="save">
                                <i data-lucide="save"></i>
                                Save Official
                            </button>
                        </div>
                    </form>
                </div>
            </div>

</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
lucide.createIcons();

document.querySelectorAll(".menu-title").forEach(button=>{
    button.addEventListener("click",()=>{
        button.parentElement.classList.toggle("open");
    });
});

// Dirty checker
let formDirty = false;

const officialForm = document.getElementById('officialForm');

officialForm.addEventListener('input', () => {
    formDirty = true;
});

officialForm.addEventListener('change', () => {
    formDirty = true;
});

// Open modal
function openOfficialModal() {
    formDirty = false;
    removeImage.value = '0';
    document.getElementById('officialModal').hidden = false;
}

document.getElementById('addOfficialBtn').addEventListener(
    'click',
    openOfficialModal
);

// Close modal
function closeOfficialModal() {
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

document.getElementById('closeModalBtn').addEventListener(
    'click',
    closeOfficialModal
);

document.getElementById('cancelBtn').addEventListener(
    'click',
    closeOfficialModal
);

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


// Filter and search
const officialSearch = document.getElementById('officialSearch');
const officialFilter = document.getElementById('officialFilter');
const applyExoffFilter = document.getElementById('applyExoffFilter');
const officialsTableContainer = document.getElementById('officialsTableContainer');
const exoffCount = document.getElementById('exoffCount');

async function loadOfficials() {
    const params = new URLSearchParams({
        request: 'filter',
        search: officialSearch.value.trim(),
        filter: officialFilter.value,
    });

    try {

        const response = await fetch(`exoff.php?${params}`);

        if (!response.ok) {

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load officials.'
            });

            return;
        }

        const result = await response.json();
        officialsTableContainer.innerHTML = result.html;
        exoffCount.textContent = `${result.count} ${result.count === 1 ? 'official' : 'officials'}`;

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

applyExoffFilter.addEventListener('click', loadOfficials);

// View modal
const viewModal = document.getElementById('viewModal');
const closeViewBtn = document.getElementById('closeViewBtn');
const viewPhoto = document.getElementById('viewPhoto');
const viewName = document.getElementById('viewName');
const viewPosition = document.getElementById('viewPosition');
const viewStatus = document.getElementById('viewStatus');
const viewBio = document.getElementById('viewBio');

async function viewOfficial(exoffId) {

    const params = new URLSearchParams({
        request: 'view',
        exoff_id: exoffId
    });

     try {

        const response = await fetch(`exoff.php?${params}`);

        const result = await response.json();

        if (!response.ok) {

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message
            });

            return;
        }

        viewName.textContent = result.name;
        viewPosition.textContent = result.position;
        viewBio.textContent = result.bio || '—';
        viewStatus.textContent = result.status.charAt(0).toUpperCase() + result.status.slice(1);
        viewStatus.className = `status-badge ${result.status === 'active' ? 'active' : 'hidden-status'}`;

        if (result.image) {
            viewPhoto.innerHTML = `<img src="/admin/storage/uploads/${result.image}" alt="${result.name}">`;
        } else {
            viewPhoto.innerHTML = `<span>${result.name.substring(0, 2).toUpperCase()}</span>`;
        }

        viewModal.hidden = false;

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
        const exoffId = viewButton.dataset.id;
        viewOfficial(exoffId);

        return;
    }

     if (deleteButton) {

        event.preventDefault();

        const result = await Swal.fire({
            title: 'Remove official?',
            text: 'This official will be removed from the list.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) {
            return;
        }

        const form = deleteButton.closest('form');
        const exoffId = form.querySelector('input[name="exoff_id"]').value;
        const csrfToken = form.querySelector('input[name="csrf_key"]').value;

        try {
            const response = await fetch('exoff.php', {
                method: 'POST',

                body: new URLSearchParams({
                    action: 'remove',
                    exoff_id: exoffId,
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
            
            loadOfficials();

        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Unable to communicate with the server.'
            });
        }

        return;
    }

    if (editButton) {
        const exoffId = editButton.dataset.id;

        try {
            const params = new URLSearchParams({
                request: 'edit',
                exoff_id: exoffId
            });

            const response = await fetch(`exoff.php?${params}`);
            const result = await response.json();

            if (!response.ok) {

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Cannot find official to edit.'
                });

                return;
            }

            const exoff = result.exoff;
            document.getElementById('modalTitle').textContent = 'Edit Official';
            document.getElementById('name').value = exoff.name;
            document.getElementById('position').value = exoff.position;
            document.getElementById('bio').value = exoff.bio ?? '';
            document.getElementById('status').value = exoff.status;
            document.getElementById('exoffId').value = exoff.id;

            if (exoff.image) {
                imagePreview.src = '/admin/storage/uploads/' + exoff.image;
                imagePreview.hidden = false;
                uploadPlaceholder.hidden = true;

            } else {
                imagePreview.removeAttribute('src');
                imagePreview.hidden = true;
                uploadPlaceholder.hidden = false;
            }

            removeImage.value = '0';
            removeImageBtn.hidden = !exoff.image;
            formDirty = false;
            document.getElementById('officialModal').hidden = false;

            loadOfficials();

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
closeViewBtn.addEventListener('click', () => {
    viewModal.hidden = true;
});

// Exoff form
officialForm.addEventListener('submit', async (event) => {

    event.preventDefault();

    try {

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: new FormData(
                officialForm,
                event.submitter
            )
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
