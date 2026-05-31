<?php
require_once 'config/db.php';
requireLogin();
$current_page = 'direktori';
$role = $_SESSION['role'];

// Ambil data RAMP jika role pemilik_ramp untuk kelola harga
$my_ramps = [];
if ($role === 'pemilik_ramp') {
    $stmt = $conn->prepare("SELECT * FROM ramp_directory WHERE id_user = ?");
    $stmt->bind_param("i", $_SESSION['id_user']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $my_ramps[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direktori RAMP - Matang Boss</title>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <h1 class="page-title">Direktori RAMP</h1>
                    <p class="page-subtitle">Temukan Pabrik Kelapa Sawit (PKS) terdekat dengan harga terbaik.</p>
                </div>
                <div>
                    <button class="btn btn-outline" onclick="loadRampData()">
                        <i class="fas fa-sync-alt"></i> Refresh Data
                    </button>
                    <?php if ($role === 'pemilik_ramp' || $role === 'admin'): ?>
                    <button class="btn btn-primary" onclick="openRampModal()" style="margin-left: 8px;">
                        <i class="fas fa-plus"></i> Daftar RAMP Baru
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($role === 'pemilik_ramp' && !empty($my_ramps)): ?>
            <!-- Kelola RAMP Sendiri -->
            <div class="card">
                <h3 class="card-title"><i class="fas fa-store" style="color: var(--primary-mid);"></i> RAMP Milik Anda</h3>
                <div class="data-table-wrap" style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama RAMP</th>
                                <th>Status</th>
                                <th>Harga Jual (Rp/Kg)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_ramps as $mr): ?>
                            <tr>
                                <td class="fw-600"><?= htmlspecialchars($mr['nama_ramp']) ?></td>
                                <td>
                                    <?php if ($mr['status_approval'] === 'approved'): ?>
                                        <span class="badge badge-success">Approved</span>
                                    <?php elseif ($mr['status_approval'] === 'rejected'): ?>
                                        <span class="badge badge-danger">Rejected</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>Rp <?= number_format($mr['harga_jual'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if ($mr['status_approval'] === 'approved'): ?>
                                    <button class="btn btn-ghost btn-sm" onclick="editHarga(<?= $mr['id_ramp'] ?>, <?= $mr['harga_jual'] ?>)">
                                        <i class="fas fa-edit"></i> Update Harga
                                    </button>
                                    <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.85rem;">Menunggu verifikasi admin</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Daftar RAMP Umum -->
            <div id="ramp-loading" style="text-align: center; padding: 40px; color: var(--primary);">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-16">Mencari lokasi Anda & memuat data RAMP...</p>
            </div>

            <div id="ramp-container" style="display: none; display: flex; flex-direction: column; gap: 16px;"></div>

        </div>
    </div>

    <!-- Modal Daftar RAMP -->
    <div class="modal" id="modalRamp">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3>Pendaftaran RAMP Baru</h3>
                <button class="modal-close" onclick="closeRampModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="form-ramp" onsubmit="submitRamp(event)">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <label class="form-label">Nama RAMP / PKS</label>
                        <input type="text" class="form-input" name="nama_ramp" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea class="form-input" name="alamat_lengkap" rows="3" required></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Latitude</label>
                            <input type="text" class="form-input" name="latitude" id="input_lat" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Longitude</label>
                            <input type="text" class="form-input" name="longitude" id="input_lng" required>
                            <button type="button" class="btn btn-ghost btn-sm" style="margin-top: 8px; width: 100%;" onclick="getLocationInput()">
                                <i class="fas fa-location-crosshairs"></i> Gunakan Lokasi Saat Ini
                            </button>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">No. WhatsApp (Awali dgn 62)</label>
                            <input type="text" class="form-input" name="no_whatsapp" placeholder="6281234..." required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga Pembelian (Rp/Kg)</label>
                            <input type="number" class="form-input" name="harga_jual" required>
                        </div>
                    </div>

                    <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
                        <button type="button" class="btn btn-ghost" onclick="closeRampModal()">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-submit-ramp">Kirim Pendaftaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        let userLat = 0;
        let userLng = 0;

        document.addEventListener('DOMContentLoaded', () => {
            loadRampData();
        });

        function loadRampData() {
            document.getElementById('ramp-loading').style.display = 'block';
            document.getElementById('ramp-container').style.display = 'none';

            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        userLat = pos.coords.latitude;
                        userLng = pos.coords.longitude;
                        fetchData();
                    },
                    () => { fetchData(); } // proceed anyway
                );
            } else {
                fetchData();
            }
        }

        function fetchData() {
            fetch(`api/ramp_crud.php?action=list&lat=${userLat}&lng=${userLng}`)
                .then(res => res.json())
                .then(res => {
                    document.getElementById('ramp-loading').style.display = 'none';
                    const container = document.getElementById('ramp-container');
                    container.style.display = 'flex';
                    container.innerHTML = '';

                    if (res.data.length === 0) {
                        container.innerHTML = '<div class="card text-center"><p class="text-muted">Belum ada data RAMP yang tersedia.</p></div>';
                        return;
                    }

                    res.data.forEach(r => {
                        let distanceText = r.jarak_km > 0 ? `<i class="fas fa-location-arrow"></i> ${r.jarak_km} km dari lokasi Anda` : '';
                        
                        container.innerHTML += `
                            <div class="card hoverable ramp-card">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                        <h2 style="font-size: 1.3rem; font-family: 'Inter', sans-serif;">${r.nama_ramp}</h2>
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Terverifikasi</span>
                                    </div>
                                    <p style="color: var(--text-secondary); margin-bottom: 12px; font-size: 0.95rem;">
                                        <i class="fas fa-map-marker-alt text-muted" style="width: 20px;"></i> ${r.alamat_lengkap}
                                    </p>
                                    <div style="display: flex; gap: 16px; align-items: center; font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                                        <span><i class="fas fa-user-circle"></i> ${r.nama_pemilik}</span>
                                        <span style="color: var(--primary);">${distanceText}</span>
                                    </div>
                                    
                                    <div style="margin-top: 20px; display: flex; gap: 12px;">
                                        <a href="https://wa.me/${r.no_whatsapp}" target="_blank" class="btn btn-whatsapp btn-sm">
                                            <i class="fab fa-whatsapp"></i> Hubungi WA
                                        </a>
                                        <a href="https://www.google.com/maps/dir/?api=1&destination=${r.latitude},${r.longitude}" target="_blank" class="btn btn-outline btn-sm">
                                            <i class="fas fa-directions"></i> Rute Maps
                                        </a>
                                    </div>
                                </div>
                                <div style="text-align: right; border-left: 1px dashed var(--border); padding-left: 24px;">
                                    <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-weight: 600; margin-bottom: 8px;">Harga Pembelian</div>
                                    <div class="price-tag">
                                        <small>Rp</small> ${parseInt(r.harga_jual).toLocaleString('id-ID')}
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 8px;">Update: ${r.last_updated.split(' ')[0]}</div>
                                </div>
                            </div>
                        `;
                    });
                })
                .catch(err => {
                    document.getElementById('ramp-loading').style.display = 'none';
                    alert("Gagal memuat data.");
                });
        }

        // Modal Form Handling
        const modal = document.getElementById('modalRamp');
        function openRampModal() { modal.style.display = 'flex'; }
        function closeRampModal() { modal.style.display = 'none'; }
        
        function getLocationInput() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(pos => {
                    document.getElementById('input_lat').value = pos.coords.latitude;
                    document.getElementById('input_lng').value = pos.coords.longitude;
                });
            } else {
                alert("GPS tidak tersedia di browser Anda.");
            }
        }

        function submitRamp(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-ramp');
            const form = document.getElementById('form-ramp');
            btn.disabled = true;
            btn.innerHTML = 'Mengirim...';

            fetch('api/ramp_crud.php', {
                method: 'POST',
                body: new FormData(form)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        html: data.message + '<br><br><b>Silakan hubungi Admin untuk konfirmasi:</b><br><a href="https://wa.me/6281234567890?text=Halo%20Admin,%20saya%20baru%20saja%20mendaftarkan%20RAMP%20baru.%20Mohon%20verifikasinya." target="_blank" class="btn btn-whatsapp btn-sm mt-16" style="text-decoration:none;"><i class="fab fa-whatsapp"></i> Hubungi Admin di WA</a>',
                        icon: 'success',
                        showConfirmButton: false,
                        showCloseButton: true
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = 'Kirim Pendaftaran';
                }
            });
        }

        function editHarga(idRamp, currentHarga) {
            Swal.fire({
                title: 'Update Harga Jual (Rp/Kg)',
                input: 'number',
                inputValue: currentHarga,
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                showLoaderOnConfirm: true,
                preConfirm: (newHarga) => {
                    const fd = new FormData();
                    fd.append('action', 'update_harga');
                    fd.append('id_ramp', idRamp);
                    fd.append('harga_jual', newHarga);

                    return fetch('api/ramp_crud.php', { method: 'POST', body: fd })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status !== 'success') throw new Error(data.message);
                            return data;
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Berhasil', 'Harga telah diperbarui.', 'success').then(() => location.reload());
                }
            });
        }
    </script>
</body>
</html>
