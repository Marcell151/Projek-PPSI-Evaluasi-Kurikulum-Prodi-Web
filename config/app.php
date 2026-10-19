<?php
// config/app.php
session_start();
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_dir = '/evaluasi_kurikulum';
define('BASE_URL', $protocol . '://' . $host . $base_dir);

// Waktu zona Indonesia
date_default_timezone_set('Asia/Jakarta');
?>
