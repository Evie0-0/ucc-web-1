<?php
declare(strict_types=1);

namespace Repositories;

require_once __DIR__ . '/../../bootstrap.php';

use PDO;

final class ProgramRepository {
    private PDO $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function add(array $data): void {
        $query = 'INSERT INTO programs (college, name, slug, degree_level, description, downloaded_image, author_id, editor_id, status, url_image)
            VALUES (:college, :name, :slug, :degree_level, :description, :downloaded_image, :author_id, :editor_id, :status, :url_image)';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':college' => trim($data['college']),
            ':name' => trim($data['name']),
            ':slug' => trim($data['slug']),
            ':degree_level' => $data['degree_level'],
            ':description' => trim($data['description'] ?? ''),
            ':downloaded_image' => $data['downloaded_image'] ?? '',
            ':author_id' => (int) $data['author_id'],
            ':editor_id' => $data['editor_id'] ?? null,
            ':status' => $data['status'],
            ':url_image' => trim($data['url_image'] ?? ''),
        ]);

        return;
    }

    public function update(array $data): void {
        $query = 'UPDATE programs SET college = :college, name = :name, slug = :slug, degree_level = :degree_level, description = :description, 
            downloaded_image = :downloaded_image, editor_id = :editor_id, status = :status, url_image = :url_image
            WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':id' => (int) $data['id'],
            ':college' => trim($data['college']),
            ':name' => trim($data['name']),
            ':slug' => trim($data['slug']),
            ':degree_level' => $data['degree_level'],
            ':description' => trim($data['description'] ?? ''),
            ':downloaded_image' => $data['downloaded_image'] ?? '',
            ':editor_id' => (int) $data['editor_id'],
            ':status' => $data['status'],
            ':url_image' => trim($data['url_image'] ?? ''),
        ]);

        return;
    }

    public function fetchPostAuthor(int $id): array|bool {
        $query = 'SELECT author_id FROM programs
            WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchOtherSlugs(int $id, string $slug): array|bool {
        $query = 'SELECT 1 FROM programs
            WHERE slug = ?
            AND id != ?
            LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$slug, $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchOtherProgramNames(int $id, string $name): array|bool {
        $query = 'SELECT 1 FROM programs
            WHERE name = ?
            AND id != ?
            LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$name, $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function fetchPost(int $id): array|bool {
        $query = 'SELECT * FROM programs 
            WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
           
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchPrograms(int $id, string $search, string $status): array {
        $query = 'SELECT * FROM programs 
            WHERE 1 = 1
            AND id != :id';
        $params = [':id' => $id];

        if ($search !== '') {
            $query .= ' AND (name LIKE :name_search OR college LIKE :college_search)';
            $search = "%{$search}%";
            $params[':name_search'] = $search;
            $params[':college_search'] = $search;
        }

        if ($status != 'all') {
            $query .= ' AND status = :status';
            $params['status'] = $status;    
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchProgramName(string $name): array|bool {
        $query = 'SELECT 1 FROM programs
            WHERE name = ?
            LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$name]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchSlug(string $slug): array|bool {
        $query = 'SELECT 1 FROM programs
            WHERE slug = ?
            LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$slug]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}


