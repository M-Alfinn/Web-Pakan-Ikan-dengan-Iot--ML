<?php
require_once 'config/auth.php';
requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol & Pengaturan - Monitoring Pakan Ikan</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="dashboard-page">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1>Kontrol & Pengaturan Sistem</h1>
            </div>
            
            <!-- Manual Servo Control -->
            <div class="card">
                <h3>
                    <i data-lucide="hand"></i>
                    Kontrol Servo Manual
                </h3>
                <p class="card-description">Beri makan ikan secara manual dengan jumlah bukaan servo yang dapat disesuaikan</p>
                
                <div class="control-buttons">
                    <button class="btn btn-large btn-primary" onclick="feedManual(1)">
                        <i data-lucide="play"></i>
                        <span>Beri Makan 1x</span>
                    </button>
                    <button class="btn btn-large btn-primary" onclick="feedManual(2)">
                        <i data-lucide="play"></i>
                        <span>Beri Makan 2x</span>
                    </button>
                    <button class="btn btn-large btn-primary" onclick="feedManual(3)">
                        <i data-lucide="play"></i>
                        <span>Beri Makan 3x</span>
                    </button>
                </div>
            </div>
            
            <!-- Mode Control -->
            <div class="cards-grid-2">
                <div class="card">
                    <h3>
                        <i data-lucide="zap"></i>
                        Mode Auto
                    </h3>
                    <p class="card-description">Mode otomatis dengan interval 1 detik (untuk testing)</p>
                    
                    <div class="toggle-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="mode-v7-toggle" onchange="toggleModeV7(this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label">
                            <span id="mode-v7-status">OFF</span>
                        </span>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle"></i>
                        <span>Mode ini akan memberi makan setiap 1 detik. Gunakan dengan hati-hati!</span>
                    </div>
                </div>
                
                <div class="card">
                    <h3>
                        <i data-lucide="brain"></i>
                        Mode ML (Machine Learning)
                    </h3>
                    <p class="card-description">Jadwal otomatis berdasarkan prediksi ML</p>
                    
                    <div class="toggle-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="mode-ml-toggle" onchange="toggleModeML(this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label">
                            <span id="mode-ml-status">OFF</span>
                        </span>
                    </div>
                    
                    <div class="alert alert-info">
                        <i data-lucide="info"></i>
                        <span>Jadwal akan disesuaikan berdasarkan output dari Python ML script</span>
                    </div>
                </div>
            </div>
            
            <!-- Manual Schedule Settings -->
            <div class="card">
                <h3>
                    <i data-lucide="calendar"></i>
                    Jadwal Manual
                </h3>
                <p class="card-description">Atur 4 jadwal pemberian pakan manual</p>
                
                <div class="schedule-grid">
                    <div class="schedule-item">
                        <div class="schedule-header">
                            <h4>Jadwal 1</h4>
                            <label class="toggle-switch small">
                                <input type="checkbox" id="schedule-1-active" onchange="updateSchedule(1)">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="time-inputs">
                            <input type="number" id="schedule-1-hour" min="0" max="23" placeholder="HH" onchange="updateSchedule(1)">
                            <span>:</span>
                            <input type="number" id="schedule-1-minute" min="0" max="59" placeholder="MM" onchange="updateSchedule(1)">
                        </div>
                    </div>
                    
                    <div class="schedule-item">
                        <div class="schedule-header">
                            <h4>Jadwal 2</h4>
                            <label class="toggle-switch small">
                                <input type="checkbox" id="schedule-2-active" onchange="updateSchedule(2)">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="time-inputs">
                            <input type="number" id="schedule-2-hour" min="0" max="23" placeholder="HH" onchange="updateSchedule(2)">
                            <span>:</span>
                            <input type="number" id="schedule-2-minute" min="0" max="59" placeholder="MM" onchange="updateSchedule(2)">
                        </div>
                    </div>
                    
                    <div class="schedule-item">
                        <div class="schedule-header">
                            <h4>Jadwal 3</h4>
                            <label class="toggle-switch small">
                                <input type="checkbox" id="schedule-3-active" onchange="updateSchedule(3)">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="time-inputs">
                            <input type="number" id="schedule-3-hour" min="0" max="23" placeholder="HH" onchange="updateSchedule(3)">
                            <span>:</span>
                            <input type="number" id="schedule-3-minute" min="0" max="59" placeholder="MM" onchange="updateSchedule(3)">
                        </div>
                    </div>
                    
                    <div class="schedule-item">
                        <div class="schedule-header">
                            <h4>Jadwal 4</h4>
                            <label class="toggle-switch small">
                                <input type="checkbox" id="schedule-4-active" onchange="updateSchedule(4)">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="time-inputs">
                            <input type="number" id="schedule-4-hour" min="0" max="23" placeholder="HH" onchange="updateSchedule(4)">
                            <span>:</span>
                            <input type="number" id="schedule-4-minute" min="0" max="59" placeholder="MM" onchange="updateSchedule(4)">
                        </div>
                    </div>
                </div>
                
                <button class="btn btn-primary" onclick="saveAllSchedules()">
                    <i data-lucide="save"></i>
                    <span>Simpan Semua Jadwal</span>
                </button>
            </div>
        </main>
    </div>
    
    <script src="/assets/js/kontrol.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
