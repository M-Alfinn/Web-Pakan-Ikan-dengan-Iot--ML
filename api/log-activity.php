<?php
// API untuk logging aktivitas feeding
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("INSERT INTO log_aktivitas (sumber, bukaan_servo, suhu_air, pesan) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sids", $data['sumber'], $data['bukaan_servo'], $data['suhu_air'], $data['pesan']);

if ($stmt->execute()) {
    // Update daily stats
    $conn->query("CALL update_daily_stats()");
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
closeDBConnection($conn);
?>
