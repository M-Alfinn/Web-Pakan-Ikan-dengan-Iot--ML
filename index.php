<?php
require_once 'config/auth.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Monitoring Pakan Ikan Otomatis - IoT & ML</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="landing-page">
    <div class="container">
        <!-- Hero Section -->
        <header class="hero">
            <div class="hero-content">
                <div class="logo-section">
                    <div class="icon-wrapper">
                        <i data-lucide="fish" class="hero-icon"></i>
                    </div>
                    <h1 class="hero-title">Sistem Monitoring & Kontrol<br>Pakan Ikan Otomatis</h1>
                    <p class="hero-subtitle">Berbasis IoT & Machine Learning</p>
                </div>
                
                <p class="hero-description">
                    Solusi cerdas untuk optimasi pemberian pakan ikan dengan teknologi IoT dan prediksi Machine Learning
                </p>
                
                <div class="cta-buttons">
                    <a href="/login.php" class="btn btn-primary">
                        <i data-lucide="log-in"></i>
                        <span>Login Admin</span>
                    </a>
                    <a href="#features" class="btn btn-secondary">
                        <i data-lucide="info"></i>
                        <span>Pelajari Lebih Lanjut</span>
                    </a>
                </div>
            </div>
            
            <div class="hero-illustration">
                <img src="https://image2url.com/images/1764692367353-02d0f8c6-c5b6-4fbe-896f-fd2c71f4cf28.png"">
            </div>
        </header>
        
        <!-- Features Section -->
        <section id="features" class="features">
            <h2 class="section-title">Fitur Unggulan</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="thermometer"></i>
                    </div>
                    <h3>Monitoring Real-time</h3>
                    <p>Pantau suhu air dan kondisi sensor secara langsung dengan update otomatis setiap detik</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="brain"></i>
                    </div>
                    <h3>Prediksi Machine Learning</h3>
                    <p>Random Forest algorithm untuk prediksi jumlah pakan optimal berdasarkan kondisi ikan</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="cog"></i>
                    </div>
                    <h3>Kontrol Otomatis</h3>
                    <p>Servo feeder otomatis dengan jadwal cerdas dan mode manual untuk kontrol penuh</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="calendar-clock"></i>
                    </div>
                    <h3>Optimasi Jadwal</h3>
                    <p>Penjadwalan pemberian pakan yang dioptimalkan berdasarkan umur dan jenis ikan</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="line-chart"></i>
                    </div>
                    <h3>Visualisasi Data</h3>
                    <p>Grafik interaktif untuk tracking suhu, aktivitas feeding, dan performa sistem</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i data-lucide="scroll-text"></i>
                    </div>
                    <h3>Log Aktivitas Lengkap</h3>
                    <p>Rekam semua aktivitas sistem dengan detail waktu, sumber, dan kondisi sensor</p>
                </div>
            </div>
        </section>
        
        <!-- Tech Stack Section -->
        <section class="tech-stack">
            <h2 class="section-title">Teknologi yang Digunakan</h2>
            
            <div class="tech-grid">
                <div class="tech-item">
                    <i data-lucide="cpu"></i>
                    <span>ESP32</span>
                </div>
                <div class="tech-item">
                    <i data-lucide="wifi"></i>
                    <span>IoT</span>
                </div>
                <div class="tech-item">
                    <i data-lucide="brain"></i>
                    <span>ML Random Forest</span>
                </div>
                <div class="tech-item">
                    <i data-lucide="database"></i>
                    <span>MySQL</span>
                </div>
                <div class="tech-item">
                    <i data-lucide="code-2"></i>
                    <span>PHP & JavaScript</span>
                </div>
            </div>
        </section>
        
        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2025 Sistem Pakan Ikan Otomatis. Kelompok 5 IoT Project.</p>
        </footer>
    </div>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
