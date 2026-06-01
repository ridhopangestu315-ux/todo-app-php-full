<?php
error_reporting(E_ERROR);
@ini_set('display_errors', '0');
require 'koneksi.php';
require 'password_reset_helpers.php';
require 'smtp_config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot-password.php");
    exit;
}

pastikanTabelPasswordResets($conn);

$email = trim(strtolower($_POST['email'] ?? ''));
$_SESSION['reset_email_input'] = $email;

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['reset_error'] = 'Masukkan alamat email yang valid.';
    header("Location: forgot-password.php");
    exit;
}

$user = ambilUserResetByEmail($conn, $email);
if (!$user) {
    $_SESSION['reset_error'] = 'Email tidak terdaftar di StudyFlow.';
    header("Location: forgot-password.php");
    exit;
}

$otp = (string)random_int(100000, 999999);
$otpHash = password_hash($otp, PASSWORD_DEFAULT);
$expiresAt = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

tandaiOtpAktifSebagaiUsed($conn, (int)$user['id'], $email);

$stmt = mysqli_prepare($conn, "
    INSERT INTO password_resets (user_id, email, otp, expires_at, used)
    VALUES (?, ?, ?, ?, 0)
");
if (!$stmt) {
    $_SESSION['reset_error'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
    header("Location: forgot-password.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "isss", $user['id'], $email, $otpHash, $expiresAt);
$saved = mysqli_stmt_execute($stmt);
$resetId = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

if (!$saved) {
    $_SESSION['reset_error'] = 'Gagal membuat kode OTP. Silakan coba lagi.';
    header("Location: forgot-password.php");
    exit;
}

try {
    kirimEmailOtpResetPassword($email, $user['nama'], $otp);
} catch (Throwable $e) {
    $stmt = mysqli_prepare($conn, "UPDATE password_resets SET used = 1 WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $resetId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    $_SESSION['reset_error'] = 'OTP gagal dikirim. Periksa konfigurasi SMTP aplikasi.';
    header("Location: forgot-password.php");
    exit;
}

$_SESSION['reset_email'] = $email;
$_SESSION['reset_user_id'] = (int)$user['id'];
resetOtpAttempts($email);
unset($_SESSION['reset_email_input']);

header("Location: verify-otp.php?sent=1");
exit;
?>
