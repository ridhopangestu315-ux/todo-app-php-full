<?php require 'koneksi.php'; 
if (isset($_SESSION['user_id'])) header("Location: index.php");

$error = '';
if ($_POST) {
    $email = trim($_POST['email']);
    $stmt = mysqli_prepare($conn, "SELECT id, nama, password FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            header("Location: index.php");
            exit;
        }
    }
    $error = "Email atau password salah!";
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
    <?php if($error): ?><p style="color:red;text-align:center"><?= $error ?></p><?php endif; ?>
    
    <form method="POST">
      <div class="grup-form"><label>Email</label><input type="email" name="email" required></div>
      <div class="grup-form"><label>Password</label><input type="password" name="password" required></div>
      <button type="submit" class="tombol-utama" style="width:100%;margin-top:20px">Login</button>
    </form>
    <p style="text-align:center;margin-top:20px">Belum punya akun? <a href="register.php">Daftar</a></p>
  </div>
</div>
</body>
</html>