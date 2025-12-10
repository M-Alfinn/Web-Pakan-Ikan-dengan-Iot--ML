<?php
// Export logs to CSV
require_once '../config/database.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="log-aktivitas-' . date('Y-m-d') . '.csv"');

$conn = getDBConnection();
$result = $conn->query("SELECT * FROM log_aktivitas ORDER BY waktu_eksekusi DESC");

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Waktu Eksekusi', 'Sumber', 'Bukaan Servo', 'Suhu Air', 'Pesan']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['waktu_eksekusi'],
        $row['sumber'],
        $row['bukaan_servo'],
        $row['suhu_air'],
        $row['pesan']
    ]);
}

fclose($output);
closeDBConnection($conn);
?>
