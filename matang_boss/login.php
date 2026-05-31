<?php session_start();
if (isset($_SESSION['id_user'])) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Matang Boss</title>
    <meta name="description" content="Login ke platform Matang Boss">
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
                    Platform cerdas untuk klasifikasi kematangan kelapa sawit dengan teknologi 
                    Computer Vision dan Sistem Pakar terintegrasi. Pantau hasil panen Anda secara real-time.
                </p>
            </div>
        </div>

        <!-- Right Panel (Form) -->
        <div class="auth-right">
            <div class="auth-form-container">
                <h2>Masuk ke Akun</h2>
                <p class="auth-desc">Silakan masukkan kredensial Anda untuk melanjutkan.</p>

                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="auth-alert error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['register_success'])): ?>
                    <div class="auth-alert success">
                        <i class="fas fa-check-circle"></i>
                        <?= $_SESSION['register_success']; unset($_SESSION['register_success']); ?>
                    </div>
                <?php endif; ?>

                <form action="auth/login_process.php" method="POST" autocomplete="off">
                    <div class="form-group">
                        <label class="form-label">Username atau Email</label>
                        <input type="text" class="form-input" name="username" placeholder="Masukkan username atau email" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="password-toggle-wrap">
                            <input type="password" class="form-input" name="password" id="password" placeholder="Masukkan password" required>
                            <button type="button" class="toggle-pw" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 8px;">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </button>
                </form>

                <div class="auth-footer">
                    Belum punya akun? <a href="register.php">Daftar Sekarang</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pw = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
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
