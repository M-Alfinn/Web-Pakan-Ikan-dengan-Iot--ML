// Dashboard Real-time Monitoring — Versi Stabil & Aman
let tempChart, feedingChart;
let updateInterval;

document.addEventListener("DOMContentLoaded", () => {
  initCharts();
  loadDashboardData();
  updateTime();

  // Update setiap 2 detik — cukup untuk real-time tanpa beban
  updateInterval = setInterval(loadDashboardData, 2000);
  setInterval(updateTime, 1000);
});

function initCharts() {
  const tempCtx = document.getElementById("tempChart")?.getContext("2d");
  if (tempCtx) {
    tempChart = new Chart(tempCtx, {
      type: "line",
      data: {
        labels: [],
        datasets: [{
          label: "Suhu Air (°C)",
          data: [],
          borderColor: "#3B82F6",
          backgroundColor: "rgba(59, 130, 246, 0.1)",
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: false,
            min: 20,
            max: 35
          }
        }
      }
    });
  }

  const feedCtx = document.getElementById("feedingChart")?.getContext("2d");
  if (feedCtx) {
    feedingChart = new Chart(feedCtx, {
      type: "bar",
      data: {
        labels: ["Manual", "ML", "Auto V7", "Jadwal"],
        datasets: [{
          label: "Jumlah Aktivitas",
          data: [0, 0, 0, 0],
          backgroundColor: ["#3B82F6", "#10B981", "#F59E0B", "#8B5CF6"]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        }
      }
    });
  }
}

async function loadDashboardData() {
  try {
    const response = await fetch("/api/get-dashboard.php", {
      method: "GET",
      headers: { "Cache-Control": "no-cache" }
    });

    if (!response.ok) throw new Error(`HTTP ${response.status}`);

    const result = await response.json();

    if (result.status === "success") {
      // ✅ Perbarui semua komponen
      updateSensorAndESP32Status(result.data.sensor, result.data.system_status);
      updateMLCards(result.data.ml);
      updateRecentActivity(result.data.recent_activity);
      updateNextFeeding(result.data);
      updateTempChart();
    }
  } catch (error) {
    console.error("[DASHBOARD] Gagal muat data:", error.message);
  }
}

// ✅ FUNGSI UTAMA: Gabungkan sensor + status ESP32 secara akurat
function updateSensorAndESP32Status(sensor, system_status) {
  if (!sensor) return;

  // ————— Helper —————
  const safeFloat = (val) => {
    if (val === null || val === undefined || val === "") return "--";
    const f = parseFloat(val);
    return isNaN(f) ? "--" : f.toFixed(1);
  };
  const safeInt = (val) => {
    if (val === null || val === undefined || val === "") return "--";
    const i = parseInt(val);
    return isNaN(i) ? "--" : i;
  };

  const els = {
    'suhu-air': safeFloat(sensor.suhu_air),
    'jenis-ikan': sensor.jenis_ikan || "--",
    'umur-ikan': safeInt(sensor.umur_ikan),
    'jumlah-ikan': safeInt(sensor.jumlah_ikan),
    'pakan-per-bukaan': safeFloat(sensor.pakan_per_bukaan),
    'protein': sensor.protein !== null && sensor.protein !== undefined ? safeFloat(sensor.protein) : "--",
    'lemak': sensor.lemak !== null && sensor.lemak !== undefined ? safeFloat(sensor.lemak) : "--",
    'serat': sensor.serat !== null && sensor.serat !== undefined ? safeFloat(sensor.serat) : "--"
  };

  for (const [id, text] of Object.entries(els)) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  }

  // ————— Update status ESP32 —————
  const statusEl = document.getElementById("esp32-status");
  const dot = statusEl?.querySelector(".indicator-dot");
  if (!statusEl || !dot) return;

  let isOnline = false;

  // Prioritas 1: cek timestamp sensor (jika ada)
  if (sensor.timestamp) {
    const lastUpdate = new Date(sensor.timestamp);
    const now = new Date();
    const diffSec = (now - lastUpdate) / 1000;
    isOnline = diffSec < 15; // longgarkan jadi 15 detik (lebih stabil)
  }

  // Prioritas 2: fallback ke esp32_connected (jika tersedia dan = 1)
  if (!isOnline && system_status?.esp32_connected == 1) {
    isOnline = true;
  }

  // Update UI
  if (isOnline) {
    dot.classList.add("connected");
    statusEl.querySelector("span").textContent = "ESP32 Connected";
  } else {
    dot.classList.remove("connected");
    statusEl.querySelector("span").textContent = "ESP32 Offline";
  }

  // ————— Update status mode (V7 / ML) —————
  updateSystemStatus(system_status);
}

// ✅ Tetap gunakan logika lama Anda yang sudah berjalan sempurna
function updateSystemStatus(status) {
  if (!status) return;

  const v7Badge = document.getElementById("mode-v7-badge");
  const mlBadge = document.getElementById("mode-ml-badge");

  if (v7Badge) {
    if (status.mode_auto_v7 == 1 || status.mode_auto_v7 === true) {
      v7Badge.textContent = "ON";
      v7Badge.style.background = "#10B981";
    } else {
      v7Badge.textContent = "OFF";
      v7Badge.style.background = "#94A3B8";
    }
  }

  if (mlBadge) {
    if (status.mode_ml == 1 || status.mode_ml === true) {
      mlBadge.textContent = "ON";
      mlBadge.style.background = "#3B82F6";
    } else {
      mlBadge.textContent = "OFF";
      mlBadge.style.background = "#94A3B8";
    }
  }
}

function updateMLCards(ml) {
  if (!ml) return;
  
  const el = (id, text) => {
    const e = document.getElementById(id);
    if (e) e.textContent = text;
  };
  
  el("ml-total", ml.rekomendasi_pakan ? `${parseFloat(ml.rekomendasi_pakan).toFixed(1)} gram` : "-- gram");
  el("ml-freq", ml.frekuensi_pakan ? `${ml.frekuensi_pakan} kali` : "-- kali");
  el("ml-schedule", ml.waktu_pakan || "--");
}

function updateRecentActivity(activities) {
  const container = document.getElementById("recent-activity");
  if (!container) return;

  container.innerHTML = activities && activities.length
    ? activities.map(act => `
        <div class="activity-item">
          <div class="activity-icon"><i data-lucide="activity"></i></div>
          <div class="activity-content">
            <strong>${act.sumber} - ${act.bukaan_servo}x bukaan</strong>
            <span>${new Date(act.waktu_eksekusi).toLocaleString("id-ID")}</span>
          </div>
        </div>
      `).join("")
    : '<p class="no-data">Belum ada aktivitas</p>';

  if (window.lucide) window.lucide.createIcons();
}

function updateNextFeeding(data) {
  const timeEl = document.getElementById("next-feeding-time");
  const sourceEl = document.getElementById("next-feeding-source");
  const amountEl = document.getElementById("next-feeding-amount");
  
  if (!timeEl || !sourceEl || !amountEl) return;

  const status = data.system_status;
  const sensor = data.sensor;

  if (data.next_feeding && data.next_feeding.time) {
    timeEl.textContent = data.next_feeding.time;
    sourceEl.textContent = data.next_feeding.source || "Unknown";
    amountEl.textContent = sensor?.pakan_per_bukaan 
      ? `${parseFloat(sensor.pakan_per_bukaan).toFixed(1)} gram` 
      : "-- gram";
  } else {
    timeEl.textContent = "--:--";
    sourceEl.textContent = "Tidak ada jadwal";
    amountEl.textContent = "-- gram";
  }
}

async function updateTempChart() {
  try {
    const res = await fetch("/api/get-temp-chart.php");
    if (!res.ok) return;
    const data = await res.json();
    
    if (tempChart && data.labels && data.values) {
      tempChart.data.labels = data.labels;
      tempChart.data.datasets[0].data = data.values;
      tempChart.update();
    }
  } catch (err) {
    console.warn("[CHART] Gagal perbarui grafik suhu");
  }
}

function updateTime() {
  const now = new Date();
  const timeStr = now.toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit"
  });
  
  const el = document.getElementById("current-time");
  if (el) el.textContent = timeStr;
}
