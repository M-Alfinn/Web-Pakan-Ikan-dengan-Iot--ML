// ML Output Page - Minimal Version (Browser Compatible)
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("ml-input-form");
  if (!form) {
    console.error("[ML Output] Form tidak ditemukan!");
    return;
  }

  // Submit form → kirim ke predict-ml.php
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Ambil nilai dari form
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
      const res = await fetch("/api/predict-ml.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      });

      const result = await res.json();

      if (result.status === "success") {
        showPrediction(result.data);
      } else {
        alert("❌ Prediksi gagal: " + (result.message || "Error tidak diketahui"));
      }
    } catch (err) {
      alert("❌ Gagal kirim data ke server: " + err.message);
    }
  });

  // Tombol Simpan ke Jadwal ML
  document.getElementById("btn-simpan-ml")?.addEventListener("click", async () => {
    // Ambil nilai yang ditampilkan di UI hasil prediksi
    try {
      const totalText = document.getElementById("ml-output-total")?.textContent || "";
      const freqText = document.getElementById("ml-output-freq")?.textContent || "";
      const bukaanText = document.getElementById("ml-output-bukaan")?.textContent || "";

      const total = totalText.replace(" gram", "").trim();
      const freq = freqText.replace(" kali", "").trim();
      const bukaan = bukaanText.replace(" kali", "").trim();

      // Ambil schedule dari elemen schedule timeline (sesuai showPrediction)
      const scheduleEls = Array.from(document.querySelectorAll("#ml-schedule-timeline .schedule-slot"));
      const schedule = scheduleEls.map(el => el.textContent.trim()).join(";");

      // Jika user belum melihat/menjalankan prediksi, coba ambil dari form sebagai fallback
      const form = document.getElementById("ml-input-form");
      const formData = form ? Object.fromEntries(new FormData(form).entries()) : {};

      const mlData = {
        // Prioritaskan nilai yang tampil di UI
        jumlah_ikan: formData.jumlah_ikan ? Number(formData.jumlah_ikan) : undefined,
        umur_ikan: formData.umur_ikan ? Number(formData.umur_ikan) : undefined,
        pakan_per_bukaan: formData.pakan_per_bukaan ? Number(formData.pakan_per_bukaan) : undefined,

        // hasil prediksi ringkasan (harus ada)
        rekomendasi_pakan: total ? Number(total) : (formData.rekomendasi_pakan ? Number(formData.rekomendasi_pakan) : undefined),
        frekuensi_pakan: freq ? Number(freq) : (formData.frekuensi_pakan ? Number(formData.frekuensi_pakan) : undefined),
        bukaan_per_jadwal: bukaan ? Number(bukaan) : (formData.bukaan_per_jadwal ? Number(formData.bukaan_per_jadwal) : undefined),
        waktu_pakan: schedule || (formData.waktu_pakan || ""),

        // optional komposisi (jika ada di form)
        protein_percent: formData.protein_percent ? Number(formData.protein_percent) : (formData.protein ? Number(formData.protein) : undefined),
        lemak_percent: formData.lemak_percent ? Number(formData.lemak_percent) : (formData.lemak ? Number(formData.lemak) : undefined),
        serat_percent: formData.serat_percent ? Number(formData.serat_percent) : (formData.serat ? Number(formData.serat) : undefined),
        suhu_air: formData.suhu_air ? Number(formData.suhu_air) : undefined,
        jenis_ikan: formData.jenis_ikan || undefined
      };

      // Remove undefined keys to keep payload clean
      Object.keys(mlData).forEach(k => mlData[k] === undefined && delete mlData[k]);

      // Kirim ke API update-ml.php sebagai { ml_data: {...} }
      const saveRes = await fetch("/api/update-ml.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ml_data: mlData, activate_mode_ml: true })
      });

      const saveJson = await saveRes.json();

      if (!saveRes.ok || saveJson.status !== "success") {
        const errMsg = saveJson?.message || `Server returned ${saveRes.status}`;
        alert("❌ Gagal simpan ML: " + errMsg);
        return;
      }

      // Setelah tersimpan, aktifkan juga mode ML via API (tetap lakukan agar system_status sinkron)
      await fetch("/api/update-mode.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ mode: "ml", value: true })
      });

      alert("✅ Jadwal ML berhasil disimpan & mode ML diaktifkan!");
      window.location.reload();

    } catch (err) {
      console.error("[ML SAVE ERROR]", err);
      alert("❌ Error saat menyimpan jadwal ML: " + err.message);
    }
  });

  // Load riwayat prediksi
  loadMLHistory();
});

// Tampilkan hasil prediksi di UI
function showPrediction(data) {
  // Total pakan
  document.getElementById("ml-output-total").textContent = 
    Number(data.rekomendasi_pakan).toFixed(1) + " gram";

  // Frekuensi
  document.getElementById("ml-output-freq").textContent = 
    data.frekuensi_pakan + " kali";

  // Bukaan per jadwal
  document.getElementById("ml-output-bukaan").textContent = 
    data.bukaan_per_jadwal + " kali";

  // Jadwal
  const scheduleEl = document.getElementById("ml-schedule-timeline");
  if (!scheduleEl) return;

  if (data.waktu_pakan && data.waktu_pakan !== "--") {
    const slots = data.waktu_pakan.split(";").map(t => t.trim()).filter(t => t);
    if (slots.length > 0) {
      scheduleEl.innerHTML = slots.map(t => `
        <div class="schedule-slot" style="
          background: #3B82F6;
          color: white;
          padding: 4px 8px;
          border-radius: 4px;
          font-size: 0.875rem;
          font-weight: 500;
          margin: 2px 0;
        ">${t}</div>
      `).join("");
    } else {
      scheduleEl.innerHTML = '<p class="no-data">Tidak ada jadwal</p>';
    }
  } else {
    scheduleEl.innerHTML = '<p class="no-data">Tidak ada jadwal</p>';
  }

  // Tampilkan tombol simpan
  const btn = document.getElementById("btn-simpan-ml");
  if (btn) btn.style.display = "inline-flex";
}

// Load riwayat prediksi dari API
async function loadMLHistory() {
  try {
    const res = await fetch("/api/get-ml-history.php");
    const data = await res.json();

    if (data.status === "success") {
      const tbody = document.getElementById("ml-history-table")?.querySelector("tbody");
      if (!tbody) return;

      if (data.data && data.data.length > 0) {
        tbody.innerHTML = data.data.map(row => `
          <tr>
            <td>${new Date(row.timestamp).toLocaleString("id-ID")}</td>
            <td>${Number(row.rekomendasi_pakan).toFixed(1)} g</td>
            <td>${row.frekuensi_pakan}x</td>
            <td>${row.waktu_pakan || "--"}</td>
          </tr>
        `).join("");
      } else {
        tbody.innerHTML = '<tr><td colspan="4" class="no-data">Belum ada prediksi</td></tr>';
      }
    }
  } catch (err) {
    console.error("Gagal muat riwayat ML", err);
  }
}
