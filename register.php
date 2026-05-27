<?php 
error_reporting(E_ERROR);
@ini_set('display_errors', '0');
require 'koneksi.php'; 
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    if (!$nama || !$email || !$password) {
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
    
    <form method="POST" novalidate>
      <div class="grup-form">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
      </div>
      <div class="grup-form">
        <label>Email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="grup-form">
        <label for="passwordRegister">Password</label>
        <div class="password-field">
          <input id="passwordRegister" type="password" name="password" required minlength="6">
          <button class="password-toggle" type="button" data-toggle-password aria-label="Tampilkan password" aria-pressed="false">
            <span aria-hidden="true">👁</span>
          </button>
        </div>
      </div>
      <div class="grup-form">
        <label for="passwordConfirmRegister">Konfirmasi Password</label>
        <div class="password-field">
          <input id="passwordConfirmRegister" type="password" name="password_confirm" required minlength="6">
          <button class="password-toggle" type="button" data-toggle-password aria-label="Tampilkan password" aria-pressed="false">
            <span aria-hidden="true">👁</span>
          </button>
        </div>
      </div>
      <button type="submit" class="tombol-utama auth-submit">Daftar</button>
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
    button.querySelector('span').textContent = show ? '◉' : '👁';
  });
});
</script>
</body>
</html>
