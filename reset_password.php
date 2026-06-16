<?php
error_reporting(E_ERROR);
@ini_set('display_errors', '0');

require 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function buatCsrfResetPassword()
{
    if (empty($_SESSION['reset_password_csrf'])) {
        $_SESSION['reset_password_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['reset_password_csrf'];
}

function passwordBaruValid($password)
{
    if (strlen($password) < 8) {
        return 'Password baru minimal 8 karakter.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password baru harus memiliki minimal 1 huruf besar.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        return 'Password baru harus memiliki minimal 1 huruf kecil.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'Password baru harus memiliki minimal 1 angka.';
    }
    return '';
}

function ambilResetPassword($conn, $token)
{
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
        return null;
    }

    $tokenHash = hash('sha256', $token);
    $stmt = mysqli_prepare($conn, "
        SELECT pr.id, pr.user_id, pr.expired_at, u.email
        FROM password_resets pr
        INNER JOIN users u ON u.id = pr.user_id
        WHERE pr.token = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, "s", $tokenHash);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        return null;
    }

    if (strtotime($row['expired_at']) < time()) {
        $resetId = (int)$row['id'];
        $hapus = mysqli_prepare($conn, "DELETE FROM password_resets WHERE id = ?");
        if ($hapus) {
            mysqli_stmt_bind_param($hapus, "i", $resetId);
            mysqli_stmt_execute($hapus);
            mysqli_stmt_close($hapus);
        }
        return null;
    }

    return $row;
}

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$resetData = ambilResetPassword($conn, $token);
$error = '';
$success = '';
$csrfTokenReset = buatCsrfResetPassword();

if (!$resetData) {
    $error = 'Link reset password tidak valid atau sudah kedaluwarsa.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
    $csrfPost = $_POST['reset_password_csrf'] ?? '';

    if (!$csrfPost || !hash_equals($_SESSION['reset_password_csrf'] ?? '', $csrfPost)) {
        $error = 'Sesi reset password sudah kedaluwarsa. Silakan buka ulang link reset.';
    } elseif ($password === '' || $passwordConfirm === '') {
        $error = 'Password baru dan konfirmasi password wajib diisi.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif ($pesanPassword = passwordBaruValid($password)) {
        $error = $pesanPassword;
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $userId = (int)$resetData['user_id'];
        $resetId = (int)$resetData['id'];

        mysqli_begin_transaction($conn);

        $deleteOk = false;
        $hapus = mysqli_prepare($conn, "DELETE FROM password_resets WHERE id = ? AND user_id = ?");
        if ($hapus) {
            mysqli_stmt_bind_param($hapus, "ii", $resetId, $userId);
            $deleteOk = mysqli_stmt_execute($hapus) && mysqli_stmt_affected_rows($hapus) === 1;
            mysqli_stmt_close($hapus);
        }

        $updateOk = false;
        if ($deleteOk) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $passwordHash, $userId);
                $updateOk = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        if ($updateOk && $deleteOk) {
            mysqli_commit($conn);
            unset($_SESSION['reset_password_csrf']);
            $success = 'Password berhasil diubah. Silakan login dengan password baru.';
            $resetData = null;
        } else {
            mysqli_rollback($conn);
            error_log('Gagal menyimpan password baru untuk user ID ' . $userId);
            $error = 'Terjadi kesalahan saat menyimpan password baru.';
        }
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
  <link rel="stylesheet" href="style.css?v=20260527-auth-modern">
</head>
<body class="auth-page">
<div class="wadah-aplikasi auth-shell">
  <div class="panel auth-card">
    <div class="identitas-aplikasi auth-brand">
      <img src="icon.png" alt="Logo StudyFlow" class="ikon-aplikasi">
      <h2>StudyFlow</h2>
    </div>
    <div class="auth-heading">
      <p class="teks-kecil">Pemulihan akun</p>
      <h3>Reset Password</h3>
    </div>

    <?php if ($success): ?>
      <p class="notif-auth notif-auth-sukses" role="alert"><?= e($success) ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
      <p class="notif-auth notif-auth-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($resetData): ?>
      <form id="formResetPassword" method="POST" action="reset_password.php" autocomplete="on" novalidate>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <input type="hidden" name="reset_password_csrf" value="<?= e($csrfTokenReset) ?>">
        <div class="grup-form">
          <label for="passwordReset">Password Baru</label>
          <div class="password-field">
            <input id="passwordReset" type="password" name="password" required minlength="8" autocomplete="new-password">
            <button class="password-toggle" type="button" data-toggle-password aria-label="Tampilkan password" aria-pressed="false">
              <span aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></span>
            </button>
          </div>
        </div>
        <div class="grup-form">
          <label for="passwordConfirmReset">Konfirmasi Password Baru</label>
          <div class="password-field">
            <input id="passwordConfirmReset" type="password" name="password_confirm" required minlength="8" autocomplete="new-password">
            <button class="password-toggle" type="button" data-toggle-password aria-label="Tampilkan password" aria-pressed="false">
              <span aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></span>
            </button>
          </div>
        </div>
        <button id="tombolSubmitReset" type="submit" class="tombol-utama auth-submit">Simpan Password Baru</button>
      </form>
    <?php endif; ?>

    <p class="auth-switch"><a href="login.php">Kembali ke login</a></p>
  </div>
</div>
<script>
document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
  button.addEventListener('click', function () {
    var input = button.closest('.password-field').querySelector('input');
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    button.setAttribute('aria-pressed', show ? 'true' : 'false');
    button.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    button.querySelector('span').innerHTML = show ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-6.5 0-10-8-10-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>' : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>';
  });
});
var formResetPassword = document.getElementById('formResetPassword');
var tombolSubmitReset = document.getElementById('tombolSubmitReset');
if (formResetPassword && tombolSubmitReset) {
  formResetPassword.addEventListener('submit', function (event) {
    if (formResetPassword.dataset.submitted === '1') {
      event.preventDefault();
      return;
    }
    formResetPassword.dataset.submitted = '1';
    tombolSubmitReset.disabled = true;
    tombolSubmitReset.textContent = 'Menyimpan...';
  });
}
</script>
</body>
</html>
