<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$academicsActive = in_array($currentPage, ['Academics.html', 'Masterals.html'], true);
$officialsActive = in_array($currentPage, ['exoff.php', 'bor.php'], true);
$componentsActive = in_array($currentPage, ['About-UCC.html', 'eserv.php'], true);
?>

<aside class="sidebar">
	<div class="sidebar-top">
		<div class="logo-wrap">
			<img src="/admin/assets/images/ucc-LOGO.png" alt="UCC Logo">
            <div>
                <h2>UCC <?= $_SESSION['role'] ?? '' ?></h2>
                <p>University of Caloocan City</p>
            </div>
        </div>
    </div>

    <div class="gold-line"></div>

<nav class="sidebar-nav">
    <a href="/admin/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
        <i data-lucide="house"></i>
        <span>Dashboard</span>
    </a>

    <a href="/admin/posts.php" class="<?= $currentPage === 'posts.php' ? 'active' : ''?>">
        <i data-lucide="newspaper"></i>
        <span>Posts</span>
    </a>

    <div class="menu-group">
        <button class="menu-title" type="button">
            <div>
                <i data-lucide="graduation-cap"></i>
                <span>Academics</span>
            </div>
            <i data-lucide="chevron-down" class="arrow"></i>
        </button>
        <div class="submenu">
            <a href="Academics.html">Colleges</a>
        </div>
    </div>

    <div class="menu-group">
        <button class="menu-title" type="button">
            <div>
                <i data-lucide="users"></i>
                <span>Officials</span>
            </div>
            <i data-lucide="chevron-down" class="arrow"></i>
        </button>
        <div class="submenu">
            <a href="/admin/exoff.php">Executive Officials</a>
            <a href="/admin/bor.php" >Board of Regents</a>
        </div>
    </div>

    <div class="menu-group">
        <button class="menu-title" type="button">
            <div>
                <i data-lucide="building-2"></i>
                <span>Components</span>
            </div>
            <i data-lucide="chevron-down" class="arrow"></i>
        </button>
        <div class="submenu">
            <a href="About-UCC.html">About UCC</a>
            <a href="/admin/eserv.php" class="<?= $currentPage === 'eserv.php' ? 'active' : '' ?>">E-Services</a> 
        </div>
    </div>
</nav>

<div class="sidebar-bottom">
    <a href="/admin/logout.php">
        <i data-lucide="log-out"></i>
        <span>Logout</span>
    </a>
</div>

			<div>
				<h2>UCC <?= e($_SESSION['role'] ?? '') ?></h2>
				<p>University of Caloocan City</p>
			</div>
		</div>
	</div>

	<div class="gold-line"></div>

	<nav class="sidebar-nav">

		<!-- Dashboard -->
		<a
        href="/admin/index.php"
			class="<?= $currentPage === 'index.php' ? 'active' : '' ?>" >
			<i data-lucide="house"></i>
			<span>Dashboard</span>
		</a>


		<!-- Posts -->
		<a href="/admin/posts.php"
			class="<?= $currentPage === 'posts.php' ? 'active' : '' ?>">
			<i data-lucide="newspaper"></i>
			<span>Posts</span>
		</a>


<!-- Academics -->
<div class="menu-group">
	<button class="menu-title <?= $academicsActive ? 'active' : '' ?>" type="button">
		<div>
			<i data-lucide="graduation-cap"></i>
			<span>Academics</span>
		</div>
		<i data-lucide="chevron-down" class="arrow"></i>
	</button>
	<div class="submenu">
		<!-- Colleges -->
		<a href="Academics.html"
			class="<?= $currentPage === 'Academics.html' ? 'active' : '' ?>">
			Colleges
		</a>
		<!-- Masterals -->
		<a href="Masterals.html"
			class="<?= $currentPage === 'Masterals.html' ? 'active' : '' ?>">
			Masterals
		</a>
	</div>
</div>

<!-- Officials -->
<div class="menu-group">
	<button class="menu-title <?= $officialsActive ? 'active' : '' ?>" type="button">
		<div>
			<i data-lucide="users"></i>
			<span>Officials</span>
		</div>
		<i data-lucide="chevron-down" class="arrow"></i>
	</button>
	<div class="submenu">
		<a href="/admin/exoff.php"
			class="<?= $currentPage === 'exoff.php' ? 'active' : '' ?>">
			Executive Officials
		</a>
		<a href="/admin/bor.php"
			class="<?= $currentPage === 'bor.php' ? 'active' : '' ?>">
			<span>Board of Regents</span>
		</a>
	</div>
</div>

<!-- Components -->
<div class="menu-group">
	<button class="menu-title <?= $componentsActive ? 'active' : '' ?>" type="button">
		<div>
			<i data-lucide="building-2"></i>
			<span>Components</span>
		</div>
		<i data-lucide="chevron-down" class="arrow"></i>
	</button>
	<div class="submenu">
		<a href="About-UCC.html"
			class="<?= $currentPage === 'About-UCC.html' ? 'active' : '' ?>">
			About UCC
		</a>
		<a href="/admin/eserv.php"
			class="<?= $currentPage === 'eserv.php' ? 'active' : '' ?>">
			E-Services
		</a>
	</div>
</div>

		<!-- Bottom Navigation -->
		<div class="sidebar-bottom">

			<div class="sidebar-bottom-divider"></div>

			<a href="Password.html"
				class="<?= $currentPage === 'Password.html' ? 'active' : '' ?>"
			>
				<i data-lucide="lock"></i>
				<span>Password</span>
			</a>

            <a href="#" onclick="openLogoutModal(event)">
                <i data-lucide="log-out"></i>
                <span>Logout</span>
            </a>
		</div>
	</nav>

	<div class="sidebar-art"></div>
</aside>

<!-- LOGOUT CONFIRMATION MODAL -->
<div id="logoutModal" class="logout-modal">
	<div class="logout-backdrop" onclick="closeLogoutModal()"></div>

	<div class="logout-box">
		<div class="logout-icon">
			<i data-lucide="log-out"></i>
		</div>

		<h2>Logout</h2>

		<p>Are you sure you want to logout?</p>

		<div class="logout-actions">
			<button type="button" class="logout-cancel" onclick="closeLogoutModal()">
				Cancel
			</button>

			<button type="button" class="logout-confirm" onclick="confirmLogout()">
				Yes, Logout
			</button>
		</div>
	</div>
</div>

<script>
	function openLogoutModal(event){
		event.preventDefault();
		document.getElementById('logoutModal').classList.add('show');
		document.body.classList.add('modal-open');
	}

	function closeLogoutModal(){
		document.getElementById('logoutModal').classList.remove('show');
		document.body.classList.remove('modal-open');
	}

	function confirmLogout(){
		window.location.href='/admin/logout.php';
	}
</script>
