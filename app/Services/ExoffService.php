<?php
declare(strict_types=1);

namespace Serv;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../Support/helper.php';

use Repo\ExoffRepository;
use Core\UploadedImage;
use Core\Database;
use RuntimeException;

final class ExoffService {
    private array $statusKeys = ['active', 'hidden'];

    public function save() {
        $post = $_POST;
        $exoffId = (int) ($post['exoff_id'] ?? 0);
        $name = trim($post['name'] ?? '');
        $position = trim($post['position'] ?? '');
        $bio = trim($post['bio'] ?? '');
        $status = $post['status'] ?? '';

        if ($name === '') {
            jsonResponse(422, 'Name cannot be empty.');
        }

        if ($position === '') {
            jsonResponse(422, 'Position cannot be empty.');
        }

        if ($bio === '') {
            jsonResponse(422, 'Biography cannot be empty.');
        }

        if (!in_array($status, $this->statusKeys, true)) {
            jsonResponse(422, 'Invalid status.');
        }

        $removeImage = ($post['remove_image'] ?? '0') === '1';
        $filename = null;
        $repo = new ExoffRepository(Database::connect());

        if ($exoffId !== 0) {
            $existingExoff = $repo->fetchExoff($exoffId);

            if (!$existingExoff) {
                jsonResponse(400, 'Executive Official not found.');
            }

            // Use stored image from db
            $filename = $existingExoff['image'];

            // Remove image from db if remove is selected
            if ($removeImage) {
                $filename = null;
            }
        }

        // Won't run if filename is null (no value in $_FILES)
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $image = new UploadedImage();

                $tempFile = $image->verify($_FILES['image'] ?? []);

                if ($tempFile === false) {
                    jsonResponse(422, 'The uploaded image is invalid.');
                }

                $filename = $image->store(
                    $tempFile,
                    __DIR__ . '/../../public/admin/storage/uploads'
                );
            } catch (RuntimeException $e) {
                error_log($e->getMessage());

                jsonResponse(
                    500,
                    'An unexpected error occurred while processing the image.'
                );
            }
        }

        $data = [
            'exoff_id' => $exoffId,
            'name' => $name,
            'position' => $position,
            'bio' => $bio,
            'image' => $filename,
            'status' => $status,
            'author_id' => $_SESSION['user_id'],
            'editor_id' => $_SESSION['user_id'],
        ];

        if ($exoffId !== 0) {
            $repo->update($data);
        } else {
            $repo->insert($data);
        }

        jsonResponse(200, 'Executive Official saved successfully.');
    }

    public function filter(): void {
        $search = trim($_GET['search'] ?? '');
        $filter = $_GET['filter'] ?? 'all';

        $repo = new ExoffRepository(Database::connect());

        $exoffs = $repo->fetchFilteredExoffs($search, $filter,);

        ob_start();

        require __DIR__ . '/../../public/admin/template/exoff-table.php';

        $html = ob_get_clean();

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'html' => $html,
            'count' => count($exoffs)
        ]);

        exit;
    }

    public function view(): void {
        $exoffId = (int) ($_GET['exoff_id'] ?? 0);

        if ($exoffId <= 0) {
            jsonResponse(400, 'Invalid executive official ID.');
        }

        $repo = new ExoffRepository(Database::connect());
        $exoff = $repo->fetchExoff($exoffId);

        if (!$exoff) {
            jsonResponse(404, 'Executive Official not found.');
        }

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'id' => $exoff['id'],
            'name' => $exoff['name'],
            'position' => $exoff['position'],
            'bio' => $exoff['bio'],
            'image' => $exoff['image'],
            'status' => $exoff['status']
        ]);

        exit;
    }

    public function edit(): void {
        $exoffId = (int) ($_GET['exoff_id'] ?? 0);

        if ($exoffId <= 0) {
            jsonResponse(400, 'Invalid executive official ID.');
        }

        $repo = new ExoffRepository(Database::connect());
        $exoff = $repo->fetchExoff($exoffId);

        if (!$exoff) {
            jsonResponse(400, 'Executive Official not found.');
        }

        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'exoff' => $exoff
        ]);

        exit;
    }

    public function remove(): void {
        $exoffId = (int) ($_POST['exoff_id'] ?? 0);

        if ($exoffId === 0) {
            jsonResponse(400, 'Invalid executive official ID.');
        }

        $repo = new ExoffRepository(Database::connect());
        $repo->inactiveExoff($exoffId);

        jsonResponse(200, 'Executive Official successfully removed.');
    }
}
