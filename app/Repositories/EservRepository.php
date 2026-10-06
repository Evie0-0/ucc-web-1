<?php
declare(strict_types=1);

namespace Repo;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use PDO;

final class EservRepository {
    private PDO $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function fetchService(int $serviceId): array|bool {
        $query = 'SELECT * FROM eserv
            WHERE id = ?
            AND active = 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$serviceId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): void {
        $query = 'INSERT INTO eserv (name, category, description, url, logo, author_id, editor_id)
            VALUES (:name, :category, :description, :url, :logo, :author_id, :editor_id)';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':name' => $data['name'],
            ':category' => $data['category'],
            ':description' => $data['description'],
            ':url' => $data['url'],
            ':logo' => $data['logo'],
            ':author_id' => $data['author_id'],
            ':editor_id' => $data['editor_id'],
        ]);

        return;
    }


    public function update(array $data): void {
        $query = 'UPDATE eserv SET name = :name, category = :category, description = :description, url = :url, logo = :logo,
            editor_id = :editor_id
            WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':name' => $data['name'],
            ':category' => $data['category'],
            ':description' => $data['description'],
            ':url' => $data['url'],
            ':logo' => $data['logo'],
            ':editor_id' => $data['editor_id'],
            ':id' => $data['service_id'],
        ]);

        return;
    }

    public function fetchSearchedServices(string $search = '', int $editingId = 0): array {
        $query = 'SELECT * FROM eserv
            WHERE active = 1';
        $params = [];

        // Do not show the service currently being edited
        if ($editingId > 0) {
            $query .= ' AND id != :editing_id';
            $params['editing_id'] = $editingId;
        }

        // Search by service name
        if ($search !== '') {
            $query .= ' AND name LIKE :search';
            $params['search'] = "%{$search}%";
        }

        // Most recently updated first
        $query .= ' ORDER BY updated_at DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inactiveService(int $serviceId): void {
        $query = 'UPDATE eserv SET active = 0
            WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$serviceId]);
    }

}
