<?php
// Ensure session is active
if (session_status() === PHP_SESSION_NONE) session_start();
$user_nama = $_SESSION['nama_lengkap'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'petani';
$user_initials = strtoupper(substr($user_nama, 0, 1));
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="fas fa-leaf"></i>
        </div>
        <span class="sidebar-brand-text">Matang Boss</span>
    </div>

    <div class="sidebar-menu">
        <div class="sidebar-section-title">Menu Utama</div>
        <ul>
            <li>
                <a href="index.php" class="<?= ($current_page == 'dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="scan.php" class="<?= ($current_page == 'scan') ? 'active' : '' ?>">
                    <i class="fas fa-camera"></i> Scan Buah AI
                </a>
            </li>
            <li>
                <a href="riwayat.php" class="<?= ($current_page == 'riwayat') ? 'active' : '' ?>">
                    <i class="fas fa-clock-rotate-left"></i> Riwayat Panen
                </a>
            </li>
        </ul>

        <div class="sidebar-section-title">Marketplace</div>
        <ul>
            <li>
                <a href="direktori.php" class="<?= ($current_page == 'direktori') ? 'active' : '' ?>">
                    <i class="fas fa-store"></i> Direktori RAMP
                </a>
            </li>
        </ul>

        <?php if ($user_role === 'admin'): ?>
        <div class="sidebar-section-title">Admin Panel</div>
        <ul>
            <li>
                <a href="persetujuan.php" class="<?= ($current_page == 'persetujuan') ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-check"></i> Persetujuan RAMP
                    <span class="menu-badge" id="pending-count"></span>
                </a>
            </li>
        </ul>
        <?php endif; ?>

        <div class="sidebar-section-title">Lainnya</div>
        <ul>
            <li>
                <a href="auth/logout.php">
                    <i class="fas fa-arrow-right-from-bracket"></i> Keluar
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-bottom">
        <div class="sidebar-user-card">
            <div class="sidebar-user-avatar"><?= $user_initials ?></div>
            <div class="sidebar-user-info">
                <div class="name"><?= htmlspecialchars($user_nama) ?></div>
                <div class="role"><?= ucfirst($user_role) ?></div>
            </div>
        </div>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
