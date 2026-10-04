<?php
declare(strict_types=1);

namespace Core;

require_once __DIR__ . '/../../bootstrap.php';

use Core\Message;
use PDO;

final class User {
    private PDO $conn;
    private const string TABLE_NAME = 'users';
    public const string SESSION_KEY = 'user_id';
    public const array ROLE_KEYS = ['editor', 'admin'];

    public string $username = '';
    public string $email = '';
    public string $password = ''; 
    public string $role = '';
    public int $active;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // REMINDER: add email related functionalities (checking...)
    public function create() : bool {
        $username = trim($this->username);
        $email = trim($this->email);
        $password = trim($this->password);

        if (!$this->isValidUsername($username)) {
            Message::error('Invalid username.');
            return false;   
        } 

        if (!$this->isValidPassword($password)) {
            Message:error('Invalid password.');
            return false;
        }

        try {
            $query = 'SELECT username FROM ' . self::TABLE_NAME . '
                WHERE LOWER(username) = :username 
                LIMIT 1';
            $stmt = $this->conn->prepare($query);
            $loweredUsername = strtolower($username);
            $stmt->bindParam(':username', $loweredUsername);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Username exists
                return false;
            }

            $query = 'INSERT INTO ' . self::TABLE_NAME . ' 
                (username, email, password, role)
                VALUES (:username, :email, :password, :role)';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $this->password = password_hash($password, PASSWORD_BCRYPT);
            $stmt->bindParam(':password', $this->password);

            // Insert user
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            throw new Exception('err creating user failed', 0, $e);
        }
    }
    
    public function login(): bool {
        try {
            $query = 'SELECT * FROM ' . self::TABLE_NAME . '
                WHERE username = ?
                AND active = 1
                LIMIT 1';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->username]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || !password_verify($this->password, $row['password'])) {
                Message::error('Incorrect username or password');
                return false;
            }
    
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            $this->active = $row['active'];

            return true;
        } catch (PDOException $e) {
            Message::error('Something unexpected happend while logging you in.');
            throw new Exception('Error: Loggin in user failed', 0, $e);
        }
    }

    public static function logout(): void {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'],$params['secure'],
                $params['httponly']);
        }

        session_destroy();
    }

    public function fetchUser(int $id): array|false {
        $query = 'SELECT * FROM users 
            WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // IMPORTANT change the name to isAuthenticated, update others connecting to it
    public static function isAuthenticated(): void {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            Message::error('Authentication required.');
            header('Location: /admin/login.php');
            exit;
        }
    }

    private function isValidUsername(string $username): bool {
        // Check if it follows the rule (allowed: a-z, A-Z, 0-9, space)
        return preg_match('/^[a-zA-Z0-9 ]+$/', $username) 
            && strlen($username) >= 4 
            && strlen($username) <= 30;
    }

    private function isValidPassword(string $password): bool {
        // Check if it follows the rule (allowed: a-z, A-Z, 0-9, !, @, #, $)
        return preg_match('/^[a-zA-Z0-9!@#$]+$/', $password)
            && strlen($password) >= 8 
            && strlen($password) <= 64;
    }

    private function isValidEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    private function isValidRole(string $role): bool {
        return in_array($role, self::ROLE_KEYS, true);
    }
}
