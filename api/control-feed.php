<?php
// API untuk kontrol manual feeding
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['bukaan'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bukaan not specified']);
    exit;
}

$bukaan = intval($data['bukaan']);

$conn = getDBConnection();

// 1️⃣ Log aktivitas
$stmt = $conn->prepare("INSERT INTO log_aktivitas (sumber, bukaan_servo, pesan) VALUES ('Manual Web', ?, 'Feeding manual dari website')");
$stmt->bind_param("i", $bukaan);
$stmt->execute();
$stmt->close();

// 2️⃣ 🔥 Set flag untuk ESP32: feed_now = jumlah bukaan
$stmt2 = $conn->prepare("UPDATE system_status SET feed_now = ?");
$stmt2->bind_param("i", $bukaan);
$stmt2->execute();
$stmt2->close();

closeDBConnection($conn);

echo json_encode([
    'status' => 'success',
    'message' => 'Feed command queued',
    'bukaan' => $bukaan
]);
?>