<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="sidebar">
    <nav class="sidebar-nav">
        <a href="/dashboard.php" class="nav-item <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="/log-aktivitas.php" class="nav-item <?php echo $current_page === 'log-aktivitas' ? 'active' : ''; ?>">
            <i data-lucide="scroll-text"></i>
            <span>Log Aktivitas</span>
        </a>
        
        <a href="/ml-output.php" class="nav-item <?php echo $current_page === 'ml-output' ? 'active' : ''; ?>">
            <i data-lucide="brain"></i>
            <span>ML Output</span>
        </a>
        
        <a href="/kontrol.php" class="nav-item <?php echo $current_page === 'kontrol' ? 'active' : ''; ?>">
            <i data-lucide="settings"></i>
            <span>Kontrol & Pengaturan</span>
        </a>
    </nav>
</aside>
