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
$resetId = (int)($_SESSION['reset_verified_id'] ?? 0);

if (!$email || !$userId || !$resetId) {
    $_SESSION['reset_error'] = 'Verifikasi OTP terlebih dahulu.';
    header("Location: forgot-password.php");
    exit;
}

$stmt = mysqli_prepare($conn, "
    SELECT id, expires_at, used
    FROM password_resets
    WHERE id = ? AND user_id = ? AND email = ?
    LIMIT 1
");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "iis", $resetId, $userId, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $reset = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    $reset = null;
}

if (!$reset || (int)$reset['used'] === 1 || strtotime($reset['expires_at']) < time()) {
    unset($_SESSION['reset_verified_id']);
    $_SESSION['reset_error'] = 'Sesi reset password sudah tidak valid. Minta kode OTP baru.';
    header("Location: forgot-password.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['password_confirm'] ?? '');

    if (!$password || !$confirm) {
        $error = 'Password baru dan konfirmasi wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        mysqli_begin_transaction($conn);
        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $hash, $userId);
            $updated = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $updated = false;
        }

        $stmt = mysqli_prepare($conn, "UPDATE password_resets SET used = 1 WHERE id = ? AND user_id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $resetId, $userId);
            $used = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $used = false;
        }

        if ($updated && $used) {
            mysqli_commit($conn);
            unset($_SESSION['reset_email'], $_SESSION['reset_user_id'], $_SESSION['reset_verified_id']);
            $_SESSION['flash_reset_password'] = 'berhasil';
            header("Location: login.php?reset=berhasil");
            exit;
        }

        mysqli_rollback($conn);
        $error = 'Gagal menyimpan password baru. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - StudyFlow</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=20260601-reset-password">
</head>
<body class="auth-page">
<div class="wadah-aplikasi auth-shell">
  <div class="panel auth-card">
    <div class="identitas-aplikasi auth-brand"><span class="ikon-aplikasi">SF</span><h2>StudyFlow</h2></div>
    <div class="auth-heading">
      <p class="teks-kecil">Password baru</p>
      <h3>Buat Kata Sandi Baru</h3>
      <p class="auth-helper">Gunakan password yang kuat dan belum pernah dipakai di tempat lain.</p>
    </div>

    <?php if ($error): ?><p class="notif-auth notif-auth-error"><?= e_reset($error) ?></p><?php endif; ?>

    <form method="POST" action="reset-password.php" autocomplete="on" data-loading-form>
      <div class="grup-form">
        <label for="passwordResetBaru">Password Baru</label>
        <div class="password-field">
          <input id="passwordResetBaru" type="password" name="password" required minlength="6" autocomplete="new-password">
        </div>
      </div>
      <div class="grup-form">
        <label for="passwordResetConfirm">Ulangi Password Baru</label>
        <div class="password-field">
          <input id="passwordResetConfirm" type="password" name="password_confirm" required minlength="6" autocomplete="new-password">
        </div>
      </div>
      <button type="submit" class="tombol-utama auth-submit" data-loading-text="Menyimpan...">Simpan Password</button>
    </form>
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
