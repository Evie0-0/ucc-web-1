<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Core\User;

$pageTitle = 'Academics';

User::requireAuthentication();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>UCC Admin | Academics</title>
	<link rel="icon" type="image/png" href="images/ucc-LOGO.png">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Libre+Baskerville:wght@700&display=swap" rel="stylesheet">
	<script src="https://unpkg.com/lucide@latest"></script>
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php require_once __DIR__ . '/template/sidebar.php' ?>
        <main class="main-content">
            <?php require_once __DIR__ . '/template/header.php' ?>
			<section class="content-area">
				<div class="heading-row">
					<div>
						<h2>Colleges</h2>
						<p>Manage the colleges and academic units of the University of Caloocan City.</p>
					</div>
					<div class="tagline">
						<span>A BRIGHTER TOMORROW TOGETHER</span>
						<div class="tag-line"></div>
					</div>
				</div>
			</section>
		</main>
	</div>
<script src="components/components.js"></script>
<script>
    async function initializeAcademics(){
        initializeComponents();
    }
    document.addEventListener("DOMContentLoaded",initializeAcademics);
</script>
</body>
</html>
