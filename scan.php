<?php
require_once 'config/db.php';
requireLogin();
$current_page = 'scan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Buah AI - Matang Boss</title>
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
                    <h1 class="page-title">Scan Buah AI</h1>
                    <p class="page-subtitle">Deteksi kematangan dan penyakit secara instan dengan Computer Vision.</p>
                </div>
                <span class="badge badge-warning" id="gps-status" style="padding: 8px 16px; font-size: 0.88rem;">
                    <i class="fas fa-spinner fa-spin"></i> Mencari Lokasi...
                </span>
            </div>

            <div class="card" style="max-width: 800px; margin: 0 auto;">
                <!-- Camera Area -->
                <div class="scanner-area" id="camera-container">
                    <video id="camera-stream" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0; display: none;"></video>
                    <img id="uploaded-preview" style="width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0; display: none;" />
                    <canvas id="capture-canvas" style="display: none;"></canvas>

                    <div class="bounding-box" id="bounding-box">
                        <div class="corner tl"></div>
                        <div class="corner tr"></div>
                        <div class="corner bl"></div>
                        <div class="corner br"></div>
                        <div class="scan-line" id="scan-line"></div>
                    </div>

                    <div id="camera-fallback" style="text-align: center; color: white; z-index: 1;">
                        <i class="fas fa-video" style="font-size: 3rem; margin-bottom: 12px; color: rgba(255,255,255,0.3); display: block;"></i>
                        <p style="font-weight: 500; font-size: 1.05rem; color: rgba(255,255,255,0.6);">Hidupkan kamera atau upload foto</p>
                    </div>

                    <!-- Scanning Overlay -->
                    <div id="scan-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.7); display: none; align-items: center; justify-content: center; z-index: 10; flex-direction: column; backdrop-filter: blur(6px);">
                        <div style="font-size: 4.5rem; font-weight: 900; color: var(--primary-light); font-family: 'Outfit';" id="scan-percent">0%</div>
                        <div style="color: rgba(255,255,255,0.7); margin-top: 8px; font-weight: 500; font-size: 0.95rem; letter-spacing: 2px; text-transform: uppercase;">AI Sedang Memproses</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 12px; margin-top: 20px;">
                    <button id="btn-start-camera" class="btn btn-ghost" style="flex: 1;">
                        <i class="fas fa-power-off"></i> Hidupkan Kamera
                    </button>
                    <label for="upload-image" class="btn btn-outline" style="flex: 1; text-align: center; cursor: pointer;">
                        <i class="fas fa-upload"></i> Upload Foto
                    </label>
                    <input type="file" id="upload-image" accept="image/*" style="display: none;">
                </div>
                <div style="display: flex; gap: 12px; margin-top: 12px;">
                    <button id="btn-scan" class="btn btn-primary btn-block" disabled>
                        <i class="fas fa-expand"></i> Pindai Sekarang
                    </button>
                </div>
            </div>

            <!-- Result Card -->
            <div id="result-card" class="card" style="max-width: 800px; margin: 24px auto; display: none; border-left: 5px solid var(--primary-mid);">
                <h3 class="card-title" style="font-size: 1.35rem;">
                    <i class="fas fa-clipboard-check" style="color: var(--primary-mid); margin-right: 8px;"></i> Hasil Analisis AI
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div style="background: var(--bg-subtle); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                        <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 8px;">Tingkat Kematangan</div>
                        <div style="font-size: 1.6rem; font-weight: 800; font-family: 'Outfit';" id="res-kematangan">—</div>
                        <div class="mt-16"><span class="badge badge-success" id="res-confidence">—</span></div>
                    </div>
                    <div style="background: var(--bg-subtle); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                        <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 8px;">Deteksi Penyakit</div>
                        <div style="font-size: 1.2rem; font-weight: 700; margin-top: 8px;" id="res-penyakit">—</div>
                    </div>
                </div>
                <div style="padding: 20px; background: #FFFBEB; border-radius: var(--radius-md); border-left: 4px solid #F59E0B;">
                    <div style="font-weight: 700; font-size: 0.85rem; text-transform: uppercase; color: #92400E; margin-bottom: 8px;">
                        <i class="fas fa-lightbulb"></i> Rekomendasi Sistem Pakar
                    </div>
                    <p style="line-height: 1.7; color: var(--text-primary); font-weight: 500;" id="res-solusi">—</p>
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button class="btn btn-primary btn-sm" id="btn-simpan-hasil">
                        <i class="fas fa-save"></i> Simpan ke Riwayat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script src="assets/js/kamera.js"></script>
</body>
</html>
