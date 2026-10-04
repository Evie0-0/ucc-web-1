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
        $id = (int) ($post['post_id'] ?? 0);
        $title = trim($post['title'] ?? '');
        $categoryId = (int) ($post['category_id'] ?? 0);
        $publishDate = ($post['publish_date']) ?? '';
        $status = $post['status'] ?? '';
        $slug = trim($post['slug'] ?? '');
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

        if ($id !== 0) {
            if ($repo->findDiffSlug($slug)) {
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

        $filename = null;

        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $image = new UploadedImage();

                $tempFile = $image->verify($_FILES['featured_image'] ?? []);

                if ($tempFile === false) {
                    jsonResponse(422, 'The uploaded image is invalid.');
                }

                $filename = $image->store(
                    $tempFile,
                    __DIR__ . '/../../storage/uploads'
                ); 
            } catch (RuntimeException $e) {
                error_log($e->getMessage());
                jsonResponse(500, 'An unexpected error occured while processing the image.');
            }
        }

        $data = [
            'id' => $id,
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

        if ($id !== 0) {
            $repo->update($data); 
        } else {
            $repo->insert($data);
        }

        jsonResponse(200, 'Post saved successfully.');
    } 

    public function publish() {
        echo 'Publish this';
    }
}
