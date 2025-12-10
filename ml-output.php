<?php
require_once 'config/auth.php';
requireLogin();
$user = getCurrentUser();

// Cek jika ada parameter success
$successMessage = isset($_GET['success']) ? $_GET['success'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ML Output - Monitoring Pakan Ikan</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest    "></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js    "></script>
</head>
<body class="dashboard-page">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php if ($successMessage): ?>
            <div class="alert alert-success" style="margin-bottom: 20px; animation: slideDown 0.5s ease;">
                <i data-lucide="check-circle"></i>
                <span><?= htmlspecialchars($successMessage) ?></span>
                <button onclick="this.parentElement.remove()" style="margin-left: auto; background: none; border: none; color: inherit; cursor: pointer;">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <?php endif; ?>
            
            <div class="content-header">
                <h1>Machine Learning Output</h1>
                <div class="header-actions">
                    <div class="status-badge">Random Forest Model</div>
                </div>
            </div>
            
            <!-- ML Input Section -->
            <div class="card">
                <h3>
                    <i data-lucide="edit-3"></i>
                    Input Data untuk Prediksi ML
                </h3>
                <form id="ml-input-form">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-size: 14px; color: var(--text-secondary); margin-bottom: 6px;">Jenis Ikan</label>
                            <select name="jenis_ikan" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 15px; background: var(--bg-card);" required>
                                <option value="">Pilih jenis ikan</option>
                                <option value="Nila" selected>Nila</option>
                                <option value="Lele">Lele</option>
                                <option value="Gurame">Gurame</option>
                                <option value="Bawal">Bawal</option>
                                <option value="Koi">Koi</option> <!-- ✅ Perbaiki duplikat -->
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 14px; color: var(--text-secondary); margin-bottom: 6px;">Umur Ikan (hari)</label>
                            <input type="number" name="umur_ikan" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 15px;" min="1" value="5" required> <!-- ✅ Default realistis -->
                        </div>
                        <div>
                            <label style="display: block; font-size: 14px; color: var(--text-secondary); margin-bottom: 6px;">Jumlah Ikan (ekor)</label>
                            <input type="number" name="jumlah_ikan" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 15px;" min="1" value="100" required> <!-- ✅ Default realistis -->
                        </div>
                        <div>
                            <label style="display: block; font-size: 14px; color: var(--text-secondary); margin-bottom: 6px;">Pakan per Bukaan (gram)</label>
                            <input type="number" name="pakan_per_bukaan" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 15px;" min="0.1" step="0.1" value="5.0" required> <!-- ✅ Default realistis -->
                        </div>
                        <div>
                            <label style="display: block; font-size: 14px; color: var(--text-secondary); margin-bottom: 6px;">Protein (%)</label>
                            <input type="number" name="protein_percent" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 15px;" min="0" max="100" step="0.1" value="30.0" required> <!-- ✅ Default realistis -->
                        </div>
                        <div>
                            <label style="display: block; font-size: 14px; color: var(--text-secondary); margin-bottom: 6px;">Lemak (%)</label>
                            <input type="number" name="lemak_percent" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 15px;" min="0" max="100" step="0.1" value="5.0" required> <!-- ✅ Default realistis -->
                        </div>
                        <div>
                            <label style="display: block; font-size: 14px; color: var(--text-secondary); margin-bottom: 6px;">Serat (%)</label>
                            <input type="number" name="serat_percent" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 15px;" min="0" max="100" step="0.1" value="3.0" required> <!-- ✅ Default realistis -->
                        </div>
                        <div>
                            <label style="display: block; font-size: 14px; color: var(--text-secondary); margin-bottom: 6px;">Suhu Air (°C)</label>
                            <input type="number" name="suhu_air" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 15px;" min="15" max="35" step="0.1" value="28.0" required> <!-- ✅ Default realistis -->
                        </div>
                    </div>
                    <div style="margin-top: 20px;">
                        <button type="submit" style="padding: 12px 24px; background: var(--primary); color: white; border: none; border-radius: var(--radius-sm); font-size: 16px; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                            <i data-lucide="brain" style="width: 20px; height: 20px; margin-right: 8px;"></i> Hitung Prediksi Pakan
                        </button>
                    </div>
                </form>
            </div>

            <!-- ML Output Section -->
            <div class="card ml-output-card">
                <h3>
                    <i data-lucide="brain"></i>
                    Output Prediksi Model
                </h3>
                <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px;">
                    <div style="flex: 1; min-width: 250px; background: var(--bg-secondary); border-radius: var(--radius-sm); padding: 20px; text-align: center;">
                        <div style="background: var(--primary); color: white; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-sm); margin: 0 auto 16px; font-size: 24px;">
                            <i data-lucide="package" style="width: 28px; height: 28px; color: white;"></i>
                        </div>
                        <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px;">Total Pakan Recommended</div>
                        <div class="value" id="ml-output-total" style="font-size: 32px; font-weight: 700; line-height: 1; color: var(--text-primary);">--</div>
                        <span style="font-size: 16px; color: var(--text-secondary);">gram per hari</span>
                    </div>
                    
                    <div style="flex: 1; min-width: 250px; background: var(--bg-secondary); border-radius: var(--radius-sm); padding: 20px; text-align: center;">
                        <div style="background: var(--primary); color: white; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-sm); margin: 0 auto 16px; font-size: 24px;">
                            <i data-lucide="repeat" style="width: 28px; height: 28px; color: white;"></i>
                        </div>
                        <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px;">Frekuensi Makan</div>
                        <div class="value" id="ml-output-freq" style="font-size: 32px; font-weight: 700; line-height: 1; color: var(--text-primary);">--</div>
                        <span style="font-size: 16px; color: var(--text-secondary);">kali per hari</span>
                    </div>
                    
                    <div style="flex: 1; min-width: 250px; background: var(--bg-secondary); border-radius: var(--radius-sm); padding: 20px; text-align: center;">
                        <div style="background: var(--primary); color: white; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-sm); margin: 0 auto 16px; font-size: 24px;">
                            <i data-lucide="cog" style="width: 28px; height: 28px; color: white;"></i>
                        </div>
                        <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 8px;">Bukaan per Jadwal</div>
                        <div class="value" id="ml-output-bukaan" style="font-size: 32px; font-weight: 700; line-height: 1; color: var(--text-primary);">--</div>
                        <span style="font-size: 16px; color: var(--text-secondary);">kali</span>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top:1px solid var(--border);">
                    <h4 style="display: flex; align-items: center; gap: 8px; font-size: 16px; margin-bottom: 16px;">
                        <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
                        Jadwal Otomatis dari ML
                    </h4>
                    <div class="schedule-timeline" id="ml-schedule-timeline" style="min-height: 80px; padding: 16px; background: var(--bg-secondary); border-radius: var(--radius-sm); margin-bottom: 16px;">
                        <p class="no-data" style="text-align: center; color: var(--text-muted); padding: 40px 0;">Belum ada jadwal</p>
                    </div>
                    
                    <button id="btn-simpan-ml" style="padding: 12px 24px; background: var(--success); color: white; border: none; border-radius: var(--radius-sm); font-size: 16px; font-weight: 500; cursor: pointer; transition: all 0.2s; display: none;">
                        <i data-lucide="save" style="width: 20px; height: 20px; margin-right: 8px;"></i> Simpan ke Jadwal ML
                    </button>
                </div>
            </div>
            
            <!-- Feature Importance Chart -->
            <div class="card">
                <h3>
                    <i data-lucide="bar-chart"></i>
                    Feature Importance
                </h3>
                <canvas id="featureChart" style="height: 200px;"></canvas>
            </div>
            
            <!-- ML History -->
            <div class="card">
                <h3>
                    <i data-lucide="history"></i>
                    Riwayat Prediksi ML
                </h3>
                <div class="table-container">
                    <table class="simple-table" id="ml-history-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Total Pakan</th>
                                <th>Frekuensi</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="no-data" style="text-align: center; color: var(--text-muted); padding: 40px;">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // ML OUTPUT JS — VERSI PERBAIKAN FINAL
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('ml-input-form');
            const totalEl = document.getElementById('ml-output-total');
            const freqEl = document.getElementById('ml-output-freq');
            const bukaanEl = document.getElementById('ml-output-bukaan');
            const scheduleEl = document.getElementById('ml-schedule-timeline');
            const btnSimpan = document.getElementById('btn-simpan-ml');
            const featureCtx = document.getElementById('featureChart')?.getContext('2d');
            const historyTable = document.getElementById('ml-history-table').querySelector('tbody');

            console.log('[ML Output] Script loaded');

            // Feature Importance Chart
            if (featureCtx) {
                new Chart(featureCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Jumlah Ikan', 'Umur', 'Pakan/Bukaan', 'Protein', 'Lemak', 'Serat', 'Suhu'],
                        datasets: [{
                            label: 'Importance',
                            data: [0.32, 0.18, 0.15, 0.12, 0.09, 0.08, 0.06],
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            // Load riwayat dari database
            loadMLHistory();

            // Event listener untuk form submit
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                console.log('[ML Output] Form submitted');
                
                // Tampilkan loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i data-lucide="loader-2" style="width: 20px; height: 20px; margin-right: 8px; animation: spin 1s linear infinite;"></i> Menghitung...';
                submitBtn.disabled = true;
                
                try {
                    const formData = new FormData(form);
                    
                    // Persiapan data untuk API lokal (PHP proxy)
                    const data = {
                        jenis_ikan: formData.get('jenis_ikan'),
                        umur_ikan: parseInt(formData.get('umur_ikan')),
                        jumlah_ikan: parseFloat(formData.get('jumlah_ikan')),
                        pakan_per_bukaan: parseFloat(formData.get('pakan_per_bukaan')),
                        protein_percent: parseFloat(formData.get('protein_percent')),
                        lemak_percent: parseFloat(formData.get('lemak_percent')),
                        serat_percent: parseFloat(formData.get('serat_percent')),
                        suhu_air: parseFloat(formData.get('suhu_air'))
                    };

                    console.log('[ML Output] Sending data to predict:', data);

                    // Kirim ke API lokal (PHP proxy) untuk hindari CORS
                    const response = await fetch('/api/predict-ml.php', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    console.log('[ML Output] Response status:', response.status);

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const result = await response.json();
                    console.log('[ML Output] Prediction result:', result);

                    if (result.status === 'success') {
                        const d = result.data;
                        
                        // Update UI dengan hasil prediksi
                        totalEl.textContent = d.rekomendasi_pakan;
                        freqEl.textContent = d.frekuensi_pakan;
                        bukaanEl.textContent = d.bukaan_per_jadwal;
                        
                        // Tampilkan jadwal
                        if (d.waktu_pakan && d.waktu_pakan !== "--") {
                            const times = d.waktu_pakan.split(';').filter(t => t.trim() !== '');
                            if (times.length > 0) {
                                const scheduleHTML = times.map(time => 
                                    `<div class="schedule-item" style="
                                        background: var(--primary);
                                        color: white;
                                        padding: 8px 16px;
                                        border-radius: var(--radius-sm);
                                        font-size: 14px;
                                        font-weight: 500;
                                        display: inline-block;
                                        margin: 4px;
                                    ">${time.trim()}</div>`
                                ).join('');
                                scheduleEl.innerHTML = scheduleHTML;
                            } else {
                                scheduleEl.innerHTML = '<p class="no-data">Tidak ada jadwal</p>';
                            }
                        } else {
                            scheduleEl.innerHTML = '<p class="no-data">Tidak ada jadwal</p>';
                        }
                        
                        // Tampilkan tombol simpan
                        btnSimpan.style.display = 'inline-flex';
                        
                        // Tampilkan success message
                        showNotification('Prediksi berhasil dihitung!', 'success');
                        
                    } else {
                        showNotification('❌ ' + (result.message || 'Prediksi gagal'), 'error');
                    }

                } catch (error) {
                    console.error('[ML Output] Error:', error);
                    showNotification('Gagal menghubungi server: ' + error.message, 'error');
                    
                } finally {
                    // Restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    lucide.createIcons();
                }
            });

            // Tombol simpan ke jadwal ML
            btnSimpan.addEventListener('click', async function() {
                console.log('[ML Output] Saving ML schedule to system...');
                
                // Tampilkan loading
                const originalText = btnSimpan.innerHTML;
                btnSimpan.innerHTML = '<i data-lucide="loader-2" style="width: 20px; height: 20px; margin-right: 8px; animation: spin 1s linear infinite;"></i> Menyimpan...';
                btnSimpan.disabled = true;
                
                try {
                    const formData = new FormData(form);
                    
                    // Ambil jadwal dari UI
                    const scheduleItems = scheduleEl.querySelectorAll('.schedule-item');
                    let waktuML = '';
                    if (scheduleItems.length > 0) {
                        waktuML = Array.from(scheduleItems).map(el => {
                            const time = el.textContent.trim();
                            // Pastikan format HH:MM
                            if (time.includes(':')) {
                                return time;
                            } else if (time.length === 4) {
                                // Format HHMM -> HH:MM
                                return time.substring(0, 2) + ':' + time.substring(2);
                            }
                            return time;
                        }).filter(t => t !== '').join(';');
                    }
                    
                    // Data lengkap untuk API
                    const mlData = {
                        // Data input dari form
                        jenis_ikan: formData.get('jenis_ikan') || 'Nila',
                        umur_ikan: parseInt(formData.get('umur_ikan')) || 28, // ✅ Default realistis
                        jumlah_ikan: parseFloat(formData.get('jumlah_ikan')) || 100,
                        pakan_per_bukaan: parseFloat(formData.get('pakan_per_bukaan')) || 5,
                        protein_percent: parseFloat(formData.get('protein_percent')) || 35,
                        lemak_percent: parseFloat(formData.get('lemak_percent')) || 8,
                        serat_percent: parseFloat(formData.get('serat_percent')) || 5,
                        suhu_air: parseFloat(formData.get('suhu_air')) || 28,
                        
                        // Data hasil prediksi
                        rekomendasi_pakan: parseFloat(totalEl.textContent) || 0,
                        frekuensi_pakan: parseInt(freqEl.textContent) || 1,
                        bukaan_per_jadwal: parseInt(bukaanEl.textContent) || 1,
                        waktu_pakan: waktuML || '08:00;16:00' // default jika kosong
                    };

                    console.log('[ML Output] Sending data to update-ml.php:', mlData);

                    // Kirim ke API update-ml.php
                    const response = await fetch('/api/update-ml.php', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'save_and_activate',
                            ml_data: mlData,
                            activate_mode_ml: true
                        })
                    });

                    const result = await response.json();
                    console.log('[ML Output] API Response:', result);
                    
                    if (result.status === 'success') {
                        showNotification('Jadwal ML berhasil disimpan dan diaktifkan!', 'success');
                        
                        // ✅ Hanya refresh riwayat — TIDAK redirect
                        loadMLHistory();
                        
                    } else {
                        showNotification('Gagal menyimpan: ' + (result.message || 'Unknown error'), 'error');
                        console.error('[ML Output] Save failed:', result);
                    }
                    
                } catch (err) {
                    console.error('[ML Output] Save error:', err);
                    showNotification('Gagal menyimpan: ' + err.message, 'error');
                    
                } finally {
                    // Restore button
                    btnSimpan.innerHTML = originalText;
                    btnSimpan.disabled = false;
                    lucide.createIcons();
                }
            });

            // Fungsi untuk tampilkan notifikasi
            function showNotification(message, type = 'info') {
                // Hapus notifikasi sebelumnya
                const existing = document.querySelector('.ml-notification');
                if (existing) existing.remove();
                
                const notification = document.createElement('div');
                notification.className = `ml-notification ${type}`;
                notification.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 12px 20px;
                    border-radius: var(--radius-sm);
                    background: ${type === 'success' ? 'var(--success)' : type === 'error' ? 'var(--danger)' : 'var(--primary)'};
                    color: white;
                    font-weight: 500;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    z-index: 9999;
                    animation: slideIn 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                `;
                notification.innerHTML = `
                    <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'info'}" style="width: 20px; height: 20px;"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.remove()" style="margin-left: 10px; background: none; border: none; color: inherit; cursor: pointer; padding: 0;">
                        <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                    </button>
                `;
                
                document.body.appendChild(notification);
                lucide.createIcons();
                
                // Auto remove setelah 5 detik
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.style.animation = 'slideOut 0.3s ease';
                        setTimeout(() => notification.remove(), 300);
                    }
                }, 5000);
            }

            // 🔧 HANYA INI YANG DIPERBAIKI — SESUAI STRUKTUR `ml_predictions.timestamp`
            async function loadMLHistory() {
                try {
                    const response = await fetch('/api/get-ml-history.php');
                    if (response.ok) {
                        const result = await response.json();
                        
                        if (result.status === 'success' && result.data && result.data.length > 0) {
                            // ✅ Cari prediksi terbaru berdasarkan `id` tertinggi
                            const latestId = Math.max(...result.data.map(r => r.id));
                            
                            historyTable.innerHTML = result.data.map(row => `
                                <tr>
                                    <td>${formatDateTime(row.timestamp)}</td>
                                    <td><strong>${parseFloat(row.rekomendasi_pakan).toFixed(2)}</strong> g</td>
                                    <td>${row.frekuensi_pakan}x</td>
                                    <td><small>${row.waktu_pakan || '--'}</small></td>
                                    <td>
                                        <span class="status-badge ${row.id === latestId ? 'active' : 'inactive'}" style="font-size: 12px; padding: 2px 8px;">
                                            ${row.id === latestId ? 'Aktif' : 'Tidak Aktif'}
                                        </span>
                                    </td>
                                </tr>
                            `).join('');
                        } else {
                            historyTable.innerHTML = '<tr><td colspan="5" class="no-data" style="text-align: center; color: var(--text-muted); padding: 20px;">Belum ada riwayat prediksi</td></tr>';
                        }
                    } else {
                        historyTable.innerHTML = '<tr><td colspan="5" class="no-data" style="text-align: center; color: var(--text-muted); padding: 20px;">Gagal memuat riwayat</td></tr>';
                    }
                } catch (err) {
                    console.error('[ML Output] Error loading history:', err);
                    historyTable.innerHTML = '<tr><td colspan="5" class="no-data" style="text-align: center; color: var(--text-muted); padding: 20px;">Error memuat data</td></tr>';
                }
            }

            // ✅ Perbaikan utama: Format `timestamp` MySQL → ISO → lokal
            function formatDateTime(ts) {
                if (!ts) return '—';
                // ✅ Konversi "Y-m-d H:i:s" → ISO 8601
                const iso = ts.replace(' ', 'T');
                const d = new Date(iso);
                return isNaN(d.getTime()) ? ts : d.toLocaleString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            // CSS untuk animations
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
                @keyframes slideDown {
                    from { transform: translateY(-20px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                .schedule-item {
                    transition: all 0.2s;
                    cursor: pointer;
                }
                .schedule-item:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                }
                .status-badge.active {
                    background: var(--success);
                    color: white;
                }
                .status-badge.inactive {
                    background: var(--danger);
                    color: white;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>