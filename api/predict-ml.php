<?php
// API: Prediksi ML dari input user web — FINAL VERSION (NO JENIS IKAN)
ob_clean();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
    exit;
}

// Validasi field yang benar-benar dipakai ML
$required = [
    'umur_ikan',
    'jumlah_ikan',
    'pakan_per_bukaan',
    'protein_percent',
    'lemak_percent',
    'serat_percent',
    'suhu_air'
];

foreach ($required as $field) {
    if (!isset($data[$field]) || $data[$field] === '') {
        echo json_encode(['status' => 'error', 'message' => "$field wajib diisi"]);
        exit;
    }
}

// NORMALISASI KE FORMAT FLASK
$data_flask = [
    'jumlah_ikan'      => floatval($data['jumlah_ikan']),
    'umur_minggu'      => intval($data['umur_ikan']),
    'pakan_per_bukaan' => floatval($data['pakan_per_bukaan']),
    'protein_pct'      => floatval($data['protein_percent']),
    'lemak_pct'        => floatval($data['lemak_percent']),
    'serat_pct'        => floatval($data['serat_percent']),
    'suhu_c'           => floatval($data['suhu_air'])
];

// URL ML API kamu di Render
$flask_url = "https://ml-api-3-i4b0.onrender.com/predict";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $flask_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_flask));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($data_flask))
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code != 200) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal hubung ke ML API',
        'code' => $http_code,
        'debug' => $response
    ]);
    exit;
}

echo $response;
?>
