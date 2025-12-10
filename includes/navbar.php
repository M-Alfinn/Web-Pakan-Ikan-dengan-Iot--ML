<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar">
    <div class="navbar-brand">
        <i data-lucide="fish"></i>
        <span>Pakan Ikan IoT</span>
    </div>
    
    <div class="navbar-menu">
        <div class="user-menu">
            <div class="user-info">
                <i data-lucide="user"></i>
                <span><?php echo htmlspecialchars($user['nama_lengkap']); ?></span>
            </div>
            <a href="/logout.php" class="btn btn-small btn-secondary">
                <i data-lucide="log-out"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</nav>
