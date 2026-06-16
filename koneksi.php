<?php
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
date_default_timezone_set('Asia/Jakarta');

$config = [
    'host' => 'localhost',
    'db'   => 'studyflow',
    'user' => 'root',
    'pass' => '',
];

$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    $customConfig = require $localConfig;
    if (is_array($customConfig)) {
        $config = array_merge($config, $customConfig);
    }
}

$host = $config['host'];
$db   = $config['db'];
$user = $config['user'];
$pass = $config['pass'];

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal. Periksa konfigurasi database hosting di config.local.php.");
}
mysqli_set_charset($conn, "utf8mb4");
?>
