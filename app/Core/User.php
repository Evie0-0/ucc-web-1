<?php
declare(strict_types=1);

namespace Core;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Repo\UserRepository;
use Core\Database;

final class User {
    
    public function login(): void {
        $post = $_POST;
        $username = trim($post['username']);
        $password = trim($post['password']);

        if ($username === '') {
            jsonResponse(422, 'Usename cannot be empty.');
        } 
        if ($password === '') {
            jsonResponse(422, 'Password cannot be empty.');
        }

        $repo = new UserRepository(Database::connect());
        $foundUser = $repo->findUserByUsername($username);
        $fetchedPassword = '';

        if ($foundUser !== false) {
            $fetchedPassword = $foundUser['password'];
        } else {
            jsonResponse(422, 'User not found.');
        }

        if (!password_verify($password, $fetchedPassword)) {
            jsonResponse(401, 'Incorrect password.');
        } 

        session_regenerate_id(true);
        $_SESSION['user_id'] = $foundUser['id'];
        $_SESSION['username'] = $foundUser['username'];
        $_SESSION['role'] = $foundUser['role'];
        jsonResponse(200, 'Logging in.', ['redirect' => '/admin/index.php']); 
    }

    public static function requireAuthentication() {
        if (!isset($_SESSION['user_id'])) {
            redirectAdmin('login.php'); 
            exit('Authentication required.');
        }
    }
}
