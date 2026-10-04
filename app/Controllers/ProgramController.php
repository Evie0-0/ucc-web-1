<?php
declare(strict_types=1);

namespace Controllers;

require_once __DIR__ . '/../../bootstrap.php';

use Services\ProgramService;
use Repositories\ProgramRepository;
use Core\Message;
use Core\Database;

final class ProgramController {
    // Reference
    private const array IMAGE_SOURCES = ['current', 'none', 'url', 'uploaded'];
    
    public const array STATUSES = ['Active', 'Inactive'];
    public const array DEGREE_LEVELS = ['Undergraduate'];

    public function __construct() {
        $_SESSION['form'] ??= [
            'id' => 0,
            'author_id' => 0,
            'editor_id' => null,
            'data' => [
                'college' => '',
                'name' => '',
                'slug' => '',
                'degree_level' => '',
                'description' => '',
                'image_source' => '',
                'url_image' => '',
                'status' => '',
            ],
            'original' => [
                'downloaded_image' => '',
                'url_image' => '',
            ],
        ];
        
        $_SESSION['filter'] ??= [
            'search' => '',
            'status' => 'all',
        ];
    }

    public function savePost(): bool {
        $post = $_POST;
        $files = $_FILES;

        if (trim(($post['college'] ?? '')) === '') {
            Message::error('College name cannot be empty.');
            return false;
        } elseif (trim(($post['name'] ?? '')) === '') {
            Message::error('Program name cannot be empty.');
            return false;
        } elseif (trim(($post['slug'] ?? '')) === '') {
            Message::error('Slug cannot be empty.');
            return false;
        } elseif (!in_array($post['degree_level'], self::DEGREE_LEVELS, true)) {
            Message::error('Invalid degree level.');
            return false;
        } elseif (!in_array($post['image_source'], self::IMAGE_SOURCES, true)) {
            Message::error('Invalid image source.');
            return false;
        }

        if ($post['image_source'] === 'url' && trim(($post['url_image'] ?? '')) === '') {
            Message::error('URL image cannot be empty.');
            return false;
        } elseif ($post['image_source'] === 'uploaded' && (($files['uploaded_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)) {
            Message::error('Uploaded image cannot be empty.');
            return false;
        } elseif (!in_array($post['status'], self::STATUSES, true)) {
            Message::error('Invalid status.');
            return false;
        }

        $service = new ProgramService();

        if ((int) $post['id'] === 0) {
            return $service->create($post, $files);
        } else {
            return $service->edit((int) $post['id'], $post, $files);
        }

        return false;
    }

    public function findPost(int $id): bool {
        $db = new Database();
        $repo = new ProgramRepository($db->connect());
        $fetchedPost = $repo->fetchPost($id);

        if ($fetchedPost === false) {
            Message::error('Could not find post.');
            return false;
        }

        $_SESSION['form']['id'] = (int) $fetchedPost['id'];
        $_SESSION['form']['author_id'] = (int) $fetchedPost['author_id'];
        $_SESSION['form']['editor_id'] = $fetchedPost['editor_id'] === null ? null : (int) $fetchedPost['editor_id'];
        $_SESSION['form']['data']['college'] = (string) $fetchedPost['college'];
        $_SESSION['form']['data']['name'] = (string) $fetchedPost['name'];
        $_SESSION['form']['data']['slug'] = (string) $fetchedPost['slug'];
        $_SESSION['form']['data']['degree_level'] = (string) $fetchedPost['degree_level'];
        $_SESSION['form']['data']['description'] = (string) ($fetchedPost['description'] ?? '');
        $_SESSION['form']['data']['url_image'] = (string) ($fetchedPost['url_image'] ?? '');
        $_SESSION['form']['data']['status'] = (string) $fetchedPost['status'];
        $_SESSION['form']['original']['downloaded_image'] = (string) ($fetchedPost['downloaded_image'] ?? '');
        $_SESSION['form']['original']['url_image'] = (string) ($fetchedPost['url_image'] ?? '');

        Message::success('Post successfully loaded.');
        
        return true;
    }

    // Save data from post method form (not including uploaded image)
    public function saveFormData(array $post) {
        $_SESSION['form']['id'] = (int) $post['id'];
        $_SESSION['form']['data']['college'] = (string) $post['college'];
        $_SESSION['form']['data']['name'] = (string) $post['name'];
        $_SESSION['form']['data']['slug'] = (string) $post['slug'];
        $_SESSION['form']['data']['degree_level'] = (string) $post['degree_level'];
        $_SESSION['form']['data']['description'] = (string) $post['description'];
        $_SESSION['form']['data']['image_source'] = (string) $post['image_source'];
        $_SESSION['form']['data']['url_image'] = (string) $post['url_image'];
        $_SESSION['form']['data']['status'] = (string) $post['status'];
    }
}
