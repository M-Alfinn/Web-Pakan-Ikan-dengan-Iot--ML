<?php
// API untuk riwayat prediksi ML — Versi Diperbaiki (Fix Invalid Date)
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once '../config/database.php';

try {
    $conn = getDBConnection();
    
    // Ambil riwayat + tandai prediksi terbaru sebagai aktif
    $stmt = $conn->prepare("
        SELECT 
            id,
            input_jumlah_ikan,
            input_umur_ikan,
            input_pakan_per_bukaan,
            input_protein,
            input_lemak,
            input_serat,
            input_suhu,
            rekomendasi_pakan,
            frekuensi_pakan,
            bukaan_per_jadwal,
            waktu_pakan,
            timestamp
        FROM ml_predictions 
        ORDER BY timestamp DESC 
        LIMIT 20
    ");
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Cari ID prediksi terbaru
    $latest_id = null;
    foreach ($results as $row) {
        if ($latest_id === null || $row['id'] > $latest_id) {
            $latest_id = $row['id'];
        }
    }

    // Format data + tambahkan `is_active`
    $formatted = [];
    foreach ($results as $row) {
        // ✅ Konversi timestamp ke format ISO 8601 yang aman untuk JS
        $ts = $row['timestamp'];
        $formatted_ts = $ts ? date('c', strtotime($ts)) : 'Invalid Date';
        
        $formatted[] = [
            'id' => (int)$row['id'],
            'input_jumlah_ikan' => (float)$row['input_jumlah_ikan'],
            'input_umur_ikan' => (int)$row['input_umur_ikan'],
            'input_pakan_per_bukaan' => (float)$row['input_pakan_per_bukaan'],
            'input_protein' => (float)$row['input_protein'],
            'input_lemak' => (float)$row['input_lemak'],
            'input_serat' => (float)$row['input_serat'],
            'input_suhu' => (float)$row['input_suhu'],
            'rekomendasi_pakan' => (float)$row['rekomendasi_pakan'],
            'frekuensi_pakan' => (int)$row['frekuensi_pakan'],
            'bukaan_per_jadwal' => (int)$row['bukaan_per_jadwal'],
            'waktu_pakan' => $row['waktu_pakan'] ?? '--',
            'timestamp' => $formatted_ts, // ⬅️ INI YANG ANDA BUTUHKAN!
            'is_active' => ($row['id'] === $latest_id) ? 1 : 0
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $formatted
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal memuat riwayat: ' . $e->getMessage()
    ]);
}
?>