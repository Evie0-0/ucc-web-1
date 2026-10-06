<?php
declare(strict_types=1);

namespace Serv;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../Support/helper.php';

use Repo\PostsRepository;
use Core\UploadedImage;
use Core\Database;
use Core\HtmlSanitizer;
use RuntimeException;
use DateTime;

final class PostsService {
    private array $statusKeys = ['draft', 'published'];

    public function save() {
        $post = $_POST;
        $postId = (int) ($post['post_id'] ?? 0);
        $title = trim($post['title'] ?? '');
        $categoryId = (int) ($post['category_id'] ?? 0);
        $publishDate = $post['publish_date'] ?? '';
        $status = $post['status'] ?? '';
        $slug = sluggify($post['slug'] ?? '');
        $excerpt = trim($post['excerpt'] ?? ''); 

        $htmlSanitizer = new HtmlSanitizer();
        $content = $htmlSanitizer->sanitize($post['content'] ?? '');

        if ($title === '') {
            jsonResponse(422, 'Title cannot be empty.');
        } 
        
        $repo = new PostsRepository(Database::connect());
        $categories = $repo->fetchPostsCategories();

        if (!in_array($categoryId, array_column($categories, 'id'), true)) {
            jsonResponse(422, 'Invalid category selection.');
        } 
        
        if ($publishDate !== '') {
            $date = DateTime::createFromFormat('Y-m-d', $publishDate);
            
            if ($date === false || $date->format('Y-m-d') !== $publishDate) {
                jsonResponse(422, 'Invalid Publish Date.');
            } 

            $publishDate .= ' 00:00:00';
        } 

        if (!in_array($status, $this->statusKeys, true)) {
            jsonResponse(422, 'Invalid status selection.');
        }
        
        if ($slug === '') {
            jsonResponse(422, 'Slug cannot be empty.');
        } 

        if ($postId !== 0) {
            if ($repo->findDiffSlug($slug, $postId)) {
                jsonResponse(422, 'Slug already used.');
            } 
        } else {
            if ($repo->findSlug($slug)) {
                jsonResponse(422, 'Slug already used.');
            } 
        } 
        
        if (trim(strip_tags($content)) === '') {
            jsonResponse(422, 'Content cannot be empty.');
        }

        $removeImage = ($post['remove_image'] ?? '0') === '1';
        $filename = null;

        if ($postId !== 0) {
            $existingPost= $repo->fetchPost($postId);
            
            if (!$existingPost) {
                jsonResponse(400, 'Post not found.');
            } 

            // Use stored featured image from db
            $filename = $existingPost['featured_image'];

            // Remove featured image from db if remove is selected
            if ($removeImage) {
                $filename = null;
            }
        }

        // Won't run if filename is null (no value in $_FILES)
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $image = new UploadedImage();

                $tempFile = $image->verify($_FILES['featured_image'] ?? []);

                if ($tempFile === false) {
                    jsonResponse(422, 'The uploaded image is invalid.');
                }

                $filename = $image->store(
                    $tempFile,
                    __DIR__ . '/../../public/admin/storage/uploads'
                ); 
            } catch (RuntimeException $e) {
                error_log($e->getMessage());
                jsonResponse(500, 'An unexpected error occurred while processing the image.');
            }
        }

        $data = [
            'post_id' => $postId,
            'category_id' => $categoryId,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'featured_image' => $filename,
            'author_id' => $_SESSION['user_id'],
            'editor_id' => $_SESSION['user_id'],
            'status' => $status,
            'published_at' => $publishDate,
        ];

        if ($postId !== 0) {
            $repo->update($data); 
        } else {
            $repo->insert($data);
        }

        jsonResponse(200, 'Post saved successfully.');
    } 

    public function filter() {
        $search = trim($_GET['search'] ?? '');
        $filter = $_GET['filter'] ?? 'All';
        $editingId = (int) ($_GET['editing_id'] ?? 0);

        $repo = new PostsRepository(Database::connect());
        $posts = $repo->fetchFilteredPosts($search, $filter, $editingId);

        ob_start();

        require __DIR__ . '/../../public/admin/template/posts-table.php';
        
        $html = ob_get_clean();

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'html' => $html,
            'count' => count($posts)
        ]);

        exit;
    }

    public function edit() {
        $postId = (int) ($_GET['post_id'] ?? 0);

        if ($postId <= 0) {
            jsonResponse(400, 'Invalid post ID.');
        } 

        $repo = new PostsRepository(Database::connect());
        $post = $repo->fetchPost($postId);

        if (!$post) {
            jsonResponse(400, 'Post not found.');
        }

        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            'post' => $post
        ]);
        
        exit;
    }

    public function archive() {
        $postId = (int) ($_POST['post_id'] ?? 0);

        if ($postId === 0) {
            jsonResponse(400, 'Invalid post ID.');
        }      

        $repo = new PostsRepository(Database::connect());
        $repo->archivePost($postId);

        jsonResponse(200, 'Post successfully archived.');
    }

    public function remove() {
        $postId = (int) ($_POST['post_id'] ?? 0);
        
        if ($postId === 0) {
            jsonResponse(400, 'Invalid post ID.');
        }

        $repo = new PostsRepository(Database::connect());
        $repo->inactivePost($postId);

        jsonResponse(200, 'Post successfully removed.');
    }
}
