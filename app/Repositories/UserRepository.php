<?php
declare(strict_types=1);

namespace Repo;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use PDO;

final class UserRepository {
    private PDO $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function findUserByUsername(string $username): array|bool {
        $query = 'SELECT * FROM users 
            WHERE username = ?
            LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
