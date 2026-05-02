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

$action = isset($_GET['action']) ? $_GET['action'] : '';
$table = isset($_GET['table']) ? $_GET['table'] : '';

$allowed_tables = ['media_partner', 'staff_terbaik', 'berita_kabar'];

if (!$table || !in_array($table, $allowed_tables)) {
    echo json_encode(["error" => "Tabel tidak valid"]);
    exit;
}

// Tambahkan domain localhost agar React bisa merender gambar dengan absolute URL
$baseUrl = "http://api-ipbmengajar.rf.gd/backend/";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // READ
    try {
        $stmt = $pdo->prepare("SELECT * FROM $table ORDER BY id DESC");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($data as &$row) {
            if (!empty($row['file_gambar']) && !filter_var($row['file_gambar'], FILTER_VALIDATE_URL)) {
                 $row['file_gambar'] = $baseUrl . $row['file_gambar'];
            }
        }
        
        echo json_encode(["success" => true, "data" => $data]);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
} 
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CREATE ATAU UPDATE
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $file_gambar = "";
    $query_parts = [];
    $params = [];
    
    // Handle File Upload jika ada
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
        if ($id) {
            // UPDATE
            if ($table === 'media_partner') {
                if ($file_gambar) {
                    $stmt = $pdo->prepare("UPDATE media_partner SET file_gambar=? WHERE id=?");
                    $stmt->execute([$file_gambar, $id]);
                }
            } else if ($table === 'staff_terbaik') {
                $bulan = isset($_POST['bulan']) ? $_POST['bulan'] : '';
                if ($file_gambar) {
                    $stmt = $pdo->prepare("UPDATE staff_terbaik SET bulan=?, file_gambar=? WHERE id=?");
                    $stmt->execute([$bulan, $file_gambar, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE staff_terbaik SET bulan=? WHERE id=?");
                    $stmt->execute([$bulan, $id]);
                }
            } else if ($table === 'berita_kabar') {
                $kategori = isset($_POST['kategori']) ? $_POST['kategori'] : '';
                $tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';
                $judul = isset($_POST['judul']) ? $_POST['judul'] : '';
                $deskripsi = isset($_POST['deskripsi']) ? $_POST['deskripsi'] : '';
                
                if ($file_gambar) {
                    $stmt = $pdo->prepare("UPDATE berita_kabar SET kategori=?, tanggal=?, judul=?, deskripsi=?, file_gambar=? WHERE id=?");
                    $stmt->execute([$kategori, $tanggal, $judul, $deskripsi, $file_gambar, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE berita_kabar SET kategori=?, tanggal=?, judul=?, deskripsi=? WHERE id=?");
                    $stmt->execute([$kategori, $tanggal, $judul, $deskripsi, $id]);
                }
            }
            echo json_encode(["success" => true, "message" => "Data berhasil diupdate"]);
        } else {
            // CREATE
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
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id    = $input['id'] ?? ($_GET['id'] ?? null);

    if (!$id) {
        echo json_encode(["success" => false, "error" => "ID tidak diberikan"]);
        exit;
    }

    try {
        // ── STEP 1: Ambil nama file dari DB sebelum apapun dihapus ──────────
        $stmt = $pdo->prepare("SELECT file_gambar FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // ── STEP 2 & 3: Resolve path absolut, cek keberadaan file ──────────
        if ($row && !empty($row['file_gambar'])) {
            $savedPath = $row['file_gambar'];

            // Tolak jika tersimpan sebagai URL penuh (https://...) bukan path relatif
            if (!filter_var($savedPath, FILTER_VALIDATE_URL)) {
                // __DIR__ = folder backend/ tempat file PHP ini berada
                // path tersimpan di DB contoh: "uploads/1234_foto.jpg"
                $absolutePath = __DIR__ . '/' . ltrim($savedPath, '/');

                // ── STEP 4: Hapus file fisik dengan unlink() ────────────────
                if (file_exists($absolutePath)) {
                    if (!unlink($absolutePath)) {
                        // Log kegagalan unlink (tidak menghentikan proses delete DB)
                        error_log("[api_admin] Gagal unlink: $absolutePath");
                    }
                }
            }
        }

        // ── STEP 5: Baru hapus record dari database ─────────────────────────
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(["success" => true, "message" => "Data berhasil dihapus"]);

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}
?>
