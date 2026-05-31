<?php
$user_nama = $_SESSION['nama_lengkap'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'petani';
$user_initials = strtoupper(substr($user_nama, 0, 1));
?>
<header class="header">
    <button class="menu-toggle" id="menu-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="header-search">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Cari fitur, RAMP, riwayat...">
    </div>

    <div class="header-right">
        <button class="header-notif" title="Notifikasi">
            <i class="fas fa-bell"></i>
            <span class="notif-dot"></span>
        </button>

        <div class="user-profile-header">
            <div class="avatar"><?= $user_initials ?></div>
            <div class="user-header-info" style="text-align: left;">
                <div class="u-name"><?= htmlspecialchars($user_nama) ?></div>
                <div class="u-role"><?= ucfirst($user_role) ?></div>
            </div>
        </div>
    </div>
</header>
