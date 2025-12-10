<?php
// API Endpoint untuk ESP32 mengirim data sensor
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once '../config/database.php';

// Get POST data from ESP32
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

// Extract data
$suhu_air = $data['suhu_air'] ?? null;
$jenis_ikan = $data['jenis_ikan'] ?? null;
$umur_ikan = $data['umur_ikan'] ?? null;
$jumlah_ikan = $data['jumlah_ikan'] ?? null;
$pakan_per_bukaan = $data['pakan_per_bukaan'] ?? null;
$protein_percent = $data['protein_percent'] ?? null;
$lemak_percent = $data['lemak_percent'] ?? null;
$serat_percent = $data['serat_percent'] ?? null;

// Insert into database
$conn = getDBConnection();
$stmt = $conn->prepare("INSERT INTO sensor_data (suhu_air, jenis_ikan, umur_ikan, jumlah_ikan, pakan_per_bukaan, protein_percent, lemak_percent, serat_percent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("dsiidddd", $suhu_air, $jenis_ikan, $umur_ikan, $jumlah_ikan, $pakan_per_bukaan, $protein_percent, $lemak_percent, $serat_percent);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Data saved']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
closeDBConnection($conn);
?>
 