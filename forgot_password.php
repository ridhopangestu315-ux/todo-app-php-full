<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'koneksi.php';
require 'send_reset_email.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function ipKlien()
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return substr($ip, 0, 45);
}

function hashIdentitas($value)
{
    return hash('sha256', strtolower(trim((string)$value)));
}

function kolomTabelAda($conn, $table, $column)
{
    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");

    if (!$stmt) {
        error_log('Gagal menyiapkan pengecekan kolom tabel.');
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ss", $table, $column);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return (int)($row['total'] ?? 0) > 0;
}

function indeksTabelAda($conn, $table, $index)
{
    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND INDEX_NAME = ?
    ");

    if (!$stmt) {
        error_log('Gagal menyiapkan pengecekan indeks tabel.');
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ss", $table, $index);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return (int)($row['total'] ?? 0) > 0;
}

function pastikanTabelResetPassword($conn)
{
    // BUG FIX: Deteksi tabel password_resets dengan struktur lama (ada kolom 'email',
    // atau tidak punya user_id/token/expired_at). Drop dan buat ulang karena
    // token lama sudah tidak valid (expired).
    $cekTabelResets = mysqli_query($conn, "SHOW TABLES LIKE 'password_resets'");
    if ($cekTabelResets && mysqli_num_rows($cekTabelResets) > 0) {
        $strukturSalah = kolomTabelAda($conn, 'password_resets', 'email')
                      || !kolomTabelAda($conn, 'password_resets', 'user_id')
                      || !kolomTabelAda($conn, 'password_resets', 'token')
                      || !kolomTabelAda($conn, 'password_resets', 'expired_at');
        if ($strukturSalah) {
            mysqli_query($conn, "DROP TABLE IF EXISTS password_resets");
            error_log('Tabel password_resets strukturnya tidak sesuai, dibuat ulang.');
        }
    }

    $buatPasswordResets = mysqli_query($conn, "
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            token CHAR(64) NOT NULL,
            expired_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY uniq_password_reset_user (user_id),
            UNIQUE KEY uniq_password_reset_token (token),
            INDEX idx_user_id (user_id),
            INDEX idx_expired_at (expired_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    if (!$buatPasswordResets) {
        error_log('Gagal membuat tabel password_resets: ' . mysqli_error($conn));
    }

    if (!kolomTabelAda($conn, 'password_resets', 'user_id')) {
        mysqli_query($conn, "ALTER TABLE password_resets ADD COLUMN user_id INT NULL AFTER id");
    }

    if (!kolomTabelAda($conn, 'password_resets', 'token')) {
        mysqli_query($conn, "ALTER TABLE password_resets ADD COLUMN token CHAR(64) NULL AFTER user_id");
    }

    if (!kolomTabelAda($conn, 'password_resets', 'expired_at')) {
        mysqli_query($conn, "ALTER TABLE password_resets ADD COLUMN expired_at DATETIME NULL AFTER token");
    }

    if (!kolomTabelAda($conn, 'password_resets', 'created_at')) {
        mysqli_query($conn, "ALTER TABLE password_resets ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER expired_at");
    }

    if (kolomTabelAda($conn, 'password_resets', 'expires_at')) {
        mysqli_query($conn, "UPDATE password_resets SET expired_at = expires_at WHERE expired_at IS NULL");
    }

    if (!indeksTabelAda($conn, 'password_resets', 'idx_user_id')) {
        mysqli_query($conn, "ALTER TABLE password_resets ADD INDEX idx_user_id (user_id)");
    }

    if (!indeksTabelAda($conn, 'password_resets', 'idx_expired_at')) {
        mysqli_query($conn, "ALTER TABLE password_resets ADD INDEX idx_expired_at (expired_at)");
    }

    if (!indeksTabelAda($conn, 'password_resets', 'uniq_password_reset_user')) {
        mysqli_query($conn, "
            DELETE pr1 FROM password_resets pr1
            INNER JOIN password_resets pr2
            WHERE pr1.id > pr2.id AND pr1.user_id = pr2.user_id
        ");
        mysqli_query($conn, "ALTER TABLE password_resets ADD UNIQUE KEY uniq_password_reset_user (user_id)");
    }

    // BUG FIX: Deteksi tabel password_reset_attempts dengan struktur lama.
    // Drop dan buat ulang karena isinya hanya data rate-limit sementara (15 menit).
    $cekTabelAttempts = mysqli_query($conn, "SHOW TABLES LIKE 'password_reset_attempts'");
    if ($cekTabelAttempts && mysqli_num_rows($cekTabelAttempts) > 0) {
        $strukturSalah = !kolomTabelAda($conn, 'password_reset_attempts', 'email_hash')
                      || !kolomTabelAda($conn, 'password_reset_attempts', 'ip_hash');
        if ($strukturSalah) {
            mysqli_query($conn, "DROP TABLE IF EXISTS password_reset_attempts");
            error_log('Tabel password_reset_attempts strukturnya tidak sesuai, dibuat ulang.');
        }
    }

    $buatAttempts = mysqli_query($conn, "
        CREATE TABLE IF NOT EXISTS password_reset_attempts (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email_hash CHAR(64) NOT NULL DEFAULT '',
            ip_hash CHAR(64) NOT NULL DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email_hash_created_at (email_hash, created_at),
            INDEX idx_ip_hash_created_at (ip_hash, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    if (!$buatAttempts) {
        error_log('Gagal membuat tabel password_reset_attempts: ' . mysqli_error($conn));
    }

    if (!kolomTabelAda($conn, 'password_reset_attempts', 'email_hash')) {
        mysqli_query($conn, "ALTER TABLE password_reset_attempts ADD COLUMN email_hash CHAR(64) NOT NULL DEFAULT '' AFTER id");
    }

    if (!kolomTabelAda($conn, 'password_reset_attempts', 'ip_hash')) {
        mysqli_query($conn, "ALTER TABLE password_reset_attempts ADD COLUMN ip_hash CHAR(64) NOT NULL DEFAULT '' AFTER email_hash");
    }

    if (!kolomTabelAda($conn, 'password_reset_attempts', 'created_at')) {
        mysqli_query($conn, "ALTER TABLE password_reset_attempts ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER ip_hash");
    }

    if (!indeksTabelAda($conn, 'password_reset_attempts', 'idx_email_hash_created_at')) {
        mysqli_query($conn, "ALTER TABLE password_reset_attempts ADD INDEX idx_email_hash_created_at (email_hash, created_at)");
    }

    if (!indeksTabelAda($conn, 'password_reset_attempts', 'idx_ip_hash_created_at')) {
        mysqli_query($conn, "ALTER TABLE password_reset_attempts ADD INDEX idx_ip_hash_created_at (ip_hash, created_at)");
    }
}

function bersihkanDataResetLama($conn)
{
    if (!mysqli_query($conn, "DELETE FROM password_resets WHERE expired_at < NOW()")) {
        error_log('Gagal membersihkan password_resets kedaluwarsa: ' . mysqli_error($conn));
    }
    if (!mysqli_query($conn, "DELETE FROM password_reset_attempts WHERE created_at < (NOW() - INTERVAL 15 MINUTE)")) {
        error_log('Gagal membersihkan password_reset_attempts lama: ' . mysqli_error($conn));
    }
}

function konfigurasiEmailResetLengkap()
{
    $config = studyflow_mail_config();
    return !empty($config['smtp_username'])
        && !empty($config['smtp_password'])
        && !empty($config['from_email'])
        && !empty($config['base_url'])
        && $config['base_url'] !== 'https://domain-saya.infinityfreeapp.com';
}

function terlaluBanyakPercobaanReset($conn, $email, $ip)
{
    $emailHash = hashIdentitas($email);
    $ipHash    = hashIdentitas($ip);

    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(*) AS total
        FROM password_reset_attempts
        WHERE created_at >= (NOW() - INTERVAL 15 MINUTE)
          AND (email_hash = ? OR ip_hash = ?)
    ");

    if (!$stmt) {
        error_log('Gagal menyiapkan query rate limit reset password.');
        return true;
    }

    mysqli_stmt_bind_param($stmt, "ss", $emailHash, $ipHash);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return (int)($row['total'] ?? 0) >= 5;
}

function catatPercobaanReset($conn, $email, $ip)
{
    $emailHash = hashIdentitas($email);
    $ipHash    = hashIdentitas($ip);
    $stmt = mysqli_prepare($conn, "INSERT INTO password_reset_attempts (email_hash, ip_hash) VALUES (?, ?)");
    if (!$stmt) {
        error_log('Gagal menyiapkan query catat percobaan reset password.');
        return;
    }
    mysqli_stmt_bind_param($stmt, "ss", $emailHash, $ipHash);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

pastikanTabelResetPassword($conn);
bersihkanDataResetLama($conn);

$pesan         = '';
$error         = '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod !== 'POST' && empty($_SESSION['forgot_password_token'])) {
    $_SESSION['forgot_password_token'] = bin2hex(random_bytes(32));
}

if ($requestMethod === 'POST') {
    $email      = trim($_POST['email'] ?? '');
    $csrfToken  = $_POST['forgot_password_token'] ?? '';
    $ip         = ipKlien();
    $pesanUmum  = 'Jika email terdaftar, link reset password akan dikirim ke email tersebut.';

    if (!$csrfToken || empty($_SESSION['forgot_password_token']) || !hash_equals($_SESSION['forgot_password_token'], $csrfToken)) {
        $error = 'Sesi sudah kedaluwarsa. Silakan coba lagi.';
    } elseif (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Masukkan alamat email yang valid.';
    } elseif (!konfigurasiEmailResetLengkap()) {
        error_log('Konfigurasi email reset password belum lengkap. Periksa config.local.php.');
        $error = 'Layanan reset password belum dikonfigurasi. Hubungi admin StudyFlow.';
    } elseif (terlaluBanyakPercobaanReset($conn, $email, $ip)) {
        error_log('Rate limit reset password tercapai untuk IP/email hash.');
        $pesan = $pesanUmum;
    } else {
        catatPercobaanReset($conn, $email, $ip);

        $stmt = mysqli_prepare($conn, "SELECT id, nama, email FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user   = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($user) {
                $token      = bin2hex(random_bytes(32));
                $tokenHash  = hash('sha256', $token);
                $expiredAt  = date('Y-m-d H:i:s', time() + 3600);
                $userId     = (int)$user['id'];

                $upsert = mysqli_prepare($conn, "
                    INSERT INTO password_resets (user_id, token, expired_at)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        token = VALUES(token),
                        expired_at = VALUES(expired_at),
                        created_at = CURRENT_TIMESTAMP
                ");

                if ($upsert) {
                    mysqli_stmt_bind_param($upsert, "iss", $userId, $tokenHash, $expiredAt);
                    if (mysqli_stmt_execute($upsert)) {
                        if (!kirimEmailResetPassword($user['email'], $user['nama'], $token)) {
                            error_log('Email reset password gagal dikirim untuk user ID ' . $userId);
                        }
                    } else {
                        error_log('Gagal menyimpan token reset password: ' . mysqli_stmt_error($upsert));
                    }
                    mysqli_stmt_close($upsert);
                } else {
                    error_log('Gagal menyiapkan upsert password_resets: ' . mysqli_error($conn));
                }
            }

            unset($_SESSION['forgot_password_token']);
            $_SESSION['forgot_password_token'] = bin2hex(random_bytes(32));
            $pesan = $pesanUmum;
        } else {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}

if (empty($_SESSION['forgot_password_token'])) {
    $_SESSION['forgot_password_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password - StudyFlow</title>
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
      <h3>Lupa Password</h3>
    </div>

    <?php if ($pesan): ?>
      <p class="notif-auth notif-auth-sukses" role="alert"><?= e($pesan) ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
      <p class="notif-auth notif-auth-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form id="formForgotPassword" method="POST" action="forgot_password.php" autocomplete="on" novalidate>
      <input type="hidden" name="forgot_password_token" value="<?= e($_SESSION['forgot_password_token']) ?>">
      <div class="grup-form">
        <label for="emailForgot">Email</label>
        <input id="emailForgot" type="email" name="email" required autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <button id="tombolSubmitForgot" type="submit" class="tombol-utama auth-submit">Kirim Link Reset</button>
    </form>
    <p class="auth-switch">Ingat password? <a href="login.php">Login</a></p>
  </div>
</div>
<script>
var formForgotPassword = document.getElementById('formForgotPassword');
var tombolSubmitForgot = document.getElementById('tombolSubmitForgot');
if (formForgotPassword && tombolSubmitForgot) {
  formForgotPassword.addEventListener('submit', function (event) {
    if (formForgotPassword.dataset.submitted === '1') {
      event.preventDefault();
      return;
    }
    formForgotPassword.dataset.submitted = '1';
    tombolSubmitForgot.disabled = true;
    tombolSubmitForgot.textContent = 'Mengirim...';
  });
}
</script>
</body>
</html>