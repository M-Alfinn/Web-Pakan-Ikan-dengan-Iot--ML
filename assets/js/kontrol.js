// Control & Settings Page

// =====================
// Notifikasi ala ML Output
// =====================
// function showNotification(message, type = 'info') {
//   const existing = document.querySelector('.ml-notification');
//   if (existing) existing.remove();

//   const notification = document.createElement('div');
//   notification.className = `ml-notification ${type}`;
//   notification.style.cssText = `
//       position: fixed;
//       top: 20px;
//       right: 20px;
//       padding: 12px 20px;
//       border-radius: var(--radius-sm);
//       background: ${type === 'success' ? 'var(--success)' : type === 'error' ? 'var(--danger)' : 'var(--primary)'};
//       color: white;
//       font-weight: 500;
//       box-shadow: 0 4px 12px rgba(0,0,0,0.15);
//       z-index: 9999;
//       animation: slideIn 0.6s ease;
//       display: flex;
//       align-items: center;
//       gap: 10px;
//   `;
//   notification.innerHTML = `
//       <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'info'}"
//          style="width: 20px; height: 20px;"></i>
//       <span>${message}</span>
//       <button onclick="this.parentElement.remove()" style="margin-left: 10px; background: none; border: none; color: inherit; cursor: pointer; padding: 0;">
//           <i data-lucide="x" style="width: 16px; height: 16px;"></i>
//       </button>
//   `;

//   document.body.appendChild(notification);
//   lucide.createIcons();

//   setTimeout(() => {
//     if (notification.parentNode) {
//       notification.style.animation = 'slideOut 0.6s ease';
//       setTimeout(() => notification.remove(), 300);
//     }
//   }, 5000);
// }

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
        animation: slideIn 1s ease;
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
            notification.style.animation = 'slideOut 1s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

document.addEventListener("DOMContentLoaded", () => {
  loadCurrentSettings()
})

async function loadCurrentSettings() {
  try {
    const response = await fetch("/api/get-dashboard.php")
    const result = await response.json()

    if (result.status === "success") {
      const status = result.data.system_status
      document.getElementById("mode-v7-toggle").checked = status.mode_auto_v7 == 1
      document.getElementById("mode-v7-status").textContent = status.mode_auto_v7 == 1 ? "ON" : "OFF"

      document.getElementById("mode-ml-toggle").checked = status.mode_ml == 1
      document.getElementById("mode-ml-status").textContent = status.mode_ml == 1 ? "ON" : "OFF"

      const schedules = result.data.manual_schedules
      schedules.forEach((sch) => {
        const slot = sch.slot_number
        document.getElementById(`schedule-${slot}-active`).checked = sch.aktif == 1
        if (sch.jam !== null) document.getElementById(`schedule-${slot}-hour`).value = sch.jam
        if (sch.menit !== null) document.getElementById(`schedule-${slot}-minute`).value = sch.menit
      })
    }
  } catch (error) {
    console.error("[v0] Error loading settings:", error)
  }
}

async function feedManual(bukaan) {
  try {
    const response = await fetch("/api/control-feed.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ bukaan }),
    })

    const result = await response.json()

    if (result.status === "success") {
      showNotification(`Perintah beri makan ${bukaan}x berhasil dikirim!`, "success")
    } else {
      showNotification("Gagal mengirim perintah: " + result.message, "error")
    }
  } catch (error) {
    console.error("[v0] Error feeding manual:", error)
    showNotification("Terjadi kesalahan saat mengirim perintah", "error")
  }
}

async function toggleModeV7(checked) {
  try {
    const response = await fetch("/api/update-mode.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ mode: "v7", value: checked }),
    })

    const result = await response.json()

    if (result.status === "success") {
      document.getElementById("mode-v7-status").textContent = checked ? "ON" : "OFF"
      showNotification("Mode Auto berhasil diupdate!", "success")
    } else {
      showNotification("Gagal update mode: " + result.message, "error")
    }
  } catch (error) {
    console.error("[v0] Error updating mode V7:", error)
  }
}

async function toggleModeML(checked) {
  try {
    const response = await fetch("/api/update-mode.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ mode: "ml", value: checked }),
    })

    const result = await response.json()

    if (result.status === "success") {
      document.getElementById("mode-ml-status").textContent = checked ? "ON" : "OFF"
      showNotification("Mode ML berhasil diupdate!", "success")
    } else {
      showNotification("Gagal update mode: " + result.message, "error")
    }
  } catch (error) {
    console.error("[v0] Error updating mode ML:", error)
  }
}

async function updateSchedule(slot) {
  const active = document.getElementById(`schedule-${slot}-active`).checked
  const hour = Number.parseInt(document.getElementById(`schedule-${slot}-hour`).value) || null
  const minute = Number.parseInt(document.getElementById(`schedule-${slot}-minute`).value) || null

  try {
    const response = await fetch("/api/update-schedule.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ slot, jam: hour, menit: minute, aktif: active }),
    })

    const result = await response.json()

    if (result.status === "success") {
      console.log("[v0] Schedule updated successfully")
      showNotification(`Jadwal ${slot} berhasil diperbarui!`, "success")
    } else {
      showNotification("Gagal update jadwal: " + result.message, "error")
    }
  } catch (error) {
    console.error("[v0] Error updating schedule:", error)
  }
}

async function saveAllSchedules() {
  for (let i = 1; i <= 4; i++) {
    await updateSchedule(i)
  }
  showNotification("Semua jadwal berhasil disimpan!", "success")
}
