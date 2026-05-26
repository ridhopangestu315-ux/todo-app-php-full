<?php 
require 'koneksi.php'; 
if (isset($_SESSION['user_id'])) header("Location: index.php");

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    // Validasi
    if (!$nama || !$email || !$password) {
        $error = "Semua field wajib diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $password_confirm) {
        $error = "Password tidak cocok!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
        if (!$stmt) {
            $error = "Error: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $password_hash);
            
            if (mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($conn);
                // Buat settings default
                $stmt2 = mysqli_prepare($conn, "INSERT INTO settings (user_id, dark_mode, notifikasi) VALUES (?, 0, 1)");
                mysqli_stmt_bind_param($stmt2, "i", $user_id);
                mysqli_stmt_execute($stmt2);
                
                header("Location: login.php?daftar=berhasil");
                exit;
            } else {
                if (mysqli_errno($conn) == 1062) {
                    $error = "Email sudah terdaftar!";
                } else {
                    $error = "Terjadi kesalahan: " . mysqli_error($conn);
                }
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
  <title>Daftar - StudyFlow</title>
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
    <h3 style="text-align:center">Buat Akun Baru</h3>
    
    <?php if($error): ?>
      <p style="color:#e11d48;text-align:center;background:#ffe4e6;padding:12px;border-radius:8px;margin-bottom:16px">
        ✕ <?= htmlspecialchars($error) ?>
      </p>
    <?php endif; ?>
    
    <form method="POST">
      <div class="grup-form">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
      </div>
      <div class="grup-form">
        <label>Email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="grup-form">
        <label>Password</label>
        <input type="password" name="password" required minlength="6">
      </div>
      <div class="grup-form">
        <label>Konfirmasi Password</label>
        <input type="password" name="password_confirm" required minlength="6">
      </div>
      <button type="submit" class="tombol-utama" style="width:100%;margin-top:20px">Daftar</button>
    </form>
    <p style="text-align:center;margin-top:20px">Sudah punya akun? <a href="login.php">Login</a></p>
  </div>
</div>
</body>
</html>