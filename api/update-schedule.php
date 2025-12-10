<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
require_once '../config/database.php';

try {
    $conn = getDBConnection();

    $input = json_decode(file_get_contents('php://input'), true);

    // VALIDASI INPUT — sesuaikan dengan frontend Anda
    if (!$input || !isset($input['slot'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON input'
        ]);
        exit;
    }

    $slot = intval($input['slot']);

    // hanya slot manual 1–4
    if ($slot < 1 || $slot > 4) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Slot tidak valid'
        ]);
        exit;
    }

    // Ambil nilai
    $jam   = ($input['jam'] !== "" && isset($input['jam'])) ? intval($input['jam']) : null;
    $menit = ($input['menit'] !== "" && isset($input['menit'])) ? intval($input['menit']) : null;
    $aktif = isset($input['aktif']) ? intval($input['aktif']) : 0;

    // Jika OFF → jadwal direset
    if ($aktif == 0) {
        $jam = null;
        $menit = null;
    }

    // UPDATE database
    $stmt = $conn->prepare("
        UPDATE manual_schedules
        SET jam = ?, menit = ?, aktif = ?, updated_at = NOW()
        WHERE slot_number = ?
    ");

    // jam/menit boleh NULL
    $stmt->bind_param("iiii", $jam, $menit, $aktif, $slot);

    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'DB update failed']);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Schedule updated',
        'data' => [
            'slot' => $slot,
            'jam' => $jam,
            'menit' => $menit,
            'aktif' => $aktif
        ]
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("[SCHEDULE ERROR] " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
