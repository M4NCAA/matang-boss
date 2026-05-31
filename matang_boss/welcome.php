<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - Matang Boss</title>
    <meta name="description" content="Matang Boss - Sistem Cerdas Klasifikasi Kematangan Kelapa Sawit Berbasis AI">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: block;">

    <div class="welcome-screen" id="welcomeScreen">
        <!-- Floating Particles -->
        <div class="welcome-particles" id="particles"></div>

        <div class="welcome-content">
            <div class="welcome-icon">
                <i class="fas fa-leaf"></i>
            </div>
            <p class="welcome-greeting">👋 Selamat Datang di</p>
            <h1>Matang Boss</h1>
            <p class="welcome-subtitle">
                Sistem Informasi Cerdas Klasifikasi Kematangan<br>
                Kelapa Sawit Berbasis Computer Vision & AI
            </p>
            <button class="welcome-enter-btn" id="enterBtn">
                Masuk ke Aplikasi <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <script>
        // Generate floating particles
        const particlesContainer = document.getElementById('particles');
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.className = 'welcome-particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = (80 + Math.random() * 30) + '%';
            particle.style.animationDelay = Math.random() * 6 + 's';
            particle.style.animationDuration = (6 + Math.random() * 6) + 's';
            particle.style.width = particle.style.height = (4 + Math.random() * 6) + 'px';
            particlesContainer.appendChild(particle);
        }

        // Enter button -> redirect to login or dashboard
        document.getElementById('enterBtn').addEventListener('click', function() {
            const screen = document.getElementById('welcomeScreen');
            screen.classList.add('welcome-fadeout');
            setTimeout(function() {
                <?php if (isset($_SESSION['id_user'])): ?>
                    window.location.href = 'index.php';
                <?php else: ?>
                    window.location.href = 'login.php';
                <?php endif; ?>
            }, 500);
        });
    </script>
</body>
</html>
