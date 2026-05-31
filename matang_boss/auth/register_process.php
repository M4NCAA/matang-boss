<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../register.php');
}

$nama     = sanitize($conn, $_POST['nama_lengkap'] ?? '');
$username = sanitize($conn, $_POST['username'] ?? '');
$email    = sanitize($conn, $_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$role     = sanitize($conn, $_POST['role'] ?? 'petani');

// Validate
$errors = [];
if (empty($nama))     $errors[] = 'Nama lengkap wajib diisi.';
if (empty($username)) $errors[] = 'Username wajib diisi.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
if (strlen($password) < 8) $errors[] = 'Password minimal 8 karakter.';
if ($password !== $confirm) $errors[] = 'Konfirmasi password tidak cocok.';
if (!in_array($role, ['petani', 'pemilik_ramp'])) $role = 'petani';

if (!empty($errors)) {
    $_SESSION['register_error'] = implode('<br>', $errors);
    redirect('../register.php');
}

// Check duplicate username/email
$stmt = $conn->prepare("SELECT id_user FROM users WHERE username = ? OR email = ? LIMIT 1");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['register_error'] = 'Username atau email sudah terdaftar.';
    redirect('../register.php');
}
$stmt->close();

// Hash password and insert
$hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$stmt = $conn->prepare("INSERT INTO users (nama_lengkap, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nama, $username, $email, $hashed, $role);

if ($stmt->execute()) {
    $_SESSION['register_success'] = 'Pendaftaran berhasil! Silakan login.';
    redirect('../login.php');
} else {
    $_SESSION['register_error'] = 'Terjadi kesalahan. Silakan coba lagi.';
    redirect('../register.php');
}
$stmt->close();
