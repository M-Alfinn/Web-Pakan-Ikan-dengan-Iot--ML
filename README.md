# 📘 **README.md --- Sistem Monitoring & Prediksi Pakan Ikan Berbasis IoT + Machine Learning**

# 🐟 **Smart Fish Feeding System -- IoT & Machine Learning**

Sistem monitoring dan pemberian pakan ikan otomatis berbasis **IoT**,
**website**, dan **Machine Learning (Random Forest)**.\
Proyek ini dirancang untuk mempermudah pembudidaya ikan dalam mengontrol
jumlah pakan, memonitor suhu air, serta mengotomatisasi proses feeding
dengan akurasi tinggi.

Website berjalan **realtime**, terhubung langsung dengan **ESP32** dan
**ML API Flask** untuk melakukan prediksi pakan berdasarkan kondisi
kolam.

------------------------------------------------------------------------

## 🚀 **Fitur Utama**

### 🔹 **1. Real-time Sensor Monitoring**

Pantau suhu air, kondisi kolam, dan status perangkat secara live melalui
dashboard web.

### 🔹 **2. Machine Learning Prediction (Random Forest)**

Model ML memprediksi: - Total pakan optimal (gram) - Frekuensi pemberian
pakan per hari - Jadwal feeding otomatis - Jumlah bukaan servo per
jadwal - Kategori suhu & rekomendasi kualitas pakan

### 🔹 **3. Automatic & Manual Feeding Control**

-   Mode otomatis via prediksi ML\
-   Mode manual via website untuk membuka servo feeder\
-   Mode jadwal spesifik (custom schedule)

### 🔹 **4. Riwayat & Log Aktivitas**

-   Setiap feeding terekam otomatis\
-   Filter berdasarkan rentang waktu\
-   Export ke CSV

### 🔹 **5. Dashboard Modern**

UI minimalis, responsif, dan informatif: - Grafik suhu realtime\
- Riwayat ML\
- Status perangkat\
- Kontrol cepat pakan

------------------------------------------------------------------------

## 📁 **Struktur Direktori Proyek**

    /
    ├── index.php              # Landing page
    ├── login.php              # Login admin
    ├── logout.php             # Logout handler
    ├── dashboard.php          # Dashboard utama
    ├── log-aktivitas.php      # Log aktivitas sistem
    ├── ml-output.php          # Output Machine Learning
    ├── kontrol.php            # Kontrol feeding & pengaturan
    ├── database.sql           # Struktur database

    ├── config/
    │   ├── database.php       # Koneksi database
    │   └── auth.php           # Sistem autentikasi

    ├── includes/
    │   ├── navbar.php         # Navbar
    │   └── sidebar.php        # Sidebar navigasi

    ├── api/
    │   ├── update-sensor.php      # Endpoint data sensor (ESP32 → Web)
    │   ├── update-ml.php          # Input prediksi ML (API → Web)
    │   ├── log-activity.php       # Mencatat log feeding
    │   ├── get-dashboard.php      # Data dashboard
    │   ├── get-logs.php           # Data log aktivitas
    │   ├── export-logs.php        # Export CSV
    │   ├── control-feed.php       # Kontrol servo manual
    │   ├── update-mode.php        # Ganti mode (manual/auto)
    │   ├── update-schedule.php    # Update jadwal manual
    │   ├── get-ml-history.php     # Riwayat ML
    │   └── get-temp-chart.php     # Grafik suhu

    ├── assets/
    │   ├── css/
    │   │   └── style.css          # Tampilan & tema
    │   └── js/
    │       ├── dashboard.js       # Logic dashboard
    │       ├── ml-output.js       # Logic ML output
    │       └── kontrol.js         # Logic kontrol feeder
    └── README.md

------------------------------------------------------------------------

## 🔌 **Integrasi IoT (ESP32 → Website)**

### **Update Sensor Data**

    POST /api/update-sensor.php

Contoh JSON:

``` json
{
  "suhu_air": 28.5,
  "jenis_ikan": "Nila",
  "umur_ikan": 4,
  "jumlah_ikan": 50,
  "pakan_per_bukaan": 2.5,
  "protein_percent": 32.0,
  "lemak_percent": 8.5,
  "serat_percent": 4.2
}
```

------------------------------------------------------------------------

## 🤖 **Integrasi Machine Learning (Flask API → Website)**

### **Update ML Prediction**

    POST /api/update-ml.php

Contoh JSON:

``` json
{
  "rekomendasi_pakan": 125.5,
  "frekuensi_pakan": 3,
  "waktu_pakan": "07:00;12:00;18:00",
  "bukaan_per_jadwal": 2,
  "input_jumlah_ikan": 50,
  "input_umur_ikan": 4,
  "input_pakan_per_bukaan": 2.5,
  "input_protein": 32.0,
  "input_lemak": 8.5,
  "input_serat": 4.2,
  "input_suhu": 28.5
}
```

------------------------------------------------------------------------

## 📝 **Contoh Code ESP32**

``` cpp
#include <HTTPClient.h>

void kirimKeWebsite() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin("http://kel5.myiot.fun/api/update-sensor.php");
    http.addHeader("Content-Type", "application/json");

    String jsonData = "{"suhu_air":" + String(suhu_air, 2) + 
                      ","jenis_ikan":"" + jenis_ikan + """ +
                      ","umur_ikan":" + String(umur_ikan) +
                      ","jumlah_ikan":" + String(jumlah_ikan) +
                      ","pakan_per_bukaan":" + String(pakan_per_bukaan, 2) +
                      ","protein_percent":" + String(protein_percent, 2) +
                      ","lemak_percent":" + String(lemak_percent, 2) +
                      ","serat_percent":" + String(serat_percent, 2) + "}";

    http.POST(jsonData);
    http.end();
  }
}
```

------------------------------------------------------------------------

## 🌐 **Teknologi yang Digunakan**

-   PHP 8 Backend\
-   MySQL Database\
-   ESP32 (IoT Client)\
-   Flask API (Python Machine Learning Server)\
-   Random Forest Regressor\
-   AJAX Realtime Update\
-   HTML + CSS + JS Frontend

------------------------------------------------------------------------

## ❤️ **Tujuan Proyek**

-   Mengukur dan mengoptimalkan pakan ikan\
-   Mengurangi pemborosan pakan\
-   Menjaga kesehatan ikan melalui feeding yang tepat\
-   Mengotomatisasi sistem budidaya agar lebih efisien
