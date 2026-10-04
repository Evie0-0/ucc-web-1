<header class="top-header">
	<div class="header-left">
        <h1 id="pageTitle"><?= e($pageTitle ?? '') ?></h1>
	</div>
	<div class="header-right">
		<button type="button" onclick="window.location.href='/'" class="visit-website">
			<i data-lucide="external-link"></i>
			<span>Visit Website</span>
		</button>
		<div class="admin-profile">
			<div class="admin-text">
                <small><?= e($_SESSION['role'] ?? '') ?></small>
                <span>Welcome, <?= e($_SESSION['username'] ?? '') ?>!</span>
			</div>
			<div class="avatar">
				<i data-lucide="circle-user-round"></i>
			</div>
		</div>
	</div>
	<div class="header-art"></div>
	<div class="gold-divider">
		<span></span>
	</div>
</header>
