<?php
require_once '../config/db.php';
header('Content-Type: application/json');

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$action  = sanitize($conn, $_POST['action'] ?? '');
$id_ramp = intval($_POST['id_ramp'] ?? 0);
$catatan = sanitize($conn, $_POST['catatan'] ?? '');

if (!in_array($action, ['approve', 'reject', 'delete'])) {
    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid.']);
    exit;
}

if ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM ramp_directory WHERE id_ramp = ?");
    $stmt->bind_param("i", $id_ramp);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'RAMP berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus RAMP.']);
    }
    $stmt->close();
    exit;
}

$new_status = ($action === 'approve') ? 'approved' : 'rejected';

$stmt = $conn->prepare("UPDATE ramp_directory SET status_approval = ?, catatan_admin = ? WHERE id_ramp = ?");
$stmt->bind_param("ssi", $new_status, $catatan, $id_ramp);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'RAMP berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak') . '.',
        'new_status' => $new_status
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status.']);
}
$stmt->close();
