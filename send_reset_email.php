<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/vendor/autoload.php';

function studyflow_mail_config()
{
    $default = [
        'smtp_host'     => 'smtp.gmail.com',
        'smtp_port'     => 465,
        'smtp_secure'   => PHPMailer::ENCRYPTION_SMTPS,
        'smtp_username' => '',
        'smtp_password' => '',
        'from_email'    => '',
        'from_name'     => 'StudyFlow',
        'base_url'      => 'https://domain-saya.infinityfreeapp.com',
    ];

    $localConfig = __DIR__ . '/config.local.php';
    if (is_file($localConfig)) {
        $customConfig = require $localConfig;
        if (is_array($customConfig)) {
            $default = array_merge($default, $customConfig);
        }
    }

    // BUG FIX: Normalkan nilai smtp_secure dari string ke konstanta PHPMailer
    // config.local.php menyimpan 'ssl' atau 'tls' sebagai string,
    // tapi PHPMailer butuh PHPMailer::ENCRYPTION_SMTPS ('ssl') atau ENCRYPTION_STARTTLS ('tls').
    // Nilai string 'ssl' / 'tls' sebenarnya sudah cocok dengan konstanta PHPMailer,
    // tapi untuk konsistensi dan kejelasan, kita normalisasi di sini.
    $smtpSecure = strtolower((string)$default['smtp_secure']);
    if ($smtpSecure === 'ssl' || $smtpSecure === PHPMailer::ENCRYPTION_SMTPS) {
        $default['smtp_secure'] = PHPMailer::ENCRYPTION_SMTPS; // 'ssl'
    } elseif ($smtpSecure === 'tls' || $smtpSecure === PHPMailer::ENCRYPTION_STARTTLS) {
        $default['smtp_secure'] = PHPMailer::ENCRYPTION_STARTTLS; // 'tls'
    } else {
        // Fallback: port 465 = SSL, port 587 = TLS
        $default['smtp_secure'] = ((int)$default['smtp_port'] === 587)
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;
    }

    if (empty($default['from_email']) && !empty($default['smtp_username'])) {
        $default['from_email'] = $default['smtp_username'];
    }

    return $default;
}

function studyflow_base_url()
{
    $config = studyflow_mail_config();
    return rtrim((string)$config['base_url'], '/');
}

function kirimEmailResetPassword($emailTujuan, $namaTujuan, $token)
{
    $config = studyflow_mail_config();

    if (empty($config['smtp_username']) || empty($config['smtp_password']) || empty($config['from_email'])) {
        error_log('Konfigurasi SMTP StudyFlow belum lengkap.');
        return false;
    }

    $resetLink = studyflow_base_url() . '/reset_password.php?token=' . urlencode($token);
    $namaAman  = htmlspecialchars((string)$namaTujuan, ENT_QUOTES, 'UTF-8');
    $linkAman  = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $config['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port       = (int)$config['smtp_port'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($emailTujuan, $namaTujuan);

        $mail->isHTML(true);
        $mail->Subject = 'Reset Password StudyFlow';
        $mail->Body    = '
            <div style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
                <h2 style="margin-bottom: 12px;">Reset Password StudyFlow</h2>
                <p>Halo ' . $namaAman . ',</p>
                <p>Kami menerima permintaan untuk mengatur ulang password akun StudyFlow kamu.</p>
                <p>
                    <a href="' . $linkAman . '" style="display: inline-block; padding: 12px 18px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 8px;">
                        Reset Password
                    </a>
                </p>
                <p>Link ini berlaku selama 1 jam. Jika kamu tidak meminta reset password, abaikan email ini.</p>
                <p>Jika tombol tidak bisa dibuka, salin link berikut ke browser:</p>
                <p><a href="' . $linkAman . '">' . $linkAman . '</a></p>
            </div>
        ';
        $mail->AltBody = "Halo {$namaTujuan},\n\nBuka link berikut untuk reset password StudyFlow:\n{$resetLink}\n\nLink berlaku selama 1 jam. Jika kamu tidak meminta reset password, abaikan email ini.";

        return $mail->send();
    } catch (Exception $e) {
        error_log('Gagal mengirim email reset password: ' . $mail->ErrorInfo);
        return false;
    }
}