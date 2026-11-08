<?php
// config/app.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) || 
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
            ? "https" : "http";

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Deteksi otomatis base_dir secara dinamis:
// Bekerja otomatis untuk subfolder lokal (misal /evaluasi_kurikulum_web) maupun di hosting cPanel root domain
$doc_root = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']) : '';
$app_root = str_replace('\\', '/', dirname(__DIR__));

if (!empty($doc_root) && strpos($app_root, $doc_root) === 0) {
    $base_dir = rtrim(substr($app_root, strlen($doc_root)), '/');
} else {
    // Fallback dinamis: gunakan nama folder aplikasi saat ini
    $base_dir = '/' . basename($app_root);
}

define('BASE_URL', rtrim($protocol . '://' . $host . $base_dir, '/'));
define('APP_NAME', 'Evaluasi Kurikulum');

// Waktu zona Indonesia
date_default_timezone_set('Asia/Jakarta');
?>
