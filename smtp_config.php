<?php
use PHPMailer\PHPMailer\PHPMailer;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/*
|--------------------------------------------------------------------------
| Konfigurasi SMTP StudyFlow
|--------------------------------------------------------------------------
| Isi nilai di bawah sesuai provider email kamu.
| Gmail: host smtp.gmail.com, port 587, encryption tls.
| Brevo: host smtp-relay.brevo.com, port 587, encryption tls.
*/
const STUDYFLOW_SMTP_HOST = 'smtp.gmail.com';
const STUDYFLOW_SMTP_PORT = 587;
const STUDYFLOW_SMTP_USERNAME = 'email-kamu@gmail.com';
const STUDYFLOW_SMTP_PASSWORD = 'app-password-atau-smtp-key';
const STUDYFLOW_SMTP_ENCRYPTION = 'tls';
const STUDYFLOW_MAIL_FROM = 'email-kamu@gmail.com';
const STUDYFLOW_MAIL_FROM_NAME = 'StudyFlow';

function studyflowMailer() {
    if (!class_exists(PHPMailer::class)) {
        throw new RuntimeException('PHPMailer belum terpasang. Jalankan: composer require phpmailer/phpmailer');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = STUDYFLOW_SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = STUDYFLOW_SMTP_USERNAME;
    $mail->Password = STUDYFLOW_SMTP_PASSWORD;
    $mail->SMTPSecure = STUDYFLOW_SMTP_ENCRYPTION;
    $mail->Port = STUDYFLOW_SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom(STUDYFLOW_MAIL_FROM, STUDYFLOW_MAIL_FROM_NAME);

    return $mail;
}

function kirimEmailOtpResetPassword($tujuanEmail, $namaUser, $otp) {
    $mail = studyflowMailer();
    $mail->addAddress($tujuanEmail, $namaUser ?: $tujuanEmail);
    $mail->isHTML(true);
    $mail->Subject = 'Kode Verifikasi Reset Password StudyFlow';
    $mail->Body = '
      <div style="font-family:Inter,Arial,sans-serif;background:#f5f7fb;padding:24px;color:#1f2937">
        <div style="max-width:520px;margin:auto;background:#fff;border:1px solid #e5e7eb;border-radius:18px;overflow:hidden">
          <div style="background:linear-gradient(135deg,#2563eb,#7c3aed);padding:24px;color:#fff">
            <h2 style="margin:0;font-size:22px">StudyFlow</h2>
            <p style="margin:8px 0 0;opacity:.9">Reset kata sandi akun kamu</p>
          </div>
          <div style="padding:24px">
            <p>Halo ' . htmlspecialchars($namaUser ?: 'pengguna', ENT_QUOTES, 'UTF-8') . ',</p>
            <p>Gunakan kode OTP berikut untuk melanjutkan reset password StudyFlow:</p>
            <div style="margin:22px 0;padding:18px;border-radius:14px;background:#eef2ff;text-align:center">
              <div style="font-size:34px;font-weight:800;letter-spacing:6px;color:#4f46e5">' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</div>
            </div>
            <p>Kode ini berlaku selama <strong>10 menit</strong>. Abaikan email ini jika kamu tidak meminta reset password.</p>
            <p style="margin-top:24px;color:#6b7280;font-size:13px">Email ini dikirim otomatis oleh StudyFlow.</p>
          </div>
        </div>
      </div>';
    $mail->AltBody = "Kode OTP reset password StudyFlow kamu: {$otp}. Kode berlaku selama 10 menit.";
    $mail->send();
}
?>
