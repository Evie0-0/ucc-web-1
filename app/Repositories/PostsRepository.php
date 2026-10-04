<?php
declare(strict_types=1);

namespace Repo;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use PDO;

final class PostsRepository {
    private PDO $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function fetchPostsCategories(): array { 
        $query = 'SELECT * FROM categories
            WHERE active != 0';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }   

    public function findSlug(string $slug): bool {
        $query = 'SELECT slug FROM posts
            WHERE slug = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row !== false;
    }

    public function findDiffSlug(string $slug, int $id): bool {
        $query = 'SELECT slug FROM posts
            WHERE slug = ? 
            AND id != ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$slug, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row !== false;
    }

    public function insert(array $data): void {
        $query = 'INSERT INTO posts (category_id, title, slug, excerpt, content, featured_image, author_id, editor_id, status, published_at)
            VALUES (:category_id, :title, :slug, :excerpt, :content, :featured_image, :author_id, :editor_id, :status, :published_at)';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':category_id' => $data['category_id'],
            ':title' => $data['title'],
            ':slug' => $data['slug'],
            ':excerpt' => $data['excerpt'],
            ':content' => $data['content'],
            ':featured_image' => $data['featured_image'],
            ':author_id' => $data['author_id'],
            ':editor_id' => $data['editor_id'],
            ':status' => $data['status'],
            ':published_at' => $data['published_at'],
        ]);

        return; 
    }

    public function update(array $data): void {
        $query = 'UPDATE posts SET category_id = :category_id, title = :title, slug = :slug, excerpt = :excerpt, content = :content, 
            featured_image = :featured_image, editor_id = :editor_id, status = :status, published_at = :published_at';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':category_id' => $data['category_id'],
            ':title' => $data['title'],
            ':slug' => $data['slug'],
            ':excerpt' => $data['excerpt'],
            ':content' => $data['content'],
            ':featured_image' => $data['featured_image'],
            ':editor_id' => $data['editor_id'],
            ':status' => $data['status'],
            ':published_at' => $data['published_at'],
        ]);

        return;
    }

    public function fetchFilteredPosts(string $search = '', string $filter = 'All'): array {
        $query = 'SELECT posts.*, categories.name AS category FROM posts
            JOIN categories ON posts.category_id = categories.id
            WHERE 1 = 1
            AND posts.active != 0';
        $params = [];

        if ($search !== '') {
            $query .= ' AND title LIKE :search';
            $params['search'] = "%{$search}%";
        }

        switch (strtolower($filter)) {
            case 'published':
                $query .= " AND status = 'published'
                    AND published_at <= CURRENT_TIMESTAMP";
                break;
            case 'pending': 
                $query .= " AND status = 'published'
                    AND published_at > CURRENT_TIMESTAMP";
                break;
            case 'draft': 
                $query .= " AND status = 'draft'
                    AND archived_at IS NULL";
                break;
            case 'archived':
                $query .= ' AND archived_at IS NOT NULL';
                break;
        }

        $query .= ' ORDER BY updated_at DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
