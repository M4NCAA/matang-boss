<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// GET - Ambil daftar RAMP (approved), hitung jarak Haversine
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'list') {
    $user_lat = floatval($_GET['lat'] ?? 0);
    $user_lng = floatval($_GET['lng'] ?? 0);

    // Haversine formula dalam SQL (KM)
    $sql = "SELECT r.*, u.nama_lengkap as nama_pemilik,
            (6371 * ACOS(
                COS(RADIANS(?)) * COS(RADIANS(r.latitude)) * 
                COS(RADIANS(r.longitude) - RADIANS(?)) + 
                SIN(RADIANS(?)) * SIN(RADIANS(r.latitude))
            )) AS jarak_km
            FROM ramp_directory r
            JOIN users u ON r.id_user = u.id_user
            WHERE r.status_approval = 'approved'
            ORDER BY jarak_km ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddd", $user_lat, $user_lng, $user_lat);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['jarak_km'] = round($row['jarak_km'], 2);
        $data[] = $row;
    }
    $stmt->close();

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

// POST - Daftarkan RAMP baru
if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    requireLogin();

    $id_user   = $_SESSION['id_user'];
    $nama      = sanitize($conn, $_POST['nama_ramp'] ?? '');
    $alamat    = sanitize($conn, $_POST['alamat_lengkap'] ?? '');
    $lat       = floatval($_POST['latitude'] ?? 0);
    $lng       = floatval($_POST['longitude'] ?? 0);
    $wa        = sanitize($conn, $_POST['no_whatsapp'] ?? '');
    $harga     = intval($_POST['harga_jual'] ?? 0);

    if (empty($nama) || empty($alamat) || empty($wa) || $harga <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO ramp_directory (id_user, nama_ramp, alamat_lengkap, latitude, longitude, no_whatsapp, harga_jual) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issddsi", $id_user, $nama, $alamat, $lat, $lng, $wa, $harga);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Pendaftaran RAMP berhasil dikirim. Menunggu persetujuan admin.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mendaftarkan RAMP.']);
    }
    $stmt->close();
    exit;
}

// POST - Update harga (pemilik RAMP)
if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_harga') {
    requireLogin();

    $id_ramp = intval($_POST['id_ramp'] ?? 0);
    $harga   = intval($_POST['harga_jual'] ?? 0);
    $id_user = $_SESSION['id_user'];

    $stmt = $conn->prepare("UPDATE ramp_directory SET harga_jual = ? WHERE id_ramp = ? AND id_user = ? AND status_approval = 'approved'");
    $stmt->bind_param("iii", $harga, $id_ramp, $id_user);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Harga berhasil diperbarui.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui harga.']);
    }
    $stmt->close();
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Endpoint tidak ditemukan.']);
