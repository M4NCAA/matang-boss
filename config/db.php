<?php
/**
 * Matang Boss - Database Configuration
 * Koneksi ke MySQL via XAMPP
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'matangboss_db');
define('DB_CHARSET', 'utf8mb4');

// Create MySQLi connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Set charset
$conn->set_charset(DB_CHARSET);

// Check connection
if ($conn->connect_error) {
    // In production, log this error - don't expose to user
    error_log("DB Connection Failed: " . $conn->connect_error);
    die(json_encode(['status' => 'error', 'message' => 'Koneksi database gagal. Pastikan XAMPP aktif.']));
}

// Helper: Sanitize input
function sanitize($conn, $input) {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($input))));
}

// Helper: Redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Session start jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth check helper
function requireLogin() {
    if (!isset($_SESSION['id_user'])) {
        redirect('../login.php');
    }
}

function requireAdmin() {
    if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
        redirect('../index.php');
    }
}
