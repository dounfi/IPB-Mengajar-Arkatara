<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");

require_once 'koneksi.php';

$table = isset($_GET['table']) ? $_GET['table'] : '';
$allowed_tables = ['media_partner', 'staff_terbaik', 'berita_kabar'];

if (!in_array($table, $allowed_tables)) {
    echo json_encode(["error" => "Tabel tidak valid"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM $table ORDER BY id DESC");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tambahkan domain localhost agar React bisa merender gambar dengan absolute URL
    $baseUrl = "http://api-ipbmengajar.rf.gd/backend/";
    
    foreach ($data as &$row) {
        if (!empty($row['file_gambar']) && !filter_var($row['file_gambar'], FILTER_VALIDATE_URL)) {
             $row['file_gambar'] = $baseUrl . $row['file_gambar'];
        }
    }
    
    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
