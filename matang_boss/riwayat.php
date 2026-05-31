<?php
require_once 'config/db.php';
requireLogin();
$current_page = 'riwayat';
$id_user = $_SESSION['id_user'];

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Total records
$stmt = $conn->prepare("SELECT COUNT(*) FROM scan_history WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$total_data = $stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total_data / $limit);
$stmt->close();

// Data
$stmt = $conn->prepare("SELECT * FROM scan_history WHERE id_user = ? ORDER BY timestamp DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $id_user, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Panen - Matang Boss</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'components/header.php'; ?>

        <div class="page-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Riwayat Panen</h1>
                    <p class="page-subtitle">Daftar rekaman pemindaian AI yang pernah Anda lakukan.</p>
                </div>
            </div>

            <?php if ($result->num_rows > 0): ?>
            <div class="riwayat-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card hoverable" style="padding: 0; overflow: hidden; margin-bottom: 0;">
                    <div style="height: 180px; background: var(--bg-subtle); position: relative;">
                        <?php if (!empty($row['file_gambar']) && file_exists($row['file_gambar'])): ?>
                            <img src="<?= $row['file_gambar'] ?>" alt="Foto Sawit" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image" style="font-size: 3rem; color: var(--border);"></i>
                            </div>
                        <?php endif; ?>
                        
                        <?php 
                        $badge_class = 'badge-success';
                        if ($row['hasil_kematangan'] === 'Mentah') $badge_class = 'badge-warning';
                        if ($row['hasil_kematangan'] === 'Lewat Matang') $badge_class = 'badge-danger';
                        ?>
                        <div style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); padding: 6px 12px; border-radius: var(--radius-full); color: white; font-weight: 600; font-size: 0.85rem; border: 1px solid rgba(255,255,255,0.2);">
                            <?= $row['confidence_score'] ?>% Akurasi
                        </div>
                    </div>
                    
                    <div style="padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <h3 style="font-size: 1.25rem; font-family: 'Inter', sans-serif;"><?= $row['hasil_kematangan'] ?></h3>
                            <span class="badge <?= $badge_class ?>"><i class="fas fa-circle" style="font-size: 0.5rem;"></i></span>
                        </div>

                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-calendar-alt"></i> <?= date('d M Y, H:i', strtotime($row['timestamp'])) ?>
                        </div>

                        <?php if ($row['nama_penyakit']): ?>
                        <div style="padding: 8px 12px; background: #FEF2F2; color: #DC2626; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 500; margin-bottom: 16px;">
                            <i class="fas fa-virus"></i> <?= $row['nama_penyakit'] ?>
                        </div>
                        <?php endif; ?>

                        <div style="border-top: 1px solid var(--border-light); padding-top: 16px;">
                            <?php if ($row['latitude'] && $row['longitude']): ?>
                            <button class="btn btn-ghost btn-block btn-sm" onclick="openMapModal(<?= $row['latitude'] ?>, <?= $row['longitude'] ?>)">
                                <i class="fas fa-map-marker-alt" style="color: #EF4444;"></i> Lihat Peta Lokasi
                            </button>
                            <?php else: ?>
                            <button class="btn btn-ghost btn-block btn-sm" disabled style="opacity: 0.5; cursor: not-allowed;">
                                <i class="fas fa-location-crosshairs"></i> Lokasi Tidak Tersedia
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="page-btn <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="card" style="text-align: center; padding: 60px 20px;">
                <div style="width: 80px; height: 80px; background: var(--bg-subtle); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: var(--text-muted); font-size: 2rem;">
                    <i class="fas fa-camera"></i>
                </div>
                <h3 style="margin-bottom: 8px;">Belum Ada Riwayat</h3>
                <p style="color: var(--text-secondary); margin-bottom: 24px;">Anda belum melakukan pemindaian buah sawit. Mulai pindai sekarang.</p>
                <a href="scan.php" class="btn btn-primary"><i class="fas fa-plus"></i> Scan Buah Baru</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Map Modal -->
    <div class="modal" id="mapModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-map-marker-alt" style="color: #EF4444; margin-right: 8px;"></i> Lokasi Panen</h3>
                <button class="modal-close" onclick="closeMapModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div id="map" style="height: 400px; width: 100%; z-index: 1;"></div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/map-modal.js"></script>
</body>
</html>
