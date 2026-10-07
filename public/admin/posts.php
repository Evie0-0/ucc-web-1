<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Serv\PostsService;
use Repo\PostsRepository;
use Core\User;
use Core\Database;
use Core\Token;

$pageTitle = 'Posts';

User::requireAuthentication();

$serv = new PostsService();
$repo = new PostsRepository(Database::connect());
$posts = $repo->fetchFilteredPosts('', 'All');

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
        case 'archive':
            $serv->archive();
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
    <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet"> 
    <link rel="stylesheet" href="/admin/assets/css/style.css">
    <link rel="stylesheet" href="/admin/assets/css/posts.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php require_once __DIR__ . '/template/sidebar.php' ?>
		<main class="main-content">
            <?php require_once __DIR__ . '/template/header.php' ?>
            <section class="posts-page">
                <div class="posts-toolbar">
                <div class="posts-tools-left">
                    <div class="posts-search">
                        <input type="search" id="postSearch" placeholder="Search posts..." aria-label="Search posts">
                        <i data-lucide="search"></i>
                    </div>

                    <select id="postFilter" class="posts-filter" aria-label="Filter posts">
                        <option value="All">All</option>
                        <option value="Published">Published</option>
                        <option value="Draft">Draft</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
                    <button class="post-btn post-btn-primary" id="addPostBtn" type="button">
                        <i data-lucide="plus"></i> Add New Post
                    </button>
                </div>

                <div class="posts-card">
                    <div class="posts-card-head">
                        <h2>Posts</h2>
                        <span class="posts-count" id="postsCount"><?= count($posts ?? []) ?> <?= count($posts ?? []) <= 1 ? 'post' : 'posts' ?></span>
                    </div>
                    <div class="posts-table-wrap">
                        <table class="posts-table" id="postsTable">
                            <thead>
                                <tr>
                                    <th>Featured Image</th><th>Title</th><th>Category</th>
                                    <th>Publish Date</th><th>Views</th><th>Status</th><th class="actions-column">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="postsTableBody">
                                <?php require __DIR__ . '/template/posts-table.php'; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="post-modal" id="postModal" aria-hidden="true">
                    <div class="post-modal-backdrop"></div>
                    <div class="post-modal-panel" role="dialog" aria-modal="true" aria-labelledby="postFormTitle">
                        <div class="post-modal-head">
                            <div>
                                <h2 id="postFormTitle">Add New Post</h2>
                                <p>Create or edit a post.</p>
                            </div>
                            <button class="modal-close" id="modalClose" type="button" aria-label="Close">
                                <i data-lucide="x"></i>
                            </button>
                        </div>

                        <form method="post" enctype="multipart/form-data" class="post-form" id="postForm">
                            <input type="hidden" name="csrf_key" value="<?= e(Token::generate()) ?>">
                            <input type="hidden" name="post_id" value="0" id="postId">
                            <input type="hidden" name="remove_image" value="0" id="removeImage">

                            <div class="image-upload-area">
                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <i data-lucide="image-plus"></i>
                                    <strong>Featured Image</strong>
                                    <span>Upload an image</span>
                                </div>
                                <img id="imagePreview" class="image-preview" alt="Featured image preview">
                                <input type="file" name="featured_image" id="featuredImage" accept="image/jpeg,image/png,image/webp" hidden>
                                <button type="button" class="post-btn post-btn-ghost" id="uploadImageBtn">
                                    <i data-lucide="upload"></i> Upload Image
                                </button>
                                <button type="button" id="removeImageBtn" hidden>Remove Image</button>
                            </div>

                            <div class="form-grid">
                                <label><span>Post Title</span><input type="text" name="title" id="postTitle" placeholder="Enter post title" required></label>
                                <label><span>Category</span>
                                    <select name="category_id" id="postCategory">
                                        <?php foreach (($repo->fetchPostsCategories() ?? []) as $category): ?>
                                            <option value="<?= e((string) $category['id'] ?? '') ?>"><?= e($category['name'] ?? '')?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label><span>Publish Date</span><input type="date" name="publish_date" id="postDate" required></label>
                                <label><span>Status</span>
                                    <select name="status" id="postStatus">
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                    </select>
                                </label>
                                <label>Slug</label>
                                <input type="text" name="slug" id="postSlug" required>
                                <label>Excerpt</label>
                                <input type="text" name="excerpt" id="postExcerpt">
                            </div>

                            <div>
                                <span>Content Editor</span>
                                <div id="editor"></div>
                                <textarea name="content" id="content" hidden></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="post-btn post-btn-ghost" id="cancelPostBtn">Cancel</button>
                                <div>
                                    <button type="submit" name="action" value="save" class="post-btn post-btn-primary">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="ui-toast" id="uiToast"></div>
            </section>

</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script> 
<script>
let formDirty = false;
</script>
<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script src="/admin/assets/js/editor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
lucide.createIcons();

document.querySelectorAll(".menu-title").forEach(button=>{
    button.addEventListener("click",()=>{
        button.parentElement.classList.toggle("open");
    });
});

// Opens modal
document.getElementById('addPostBtn').addEventListener('click', () => {
    document.getElementById('postModal').classList.add('open');
});

// Upload image and remove image
const uploadImageBtn = document.getElementById('uploadImageBtn');
const featuredImage = document.getElementById('featuredImage');
const imagePreview = document.getElementById('imagePreview');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const removeImageBtn = document.getElementById('removeImageBtn');
const removeImage = document.getElementById('removeImage');

function updateRemoveImageBtn() {
    removeImageBtn.hidden = !imagePreview.getAttribute('src');
}

updateRemoveImageBtn();

uploadImageBtn.addEventListener('click', () => {
    featuredImage.click();
});

featuredImage.addEventListener('change', () => {
    const file = featuredImage.files[0];

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
    featuredImage.value = '';
    imagePreview.removeAttribute('src');
    imagePreview.style.display = 'none';
    uploadPlaceholder.style.display = 'flex';
    document.getElementById('removeImage').value = '1';
    formDirty = true;

    updateRemoveImageBtn();
});

// Dirty checker
const postForm = document.getElementById('postForm');

postForm.addEventListener('input', () => {
    formDirty = true;
});

postForm.addEventListener('change', () => {
    formDirty = true;
});

// Filter & search
const postSearch = document.getElementById('postSearch');
const postFilter = document.getElementById('postFilter');
const applyPostFilter = document.getElementById('applyPostFilter');
const postsTableBody = document.getElementById('postsTableBody');
const postsCount = document.getElementById('postsCount');

async function loadPosts() {
    const params = new URLSearchParams({
        request: 'filter',
        search: postSearch.value.trim(),
        filter: postFilter.value,
    });

    try {
        const response = await fetch(`posts.php?${params}`);

        if (!response.ok) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load posts'
            });

            return
        }

        const result = await response.json();
        postsTableBody.innerHTML = result.html;
        postsCount.textContent = `${result.count} ${result.count === 1 ? 'post' : 'posts'}`;

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

//Filter
let searchTimeout;

postSearch.addEventListener('input', () => {
	clearTimeout(searchTimeout);

	searchTimeout = setTimeout(() => {
		loadPosts();
	}, 300);
});

postSearch.addEventListener('keydown', (event) => {
	if (event.key === 'Enter') {
		event.preventDefault();
		clearTimeout(searchTimeout);
		loadPosts();
	}
});

postFilter.addEventListener('change', () => {
	loadPosts();
});

// Close modal / discard editing
function closePostModal() {
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

document.getElementById('modalClose').addEventListener('click', closePostModal);
document.getElementById('cancelPostBtn').addEventListener('click', closePostModal);

// List actions
postsTableBody.addEventListener('click', async (event) => {
    // Check which button is clicked
    const editButton = event.target.closest('.edit-post-btn');
    const archiveButton = event.target.closest('.archive');
    const deleteButton = event.target.closest('.delete');

    if (archiveButton) {
        event.preventDefault();

        const result = await Swal.fire({
            title: 'Archive post?',
            text: 'This post will be moved to the archive.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it',
            cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) {
            return;
        }

        const form = archiveButton.closest('.post-action-form');
        const postId = form.querySelector('input[name="post_id"]').value;
        const csrfToken = form.querySelector('input[name="csrf_key"]').value; 
        
        try {
            const response = await fetch('posts.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'archive',
                    post_id: postId,
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

            loadPosts();
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

    if (deleteButton) {
        event.preventDefault();
        
        const result = await Swal.fire({
            title: 'Delete post?',
            text: 'This post will be removed and will no longer appear here.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) {
            return;
        }

        const form = deleteButton.closest('.post-action-form');
        const postId = form.querySelector('input[name="post_id"]').value;
        const csrfToken = form.querySelector('input[name="csrf_key"]').value;

        try {
            const response = await fetch('posts.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'remove',
                    post_id: postId,
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

            loadPosts();
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

    // Edit
    if (editButton) {
        // Get post to edit
        const postId = editButton.dataset.postId;

        try {
            const params = new URLSearchParams({
                request: 'edit',
                post_id: postId
            });

            const response = await fetch(`posts.php?${params}`);
            const result = await response.json();
            
            if (!response.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Cannot find post to edit.'
                });

                return;
            }

            const post = result.post;
            document.getElementById('postFormTitle').textContent = 'Edit Post';
            document.getElementById('postTitle').value = post.title;
            document.getElementById('postCategory').value = post.category_id;
            document.getElementById('postDate').value = post.published_at ? post.published_at.substring(0, 10) : '';
            document.getElementById('postStatus').value = post.status;
            document.getElementById('postSlug').value = post.slug;
            document.getElementById('postExcerpt').value = post.excerpt ?? '';
            quill.clipboard.dangerouslyPasteHTML(post.content ?? '');
            document.getElementById('postId').value = post.id;

            if (post.featured_image) {
                imagePreview.src = '/admin/storage/uploads/' + post.featured_image;
                imagePreview.style.display = 'block';
                uploadPlaceholder.style.display = 'none';
            } else {
                imagePreview.removeAttribute('src');
                imagePreview.style.display = 'none';
                uploadPlaceholder.style.display = 'flex';
            }

            document.getElementById('removeImage').value = '0';

            updateRemoveImageBtn();

            formDirty = false;
            document.getElementById('postModal').classList.add('open');

            loadPosts();
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

// Post form
postForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: new FormData(postForm, event.submitter)  
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
