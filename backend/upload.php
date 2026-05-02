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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = isset($_POST['table']) ? $_POST['table'] : '';
    $allowed_tables = ['media_partner', 'staff_terbaik', 'berita_kabar'];

    if (!in_array($table, $allowed_tables)) {
        echo json_encode(["error" => "Tabel tidak valid"]);
        exit;
    }

    $file_gambar = "";
    
    // Handle File Upload
    if (isset($_FILES['file_gambar']) && $_FILES['file_gambar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", basename($_FILES['file_gambar']['name']));
        $targetFilePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['file_gambar']['tmp_name'], $targetFilePath)) {
            $file_gambar = $targetFilePath;
        } else {
            echo json_encode(["error" => "Gagal mengupload gambar"]);
            exit;
        }
    }

    try {
        if ($table === 'media_partner') {
            $stmt = $pdo->prepare("INSERT INTO media_partner (file_gambar) VALUES (?)");
            $stmt->execute([$file_gambar]);
            
        } else if ($table === 'staff_terbaik') {
            $bulan = isset($_POST['bulan']) ? $_POST['bulan'] : '';
            $stmt = $pdo->prepare("INSERT INTO staff_terbaik (bulan, file_gambar) VALUES (?, ?)");
            $stmt->execute([$bulan, $file_gambar]);
            
        } else if ($table === 'berita_kabar') {
            $kategori = isset($_POST['kategori']) ? $_POST['kategori'] : '';
            $tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : date('d M Y');
            $judul = isset($_POST['judul']) ? $_POST['judul'] : '';
            $deskripsi = isset($_POST['deskripsi']) ? $_POST['deskripsi'] : '';
            
            $stmt = $pdo->prepare("INSERT INTO berita_kabar (kategori, tanggal, judul, deskripsi, file_gambar) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$kategori, $tanggal, $judul, $deskripsi, $file_gambar]);
        }
        
        echo json_encode(["success" => true, "message" => "Data berhasil ditambahkan"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}
?>
