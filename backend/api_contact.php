<?php
/**
 * api_contact.php
 * Handles contact form submission and sends email via PHPMailer + Gmail SMTP.
 *
 * Requires PHPMailer. Install via Composer:
 *   cd backend && composer require phpmailer/phpmailer
 *
 * Or download manually from: https://github.com/PHPMailer/PHPMailer
 * and place PHPMailer files in backend/PHPMailer/
 */

// ─── CORS Headers ────────────────────────────────────────────────────────────
// Allows requests from your Vite dev server and production domain
$allowed_origins = [
    "http://localhost:8080",
    "http://localhost:5173",
    "http://localhost:3000",
    // Add your production URL here, e.g.: "https://ipbmengajar.ipb.ac.id"
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request (browser sends this before POST)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ─── Only allow POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}

// ─── Parse JSON body from React fetch ────────────────────────────────────────
$body = json_decode(file_get_contents("php://input"), true);

$nama    = trim($body['nama']    ?? '');
$email   = trim($body['email']   ?? '');
$subjek  = trim($body['subjek']  ?? '');
$pesan   = trim($body['pesan']   ?? '');

// ─── Basic Validation ────────────────────────────────────────────────────────
$errors = [];
if (empty($nama))   $errors[] = "Nama tidak boleh kosong.";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                    $errors[] = "Email tidak valid.";
if (empty($subjek)) $errors[] = "Subjek tidak boleh kosong.";
if (empty($pesan))  $errors[] = "Pesan tidak boleh kosong.";

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => implode(" ", $errors)]);
    exit;
}

// ─── PHPMailer Setup ──────────────────────────────────────────────────────────
// If using Composer autoload:
require __DIR__ . '/vendor/autoload.php';

// If using manual PHPMailer (download and put in backend/PHPMailer/):
// require __DIR__ . '/PHPMailer/src/Exception.php';
// require __DIR__ . '/PHPMailer/src/PHPMailer.php';
// require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ─── SMTP Credentials — FILL THESE IN ────────────────────────────────────────
// This is the Gmail account YOU control (the "kurir" / sender account).
// It does NOT have to be the ipbmengajar@gmail.com account.
// Can be any Gmail you own with an App Password generated.
define('SMTP_USER',     'YOUR_GMAIL@gmail.com');      // ← Ganti dengan Gmail kamu
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx');        // ← Ganti dengan App Password Gmail
define('SMTP_FROM_NAME','IPB Mengajar — Website');

// ─── Recipient — hardcoded as requested ──────────────────────────────────────
define('MAIL_TO',       'ipbmengajar@gmail.com');
define('MAIL_TO_NAME',  'Admin IPB Mengajar');

// ─── Build HTML Email Body ───────────────────────────────────────────────────
$emailBody = "
<!DOCTYPE html>
<html lang='id'>
<head>
  <meta charset='UTF-8'>
  <style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background:#f4f4f4; margin:0; padding:0; }
    .wrap { max-width:600px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08); }
    .header { background:#1a3a1a; padding:32px 40px; }
    .header h1 { color:#f5c400; font-size:22px; margin:0; }
    .header p { color:rgba(255,255,255,0.6); font-size:13px; margin:6px 0 0; }
    .body { padding:32px 40px; }
    .field { margin-bottom:20px; }
    .field .label { font-size:11px; text-transform:uppercase; letter-spacing:0.1em; color:#888; font-weight:700; margin-bottom:6px; }
    .field .value { font-size:15px; color:#1a3a1a; font-weight:600; }
    .message-box { background:#f8f6f0; border-left:4px solid #f5c400; border-radius:8px; padding:16px 20px; color:#333; line-height:1.7; font-size:14px; white-space:pre-line; }
    .footer { background:#f8f6f0; padding:20px 40px; text-align:center; font-size:12px; color:#aaa; }
    .badge { display:inline-block; background:#f5c400; color:#1a3a1a; font-size:11px; font-weight:800; padding:4px 12px; border-radius:99px; margin-bottom:16px; }
  </style>
</head>
<body>
  <div class='wrap'>
    <div class='header'>
      <h1>📬 Pesan Baru dari Website</h1>
      <p>IPB Mengajar — Arkatara | " . date('d M Y, H:i') . " WIB</p>
    </div>
    <div class='body'>
      <div class='badge'>Formulir Kontak</div>
      <div class='field'>
        <div class='label'>Nama Pengirim</div>
        <div class='value'>" . htmlspecialchars($nama) . "</div>
      </div>
      <div class='field'>
        <div class='label'>Email Pengirim</div>
        <div class='value'>" . htmlspecialchars($email) . "</div>
      </div>
      <div class='field'>
        <div class='label'>Subjek</div>
        <div class='value'>" . htmlspecialchars($subjek) . "</div>
      </div>
      <div class='field'>
        <div class='label'>Isi Pesan</div>
        <div class='message-box'>" . htmlspecialchars($pesan) . "</div>
      </div>
    </div>
    <div class='footer'>
      💡 Balas email ini untuk langsung menjawab ke <strong>" . htmlspecialchars($email) . "</strong><br>
      IPB Mengajar Arkatara &copy; " . date('Y') . "
    </div>
  </div>
</body>
</html>
";

// ─── Send via PHPMailer ───────────────────────────────────────────────────────
$mail = new PHPMailer(true); // true = throw exceptions on error

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // FROM: Your SMTP Gmail (the "kurir")
    $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);

    // TO: IPB Mengajar Gmail (hardcoded recipient)
    $mail->addAddress(MAIL_TO, MAIL_TO_NAME);

    // REPLY-TO: The visitor's email (so admin can reply directly to them)
    $mail->addReplyTo($email, $nama);

    // Content
    $mail->isHTML(true);
    $mail->Subject = "[IPB Mengajar] " . $subjek . " — dari " . $nama;
    $mail->Body    = $emailBody;
    $mail->AltBody = "Pesan dari: $nama\nEmail: $email\nSubjek: $subjek\n\n$pesan";

    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "Pesan berhasil dikirim! Tim kami akan segera menghubungi Anda."
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengirim email. Silakan coba lagi.",
        // Remove 'debug' line below in production!
        "debug"   => $mail->ErrorInfo
    ]);
}
