<?php
require_once '../config/db.php';
header('Content-Type: application/json');

requireLogin();

$id_user = $_SESSION['id_user'];
$page    = max(1, intval($_GET['page'] ?? 1));
$limit   = 10;
$offset  = ($page - 1) * $limit;

// Count total records
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM scan_history WHERE id_user = ?");
$count_stmt->bind_param("i", $id_user);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();

// Fetch paginated data
$stmt = $conn->prepare("SELECT id_scan, file_gambar, hasil_kematangan, confidence_score, nama_penyakit, solusi_penyakit, latitude, longitude, timestamp FROM scan_history WHERE id_user = ? ORDER BY timestamp DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $id_user, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$stmt->close();

echo json_encode([
    'status' => 'success',
    'data'   => $data,
    'pagination' => [
        'current_page' => $page,
        'per_page'     => $limit,
        'total_data'   => $total,
        'total_pages'  => ceil($total / $limit)
    ]
]);
