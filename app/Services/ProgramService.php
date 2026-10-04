<?php
declare(strict_types=1);

namespace Services;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../Support/helper.php';

use Repositories\ProgramRepository;
use Core\Message;
use Core\UrlImage;
use Core\UploadedImage;
use Core\Database;
use Core\HtmlSanitizer;
use Exception;

final class ProgramService {

    public function create(array $post, array $files): bool {
        $db = new Database();
        $repo = new ProgramRepository($db->connect());

        if ($repo->searchProgramName(trim($post['name'])) !== false) {
            Message::error('Program name already used.');
            return false;
        }

        if ($repo->searchSlug(sluggify(trim($post['slug']))) !== false) {
            Message::error('Slug already used by other program.');
            return false;
        } 

        $downloadedImage = null;

        if ($post['image_source'] === 'none') {
            $post['url_image'] = null;
        } elseif ($post['image_source'] === 'url') {
            $downloadedImage = $this->downloadUrlImage(trim($post['url_image']));

            if ($downloadedImage === false) {
                Message::error('Cannot save image.');
                return false;
            }
        } elseif ($post['image_source'] === 'uploaded') {
            $post['url_image'] = null;
            $downloadedImage = $this->downloadUploadedImage($files['uploaded_image']);

            if ($downloadedImage === false) {
                Message::error('Cannot save image.');
                return false;
            }
        }

        $htmlSanitizer = new HtmlSanitizer();

        $data = [
            'college' => $post['college'],
            'name' => $post['name'],
            'slug' => sluggify($post['slug']),
            'degree_level' => $post['degree_level'],
            'description' => $htmlSanitizer->sanitize($post['description']),
            'downloaded_image' => $downloadedImage,
            'author_id' => $_SESSION['user_id'], 
            'editor_id' => null, // When creating there is no editor first
            'status' => $post['status'],
            'url_image' => $post['url_image'],
        ];

        $repo->add($data);    
        Message::success('Program successfully saved.');
        
        return true;
    }

    public function edit(int $id, array $post, array $files): bool {
        $db = new Database();
        $repo = new ProgramRepository($db->connect());

        if ($repo->searchOtherProgramNames($id, trim($post['name'])) !== false) {
            Message::error('Program name already used.');
            return false;
        }

        if ($repo->searchOtherSlugs($id, sluggify(trim($post['slug']))) !== false) {
            Message::error('Slug already used by other program.');
            return false;
        } 

        $original = $_SESSION['form']['original'] ?? [];
        $downloadedImage = null;

        if ($post['image_source'] === 'current') {
            $downloadedImage = $original['downloaded_image'] ?? null;
            $post['url_image'] = $original['url_image'] ?? null;
        } elseif ($post['image_source'] === 'none') {
            $downloadedImage = null;
            $post['url_image'] = null;
        } elseif ($post['image_source'] === 'url') {
            $downloadedImage = $this->downloadUrlImage(trim($post['url_image']));

            if ($downloadedImage === false) {
                Message::error('Cannot save image.');
                return false;
            }
        } elseif ($post['image_source'] === 'uploaded') {
            $post['url_image'] = null;
            $downloadedImage = $this->downloadUploadedImage($files['uploaded_image']);

            if ($downloadedImage === false) {
                Message::error('Cannot save image.');
                return false;
            }
        }

        $htmlSanitizer = new HtmlSanitizer();

        $data = [
            'id' => $id,
            'college' => $post['college'],
            'name' => $post['name'],
            'slug' => sluggify($post['slug']),
            'degree_level' => $post['degree_level'],
            'description' => $htmlSanitizer->sanitize($post['description']),
            'downloaded_image' => $downloadedImage,
            'editor_id' => $_SESSION['user_id'],
            'status' => $post['status'],
            'url_image' => $post['url_image'],
        ];

        $repo->update($data);    
        Message::success('Program successfully saved.');
        
        return true;
    }

    private function downloadUrlImage(string $urlImage): string|false {
        $urlImageChecker = new UrlImage();
        
        try {
            $tempFile = $urlImageChecker->verify($urlImage);

            if ($tempFile === false) {
                Message::error('Invalid remote image. Please check the image and try again.');
                return false;
            } 
           
           return $urlImageChecker->store(
                $tempFile,
                __DIR__ . '/../../storage/uploads'
            );
        } catch (Exception $e) {
            Message::error('Failed to process the url image. Please try again or use a different image.');
            return false;
        }
    }

    private function downloadUploadedImage(array $uploadedImage): string|false {
        $uploadedImageChecker = new UploadedImage();
        
        try {
            $tempFile = $uploadedImageChecker->verify($uploadedImage);

            if ($tempFile === false) {
                Message::error('Invalid uploaded image. Please check the image and try again.');
                return false;
            } 
            
            return $uploadedImageChecker->store(
                $tempFile,
                __DIR__ . '/../../storage/uploads'
            );

        } catch (Exception $e) {
            Message::error('Failed to process the uploaded image. Please try again or use a different image.');
            return false;
        }
    }
}
