<?php
declare(strict_types=1);

namespace Core;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use PDO;
use PDOException;
use Exception;

final class Database {
    public static function connect(): PDO {
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
        $name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'ucc';
        $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'mky';
        $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: 'admin';
        
        try {
            $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $name,
                $user,
                $password
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch (PDOException $e) {
            redirectPublic('404.php');
            throw new Exception('Database connection failed.', 0, $e);
        }
    }
}
