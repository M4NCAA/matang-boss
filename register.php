<?php session_start();
if (isset($_SESSION['id_user'])) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Matang Boss</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: block;">

    <div class="auth-wrapper">
        <!-- Left Panel -->
        <div class="auth-left">
            <div class="auth-left-content">
                <div class="auth-logo">
                    <i class="fas fa-leaf"></i>
                </div>
                <h1>Matang Boss</h1>
                <p>
                    Bergabunglah dengan ribuan petani sawit cerdas. Dapatkan akses ke teknologi AI 
                    untuk memaksimalkan kualitas dan harga jual panen Anda.
                </p>
            </div>
        </div>

        <!-- Right Panel (Form) -->
        <div class="auth-right">
            <div class="auth-form-container">
                <h2>Buat Akun Baru</h2>
                <p class="auth-desc">Isi formulir di bawah untuk mendaftar.</p>

                <?php if (isset($_SESSION['register_error'])): ?>
                    <div class="auth-alert error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $_SESSION['register_error']; unset($_SESSION['register_error']); ?>
                    </div>
                <?php endif; ?>

                <form action="auth/register_process.php" method="POST" autocomplete="off">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-input" name="nama_lengkap" placeholder="Masukkan nama lengkap" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-input" name="username" placeholder="Pilih username unik" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" name="email" placeholder="contoh@email.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Peran / Role</label>
                        <select class="form-select" name="role">
                            <option value="petani">🌾 Petani Sawit</option>
                            <option value="pemilik_ramp">🏭 Pemilik RAMP / PKS</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="password-toggle-wrap">
                            <input type="password" class="form-input" name="password" id="password" placeholder="Minimal 8 karakter" required minlength="8">
                            <button type="button" class="toggle-pw" onclick="togglePassword('password','toggleIcon1')">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <div class="password-toggle-wrap">
                            <input type="password" class="form-input" name="confirm_password" id="confirm_password" placeholder="Ulangi password" required minlength="8">
                            <button type="button" class="toggle-pw" onclick="togglePassword('confirm_password','toggleIcon2')">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 8px;">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </button>
                </form>

                <div class="auth-footer">
                    Sudah punya akun? <a href="login.php">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const pw = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                pw.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>
