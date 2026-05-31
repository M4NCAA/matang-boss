<?php
require_once '../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

requireLogin();

$id_user           = $_SESSION['id_user'];
$hasil_kematangan  = sanitize($conn, $_POST['hasil_kematangan'] ?? '');
$confidence_score  = floatval($_POST['confidence_score'] ?? 0);
$nama_penyakit     = sanitize($conn, $_POST['nama_penyakit'] ?? '');
$solusi_penyakit   = sanitize($conn, $_POST['solusi_penyakit'] ?? '');
$latitude          = floatval($_POST['latitude'] ?? 0);
$longitude         = floatval($_POST['longitude'] ?? 0);

// Handle image upload
$file_gambar = '';
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/scans/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
    $filename = 'scan_' . time() . '_' . uniqid() . '.' . $ext;
    $target = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
        $file_gambar = 'uploads/scans/' . $filename;
    }
} elseif (!empty($_POST['gambar_base64'])) {
    // Handle base64 image from camera
    $upload_dir = '../uploads/scans/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $data = $_POST['gambar_base64'];
    // Remove data URI prefix if present
    if (strpos($data, ',') !== false) {
        $data = explode(',', $data)[1];
    }
    $decoded = base64_decode($data);
    $filename = 'scan_' . time() . '_' . uniqid() . '.jpg';
    $target = $upload_dir . $filename;
    file_put_contents($target, $decoded);
    $file_gambar = 'uploads/scans/' . $filename;
}

if (empty($nama_penyakit) || $nama_penyakit === 'null') {
    $nama_penyakit = null;
    $solusi_penyakit = null;
}

$stmt = $conn->prepare("INSERT INTO scan_history (id_user, file_gambar, hasil_kematangan, confidence_score, nama_penyakit, solusi_penyakit, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issdssdd", $id_user, $file_gambar, $hasil_kematangan, $confidence_score, $nama_penyakit, $solusi_penyakit, $latitude, $longitude);

if ($stmt->execute()) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Data scan berhasil disimpan.',
        'id_scan' => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal menyimpan data: ' . $stmt->error
    ]);
}
$stmt->close();
