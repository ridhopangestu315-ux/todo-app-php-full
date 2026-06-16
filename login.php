<?php 
error_reporting(E_ERROR);
@ini_set('display_errors', '0');
require 'koneksi.php'; 
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = isset($_GET['daftar']) && $_GET['daftar'] === 'berhasil';
if ($success) {
    unset($_SESSION['register_success_ready']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = "Email dan password wajib diisi!";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, nama, password FROM users WHERE email = ?");
        if (!$stmt) {
            $error = "Terjadi kesalahan sistem.";
        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($user = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['flash_login'] = 'berhasil';
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Password salah!";
                }
            } else {
                $error = "Email tidak terdaftar. Silakan daftar terlebih dahulu.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - StudyFlow</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=20260527-auth-modern">
</head>
<body class="auth-page">
<div class="wadah-aplikasi auth-shell">
  <div class="panel auth-card">
    <div class="identitas-aplikasi auth-brand">
      <img src="icon.PNG" alt="Logo StudyFlow" class="ikon-aplikasi">
      <h2>StudyFlow</h2>
    </div>
    <div class="auth-heading">
      <p class="teks-kecil">Student workspace</p>
      <h3>Masuk ke Akun</h3>
    </div>
    
    <?php if($success): ?>
      <p class="notif-auth notif-auth-sukses" role="alert">
        ✓ Akun berhasil dibuat! Silakan login.
      </p>
    <?php endif; ?>

    <?php if($error): ?>
      <p class="notif-auth notif-auth-error" role="alert">
        ✕ <?= htmlspecialchars($error) ?>
      </p>
    <?php endif; ?>
    
    <form id="formLogin" method="POST" action="login.php" autocomplete="on" novalidate>
      <div class="grup-form">
        <label for="emailLogin">Email</label>
        <input id="emailLogin" type="email" name="email" required autocomplete="username" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="grup-form">
        <label for="passwordLogin">Password</label>
        <div class="password-field">
          <input id="passwordLogin" type="password" name="password" required autocomplete="current-password">
          <button class="password-toggle" type="button" data-toggle-password aria-label="Tampilkan password" aria-pressed="false">
            <span aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></span>
          </button>
        </div>
      </div>
      <button id="tombolSubmitLogin" type="submit" class="tombol-utama auth-submit">Login</button>
    </form>
    <p class="auth-switch"><a href="forgot_password.php">Lupa password?</a></p>
    <p class="auth-switch">Belum punya akun? <a href="register.php">Daftar</a></p>
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
var formLogin = document.getElementById('formLogin');
var tombolSubmitLogin = document.getElementById('tombolSubmitLogin');
if (formLogin && tombolSubmitLogin) {
  formLogin.addEventListener('submit', function (event) {
    if (formLogin.dataset.submitted === '1') {
      event.preventDefault();
      return;
    }
    formLogin.dataset.submitted = '1';
    tombolSubmitLogin.disabled = true;
    tombolSubmitLogin.textContent = 'Memproses...';
  });
}
</script>
</body>
</html>
