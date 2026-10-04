<?php
declare(strict_types=1);

namespace Models;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../Support/helper.php';

use Core\RemoteImage;
use Core\UploadedImage;
use Core\HtmlSanitizer;
use Core\Message;
use PDO;
use Exception;
use RuntimeException;
use PDOException;
use DateTimeImmutable;

final class PostManager {
    private PDO $conn;

    public int $id = 0;
    public string $title = '';
    public string $slug = '';
    public ?string $excerpt = '';
    public string $content = '';
    public int $categoryId = 0;
    public string $status = '';
    public string $imageSource = 'none';
    public ?string $remoteImage = '';
    public array $uploadedImage = [];
    public ?string $publishedAt = null;

    public ?string $featuredImage = '';
    
    public const STATUS_KEYS = ['published', 'draft', 'archived'];
    public const IMAGE_SOURCE_KEYS = ['current', 'none', 'remote', 'uploaded'];

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function save(): bool {
        if (strlen($this->title) < 5 || strlen($this->title) > 255 ) {
            Message::error('Title must be within 5 - 255 in length.');
            return false;
        } elseif (strlen($this->slug) < 5 || strlen($this->slug) > 255) {
            Message::error('Slug must be within 5 - 255 in length.');
            return false;
        } 
        
        if ($this->isAlreadyUsedSlug($this->slug)) {
            Message::error('Slug already used by other post.');
            return false;
        }

        if ($this->excerpt === '') {
            // Datase null
            $this->excerpt = null;
        }

        if (strlen(trim(strip_tags($this->content))) < 5) {
            Message::error('Content must be at least 5 in length.');
            return false;
        } 

        $categories = $this->fetchCategories();
        $categoryIds = array_column($categories, 'id');

        if (!in_array($this->categoryId, $categoryIds, true)) {
            Message::error('Invalid category selection.');
            return false;
        }
        
        if (!in_array($this->status, self::STATUS_KEYS, true)) {
            Message::error('Invalid status selection.');
            return false;
        } elseif (!in_array($this->imageSource, self::IMAGE_SOURCE_KEYS, true)) {
            Message::error('Invalid image source selection');
            return false;
        } 

        if ($this->imageSource === 'remote' && $this->remoteImage === '') {
            Message::error('Please provide an image URL.');
            return false;
        }

        if ($this->imageSource === 'uploaded' && empty($this->uploadedImage['name'])) {
            Message::error('Please select an image to upload.');
            return false;
        }

        if ($this->status === 'published') {
            if ($this->publishedAt === null || !$this->isValidPublishDatetime($this->publishedAt)) {
                Message::error('Publish date and time cannot be empty or be earlier than today');
                return false;
            }

            $this->publishedAt = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $this->publishedAt)->format('Y-m-d H:i:s');
        } else {
            // Null when no value, database null
            $this->publishedAt = null; 
        }

        if ($this->imageSource === 'current') {
            $query = 'SELECT featured_image, remote_image
                FROM posts
                WHERE id = ?';

            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row === false) {
                Message::error('Unable to find the existing post.');
                return false;
            }

            $this->featuredImage = $row['featured_image'];
            $this->remoteImage = $row['remote_image'];
        } elseif ($this->imageSource === 'none') {
            // Database null
            $this->featuredImage = null;
            $this->remoteImage = null;
        } else if ($this->imageSource === 'remote') {
            $tempFile = null;
            
            try {
                $remoteImageChecker = new RemoteImage();
                $tempFile = $remoteImageChecker->verify($this->remoteImage);
                
                if ($tempFile === false) {
                    Message::error('Invalid remote image. Please check the image and try again.');
                    return false;       
                }
            } catch (Exception $e) {
                Message::error('Unable to verify the remote image. Please try again.');
                return false;
            }

            try {
                $remoteImageChecker = new RemoteImage();
                $this->featuredImage = $remoteImageChecker->store(
                    $tempFile,
                    __DIR__ . '/../../storage/uploads'
                );
            } catch (Exception $e) {
                Message::error('Failed to download the remote image. Please try again or use a different image.');
                return false;
            }
        } else if ($this->imageSource === 'uploaded') {
            // Null when not selected
            $this->remoteImage = null;
            
            $tempFile = null;

            try {
                $uploadedImageChecker = new UploadedImage();
                $tempFile = $uploadedImageChecker->verify($this->uploadedImage);

                if ($tempFile === false) {
                    Message::error('Invalid uploaded image. Please check the image and try again.');
                    return false;
                }
            } catch (Exception $e) {
                Message::error('Unable to verify the uploaded image. Please try again.');
                return false;
            }

            try {
                $uploadedImageChecker = new UploadedImage();
                $this->featuredImage = $uploadedImageChecker->store(
                    $tempFile,
                    __DIR__ . '/../../storage/uploads/'
                );
            } catch (Exception $e) {
                Message::error('Failed to download the uploaded image. Please try again or use a different image.');
                return false;
            }
        }

        if ($this->id === 0) {
            $query = 'INSERT INTO posts (category_id, title, slug, excerpt, content, featured_image, author_id, status, published_at, remote_image) 
                VALUES (:category_id, :title, :slug, :excerpt, :content, :featured_image, :author_id, :status, :published_at, :remote_image)';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':category_id' => $this->categoryId,
                ':title' => $this->title,
                ':slug' => $this->slug,
                ':excerpt' => $this->excerpt,
                ':content' => $this->content,
                ':featured_image' => $this->featuredImage,
                ':author_id' => $_SESSION['user_id'],
                ':status' => $this->status,
                ':published_at' => $this->publishedAt,
                ':remote_image' => $this->remoteImage,
            ]);
            
            Message::success('News Post successfully saved.');
            return true;
        } else {
            $query = 'UPDATE posts SET category_id = :category_id, title = :title, slug = :slug, excerpt = :excerpt, content = :content,
                featured_image = :featured_image, status = :status, published_at = :published_at, remote_image = :remote_image
                WHERE id = :id';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':category_id' => $this->categoryId,
                ':title' => $this->title,
                ':slug' => $this->slug,
                ':excerpt' => $this->excerpt,
                ':content' => $this->content,
                ':featured_image' => $this->featuredImage,
                ':status' => $this->status,
                ':published_at' => $this->publishedAt,
                ':remote_image' => $this->remoteImage,
                ':id' => $this->id,
            ]);

            Message::success('Post successfully updated');
            return true;
        }
    }

    public function archive(int $id): void {
        if ($id === 0) {
            Message::error('Cannot archive post.');
            return;
        }

        $query = 'UPDATE posts SET status = ?, archived_at = CURRENT_TIMESTAMP
            WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['archived', $id]);

        Message::success('Post successfully archived.');

        return;   
    }

    // Fetches all non archived posts excluding the current post if it exists
    public function fetchRestNonArchived(): array {
        $query = 'SELECT * FROM posts
            WHERE status != ?
            AND id != ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['archived', $this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPost(int $id): ?array {
        $query = 'SELECT * FROM posts
            WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            if (($row['status']) !== 'archived') {
                return $row;
            } else {
                Message::error('Post is in the archive.');
                return null;
            } 
        }

        return null;
    }

    public function fetchCategories(): array {
        $query = 'SELECT id, name FROM news_categories';
        return $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function isValidPublishDatetime(string $datetime): bool {
        $datetimeObject = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $datetime);

        if ($datetimeObject === false) {
            return false;
        }

        $errors = DateTimeImmutable::getLastErrors();

        if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            return false;
        }

        $now = new DateTimeImmutable();

        return $datetimeObject >= $now;
    }

    private function isAlreadyUsedSlug(string $slug): bool {
        $query = 'SELECT 1 FROM posts
            WHERE slug = :slug
            AND id != :id';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':slug' => $slug,
            ':id' => $this->id,
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
}
