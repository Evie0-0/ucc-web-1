<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Core\User;
use Core\Token;

User::requireAuthentication();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Token::verify($_POST[Token::CSRF_KEY] ?? null)) {
        redirectPublic('404.php');
        exit('Invalid csrf token.');
    }

    $action = $_POST['action']; 
    
    switch ($action) {
        case 'dean_update':
            break;
        default:
            jsonResponse(400, 'Invalid action.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <p><?= $_SESSION['username'] ?? '' ?></p>
    <p><?= $_SESSION['role'] ?? '' ?></p>
    <hr>

    <!-- Dean -->
    <form id="dean-form" method="post">
        <input type="hidden" name="<?= e(Token::SESSION_KEY) ?>" value="<?= e(Token::generate()) ?>">

        <img alt="dean image">

        <label>name</label>
        <input type="text" name="name">
        
        <label>upload image</label>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
        
        <button type="submit" name="action" value="update_dean">Update</button>
    </form>


<script>

    

</script>
</body>
</html>
