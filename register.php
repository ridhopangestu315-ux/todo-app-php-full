<?php require 'koneksi.php'; 
if (isset($_SESSION['user_id'])) header("Location: index.php");

$error = '';
if ($_POST) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $password);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: login.php?daftar=berhasil");
        exit;
    } else {
        $error = "Email sudah digunakan!";
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
    <?php if($error): ?><p style="color:red;text-align:center"><?= $error ?></p><?php endif; ?>
    
    <form method="POST">
      <div class="grup-form"><label>Nama Lengkap</label><input type="text" name="nama" required></div>
      <div class="grup-form"><label>Email</label><input type="email" name="email" required></div>
      <div class="grup-form"><label>Password</label><input type="password" name="password" required minlength="6"></div>
      <button type="submit" class="tombol-utama" style="width:100%;margin-top:20px">Daftar</button>
    </form>
    <p style="text-align:center;margin-top:20px">Sudah punya akun? <a href="login.php">Login</a></p>
  </div>
</div>
</body>
</html>