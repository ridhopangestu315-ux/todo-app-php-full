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

$error = $_SESSION['reset_error'] ?? '';
$success = $_SESSION['reset_success'] ?? '';
$email = $_SESSION['reset_email_input'] ?? '';
unset($_SESSION['reset_error'], $_SESSION['reset_success'], $_SESSION['reset_email_input']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password - StudyFlow</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=20260601-reset-password">
</head>
<body class="auth-page">
<div class="wadah-aplikasi auth-shell">
  <div class="panel auth-card">
    <div class="identitas-aplikasi auth-brand">
      <span class="ikon-aplikasi">SF</span>
      <h2>StudyFlow</h2>
    </div>
    <div class="auth-heading">
      <p class="teks-kecil">Reset password</p>
      <h3>Lupa Kata Sandi?</h3>
      <p class="auth-helper">Masukkan email akun StudyFlow kamu. Kami akan mengirim kode OTP 6 digit.</p>
    </div>

    <?php if ($success): ?><p class="notif-auth notif-auth-sukses"><?= e_reset($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="notif-auth notif-auth-error"><?= e_reset($error) ?></p><?php endif; ?>

    <form method="POST" action="send-otp.php" autocomplete="on" data-loading-form>
      <div class="grup-form">
        <label for="emailReset">Email</label>
        <input id="emailReset" type="email" name="email" required autocomplete="username" value="<?= e_reset($email) ?>">
      </div>
      <button type="submit" class="tombol-utama auth-submit" data-loading-text="Mengirim OTP...">Kirim OTP</button>
    </form>
    <p class="auth-switch">Ingat password? <a href="login.php">Login</a></p>
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
