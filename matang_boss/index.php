<?php
require_once 'config/db.php';
requireLogin();
$current_page = 'dashboard';
$id_user = $_SESSION['id_user'];
$user_nama = $_SESSION['nama_lengkap'];

// Stats
$total_scan = $conn->query("SELECT COUNT(*) as c FROM scan_history WHERE id_user = $id_user")->fetch_assoc()['c'];
$total_penyakit = $conn->query("SELECT COUNT(*) as c FROM scan_history WHERE id_user = $id_user AND nama_penyakit IS NOT NULL")->fetch_assoc()['c'];
$total_ramp = $conn->query("SELECT COUNT(*) as c FROM ramp_directory WHERE status_approval = 'approved'")->fetch_assoc()['c'];

// Recent scans (last 5)
$recent = $conn->query("SELECT * FROM scan_history WHERE id_user = $id_user ORDER BY timestamp DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Matang Boss</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'components/header.php'; ?>

        <div class="page-content">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Selamat Datang, <?= htmlspecialchars(explode(' ', $user_nama)[0]) ?>! 👋</h1>
                    <p class="page-subtitle">Pantau aktivitas panen dan kelola kebun sawit Anda dengan AI.</p>
                </div>
                <a href="scan.php" class="btn btn-primary">
                    <i class="fas fa-camera"></i> Scan Sekarang
                </a>
            </div>

            <!-- Stats -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ECFDF5; color: #059669;">
                        <i class="fas fa-expand"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-animate" data-target="<?= $total_scan ?>"><?= $total_scan ?></h3>
                        <p>Total Scan</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #FFFBEB; color: #D97706;">
                        <i class="fas fa-virus"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-animate" data-target="<?= $total_penyakit ?>"><?= $total_penyakit ?></h3>
                        <p>Penyakit Terdeteksi</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #EFF6FF; color: #2563EB;">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-animate" data-target="<?= $total_ramp ?>"><?= $total_ramp ?></h3>
                        <p>RAMP Tersedia</p>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="dashboard-grid" style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px;">
                <!-- Feature CTA -->
                <div class="card hoverable">
                    <div class="card-title"><i class="fas fa-wand-magic-sparkles" style="color: var(--primary-light); margin-right: 8px;"></i> Mulai Scan Kelapa Sawit</div>
                    <p style="color: var(--text-secondary); line-height: 1.7; margin-bottom: 24px;">
                        Gunakan teknologi AI Computer Vision untuk mendeteksi tingkat kematangan buah kelapa sawit dan mendeteksi penyakit daun secara instan dengan tingkat akurasi tinggi.
                    </p>
                    <div style="background: var(--bg-subtle); border-radius: var(--radius-lg); padding: 40px 20px; text-align: center; border: 2px dashed var(--border);">
                        <i class="fas fa-camera" style="font-size: 3rem; color: var(--primary-lighter); margin-bottom: 16px; display: block;"></i>
                        <p style="color: var(--text-secondary); font-weight: 500; margin-bottom: 20px;">Arahkan kamera ke buah sawit untuk memulai analisis</p>
                        <a href="scan.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-camera"></i> Buka Kamera AI
                        </a>
                    </div>
                </div>

                <!-- Recent History -->
                <div class="card hoverable">
                    <div class="card-title"><i class="fas fa-clock-rotate-left" style="color: #D97706; margin-right: 8px;"></i> Riwayat Terakhir</div>
                    <?php if ($recent->num_rows > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php while ($row = $recent->fetch_assoc()): ?>
                        <div style="display: flex; align-items: center; gap: 14px; padding-bottom: 14px; border-bottom: 1px solid var(--border-light);">
                            <div style="width: 48px; height: 48px; border-radius: var(--radius-sm); background: var(--bg-body); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <?php if (!empty($row['file_gambar']) && file_exists($row['file_gambar'])): ?>
                                    <img src="<?= $row['file_gambar'] ?>" alt="" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                <?php else: ?>
                                    <i class="fas fa-image" style="color: var(--text-muted);"></i>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($row['hasil_kematangan']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($row['timestamp'])) ?></div>
                            </div>
                            <span class="badge <?= $row['hasil_kematangan'] === 'Matang' ? 'badge-success' : ($row['hasil_kematangan'] === 'Mentah' ? 'badge-warning' : 'badge-danger') ?>">
                                <?= $row['confidence_score'] ?>%
                            </span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: 40px 0; color: var(--text-muted);">
                        <i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 12px; display: block; color: var(--border);"></i>
                        <p>Belum ada riwayat scan.</p>
                    </div>
                    <?php endif; ?>
                    <a href="riwayat.php" style="display: block; text-align: center; margin-top: 16px; color: var(--primary-mid); font-weight: 600; font-size: 0.9rem;">
                        Lihat Semua Riwayat <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
