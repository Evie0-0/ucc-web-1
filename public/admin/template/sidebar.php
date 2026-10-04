<?php 
$currentPage = basename($_SERVER['PHP_SELF']);
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
            <a href="Executive-Officials.html">Executive Officials</a>
            <a href="Board-of-Regents.html">Board of Regents</a>
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
            <a href="Campuses.html">Campuses</a>
            <a href="Contact-Information.html">Contact Information</a>
            <a href="Quick-Links.html">Quick Links</a>
        </div>
    </div>
</nav>

<div class="sidebar-bottom">
    <a href="Password.html">
        <i data-lucide="lock"></i>
        <span>Password</span>
    </a>

    <a href="Logout.html">
        <i data-lucide="log-out"></i>
        <span>Logout</span>
    </a>
</div>

    <div class="sidebar-art"></div>
</aside>
