<?php
error_reporting(E_ERROR);
@ini_set('display_errors', '0');
require 'koneksi.php';
require 'password_reset_helpers.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

pastikanTabelPasswordResets($conn);

$email = $_SESSION['reset_email'] ?? '';
$userId = (int)($_SESSION['reset_user_id'] ?? 0);
if (!$email || !$userId) {
    $_SESSION['reset_error'] = 'Mulai reset password dari email akun kamu.';
    header("Location: forgot-password.php");
    exit;
}

$error = '';
$success = isset($_GET['sent']) ? 'Kode OTP sudah dikirim ke email kamu.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = preg_replace('/\D/', '', $_POST['otp'] ?? '');

    if (otpAttempts($email) >= 5) {
        $error = 'Percobaan OTP terlalu banyak. Minta kode baru untuk melanjutkan.';
    } elseif (!preg_match('/^\d{6}$/', $otp)) {
        tambahOtpAttempt($email);
        $error = 'Masukkan kode OTP 6 digit.';
    } else {
        $stmt = mysqli_prepare($conn, "
            SELECT id, otp, expires_at
            FROM password_resets
            WHERE user_id = ? AND email = ? AND used = 0
            ORDER BY id DESC
            LIMIT 1
        ");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "is", $userId, $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $reset = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
        } else {
            $reset = null;
        }

        if (!$reset) {
            $error = 'Kode OTP tidak ditemukan. Minta kode baru.';
        } elseif (strtotime($reset['expires_at']) < time()) {
            $error = 'Kode OTP sudah kadaluarsa. Minta kode baru.';
        } elseif (!password_verify($otp, $reset['otp'])) {
            tambahOtpAttempt($email);
            $sisa = max(0, 5 - otpAttempts($email));
            $error = $sisa ? 'Kode OTP salah. Sisa percobaan: ' . $sisa . '.' : 'Percobaan OTP habis. Minta kode baru.';
        } else {
            resetOtpAttempts($email);
            $_SESSION['reset_verified_id'] = (int)$reset['id'];
            header("Location: reset-password.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi OTP - StudyFlow</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=20260601-reset-password">
</head>
<body class="auth-page">
<div class="wadah-aplikasi auth-shell">
  <div class="panel auth-card">
    <div class="identitas-aplikasi auth-brand"><span class="ikon-aplikasi">SF</span><h2>StudyFlow</h2></div>
    <div class="auth-heading">
      <p class="teks-kecil">Verifikasi</p>
      <h3>Masukkan OTP</h3>
      <p class="auth-helper">Kode dikirim ke <?= e_reset($email) ?> dan berlaku selama 10 menit.</p>
    </div>

    <?php if ($success): ?><p class="notif-auth notif-auth-sukses"><?= e_reset($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="notif-auth notif-auth-error"><?= e_reset($error) ?></p><?php endif; ?>

    <form method="POST" action="verify-otp.php" autocomplete="one-time-code" data-loading-form>
      <div class="grup-form">
        <label for="inputOtp">Kode OTP</label>
        <input id="inputOtp" class="otp-input" type="text" name="otp" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="123456">
      </div>
      <button type="submit" class="tombol-utama auth-submit" data-loading-text="Memverifikasi...">Verifikasi OTP</button>
    </form>
    <p class="auth-switch"><a href="forgot-password.php">Minta kode baru</a></p>
  </div>
</div>
<script>
document.querySelectorAll('[data-loading-form]').forEach(function(form) {
  form.addEventListener('submit', function() {
    var button = form.querySelector('[type="submit"]');
    if (!button) return;
    button.disabled = true;
    button.textContent = button.dataset.loadingText || 'Memproses...';
  });
});
</script>
</body>
</html>
