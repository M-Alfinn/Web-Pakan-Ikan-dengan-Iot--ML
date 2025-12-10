<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
require_once '../config/database.php';

try {
    $conn = getDBConnection(); 

    // 1) Sensor data
    $suhuStmt = $conn->prepare("
        SELECT suhu_air, jenis_ikan, umur_ikan, jumlah_ikan, pakan_per_bukaan,
               protein_percent, lemak_percent, serat_percent, timestamp
        FROM sensor_data
        ORDER BY timestamp DESC
        LIMIT 1
    ");
    $suhuStmt->execute();
    $sRes = $suhuStmt->get_result()->fetch_assoc() ?: [];
    $suhuStmt->close();

    $sensor_suhu = isset($sRes['suhu_air']) ? floatval($sRes['suhu_air']) : 28.5;
    $sensor_timestamp = $sRes['timestamp'] ?? date('Y-m-d H:i:s');

    // 2) ML predictions
    $mlLatestStmt = $conn->prepare("
        SELECT 
            id, input_id,
            jenis_ikan, rekomendasi_pakan, frekuensi_pakan,
            waktu_pakan, bukaan_per_jadwal,
            input_jumlah_ikan AS jumlah_ikan,
            input_umur_ikan AS umur_ikan,
            input_pakan_per_bukaan AS pakan_per_bukaan,
            input_protein AS protein,
            input_lemak AS lemak,
            input_serat AS serat,
            input_suhu AS suhu,
            timestamp
        FROM ml_predictions
        ORDER BY timestamp DESC
        LIMIT 1
    ");
    $mlLatestStmt->execute();
    $mlLatest = $mlLatestStmt->get_result()->fetch_assoc() ?: [];
    $mlLatestStmt->close();

    // 3) system_status
    $statusStmt = $conn->prepare("SELECT * FROM system_status WHERE id = 1 LIMIT 1");
    $statusStmt->execute();
    $status = $statusStmt->get_result()->fetch_assoc() ?: [];
    $statusStmt->close();

    // 4) Tentukan jenis ikan
    $jenisIkan = 
        $mlLatest['jenis_ikan'] ??
        $sRes['jenis_ikan'] ??
        $status['jenis_ikan'] ??
        'Nila';

    // 5) Sensor object
    $sensor = [
        'suhu_air' => $sensor_suhu,
        'jenis_ikan' => $jenisIkan,
        'umur_ikan' => $mlLatest['umur_ikan'] ?? ($sRes['umur_ikan'] ?? ($status['umur_ikan'] ?? 30)),
        'jumlah_ikan' => $mlLatest['jumlah_ikan'] ?? ($sRes['jumlah_ikan'] ?? ($status['jumlah_ikan'] ?? 100)),
        'pakan_per_bukaan' => $mlLatest['pakan_per_bukaan'] ?? ($sRes['pakan_per_bukaan'] ?? ($status['pakan_per_bukaan'] ?? 5.0)),
        'protein' => $mlLatest['protein'] ?? $sRes['protein_percent'] ?? null,
        'lemak' => $mlLatest['lemak'] ?? $sRes['lemak_percent'] ?? null,
        'serat' => $mlLatest['serat'] ?? $sRes['serat_percent'] ?? null,
        'timestamp' => $sensor_timestamp
    ];

    // 6) ML summary
    $ml = [
        'rekomendasi_pakan' => $mlLatest['rekomendasi_pakan'] ?? null,
        'frekuensi_pakan' => $mlLatest['frekuensi_pakan'] ?? null,
        'bukaan_per_jadwal' => $mlLatest['bukaan_per_jadwal'] ?? null,
        'waktu_pakan' => $mlLatest['waktu_pakan'] ?? null
    ];

    // 6b) Ambil manual schedules (slot 1–4)
    $ms = $conn->prepare("
        SELECT slot_number, 
               NULLIF(jam, -1) AS jam,
               NULLIF(menit, -1) AS menit,
               aktif
        FROM manual_schedules
        WHERE slot_number BETWEEN 1 AND 4
        ORDER BY slot_number ASC
    ");
    $ms->execute();
    $manualSchedules = $ms->get_result()->fetch_all(MYSQLI_ASSOC);
    $ms->close();

    // Normalizer penting -> jika jam/menit NULL, jangan ubah!
    foreach ($manualSchedules as &$x) {
        if ($x['aktif'] == 0) {
            $x['jam'] = null;
            $x['menit'] = null;
        }
    }

    // 7) Next feeding → ML slot (5–9)
    $nextFeeding = null;
    $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    $today = $now->format('Y-m-d');

    $schedStmt = $conn->prepare("
        SELECT 
            NULLIF(jam, -1) AS jam,
            NULLIF(menit, -1) AS menit
        FROM manual_schedules
        WHERE slot_number BETWEEN 5 AND 9
          AND aktif = 1
        ORDER BY jam ASC, menit ASC
    ");
    $schedStmt->execute();
    $scheds = $schedStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $schedStmt->close();

    foreach ($scheds as $s) {
        if ($s['jam'] === null || $s['menit'] === null) continue;

        $time = new DateTime("$today {$s['jam']}:{$s['menit']}:00", new DateTimeZone('Asia/Jakarta'));
        if ($time > $now) {
            $nextFeeding = [
                'time' => sprintf("%02d:%02d", $s['jam'], $s['menit']),
                'source' => (!empty($status['mode_ml']) && $status['mode_ml']==1)
                    ? 'Machine Learning'
                    : 'Jadwal Manual'
            ];
            break;
        }
    }

    if (!$nextFeeding && !empty($scheds)) {
        $f = $scheds[0];
        if ($f['jam'] !== null && $f['menit'] !== null) {
            $nextFeeding = [
                'time' => sprintf("%02d:%02d", $f['jam'], $f['menit']),
                'source' => (!empty($status['mode_ml']) && $status['mode_ml']==1)
                    ? 'Machine Learning'
                    : 'Jadwal Manual'
            ];
        }
    }

    // 8) Recent activity
    $activityStmt = $conn->prepare("
        SELECT waktu_eksekusi, sumber, bukaan_servo, pesan
        FROM log_aktivitas
        WHERE waktu_eksekusi >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY waktu_eksekusi DESC
        LIMIT 5
    ");
    $activityStmt->execute();
    $activities = $activityStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $activityStmt->close();

    // 9) Chart feeding 24 jam
    $chartStmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN sumber LIKE '%Manual%' THEN 1 ELSE 0 END), 0) as manual_count,
            COALESCE(SUM(CASE WHEN sumber = 'Auto(V7)' THEN  1 ELSE 0 END), 0) as v7_count,
            COALESCE(SUM(CASE WHEN sumber LIKE '%ML%' THEN 1 ELSE 0 END), 0) as ml_count,
            COALESCE(SUM(CASE WHEN sumber LIKE '%Web Manual%' THEN 1 ELSE 0 END), 0) as jadwal_count
        FROM log_aktivitas
        WHERE waktu_eksekusi >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $chartStmt->execute();
    $chartData = $chartStmt->get_result()->fetch_assoc();
    $chartStmt->close();

    // 10) Output JSON
    echo json_encode([
        'status' => 'success',
        'data' => [
            'sensor' => $sensor,
            'system_status' => $status,
            'ml' => $ml,
            'manual_schedules' => $manualSchedules,
            'next_feeding' => $nextFeeding,
            'recent_activity' => $activities,
            'today_feeding' => $chartData
        ]
    ]);

} catch (Exception $e) {
    error_log('[GET DASHBOARD ERROR] '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Server error']);
}
