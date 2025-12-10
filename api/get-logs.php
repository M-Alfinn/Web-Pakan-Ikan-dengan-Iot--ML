<?php
// API untuk DataTables log aktivitas
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once '../config/database.php';

$conn = getDBConnection();
$result = $conn->query("SELECT * FROM log_aktivitas ORDER BY waktu_eksekusi DESC LIMIT 1000");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

closeDBConnection($conn);

echo json_encode([
    'data' => $data
]);
?>
