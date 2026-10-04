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
    if (!Token::verify($_POST[Token::CSRF_KEY] ?? null)) {
        header('Location: /404.php');
        exit('Invalid csrf token.');
    }

    $serv->save();

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'edit') {

    }

    if (isset($_GET['request']) && $_GET['request'] === 'filter') {
        $search = trim($_GET['search'] ?? '');
        $filter = $_GET['filter'] ?? 'All';

        $posts = $repo->fetchFilteredPosts($search, $filter);

        require __DIR__ . '/template/posts-table.php';
        exit;
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
                            <option value="Archived">Archived</option>
                        </select>
                        <button type="button" id="applyPostFilter">Search</button>
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
                        <table class="posts-table">
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
                            <button class="modal-close" type="button" data-close-modal aria-label="Close">
                                <i data-lucide="x"></i>
                            </button>
                        </div>

                        <form method="post" class="post-form" id="postForm">
                            <input type="hidden" name="<?= e(Token::CSRF_KEY) ?>" value="<?= e(Token::generate()) ?>">
                            <input type="hidden" name="post_id" value="<?= e((string) ($_GET['id'] ?? 0))?>">

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
                                <button type="button" id="removeImageBtn">Remove</button>
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
                                <input type="text" name="slug" required>
                                <label>Excerpt</label>
                                <input type="text" name="excerpt">
                            </div>

                            <div>
                                <span>Content Editor</span>
                                <div id="editor"></div>
                                <textarea name="content" id="content" hidden></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="post-btn post-btn-ghost" id="cancelPostBtn" data-close-modal>Cancel</button>
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
<script>
 /*
document.addEventListener("DOMContentLoaded", () => {
    const posts = [
        {id:1,title:"University of Caloocan City News",category:"News",date:"2026-09-10",views:1240,status:"Published",image:"",content:"<p>Write your post content here...</p>"},
        {id:2,title:"Important University Announcement",category:"Announcement",date:"2026-09-08",views:865,status:"Published",image:"",content:"<p>Write your post content here...</p>"},
        {id:3,title:"Upcoming University Event",category:"Event",date:"2026-09-20",views:0,status:"Pending",image:"",content:"<p>Write your post content here...</p>"},
        {id:4,title:"Student Activities Update",category:"News",date:"2026-09-05",views:0,status:"Draft",image:"",content:"<p>Write your post content here...</p>"},
        {id:5,title:"Archived University Notice",category:"Announcement",date:"2026-08-28",views:0,status:"Archived",image:"",content:"<p>Write your post content here...</p>"}
    ];

    let editingId = null;
    const $ = id => document.getElementById(id);
    const tableBody=$("postsTableBody"), search=$("postSearch"), filter=$("postFilter");
    const modal=$("postModal"), form=$("postForm"), file=$("featuredImage"), preview=$("imagePreview");
    const placeholder=$("uploadPlaceholder"), toast=$("uiToast"), content=$("postContent");

    const escapeHtml = v => String(v).replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;");
    const formatDate = d => d ? new Date(d+"T00:00:00").toLocaleDateString("en-US",{year:"numeric",month:"short",day:"numeric"}) : "—";

    function render(){
        const q=search.value.trim().toLowerCase(), f=filter.value;
        const list=posts.filter(p=>(p.title.toLowerCase().includes(q)||p.category.toLowerCase().includes(q))&&(f==="All"||p.status===f));
        $("postsCount").textContent=`${list.length} ${list.length===1?"post":"posts"}`;
        tableBody.innerHTML=list.length ? list.map(p=>`
            <tr>
                <td><div class="featured-thumb">${p.image?`<img src="${p.image}" alt="">`:`<i data-lucide="image"></i>`}</div></td>
                <td class="post-title-cell">${escapeHtml(p.title)}</td>
                <td class="category-text">${escapeHtml(p.category)}</td>
                <td>${formatDate(p.date)}</td><td>${p.views.toLocaleString()}</td>
                <td><span class="status-badge status-${p.status.toLowerCase()}">${escapeHtml(p.status)}</span></td>
                <td><div class="action-buttons">
                    <button class="action-btn" data-action="edit" data-id="${p.id}" title="Edit"><i data-lucide="pencil"></i></button>
                    <button class="action-btn archive" data-action="archive" data-id="${p.id}" title="Archive"><i data-lucide="archive"></i></button>
                    <button class="action-btn delete" data-action="delete" data-id="${p.id}" title="Delete"><i data-lucide="trash-2"></i></button>
                </div></td>
            </tr>`).join("") : `<tr><td colspan="7" class="empty-row">No posts found.</td></tr>`;
        lucide.createIcons();
    }

    function openModal(p=null){
        editingId=p?.id||null;
        $("postFormTitle").textContent=p?"Edit Post":"Add New Post";
        $("postTitle").value=p?.title||"";$("postCategory").value=p?.category||"News";
        $("postDate").value=p?.date||"";$("postStatus").value=p?.status==="Archived"?"Draft":(p?.status||"Draft");
        content.innerHTML=p?.content||"<p>Write your post content here...</p>";
        if(p?.image){preview.src=p.image;preview.style.display="block";placeholder.style.display="none"}else{preview.removeAttribute("src");preview.style.display="none";placeholder.style.display="flex"}
        modal.classList.add("open");modal.setAttribute("aria-hidden","false");lucide.createIcons();
    }
    function closeModal(){modal.classList.remove("open");modal.setAttribute("aria-hidden","true");editingId=null;form.reset();content.innerHTML="<p>Write your post content here...</p>";preview.removeAttribute("src");preview.style.display="none";placeholder.style.display="flex"}
    function notify(msg){toast.textContent=msg;toast.classList.add("show");clearTimeout(notify.t);notify.t=setTimeout(()=>toast.classList.remove("show"),2200)}

    function save(status){
        const data={title:$("postTitle").value.trim()||"Untitled Post",category:$("postCategory").value,date:$("postDate").value,views:editingId?(posts.find(p=>p.id===editingId)?.views||0):0,status,content:content.innerHTML,image:preview.src||""};
        if(editingId){Object.assign(posts.find(p=>p.id===editingId),data);notify(status==="Published"?"Post updated and published.":"Draft updated.")}
        else{posts.unshift({id:Date.now(),...data});notify(status==="Published"?"Post published.":"Draft saved.")}
        render();closeModal();
    }

    $("addPostBtn").onclick=()=>openModal();
    search.oninput=render;filter.onchange=render;
    tableBody.onclick=e=>{
        const b=e.target.closest("[data-action]");if(!b)return;
        const p=posts.find(x=>x.id===Number(b.dataset.id));if(!p)return;
        if(b.dataset.action==="edit")openModal(p);
        if(b.dataset.action==="archive"){p.status=p.status==="Archived"?"Draft":"Archived";render();notify(p.status==="Archived"?"Post archived.":"Post moved back to Draft.")}
        if(b.dataset.action==="delete"&&confirm(`Delete "${p.title}"?`)){posts.splice(posts.indexOf(p),1);render();notify("Post deleted from the UI.")}
    };
    document.querySelectorAll("[data-close-modal]").forEach(b=>b.onclick=closeModal);
    $("saveDraftBtn").onclick=()=>save("Draft");
    form.onsubmit=e=>{e.preventDefault();save("Published")};
    $("uploadImageBtn").onclick=()=>file.click();
    file.onchange=()=>{const f=file.files[0];if(!f)return;const r=new FileReader();r.onload=e=>{preview.src=e.target.result;preview.style.display="block";placeholder.style.display="none"};r.readAsDataURL(f)};
    document.querySelectorAll(".editor-toolbar button").forEach(b=>b.onclick=()=>{content.focus();document.execCommand(b.dataset.command,false,null)});
    document.onkeydown=e=>{if(e.key==="Escape"&&modal.classList.contains("open"))closeModal()};
    render();
});
  */
</script>
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

// Upload image preview and remove image preview
const uploadImageBtn = document.getElementById('uploadImageBtn');
const featuredImage = document.getElementById('featuredImage');
const imagePreview = document.getElementById('imagePreview');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const removeImageBtn = document.getElementById('removeImageBtn');

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
});

removeImageBtn.addEventListener('click', () => {
    featuredImage.value = '';
    imagePreview.removeAttribute('src');
    imagePreview.style.display = 'none';
    uploadPlaceholder.style.display = 'flex';

    formDirty = true;
});

// Require publish date when status is published
const postStatus = document.getElementById('postStatus');
const postDate = document.getElementById('postDate');

postStatus.addEventListener('change', () => {
    formDirty = true;
    postDate.required = postStatus.value === 'published';
});

// X closes immediately
document.querySelector('.modal-close').addEventListener('click', () => {
    document.getElementById('postModal').classList.remove('open');
});

// Dirty checker
const postForm = document.getElementById('postForm');

postForm.addEventListener('input', () => {
    formDirty = true;
});

postForm.addEventListener('change', () => {
    formDirty = true;
});


// Cancel checks for unsaved changes
document.getElementById('cancelPostBtn').addEventListener('click', () => {

    if (!formDirty) {
        document.getElementById('postModal').classList.remove('open');
        return;
    }

    Swal.fire({
        title: 'Discard changes?',
        text: 'You have unsaved changes.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Discard',
        cancelButtonText: 'Keep editing'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.reload();            
        }
    });
});

// Filter & search
const postSearch = document.getElementById('postSearch');
const postFilter = document.getElementById('postFilter');
const postsTable = document.querySelector('.posts-table');

async function loadPosts() {
    const params = new URLSearchParams({
        request: 'filter',
        search: postSearch.value.trim(),
        filter: postFilter.value
    });

    try {
        const response = await fetch(`posts.php?${params}`);

        if (!response.ok) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load posts'
            });
        }

        const html = await response.text();
        const tableBody = document.getElementById('postsTableBody');
        tableBody.innerHTML = html;
        
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

applyPostFilter.addEventListener('click', loadPosts);



// AJAX
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
