<?php
// API untuk update mode sistem — Versi FINAL FIX
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['mode']) || !isset($data['value'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$mode = $data['mode'];
$value = intval($data['value']);

$conn = getDBConnection();

// Daftar mode → kolom valid di database
$modeMap = [
    'v7' => 'mode_auto_v7',
    'ml' => 'mode_ml',
    'esp32_connected' => 'esp32_connected',
    'feed_now' => 'feed_now'
];

// Jika mode tidak valid
if (!isset($modeMap[$mode])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid mode: ' . $mode,
        'valid_modes' => array_keys($modeMap)
    ]);
    closeDBConnection($conn);
    exit;
}

$columnName = $modeMap[$mode];

// // ---------------------------------------------------------
// // MODE KHUSUS: FEED_NOW
// // ---------------------------------------------------------
// if ($mode === "feed_now") {
//     // Set feed_now = 1 → nanti ESP32 reset ke 0
//     $stmt = $conn->prepare("
//         UPDATE system_status 
//         SET feed_now = 1, updated_at = NOW()
//         WHERE id = 1
//     ");
//     $stmt->execute();
//     $stmt->close();

//     echo json_encode([
//         'status' => 'success',
//         'message' => 'Feed Now triggered'
//     ]);
//     closeDBConnection($conn);
//     exit;
// }


// ---------------------------------------------------------
// MODE KHUSUS: FEED_NOW — BISA 1 (trigger) atau 0 (reset)
// ---------------------------------------------------------
if ($mode === "feed_now") {
    // Izinkan nilai 0 (reset) atau 1 (trigger)
    $stmt = $conn->prepare("
        UPDATE system_status 
        SET feed_now = ?, updated_at = NOW()
        WHERE id = 1
    ");
    $stmt->bind_param("i", $value);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => $value === 1 
            ? 'Feed Now triggered' 
            : 'Feed Now reset'
    ]);
    closeDBConnection($conn);
    exit;
}
// ---------------------------------------------------------
// MODE KHUSUS: ML → otomatis matikan V7
// ---------------------------------------------------------
if ($mode === "ml") {
    $stmt = $conn->prepare("
        UPDATE system_status
        SET mode_ml = ?, mode_auto_v7 = 0, updated_at = NOW()
        WHERE id = 1
    ");
    $stmt->bind_param("i", $value);
    $stmt->execute();
    $stmt->close();

    // Ambil status terbaru
    $result = $conn->query("SELECT * FROM system_status LIMIT 1");
    $status = $result->fetch_assoc();

    echo json_encode([
        'status' => 'success',
        'message' => 'Mode ML updated',
        'data' => $status
    ]);

    closeDBConnection($conn);
    exit;
}

// ---------------------------------------------------------
// MODE BIASA (v7, esp32_connected)
// ---------------------------------------------------------
$stmt = $conn->prepare("
    UPDATE system_status 
    SET $columnName = ?, updated_at = NOW()
    WHERE id = 1
");
$stmt->bind_param("i", $value);

if ($stmt->execute()) {
    // Ambil status terbaru
    $result = $conn->query("SELECT * FROM system_status LIMIT 1");
    $status = $result->fetch_assoc();

    echo json_encode([
        'status' => 'success',
        'data' => $status
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => $stmt->error
    ]);
}

$stmt->close();
closeDBConnection($conn);
?>
