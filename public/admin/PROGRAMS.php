<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Controllers\ProgramController;
use Repositories\ProgramRepository;
use Core\User;
use Core\Token;
use Core\Database;
use Core\Message;

User::isAuthenticated();

$db = new Database();
$user = new User($db->connect());
$currentUser = $user->fetchUser($_SESSION['user_id']);
$controller = new ProgramController();
$repo = new ProgramRepository($db->connect());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Token::verify($_POST[Token::SESSION_KEY] ?? null)) {
        redirectPublic('404.php');
        exit('Invalid csrf token.');
    }
    
    if (($_POST['action'] ?? '') === 'save' && $controller->savePost()) {
        unset($_SESSION['form']);
        unset($_SESSION['filter']);
        redirectAdmin('programs.php');
        exit;      
    } elseif (($_POST['action'] ?? '') === 'cancel') {
        unset($_SESSION['form']);
        unset($_SESSION['filter']);
        redirectAdmin('programs.php');
        exit;
    }

    $controller->saveFormData($_POST);
    redirectAdmin('programs.php');
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (($_GET['action'] ?? '') === 'filter') {
        $_SESSION['filter']['search'] = (string) ($_GET['search'] ?? '');
        $_SESSION['filter']['status'] = (string) ($_GET['status'] ?? 'all');
        redirectAdmin('programs.php');
    } elseif (($_GET['action'] ?? '') === 'edit' && isset($_GET['id'])) {
        unset($_SESSION['form']);
        $controller->findPost((int) ($_GET['id'] ?? 0));
        redirectAdmin('programs.php');
    }
}

$form = $_SESSION['form'] ?? [];
$data = $form['data'] ?? [];
$original = $form['original'] ?? [];
$filter = $_SESSION['filter'] ?? [
    'search' => '',
    'status' => 'all',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>UCC Admin | Pages </title>
	<link rel="icon" type="image/png" href="/admin/assets/images/ucc-LOGO.png">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Libre+Baskerville:wght@700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
	<link rel="stylesheet" href="/admin/assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script> 
</head>
<body>
    <?php if (isset($_SESSION[Message::SUCCESS])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: <?= json_encode($_SESSION[Message::SUCCESS]); ?>
                });
            });
        </script>
        <?php unset($_SESSION[Message::SUCCESS]); ?>
    <?php elseif (isset($_SESSION[Message::ERROR])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: <?= json_encode($_SESSION[Message::ERROR]); ?>
                });
            });
        </script>
        <?php unset($_SESSION[Message::ERROR]); ?>
    <?php endif; ?> 
    
    <div class="dashboard-layout">
		<aside class="sidebar">
			<div class="sidebar-top">
				<div class="logo-wrap">
					<img src="/admin/assets/images/ucc-LOGO.png" alt="UCC Logo">
					<div>
						<h2>UCC Admin</h2>
						<p>University of Caloocan City</p>
					</div>
				</div>
			</div>

			<div class="gold-line"></div>

        <nav class="sidebar-nav">
            <a href="/admin/index.php">
                <i data-lucide="house"></i>
                <span>Dashboard</span>
            </a>

            <a href="/admin/posts.php" class="active">
                <i data-lucide="newspaper"></i>
                <span>Posts</span>
            </a>

            <div class="menu-group">
                <button class="menu-title" type="button">
                    <div>
                        <i data-lucide="graduation-cap"></i>
                        <span>Academics</span>
                    </div>
                    <i data-lucide="chevron-down" class="arrow"></i>
                </button>
                <div class="submenu">
                    <a href="Academics.html">Colleges</a>
                </div>
            </div>

            <div class="menu-group">
                <button class="menu-title" type="button">
                    <div>
                        <i data-lucide="users"></i>
                        <span>Officials</span>
                    </div>
                    <i data-lucide="chevron-down" class="arrow"></i>
                </button>
                <div class="submenu">
                    <a href="Executive-Officials.html">Executive Officials</a>
                    <a href="Board-of-Regents.html">Board of Regents</a>
                </div>
            </div>

            <div class="menu-group">
                <button class="menu-title" type="button">
                    <div>
                        <i data-lucide="building-2"></i>
                        <span>Components</span>
                    </div>
                    <i data-lucide="chevron-down" class="arrow"></i>
                </button>
                <div class="submenu">
                    <a href="Campuses.html">Campuses</a>
                    <a href="Contact-Information.html">Contact Information</a>
                    <a href="Quick-Links.html">Quick Links</a>
                </div>
            </div>
        </nav>

        <div class="sidebar-bottom">
            <a href="Password.html">
                <i data-lucide="lock"></i>
                <span>Password</span>
            </a>

            <a href="Logout.html">
                <i data-lucide="log-out"></i>
                <span>Logout</span>
            </a>
        </div>

			<div class="sidebar-art"></div>
		</aside>

		<main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Programs</h1>
                </div>

            <div class="header-right">
                <div class="admin-profile">
                    <div class="admin-text">
                        <small><?= $currentUser['role'] ?? '' ?></small>
                        <span>Welcome, <?= $currentUser['username'] ?? '' ?></span>
                    </div>
                    <div class="avatar">
                        <i data-lucide="circle-user-round"></i>
                    </div>
                </div>
            </div>

                <div class="header-art"></div>
                <div class="gold-divider">
                    <span></span>
                </div>
    </header>


    <?php if ($form['original']['downloaded_image'] ?? ''): ?>
        <p>featured image: <?= e($form['original']['downloaded_image']) ?></p>
    <?php endif; ?>

    <form id="main_form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="<?= e(Token::SESSION_KEY) ?>" value="<?= e(Token::generate()) ?>">
        <input type="hidden" name="id" value="<?= e((string) ($form['id'] ?? 0)) ?>" required>

        <label>college:</label>
        <input type="text" name="college" value="<?= e(($data['college'] ?? '')) ?>" required>

        <label>program:</label>
        <input type="text" name="name" value="<?= e(($data['name'] ?? '')) ?>" required>
        
        <label>slug:</label>
        <input type="text" name="slug" value="<?= e(($data['slug'] ?? '')) ?>" required>
        
        <label>degree level:</label >
        <select name="degree_level" required>
            <?php foreach (ProgramController::DEGREE_LEVELS ?? [] as $level): ?>
                <option value="<?= e($level) ?>" <?= ($data['degree_level'] ?? '') === $level ? 'selected' : '' ?>>
                    <?= e($level) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label>description:</label>
        <div id="editor"></div>
        <textarea name="description" id="content" hidden><?= e(($data['description'] ?? '')) ?></textarea><br>

        <?php
            $imageSource = $data['image_source'] ?? '';

            if ($imageSource === '') {
                $imageSource = ($original['downloaded_image'] ?? '') !== '' ? 'current' : 'none';
            }
        ?>
        <?php if (($original['downloaded_image'] ?? '') !== ''): ?>
            <label><input type="radio" name="image_source" value="current" <?= $imageSource === 'current' ? 'checked' : '' ?>>Don't Change</label>
        <?php endif; ?>
        <label><input type="radio" name="image_source" value="none" <?= $imageSource === 'none' ? 'checked' : '' ?>>No Image</label>
        <label><input type="radio" name="image_source" value="url" <?= $imageSource === 'url' ? 'checked' : '' ?>>Image URL</label>
        <label><input type="radio" name="image_source" value="uploaded" <?= $imageSource === 'uploaded' ? 'checked' : '' ?>>Upload Image</label>

        <?php
            $urlImageValue = $data['url_image'] ?? '';

            if ($urlImageValue === '') {
                $urlImageValue = $original['url_image'] ?? '';
            }
        ?>

        <input type="url" name="url_image" id="url_image" value="<?= e($urlImageValue) ?>">
        <input type="file" name="uploaded_image" id="uploaded_image" accept="image/jpeg,image/png,image/webp">

        <label>status:</label >
        <select name="status" required>
            <?php foreach (ProgramController::STATUSES ?? [] as $status): ?>
                <option value="<?= e($status) ?>" <?= ($data['status'] ?? '') === $status ? 'selected' : '' ?>>
                    <?= e($status) ?>
                </option>
            <?php endforeach; ?>
        </select><br>
 
        <button type="submit" name="action" value="save">save</button>
        <button type="submit" name="action" value="cancel">cancel</button>
    </form>

    <form action="programs.php" method="get">
        <input type="search" name="search" placeholder="Search program or college..." value="<?= e(($filter['search'] ?? '')) ?>">

        <label><input type="radio" name="status" value="all" <?= ($filter['status'] ?: 'all') === 'all' ? 'checked' : '' ?>>All</input></label>
        <label><input type="radio" name="status" value="Active" <?= ($filter['status'] ?? '') === 'Active' ? 'checked' : '' ?>>Active</input></label>
        <label><input type="radio" name="status" value="Inactive" <?= ($filter['status'] ?? '') === 'Inactive' ? 'checked' : '' ?>>Inactive</input></label>

        <button type="submit" name="action" value="filter">Filter</button>
    </form>

    <?php foreach ($repo->fetchPrograms((int) $form['id'], ($filter['search'] ?? ''), $filter['status'] ?: 'all') ?? [] as $result): ?>
        <p>college: <?= e($result['college']) ?></p>
        <p>program: <?= e($result['name']) ?></p>
        <p>description: <?= e($result['description']) ?></p>
        <p>degree level: <?= e($result['degree_level']) ?></p>
        <p>image: <?= e(($result['downloaded_image'] ?? '')) ?></p>
        <p>status: <?= e($result['status']) ?></p>
        <p>author: <?= e((string) $result['author_id']) ?></p>
        <p>editor: <?= e((string) ($result['editor_id'] ?? '')) ?></p>
        <form method="get">
            <input type="hidden" name="id" value="<?= e((string) $result['id']) ?>">
            <button type="submit" name="action" value="edit">edit</button><br>
        </form>
    <?php endforeach; ?>

    <hr>
    </p>This is the end.</p><br>

</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
	lucide.createIcons();

	document.querySelectorAll(".menu-title").forEach(button=>{
		button.addEventListener("click",()=>{
			button.parentElement.classList.toggle("open");
		});
    });

    // Image source requirement
    const imageSourceRadios = document.querySelectorAll('input[name="image_source"]');

    const urlImage = document.getElementById('url_image');
    const uploadedImage = document.getElementById('uploaded_image');

    function updateImageRequirements() {
        const selected = document.querySelector('input[name="image_source"]:checked')?.value;
    
        urlImage.required = selected === 'url';
        uploadedImage.required = selected === 'uploaded';
    }

    imageSourceRadios.forEach(radio => {
        radio.addEventListener('change', updateImageRequirements);
    });

    updateImageRequirements();

    // Quil
    const quill = new Quill('#editor', {
        theme: 'snow'
    });

    const contentInput = document.querySelector('#content');
    const form = document.querySelector('#main_form');

    if (contentInput.value !== '') {
        quill.clipboard.dangerouslyPasteHTML(contentInput.value);
    }

    // Form change checker
    let formChanged = false;

    form.addEventListener('submit', function () {
        contentInput.value = quill.root.innerHTML;
    });

    form.addEventListener('input', function () {
        formChanged = true;
    });

    form.addEventListener('change', function () {
        formChanged = true;
    });

    quill.on('text-change', function () {
        formChanged = true;
    });

    // For cancel button
    const cancelButton = document.querySelector('button[value="cancel"]');
    
    cancelButton.addEventListener('click', function (event) {
        if (!formChanged) {
            return;
        }

        event.preventDefault();

        Swal.fire({
            title: 'Cancel editing?',
            text: 'Your unsaved changes will be lost.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel',
            cancelButtonText: 'Keep editing'
        }).then(function (result) {
            if (result.isConfirmed) {
            const action = document.createElement('input');

            action.type = 'hidden';
            action.name = 'action';
            action.value = 'cancel';

            form.appendChild(action);
            form.submit();
            }
        });
    });

    // For edit buttons
    const editButtons = document.querySelectorAll('button[value="edit"]');

    editButtons.forEach(function (editButton) {
        editButton.addEventListener('click', function (event) {
            if (!formChanged) {
                return;
            }

            event.preventDefault();

            Swal.fire({
                title: 'Edit another page?',
                text: 'Your unsaved changes will be lost.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, edit',
                cancelButtonText: 'Keep editing'
            }).then(function (result) {
                if (result.isConfirmed) {
                    editButton.closest('form').submit(editButton);
                }
            });
        });
    });
</script>
</body>
</html>
