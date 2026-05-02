<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");
require_once 'koneksi.php';

$baseUrl   = "http://api-ipbmengajar.rf.gd/backend/";
$uploadDir = __DIR__ . '/uploads/';

// Helper: prepend base URL ke path gambar relatif
function buildUrl(string $path, string $base): string {
    if (!$path) return '';
    if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
    return $base . $path;
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {

        // ── READ ────────────────────────────────────────────────────────────
        case 'GET':
            $stmt = $pdo->prepare("SELECT * FROM mega_proker ORDER BY urutan ASC, id ASC");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r['file_gambar'] = buildUrl($r['file_gambar'], $baseUrl);
            }
            echo json_encode(["success" => true, "data" => $rows]);
            break;

        // ── CREATE & UPDATE ─────────────────────────────────────────────────
        case 'POST':
            $id          = $_POST['id']          ?? null;
            $nama_proker = $_POST['nama_proker']  ?? '';
            $subtitle    = $_POST['subtitle']     ?? '';
            $deskripsi   = $_POST['deskripsi']    ?? '';
            $accent      = $_POST['accent']       ?? 'green';
            $grad        = $_POST['grad']         ?? 'from-green-light via-green-medium to-green-dark';
            $urutan      = (int)($_POST['urutan'] ?? 0);

            // Validasi minimal
            if (empty($nama_proker)) {
                echo json_encode(["success" => false, "error" => "Nama proker wajib diisi."]);
                exit;
            }

            // Upload gambar (opsional)
            $file_gambar = null; // null = tidak mengubah gambar lama
            if (isset($_FILES['file_gambar']) && $_FILES['file_gambar']['error'] === UPLOAD_ERR_OK) {
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext      = strtolower(pathinfo($_FILES['file_gambar']['name'], PATHINFO_EXTENSION));
                $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (!in_array($ext, $allowed)) {
                    echo json_encode(["success" => false, "error" => "Format gambar tidak didukung."]);
                    exit;
                }
                $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", basename($_FILES['file_gambar']['name']));
                $target   = 'uploads/' . $fileName;
                if (!move_uploaded_file($_FILES['file_gambar']['tmp_name'], $uploadDir . $fileName)) {
                    echo json_encode(["success" => false, "error" => "Gagal mengupload gambar."]);
                    exit;
                }
                $file_gambar = $target;
            }

            if ($id) {
                // UPDATE — hapus gambar lama jika ada gambar baru
                if ($file_gambar !== null) {
                    $old = $pdo->prepare("SELECT file_gambar FROM mega_proker WHERE id=?");
                    $old->execute([$id]);
                    $oldRow = $old->fetch(PDO::FETCH_ASSOC);
                    if ($oldRow && !empty($oldRow['file_gambar']) && !filter_var($oldRow['file_gambar'], FILTER_VALIDATE_URL)) {
                        $oldPath = __DIR__ . '/' . $oldRow['file_gambar'];
                        if (file_exists($oldPath)) @unlink($oldPath);
                    }
                    $stmt = $pdo->prepare("UPDATE mega_proker SET nama_proker=?, subtitle=?, deskripsi=?, accent=?, grad=?, urutan=?, file_gambar=? WHERE id=?");
                    $stmt->execute([$nama_proker, $subtitle, $deskripsi, $accent, $grad, $urutan, $file_gambar, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE mega_proker SET nama_proker=?, subtitle=?, deskripsi=?, accent=?, grad=?, urutan=? WHERE id=?");
                    $stmt->execute([$nama_proker, $subtitle, $deskripsi, $accent, $grad, $urutan, $id]);
                }
                echo json_encode(["success" => true, "message" => "Mega Proker berhasil diperbarui."]);
            } else {
                // CREATE
                $fg = $file_gambar ?? '';
                $stmt = $pdo->prepare("INSERT INTO mega_proker (nama_proker, subtitle, deskripsi, accent, grad, urutan, file_gambar) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$nama_proker, $subtitle, $deskripsi, $accent, $grad, $urutan, $fg]);
                echo json_encode(["success" => true, "message" => "Mega Proker berhasil ditambahkan.", "id" => $pdo->lastInsertId()]);
            }
            break;

        // ── DELETE ──────────────────────────────────────────────────────────
        case 'DELETE':
            $input = json_decode(file_get_contents("php://input"), true);
            $id    = $input['id'] ?? null;
            if (!$id) {
                echo json_encode(["success" => false, "error" => "ID tidak valid."]);
                exit;
            }
            // Hapus file gambar fisik
            $row = $pdo->prepare("SELECT file_gambar FROM mega_proker WHERE id=?");
            $row->execute([$id]);
            $data = $row->fetch(PDO::FETCH_ASSOC);
            if ($data && !empty($data['file_gambar']) && !filter_var($data['file_gambar'], FILTER_VALIDATE_URL)) {
                $path = __DIR__ . '/' . $data['file_gambar'];
                if (file_exists($path)) @unlink($path);
            }
            $pdo->prepare("DELETE FROM mega_proker WHERE id=?")->execute([$id]);
            echo json_encode(["success" => true, "message" => "Mega Proker berhasil dihapus."]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["success" => false, "error" => "Method tidak didukung."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
