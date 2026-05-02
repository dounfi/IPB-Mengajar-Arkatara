<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {

        // ── READ ────────────────────────────────────────────────────────────
        case 'GET':
            $stmt = $pdo->prepare("SELECT * FROM agenda_kegiatan ORDER BY tanggal_sort ASC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $data]);
            break;

        // ── CREATE & UPDATE ─────────────────────────────────────────────────
        case 'POST':
            $input = json_decode(file_get_contents("php://input"), true);

            // Fallback ke $_POST kalau bukan JSON
            if (!$input) $input = $_POST;

            $id           = $input['id'] ?? null;
            $tanggal_sort = $input['tanggal_sort'] ?? '';
            $teks_tanggal = $input['teks_tanggal'] ?? '';
            $judul        = $input['judul'] ?? '';
            $deskripsi    = $input['deskripsi'] ?? '';

            if (empty($tanggal_sort) || empty($judul)) {
                echo json_encode(["success" => false, "error" => "tanggal_sort dan judul wajib diisi."]);
                exit;
            }

            if ($id) {
                // UPDATE
                $stmt = $pdo->prepare(
                    "UPDATE agenda_kegiatan SET tanggal_sort=?, teks_tanggal=?, judul=?, deskripsi=? WHERE id=?"
                );
                $stmt->execute([$tanggal_sort, $teks_tanggal, $judul, $deskripsi, $id]);
                echo json_encode(["success" => true, "message" => "Agenda berhasil diperbarui."]);
            } else {
                // CREATE
                $stmt = $pdo->prepare(
                    "INSERT INTO agenda_kegiatan (tanggal_sort, teks_tanggal, judul, deskripsi) VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([$tanggal_sort, $teks_tanggal, $judul, $deskripsi]);
                echo json_encode(["success" => true, "message" => "Agenda berhasil ditambahkan.", "id" => $pdo->lastInsertId()]);
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

            $stmt = $pdo->prepare("DELETE FROM agenda_kegiatan WHERE id=?");
            $stmt->execute([$id]);
            echo json_encode(["success" => true, "message" => "Agenda berhasil dihapus."]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["success" => false, "error" => "Method tidak didukung."]);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
