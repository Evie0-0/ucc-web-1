<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Core\User;
use Core\Database;

if (isset($_SESSION['user_id'])) {
    redirectAdmin('index.php');
    exit;
}

$user = new User(Database::connect());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user->login();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>UCC Admin | Login</title>
	<link rel="icon" type="image/png" href="/admin/assets/images/ucc-LOGO.png">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Libre+Baskerville:wght@700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<link rel="stylesheet" href="/admin/assets/css/style.css">
</head>

<body class="login-page">

<div class="login-grid">

	<div class="login-brand">

		<div class="brand-content">

			<img src="/admin/assets/images/ucc-LOGO.png" alt="UCC Logo">

			<div class="brand-line"></div>

			<h1>UCC Admin</h1>

			<p>University of Caloocan City</p>

			<div class="brand-divider"></div>

			<span>PUBLIC SERVICE THROUGH QUALITY EDUCATION</span>

		</div>

	</div>

	<div class="login-side">

		<div class="top-slogan">
			A BRIGHTER CALOOCAN
			<br>
			THROUGH HIGHER LEARNING
			<div class="slogan-line"></div>
		</div>

		<div class="login-card">

        <div class="login-header">
            <div class="login-top-icon">
                <i data-lucide="shield-check"></i>
            </div>

            <h2>Administrator Login</h2>
        </div>

        <p>Sign in to access the UCC Website Admin Panel.</p>

			<form method="post" id="login-form" class="login-form">

				<div class="input-group">

					<label>Username</label>

					<div class="input-box">
						<i data-lucide="user-round"></i>
						<input type="text" name="username" placeholder="Enter Username">
					</div>

				</div>

				<div class="input-group">

					<label>Password</label>

					<div class="input-box">
						<i data-lucide="lock"></i>
						<input type="password" name="password" placeholder="Enter Password">
					</div>

				</div>

				<button type="submit" class="login-btn">
					Login
					<i data-lucide="arrow-right"></i>
				</button>

			</form>

			<div class="login-footer">
				<div class="footer-line"></div>
				<span>Secure Access • UCC Administration System</span>
			</div>

		</div>

	</div>

</div>

<script>
lucide.createIcons();

// AJAX
const loginForm = document.querySelector('#login-form');
loginForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
        const response = await fetch(loginForm.action, {
            method: 'POST',
            body: new FormData(loginForm)  
        }); 

        const result = await response.json();

        if (!response.ok) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message
            }); 

            return;
        }

        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: result.message
        }).then(() => {
            if (result.data?.redirect) {
                window.location.href = result.data.redirect;
            }
        });
    } catch (error) {
        console.error(error);

        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Unable to communicate with the server.'
        });
    }
});
</script>
</body>
</html>
