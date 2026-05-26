<?php 
require 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'dashboard.php';
?>