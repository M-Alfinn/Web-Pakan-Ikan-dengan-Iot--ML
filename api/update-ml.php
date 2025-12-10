<?php
// api/update-ml.php — FIX FINAL + jenis_ikan
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['ml_data'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request: ml_data required']);
    exit;
}

$mlData = $data['ml_data'];
$activateModeML = isset($data['activate_mode_ml']) ? (bool)$data['activate_mode_ml'] : true;

try {
    $conn = getDBConnection();
    $timestamp = date('Y-m-d H:i:s');

    // -----------------------------------------------------------
    // Normalisasi input
    // -----------------------------------------------------------
    $jenis_ikan         = $mlData['jenis_ikan'] ?? null;  // <-- DITAMBAHKAN

    $jumlah_ikan        = floatval($mlData['jumlah_ikan'] ?? 0);
    $umur_ikan          = intval($mlData['umur_ikan'] ?? 0);
    $pakan_per_bukaan   = floatval($mlData['pakan_per_bukaan'] ?? 0);

    $protein            = floatval($mlData['protein_percent'] ?? $mlData['protein'] ?? 0);
    $lemak              = floatval($mlData['lemak_percent'] ?? $mlData['lemak'] ?? 0);
    $serat              = floatval($mlData['serat_percent'] ?? $mlData['serat'] ?? 0);

    $suhu_air           = floatval($mlData['suhu_air'] ?? 0);

    $rekomendasi_pakan  = floatval($mlData['rekomendasi_pakan'] ?? 0);
    $frekuensi_pakan    = intval($mlData['frekuensi_pakan'] ?? 0);
    $bukaan_per_jadwal  = intval($mlData['bukaan_per_jadwal'] ?? 1);
    $waktu_pakan        = $mlData['waktu_pakan'] ?? "";

    // -----------------------------------------------------------
    // 1. Simpan ke ml_predictions
    // -----------------------------------------------------------
    $stmt = $conn->prepare("
        INSERT INTO ml_predictions (
            jenis_ikan,
            input_jumlah_ikan, input_umur_ikan, input_pakan_per_bukaan,
            input_protein, input_lemak, input_serat, input_suhu,
            rekomendasi_pakan, frekuensi_pakan, bukaan_per_jadwal,
            waktu_pakan, timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param(
        "sdiddddddiis",
        $jenis_ikan,
        $jumlah_ikan,
        $umur_ikan,
        $pakan_per_bukaan,
        $protein,
        $lemak,
        $serat,
        $suhu_air,
        $rekomendasi_pakan,
        $frekuensi_pakan,
        $bukaan_per_jadwal,
        $waktu_pakan
    );

    $stmt->execute();
    $predictionId = $stmt->insert_id;
    $stmt->close();

    // -----------------------------------------------------------
    // 2. Aktifkan Mode ML
    // -----------------------------------------------------------
    if ($activateModeML) {
        $conn->query("
            UPDATE system_status 
            SET mode_ml = 1, mode_auto_v7 = 0, updated_at = NOW()
            WHERE id = 1
        ");
    }

    // -----------------------------------------------------------
    // 3. Hapus jadwal ML lama (slot 5–9)
    // -----------------------------------------------------------
    $conn->query("DELETE FROM manual_schedules WHERE slot_number BETWEEN 5 AND 9");

    // -----------------------------------------------------------
    // 4. Simpan jadwal ML baru
    // -----------------------------------------------------------
    if (!empty($waktu_pakan)) {
        $times = array_filter(array_map('trim', explode(';', $waktu_pakan)));
        $slot = 5;

        foreach ($times as $time) {
            if (!str_contains($time, ':')) continue;

            [$h, $m] = explode(':', $time);
            $h = intval($h);
            $m = intval($m);

            if ($h < 0 || $h > 23 || $m < 0 || $m > 59) continue;

            $sch = $conn->prepare("
                INSERT INTO manual_schedules (slot_number, jam, menit, aktif)
                VALUES (?, ?, ?, 1)
            ");

            $sch->bind_param("iii", $slot, $h, $m);
            $sch->execute();
            $sch->close();

            $slot++;
            if ($slot > 9) break;
        }
    }

    // -----------------------------------------------------------
    // 5. Log Aktivitas
    // -----------------------------------------------------------
    $log = $conn->prepare("
        INSERT INTO log_aktivitas (waktu_eksekusi, sumber, bukaan_servo, suhu_air, pesan)
        VALUES (NOW(), 'Web ML', ?, ?, ?)
    ");

    $pesan = "ML aktif: {$rekomendasi_pakan}g / {$frekuensi_pakan}x";
    $log->bind_param("ids", $bukaan_per_jadwal, $suhu_air, $pesan);
    $log->execute();
    $log->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Jadwal ML disimpan & Mode ML aktif',
        'prediction_id' => $predictionId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
