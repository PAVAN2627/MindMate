<?php
/* ------------ Database connection --------------- */
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'mindmate';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_errno) {
    die("DB connection failed: " . $conn->connect_error);
}

/* ------------- PHPMailer SMTP settings ----------- */
require_once __DIR__ . '/../vendor/autoload.php'; // adjust path if needed
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTP(string $toEmail, string $toName, string $otp): bool
{
    $mail = new PHPMailer(true);
    try {
        /* SMTP server */
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';   // e.g. smtp.gmail.com or smtp.office365.com
        $mail->Port       = 587;
        $mail->SMTPAuth   = true;
        $mail->Username   = '';   // <-- your SMTP username
        $mail->Password   = 'x';        // <-- your SMTP password or app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 

        /* Message */
       $mail->setFrom('pavanmalith3@gmail.com', 'MindMate');  // safer

        $mail->addAddress($toEmail, $toName);
        $mail->Subject = 'Your MindMate OTP Code';
        $mail->Body    = "Hi $toName,\n\nYour OTP code is: $otp\nIt expires in 10 minutes.\n\n— MindMate";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
/** Generic mail helper (reuse for OTP + reports) */
function sendEmail(string $to, string $toName, string $subject, string $body): bool
{
    try {
        $mail = new PHPMailer(true);

        /* SMTP */
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '';
        $mail->Password   = '';          // Gmail app‑password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        /* Sender / Recipient */
        $mail->setFrom('pavanmalith3@gmail.com', 'MindMate');
        $mail->addAddress($to, $toName);

        /* ✨ KEY FIXES ✨ */
        $mail->CharSet = 'UTF-8';   // 1️⃣  UTF‑8 emoji & accents
        $mail->isHTML(true);        // 2️⃣  Treat $body as HTML

        /* Content */
        $mail->Subject = $subject;
        $mail->Body    = $body;                     // HTML body
        $mail->AltBody = strip_tags($body);         // Plain‑text fallback

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        return false;
    }
}
