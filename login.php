<?php 
require 'koneksi.php'; 
if (isset($_SESSION['user_id'])) header("Location: index.php");

$error = '';
$success = isset($_GET['daftar']) && $_GET['daftar'] === 'berhasil';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = "Email dan password wajib diisi!";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, nama, password FROM users WHERE email = ?");
        if (!$stmt) {
            $error = "Error: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($user = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama'] = $user['nama'];
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Email atau password salah!";
                }
            } else {
                $error = "Email atau password salah!";
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
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wadah-aplikasi" style="min-height:100vh;display:flex;align-items:center;justify-content:center">
  <div class="panel" style="width:380px">
    <div class="identitas-aplikasi" style="justify-content:center">
      <span class="ikon-aplikasi">SF</span>
      <h2>StudyFlow</h2>
    </div>
    <h3 style="text-align:center">Masuk ke Akun</h3>
    
    <?php if($success): ?>
      <p style="color:#16a34a;text-align:center;background:#dcfce7;padding:12px;border-radius:8px;margin-bottom:16px">
        ✓ Akun berhasil dibuat! Silakan login.
      </p>
    <?php endif; ?>
    
    <?php if($error): ?>
      <p style="color:#e11d48;text-align:center;background:#ffe4e6;padding:12px;border-radius:8px;margin-bottom:16px">
        ✕ <?= htmlspecialchars($error) ?>
      </p>
    <?php endif; ?>
    
    <form method="POST">
      <div class="grup-form">
        <label>Email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="grup-form">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="tombol-utama" style="width:100%;margin-top:20px">Login</button>
    </form>
    <p style="text-align:center;margin-top:20px">Belum punya akun? <a href="register.php">Daftar</a></p>
  </div>
</div>
</body>
</html>