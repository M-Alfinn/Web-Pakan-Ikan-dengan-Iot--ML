<?php
require_once 'config/auth.php';
require_once 'config/database.php';

requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Monitoring Pakan Ikan</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="dashboard-page">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1>Dashboard Real-time Monitoring</h1>
                <div class="header-actions">
                    <div class="status-indicator" id="esp32-status">
                        <div class="indicator-dot"></div>
                        <span>ESP32 Connecting...</span>
                    </div>
                    <span class="current-time" id="current-time"></span>
                </div>
            </div>
            
            <!-- Sensor Cards -->
            <div class="cards-grid">
                <div class="card sensor-card">
                    <div class="card-icon">
                        <i data-lucide="thermometer"></i>
                    </div>
                    <div class="card-content">
                        <h3>Suhu Air</h3>
                        <p class="value" id="suhu-air">--</p>
                        <span class="unit">°C</span>
                    </div>
                </div>
                
                <div class="card sensor-card">
                    <div class="card-icon">
                        <i data-lucide="fish"></i>
                    </div>
                    <div class="card-content">
                        <h3>Jenis Ikan</h3>
                        <p class="value small" id="jenis-ikan">--</p>
                    </div>
                </div>
                
                <div class="card sensor-card">
                    <div class="card-icon">
                        <i data-lucide="calendar"></i>
                    </div>
                    <div class="card-content">
                        <h3>Umur Ikan</h3>
                        <p class="value" id="umur-ikan">--</p>
                        <span class="unit">hari</span>
                    </div>
                </div>
                
                <div class="card sensor-card">
                    <div class="card-icon">
                        <i data-lucide="users"></i>
                    </div>
                    <div class="card-content">
                        <h3>Jumlah Ikan</h3>
                        <p class="value" id="jumlah-ikan">--</p>
                        <span class="unit">ekor</span>
                    </div>
                </div>
                
                <div class="card sensor-card">
                    <div class="card-icon">
                        <i data-lucide="package"></i>
                    </div>
                    <div class="card-content">
                        <h3>Pakan/Bukaan</h3>
                        <p class="value" id="pakan-per-bukaan">--</p>
                        <span class="unit">gram</span>
                    </div>
                </div>
                
                <div class="card sensor-card">
                    <div class="card-icon">
                        <i data-lucide="activity"></i>
                    </div>
                    <div class="card-content">
                        <h3>Status Servo</h3>
                        <p class="value small" id="status-servo">Standby</p>
                    </div>
                </div>
            </div>
            
            <!-- Mode Status Cards -->
            <div class="cards-grid-2">
                <div class="card mode-card">
                    <h3>
                        <i data-lucide="zap"></i>
                        Status Mode Sistem
                    </h3>
                    <div class="mode-status">
                        <div class="mode-item">
                            <span>Mode Auto</span>
                            <div class="status-badge" id="mode-v7-badge">OFF</div>
                        </div>
                        <div class="mode-item">
                            <span>Mode ML</span>
                            <div class="status-badge" id="mode-ml-badge">OFF</div>
                        </div>
                    </div>
                </div>
                
                <div class="card ml-card">
                    <h3>
                        <i data-lucide="brain"></i>
                        Output Machine Learning
                    </h3>
                    <div class="ml-output">
                        <div class="ml-item">
                            <span>Total Rekomendasi</span>
                            <strong id="ml-total">-- gram</strong>
                        </div>
                        <div class="ml-item">
                            <span>Frekuensi</span>
                            <strong id="ml-freq">-- kali</strong>
                        </div>
                        <div class="ml-item">
                            <span>Jadwal ML</span>
                            <strong id="ml-schedule">--</strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Next Feeding Widget -->
            <div class="card next-feeding-card">
                <h3>
                    <i data-lucide="clock"></i>
                    Next Feeding Schedule
                </h3>
                <div class="next-feeding-content">
                    <div class="feeding-time" id="next-feeding-time">--:--</div>
                    <div class="feeding-source" id="next-feeding-source">--</div>
                    <div class="feeding-amount" id="next-feeding-amount">-- gram</div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="card chart-card">
                    <h3>
                        <i data-lucide="trending-up"></i>
                        Suhu Air Real-time
                    </h3>
                    <canvas id="tempChart"></canvas>
                </div>
                
                <div class="card chart-card">
                    <h3>
                        <i data-lucide="bar-chart-3"></i>
                        Aktivitas Feeding Hari Ini
                    </h3>
                    <canvas id="feedingChart"></canvas>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="card activity-card">
                <h3>
                    <i data-lucide="activity"></i>
                    Aktivitas Terbaru
                </h3>
                <div class="activity-list" id="recent-activity">
                    <p class="no-data">Memuat aktivitas...</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tempCtx = document.getElementById('tempChart')?.getContext('2d');
            const feedCtx = document.getElementById('feedingChart')?.getContext('2d');
            let tempChart, feedChart;

            // Inisialisasi chart
            if (tempCtx) {
                tempChart = new Chart(tempCtx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Suhu Air (°C)',
                            data: [],
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: false, suggestedMin: 20, suggestedMax: 35 } }
                    }
                });
            }

            if (feedCtx) {
                feedChart = new Chart(feedCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Manual', 'Mode Auto', 'Mode ML', 'Web Manual'],
                        datasets: [{
                            label: 'Jumlah',
                            data: [0, 0, 0, 0],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 159, 64, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            // ✅ Update sensor cards — ambil Jenis Ikan, Umur, Jumlah, Pakan dari ml_predictions
            function updateSensorCards(sensor) {
                if (!sensor) return;
                document.getElementById('suhu-air').textContent = parseFloat(sensor.suhu_air).toFixed(1);
                document.getElementById('jenis-ikan').textContent = sensor.jenis_ikan || '--';
                document.getElementById('umur-ikan').textContent = sensor.umur_ikan || '--';
                document.getElementById('jumlah-ikan').textContent = sensor.jumlah_ikan || '--';
                document.getElementById('pakan-per-bukaan').textContent = parseFloat(sensor.pakan_per_bukaan).toFixed(1);
            }

            
            // Update system status & servo
            function updateSystemStatus(status) {
                if (!status) return;

                // ESP32 status
                const espStatus = document.getElementById('esp32-status');
                const dot = espStatus?.querySelector('.indicator-dot');
                const span = espStatus?.querySelector('span');
                if (dot && span) {
                    dot.style.backgroundColor = status.esp32_connected ? '#10B981' : '#EF4444';
                    span.textContent = status.esp32_connected ? 'ESP32 Connected' : 'ESP32 Offline';
                    span.style.color = status.esp32_connected ? '#10B981' : '#EF4444';
                }

                // Mode badges
                const v7Badge = document.getElementById('mode-v7-badge');
                const mlBadge = document.getElementById('mode-ml-badge');
                if (v7Badge) v7Badge.textContent = status.mode_auto_v7 ? 'ON' : 'OFF';
                if (mlBadge) mlBadge.textContent = status.mode_ml ? 'ON' : 'OFF';
                if (v7Badge) v7Badge.style.backgroundColor = status.mode_auto_v7 ? '#10B981' : '#94A3B8';
                if (mlBadge) mlBadge.style.backgroundColor = status.mode_ml ? '#3B82F6' : '#94A3B8';

            //     // ✅ Status Servo: Aktif jika ada aktivitas <5 menit lalu
            //     const servoEl = document.getElementById('status-servo');
            //     const recentFeed = document.querySelector('#recent-activity .activity-item');
            //     if (servoEl) {
            //         if (recentFeed) {
            //             const timeEl = recentFeed.querySelector('strong + div')?.textContent;
            //             if (timeEl && Date.now() - new Date(timeEl).getTime() < 5 * 60 * 1000) {
            //                 servoEl.textContent = 'Aktif';
            //                 servoEl.style.color = '#10B981';
            //                 return;
            //             }
            //         }
            //         servoEl.textContent = 'Standby';
            //         servoEl.style.color = '#64748B';
            //     }
            // }

                const servoEl = document.getElementById('status-servo');
                if (servoEl) {
                    const recentFeed = document.querySelector('#recent-activity .activity-item');
                    servoEl.textContent = recentFeed ? 'Aktif' : 'Standby';
                    servoEl.style.color = recentFeed ? '#10B981' : '#64748B';
                }
            }

            
            // Update ML cards
            function updateMLCards(ml) {
                if (!ml) {
                    document.getElementById("ml-total").textContent = "-- gram";
                    document.getElementById("ml-freq").textContent = "-- kali";
                    document.getElementById("ml-schedule").textContent = "--";
                    return;
                }
                document.getElementById("ml-total").textContent = 
                    ml.rekomendasi_pakan ? `${parseFloat(ml.rekomendasi_pakan).toFixed(1)} gram` : "-- gram";
                document.getElementById("ml-freq").textContent = 
                    ml.frekuensi_pakan ? `${ml.frekuensi_pakan} kali` : "-- kali";
                document.getElementById("ml-schedule").textContent = ml.waktu_pakan || "--";
            }

            // Update next feeding
            function updateNextFeeding(nextFeeding, ml, status) {
                const timeEl = document.getElementById('next-feeding-time');
                const sourceEl = document.getElementById('next-feeding-source');
                const amountEl = document.getElementById('next-feeding-amount');

                if (nextFeeding?.time) {
                    timeEl.textContent = nextFeeding.time;
                    sourceEl.textContent = nextFeeding.source || (status?.mode_ml ? 'Machine Learning' : 'Jadwal Manual');
                    const amount = ml?.rekomendasi_pakan && ml.frekuensi_pakan ? 
                        ml.rekomendasi_pakan / ml.frekuensi_pakan : 5.0;
                    amountEl.textContent = `${parseFloat(amount).toFixed(1)} gram`;
                } else {
                    timeEl.textContent = '--:--';
                    sourceEl.textContent = '--';
                    amountEl.textContent = '-- gram';
                }
            }

            // Update feeding chart
            function updateFeedingChart(todayFeeding) {
                if (!feedChart || !todayFeeding) return;
                feedChart.data.datasets[0].data = [
                    todayFeeding.manual_count || 0,
                    todayFeeding.v7_count || 0,
                    todayFeeding.ml_count || 0,
                    todayFeeding.jadwal_count || 0
                ];
                feedChart.update();
            }

            // ✅ Update Recent Activity — rapi + Lucide
            function updateRecentActivity(activities) {
                const container = document.getElementById('recent-activity');
                if (!container || !activities?.length) {
                    container.innerHTML = '<p class="no-data">Belum ada aktivitas</p>';
                    return;
                }

                container.innerHTML = activities.map(act => {
                    const time = new Date(act.waktu_eksekusi || Date.now());
                    const formattedTime = time.toLocaleString('id-ID', {
                        day: '2-digit',
                        month: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const source = act.sumber || '---';
                    const bukaan = act.bukaan_servo || 0;
                    const desc = source.includes('Manual') ? 
                        `${source} - ${bukaan}x bukaan` : `${source} - ${bukaan}x`;

                    return `
                        <div class="activity-item" style="padding: 12px 0; border-bottom: 1px solid var(--border);">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="background: var(--primary); width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                    <i data-lucide="activity"></i>
                                </div>
                                <div style="flex: 1;">
                                    <strong style="color: var(--text-primary);">${desc}</strong>
                                    <div style="color: var(--text-secondary); font-size: 0.85em; margin-top: 4px;">${formattedTime}</div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                setTimeout(() => lucide.createIcons(), 100);
            }

            // Load dashboard data
            async function loadDashboardData() {
                try {
                    const res = await fetch('/api/get-dashboard.php');
                    const result = await res.json();

                    if (result.status === 'success') {
                        const { sensor, system_status, ml, next_feeding, recent_activity, today_feeding } = result.data;

                        updateSensorCards(sensor);
                        updateSystemStatus(system_status);
                        updateMLCards(ml);
                        updateNextFeeding(next_feeding, ml, system_status);
                        updateFeedingChart(today_feeding);
                        updateRecentActivity(recent_activity);

                        // Update suhu chart
                        if (tempChart && sensor?.suhu_air) {
                            const now = new Date();
                            tempChart.data.labels.push(now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
                            tempChart.data.datasets[0].data.push(parseFloat(sensor.suhu_air));
                            if (tempChart.data.labels.length > 10) {
                                tempChart.data.labels.shift();
                                tempChart.data.datasets[0].data.shift();
                            }
                            tempChart.update();
                        }
                    }
                } catch (err) {
                    console.error('[DASHBOARD] Error:', err);
                }
            }

            setInterval(loadDashboardData, 10000);
            loadDashboardData();

            function updateTime() {
                document.getElementById('current-time').textContent = 
                    new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
            setInterval(updateTime, 1000);
            updateTime();

            setTimeout(() => lucide.createIcons(), 500);
        });
    </script>
</body>
</html>