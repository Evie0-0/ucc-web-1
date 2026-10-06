<?php
declare(strict_types=1);

namespace Serv;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../Support/helper.php';

use Repo\EservRepository;
use Core\UploadedImage;
use Core\Database;
use RuntimeException;

final class EservService {

    public function save() {
        $post = $_POST;
        $serviceId = (int) ($post['service_id'] ?? 0);
        $name = trim($post['name'] ?? '');
        $category = trim($post['category'] ?? '');
        $description = trim($post['description'] ?? '');
        $url = trim($post['url'] ?? '');

        if ($name === '') {
            jsonResponse(422, 'Service name cannot be empty.');
        }

        if ($category === '') {
            jsonResponse(422, 'Category cannot be empty.');
        }

        if ($description === '') {
            jsonResponse(422, 'Description cannot be empty.');
        }

        if ($url === '') {
            jsonResponse(422, 'URL cannot be empty.');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            jsonResponse(422, 'Invalid url.');
        }

        $removeImage = ($post['remove_image'] ?? '0') === '1';
        $filename = null;
        $repo = new EservRepository(Database::connect());

        if ($serviceId !== 0) {
            $existingService = $repo->fetchService($serviceId);
            
            if (!$existingService) {
                jsonResponse(400, 'E-Service not found.');
            } 

            // Use stored logo from db
            $filename = $existingService['logo'];

            // Remove logo from db if remove is selected
            if ($removeImage) {
                $filename = null;
            }
        }

        // Won't run if filename is null (no value in $_FILES)
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $image = new UploadedImage();

                $tempFile = $image->verify($_FILES['logo'] ?? []);

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
            'service_id' => $serviceId,
            'name' => $name,
            'category' => $category,
            'description' => $description,
            'url' => $url,
            'logo' => $filename,
            'author_id' => $_SESSION['user_id'],
            'editor_id' => $_SESSION['user_id'],
        ];

        if ($serviceId !== 0) {
            $repo->update($data); 
        } else {
            $repo->insert($data);
        }

        jsonResponse(200, 'E-Service saved successfully.');
    }

    public function search(): void {
        $search = trim($_GET['search'] ?? '');
        $editingId = (int) ($_GET['editing_id'] ?? 0);

        $repo = new EservRepository(Database::connect());
        $services = $repo->fetchSearchedServices($search, $editingId);

        ob_start();

        require __DIR__ . '/../../public/admin/template/eserv-table.php';

        $html = ob_get_clean();

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'html' => $html,
            'count' => count($services)
        ]);

        exit;
    }

    public function edit(): void {
        $serviceId = (int) ($_GET['service_id'] ?? 0);

        if ($serviceId === 0) {
            jsonResponse(400, 'Invalid service ID.');
        }

        $repo = new EservRepository(Database::connect());

        $service = $repo->fetchService($serviceId);

        if (!$service) {
            jsonResponse(404, 'E-Service not found.');
        }

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'service' => $service
        ]);

        exit;
    }

    public function remove(): void {
        $serviceId = (int) ($_POST['service_id'] ?? 0);

        if ($serviceId === 0) {
            jsonResponse(400, 'Invalid service ID.');
        }

        $repo = new EservRepository(Database::connect());
        $repo->inactiveService($serviceId);

        jsonResponse(200, 'E-Service successfully removed.');
    }
}
