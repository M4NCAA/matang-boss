<?php
require_once 'config/db.php';
requireAdmin();
$current_page = 'persetujuan';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Total records
$total_data = $conn->query("SELECT COUNT(*) FROM ramp_directory")->fetch_row()[0];
$pending_data = $conn->query("SELECT COUNT(*) FROM ramp_directory WHERE status_approval = 'pending'")->fetch_row()[0];
$total_pages = ceil($total_data / $limit);

// Data
$sql = "SELECT r.*, u.nama_lengkap as pemohon 
        FROM ramp_directory r 
        JOIN users u ON r.id_user = u.id_user 
        ORDER BY CASE WHEN r.status_approval = 'pending' THEN 1 ELSE 2 END, r.created_at DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan RAMP - Admin Matang Boss</title>
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
                    <h1 class="page-title">Manajemen RAMP</h1>
                    <p class="page-subtitle">Verifikasi dan kelola daftar Pabrik Kelapa Sawit dari pengguna.</p>
                </div>
            </div>

            <div class="card" style="padding: 0; overflow: hidden;">
                <div class="data-table-wrap" style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Info RAMP</th>
                                <th>Pemohon (WA)</th>
                                <th>Harga Beli</th>
                                <th>Status</th>
                                <th style="text-align: right;">Aksi Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 0.85rem;"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <div class="fw-700" style="color: var(--text-primary); margin-bottom: 4px;"><?= htmlspecialchars($row['nama_ramp']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($row['alamat_lengkap']) ?>">
                                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($row['alamat_lengkap']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-600"><?= htmlspecialchars($row['pemohon']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fab fa-whatsapp text-success"></i> <?= htmlspecialchars($row['no_whatsapp']) ?></div>
                                    </td>
                                    <td class="fw-700" style="color: var(--primary);">Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if ($row['status_approval'] === 'approved'): ?>
                                            <span class="badge badge-success">Approved</span>
                                        <?php elseif ($row['status_approval'] === 'rejected'): ?>
                                            <span class="badge badge-danger">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; min-width: 140px;">
                                        <?php if ($row['status_approval'] === 'pending'): ?>
                                            <button class="btn btn-action btn-approve" style="padding: 6px 12px; font-size: 0.85rem; background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; border-radius: var(--radius-sm);" title="Setujui" onclick="processRamp(<?= $row['id_ramp'] ?>, 'approve', '<?= htmlspecialchars($row['nama_ramp']) ?>')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-action btn-reject" style="padding: 6px 12px; font-size: 0.85rem; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: var(--radius-sm);" title="Tolak" onclick="processRamp(<?= $row['id_ramp'] ?>, 'reject', '<?= htmlspecialchars($row['nama_ramp']) ?>')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-action btn-reject" style="padding: 6px 12px; font-size: 0.85rem; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: var(--radius-sm);" title="Hapus RAMP" onclick="deleteRamp(<?= $row['id_ramp'] ?>, '<?= htmlspecialchars($row['nama_ramp']) ?>')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center" style="padding: 40px;">
                                        <i class="fas fa-clipboard-check text-muted" style="font-size: 2rem; margin-bottom: 12px; display: block;"></i>
                                        <p class="text-secondary">Tidak ada antrean persetujuan.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        function processRamp(id, action, nama) {
            const isApprove = action === 'approve';
            
            Swal.fire({
                title: isApprove ? 'Setujui RAMP?' : 'Tolak RAMP?',
                html: `Anda akan ${isApprove ? 'menyetujui' : 'menolak'} pendaftaran <br><b>${nama}</b>.`,
                icon: isApprove ? 'question' : 'warning',
                input: 'text',
                inputPlaceholder: 'Opsional: Tambahkan catatan...',
                showCancelButton: true,
                confirmButtonColor: isApprove ? '#059669' : '#DC2626',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: isApprove ? 'Ya, Setujui' : 'Ya, Tolak',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: (catatan) => {
                    const fd = new FormData();
                    fd.append('action', action);
                    fd.append('id_ramp', id);
                    fd.append('catatan', catatan);

                    return fetch('api/admin_action.php', { method: 'POST', body: fd })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status !== 'success') throw new Error(data.message);
                            return data;
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Selesai!',
                        text: result.value.message,
                        icon: 'success'
                    }).then(() => location.reload());
                }
            });
        }
        
        function deleteRamp(id, nama) {
            Swal.fire({
                title: 'Hapus RAMP?',
                html: `Anda yakin ingin menghapus permanen <b>${nama}</b> dari sistem?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const fd = new FormData();
                    fd.append('action', 'delete');
                    fd.append('id_ramp', id);
                    return fetch('api/admin_action.php', { method: 'POST', body: fd })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status !== 'success') throw new Error(data.message);
                            return data;
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Terhapus!', result.value.message, 'success').then(() => location.reload());
                }
            });
        }
        
        // Update badge count in sidebar
        const pendingCount = <?= $pending_data ?>;
        document.getElementById('pending-count').innerText = pendingCount > 0 ? pendingCount : "";
    </script>
</body>
</html>
