<?php
session_start();

$host = 'localhost';
$db   = 'studyflow';
$user = 'root';
$pass = '';  // default Laragon

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>