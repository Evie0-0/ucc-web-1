<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Models\User;
use Models\PostManager;
use Core\Token;
use Core\Database;
use Core\HtmlSanitizer;
use Core\Message;

User::isAuthenticated();

$db = new Database();
$user = new User($db->connect());
$currentUser = $user->fetchUser($_SESSION['user_id']);
$post = new PostManager($db->connect());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Token::verify($_POST[Token::CSRF_KEY] ?? null)) {
        header('Location: /404.php');
        exit('Invalid csrf token.');
    }
 
    if ($_POST['action'] === 'archive') {
        $post->archive((int) ($_POST['id'] ?? 0)); 
        Message::success('Successfully archived post.');
        exit;
    } else if ($_POST['action'] === 'cancel') {
        unset($_SESSION['draft']);
        header('Location: /admin/posts.php');
        exit;
    } 
    
    $post->id = (int) ($_SESSION['draft']['id'] ?? 0);
    
    if ($_POST['action'] === 'archive_current') {
        if ($post->id !== 0) {
            $post->archive((int) $post->id);
            unset($_SESSION['draft']);
            header('Location: /admin/posts.php');
            exit;
        } else {
            Message::error('Cannot archive post.');
        }
    }

    $htmlSanitizer = new HtmlSanitizer();

    $post->title = trim((string) ($_POST['title'] ?? ''));
    $post->slug = sluggify((string) ($_POST['slug'] ?? ''));
    $post->excerpt = trim((string) ($_POST['excerpt'] ?? ''));
    $post->content = $htmlSanitizer->sanitize((string) ($_POST['content'] ?? ''));
    $post->categoryId = (int) ($_POST['category_id'] ?? 0);
    $post->status = (string) ($_POST['status'] ?? '');
    $post->imageSource = (string) ($_POST['image_source'] ?? '');
    $post->remoteImage = trim((string) ($_POST['remote_image'] ?? ''));
    $post->uploadedImage = (array) ($_FILES['uploaded_image'] ?? []);
    $post->publishedAt= trim((string) ($_POST['published_at'] ?? ''));  

    if ($post->save()) {
        unset($_SESSION['draft']);
        header('Location: /admin/posts.php');
        exit;
    }

    $_SESSION['draft'] = [
        'id' => $post->id,
        'title' => $post->title,
        'slug' => $post->slug,
        'excerpt' => $post->excerpt,
        'content' => $post->content,
        'category_id' => $post->categoryId,
        'status' => $post->status,
        'image_source' => $post->imageSource,
        'remote_image' => $post->remoteImage,
        'published_at' => (string) $post->publishedAt,
        'featured_image' => $post->featuredImage,
    ];

    header('Location: /admin/posts.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    unset($_SESSION['draft']);
    
    $retrievedPost = $post->findPost((int) ($_GET['id'] ?? 0));
        
    if ($retrievedPost !== null) {
        $_SESSION['draft'] = [
            'id' => $retrievedPost['id'],
            'title' => $retrievedPost['title'],
            'slug' => $retrievedPost['slug'],
            'excerpt' => $retrievedPost['excerpt'],
            'content' => $retrievedPost['content'],
            'category_id' => $retrievedPost['category_id'],
            'status' => $retrievedPost['status'],
            'image_source' => !empty($retrievedPost['featured_image']) ? 'current' : 'none',
            'remote_image' => $retrievedPost['remote_image'],
            'published_at' => (string) $retrievedPost['published_at'],
            'featured_image' => $retrievedPost['featured_image'],
        ];

        Message::success('Post successfully loaded.');
    } else {
        header('Location: /admin/posts.php');
    } 
}

if (isset($_SESSION['draft'])) {
    $form = $_SESSION['draft'];
    
    $post->id = $form['id'];
    $post->title = $form['title'];
    $post->slug = $form['slug'];
    $post->excerpt = $form['excerpt'];
    $post->content = $form['content'];
    $post->categoryId = $form['category_id'];
    $post->status = $form['status'];
    $post->imageSource = $form['image_source'] ?? '';
    $post->remoteImage = $form['remote_image'] ?? '';
    $post->publishedAt = $form['published_at'];
    $post->featuredImage = $form['featured_image'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>UCC Admin | Posts </title>
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
                    <h1>Posts</h1>
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


    <?php if ($post->featuredImage ?? ''): ?>
        <p>featured image: <?= e($post->featuredImage) ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="<?= e(Token::SESSION_KEY) ?>" value="<?= e(Token::generate()) ?>">
        
        <label>title:</label>
        <input type="text" name="title" value="<?= e((string) $post->title) ?>" required>

        <label>category:</label>
        <select name="category_id" required>
            <?php foreach ($post->fetchCategories() ?? [] as $category): ?>
                <option value="<?= e((string) $category['id']) ?>" <?= $post->categoryId === (int) $category['id'] ? 'selected' : '' ?>>
                    <?= e((string) $category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label>slug:</label>
        <input type="text" name="slug" value="<?= e((string) $post->slug) ?>" required>
        
        <label>status:</label>
        <select name="status" required>
            <option value="published" <?= $post->status === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $post->status === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select><br>

        <label>excerpt:</label>
        <input type="text" name="excerpt" value="<?= e((string) $post->excerpt) ?>"></br>

        <label>content:</label>
        <div id="editor"></div>
        <textarea name="content" id="content" hidden><?= e($post->content) ?></textarea><br>

        <?php if (!empty($post->featuredImage)): ?>
            <label><input type="radio" name="image_source" value="current" 
                <?= $post->imageSource === 'current' || $post->imageSource === '' ? 'checked' : '' ?>>Don't Change
            </label>
        <?php endif; ?>
        <label><input type="radio" name="image_source" value="none" 
            <?= empty($post->featuredImage) && ($post->imageSource === 'none' || $post->imageSource === '') ? 'checked' : '' ?>>No Image
        </label>
        <label><input type="radio" name="image_source" value="remote" <?= $post->imageSource === 'remote' ? 'checked' : '' ?>>Image URL</label>
        <label><input type="radio" name="image_source" value="uploaded" <?= $post->imageSource === 'uploaded' ? 'checked' : '' ?>>Upload Image</label>
        <input type="url" name="remote_image" id="remote_image" value="<?= e($post->remoteImage) ?>">
        <input type="file" name="uploaded_image" id="uploaded_image" accept="image/jpeg,image/png,image/webp">
        <label>published at:</label>
        <input type="datetime-local" name="published_at" 
            value="<?= e((string) ($post->publishedAt ? new DateTimeImmutable($post->publishedAt)->format('Y-m-d\TH:i') : '')) ?>"><br>

        <button type="submit" name="action" value="save">save</button>
        <button type="submit" name="action" value="cancel">cancel</button>
        <?php if (!empty($post->id)): ?>
            <button type="submit" name="action" value="archive_current">archive</button>
        <?php endif; ?>
    </form>
    
    <?php foreach ($post->fetchRestNonArchived() ?? [] as $posts): ?>
        <p>id: <?= e((string) $posts['id']) ?></p>
        <p>category: <?= e((string) $posts['category_id']) ?></p>
        <p>title: <?= e((string) $posts['title']) ?></p>
        <p>slug: <?= e((string) $posts['slug']) ?></p>
        <p>excerpt: <?= e((string) $posts['excerpt']) ?></p>
        <p>content: <?= e((string) $posts['content']) ?></p>
        <p>featured image: <?= e((string) $posts['featured_image']) ?></p>
        <p>author: <?= e((string) $posts['author_id']) ?></p>
        <p>status: <?= e((string) $posts['status']) ?></p>
        <button onclick="window.location.href='?id=<?= e((string) $posts['id']) ?>'">go</button><br>
        <form method="post">
            <input type="hidden" name="<?= e(Token::SESSION_KEY) ?>" value="<?= e(Token::generate()) ?>">
            <input type="hidden" name="id" value="<?= e((string) $posts['id']) ?>">
            <button type="submit" name="action" value="archive">archive</button>    
        </form>
    <?php endforeach; ?>

    <hr>
    </p>This is the end.</p><br>

</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script src="/js/editor.js"></script>
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

    const remoteImage = document.getElementById('remote_image');
    const uploadedImage = document.getElementById('uploaded_image');

    function updateImageRequirements() {
        const selected = document.querySelector('input[name="image_source"]:checked')?.value;

        remoteImage.required = selected === 'remote';
        uploadedImage.required = selected === 'uploaded';
    }

    imageSourceRadios.forEach(radio => {
        radio.addEventListener('change', updateImageRequirements);
    });

    updateImageRequirements();
</script>
</body>
</html
