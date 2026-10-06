<?php
declare(strict_types=1);

namespace Repo;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use PDO;

final class BORRepository {
    private PDO $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function fetchBor(int $borId): array|bool {
        $query = 'SELECT * FROM bor
            WHERE id = ?
            AND active = 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$borId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): void {
        $query = 'INSERT INTO bor (name, position, bio, image, status, author_id, editor_id)
            VALUES (:name, :position, :bio, :image, :status, :author_id, :editor_id)';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':name' => $data['name'],
            ':position' => $data['position'],
            ':bio' => $data['bio'],
            ':image' => $data['image'],
            ':status' => $data['status'],
            ':author_id' => $data['author_id'],
            ':editor_id' => $data['editor_id'],
        ]);

        return;
    }

    public function update(array $data): void {
        $query = 'UPDATE bor SET name = :name, position = :position, bio = :bio, image = :image, status = :status, editor_id = :editor_id
            WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':name' => $data['name'],
            ':position' => $data['position'],
            ':bio' => $data['bio'],
            ':image' => $data['image'],
            ':status' => $data['status'],
            ':editor_id' => $data['editor_id'],
            ':id' => $data['bor_id'],
        ]);

        return;
    }

    public function fetchFilteredBors(string $search = '', string $filter = 'all'): array {
        $query = 'SELECT * FROM bor
            WHERE active = 1';
        $params = [];

        if ($search !== '') {
            $query .= ' AND name LIKE :search';
            $params['search'] = "%{$search}%";
        }

        switch (strtolower($filter)) {
            case 'active':
                $query .= " AND status = 'active'";
                break;

            case 'hidden':
                $query .= " AND status = 'hidden'";
                break;
        }

        $query .= ' ORDER BY updated_at DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inactiveBor(int $borId): void {
        $query = 'UPDATE bor SET active = 0
            WHERE id = ?';

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$borId]);
    }
}
