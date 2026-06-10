<?php
ob_start();
error_reporting(E_ERROR);
@ini_set('display_errors', '0');
require 'koneksi.php'; 
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && empty($_SESSION['register_token'])) {
    $_SESSION['register_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');
    $register_token = $_POST['register_token'] ?? '';

    if (!$register_token || empty($_SESSION['register_token']) || !hash_equals($_SESSION['register_token'], $register_token)) {
        if (!empty($_SESSION['register_success_ready'])) {
            unset($_SESSION['register_success_ready']);
            header("Location: login.php?daftar=berhasil");
            exit;
        }
        $error = "Sesi pendaftaran sudah kedaluwarsa. Silakan coba lagi.";
    } elseif (!$nama || !$email || !$password || !$password_confirm) {
        $error = "Semua field wajib diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $password_confirm) {
        $error = "Password tidak cocok!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Cek email duplikat terlebih dahulu sebelum INSERT
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        if ($cek) {
            mysqli_stmt_bind_param($cek, "s", $email);
            mysqli_stmt_execute($cek);
            mysqli_stmt_store_result($cek);
            if (mysqli_stmt_num_rows($cek) > 0) {
                $error = "Email sudah digunakan. Coba email lain atau login.";
            }
            mysqli_stmt_close($cek);
        }

        if (!$error) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
            if (!$stmt) {
                $error = "Terjadi kesalahan sistem.";
            } else {
                mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $password_hash);
                
                if (mysqli_stmt_execute($stmt)) {
                    $user_id = mysqli_insert_id($conn);
                    mysqli_stmt_close($stmt);
                    // Buat settings default
                    $stmt2 = mysqli_prepare($conn, "INSERT INTO settings (user_id, dark_mode, notifikasi) VALUES (?, 0, 1)");
                    if ($stmt2) {
                        mysqli_stmt_bind_param($stmt2, "i", $user_id);
                        mysqli_stmt_execute($stmt2);
                        mysqli_stmt_close($stmt2);
                    }
                    
                    unset($_SESSION['register_token']);
                    $_SESSION['register_success_ready'] = true;
                    header("Location: login.php?daftar=berhasil");
                    exit;
                } else {
                    $errno = mysqli_errno($conn);
                    mysqli_stmt_close($stmt);
                    if ($errno == 1062) {
                        $error = "Email sudah digunakan. Coba email lain atau login.";
                    } else {
                        $error = "Terjadi kesalahan, silakan coba lagi.";
                    }
                }
            }
        }
    }
}
if (empty($_SESSION['register_token'])) {
    $_SESSION['register_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar - StudyFlow</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=20260527-auth-modern">
</head>
<body class="auth-page">
<div class="wadah-aplikasi auth-shell">
  <div class="panel auth-card">
    <div class="identitas-aplikasi auth-brand">
      <span class="ikon-aplikasi">SF</span>
      <h2>StudyFlow</h2>
    </div>
    <div class="auth-heading">
      <p class="teks-kecil">Mulai workspace kamu</p>
      <h3>Buat Akun Baru</h3>
    </div>
    
    <?php if($error): ?>
      <p class="notif-auth notif-auth-error" role="alert">
        ✕ <?= htmlspecialchars($error) ?>
      </p>
    <?php endif; ?>
    
    <form id="formRegister" method="POST" action="register.php" autocomplete="on" novalidate>
      <input type="hidden" name="register_token" value="<?= htmlspecialchars($_SESSION['register_token']) ?>">
      <div class="grup-form">
        <label for="namaRegister">Nama Lengkap</label>
        <input id="namaRegister" type="text" name="nama" required autocomplete="name" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
      </div>
      <div class="grup-form">
        <label for="emailRegister">Email</label>
        <input id="emailRegister" type="email" name="email" required autocomplete="username" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="grup-form">
        <label for="passwordRegister">Password</label>
        <div class="password-field">
          <input id="passwordRegister" type="password" name="password" required minlength="6" autocomplete="new-password">
          <button class="password-toggle" type="button" data-toggle-password aria-label="Tampilkan password" aria-pressed="false">
            <span aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></span>
          </button>
        </div>
      </div>
      <div class="grup-form">
        <label for="passwordConfirmRegister">Konfirmasi Password</label>
        <div class="password-field">
          <input id="passwordConfirmRegister" type="password" name="password_confirm" required minlength="6" autocomplete="new-password">
          <button class="password-toggle" type="button" data-toggle-password aria-label="Tampilkan password" aria-pressed="false">
            <span aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></span>
          </button>
        </div>
      </div>
      <button id="tombolSubmitRegister" type="submit" class="tombol-utama auth-submit">Daftar</button>
    </form>
    <p class="auth-switch">Sudah punya akun? <a href="login.php">Login</a></p>
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
var formRegister = document.getElementById('formRegister');
var tombolSubmitRegister = document.getElementById('tombolSubmitRegister');
if (formRegister && tombolSubmitRegister) {
  formRegister.addEventListener('submit', function (event) {
    if (formRegister.dataset.submitted === '1') {
      event.preventDefault();
      return;
    }
    formRegister.dataset.submitted = '1';
    tombolSubmitRegister.disabled = true;
    tombolSubmitRegister.textContent = 'Memproses...';
  });
}
</script>
</body>
</html>
