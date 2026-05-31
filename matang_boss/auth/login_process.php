<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../login.php');
}

$username = sanitize($conn, $_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Username dan password wajib diisi.';
    redirect('../login.php');
}

// Query user
$stmt = $conn->prepare("SELECT id_user, nama_lengkap, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['login_error'] = 'Username atau password salah.';
    redirect('../login.php');
}

$user = $result->fetch_assoc();
$stmt->close();

// Verify password
if (!password_verify($password, $user['password'])) {
    $_SESSION['login_error'] = 'Username atau password salah.';
    redirect('../login.php');
}

// Set session
$_SESSION['id_user']      = $user['id_user'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['username']     = $user['username'];
$_SESSION['email']        = $user['email'];
$_SESSION['role']         = $user['role'];

// Mark as logged in (clear any error)
unset($_SESSION['login_error']);

redirect('../index.php');
