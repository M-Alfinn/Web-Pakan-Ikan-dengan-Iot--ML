<?php
// API untuk data chart suhu
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once '../config/database.php';

$conn = getDBConnection();
$result = $conn->query("SELECT suhu_air, timestamp FROM sensor_data ORDER BY timestamp DESC LIMIT 20");

$labels = [];
$values = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = date('H:i', strtotime($row['timestamp']));
    $values[] = floatval($row['suhu_air']);
}

closeDBConnection($conn);

echo json_encode([
    'labels' => array_reverse($labels),
    'values' => array_reverse($values)
]);
?>
