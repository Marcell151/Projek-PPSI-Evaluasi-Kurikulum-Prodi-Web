<?php
// config/app.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) || 
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
            ? "https" : "http";

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Deteksi otomatis base_dir (di XAMPP lokal biasanya /evaluasi_kurikulum, di cPanel hosting biasanya di root public_html)
$is_local = in_array($host, ['localhost', '127.0.0.1'])
    || strpos($host, 'localhost:') === 0
    || strpos($host, '127.0.0.1:') === 0
    || (php_sapi_name() === 'cli' && empty($host));

if ($is_local) {
    $base_dir = '/evaluasi_kurikulum';
} else {
    // Pada hosting (cPanel), jika ditaruh di root domain (public_html), base_dir kosong.
    // Jika ditaruh di sub-folder cPanel, otomatis mendeteksi nama foldernya.
    $doc_root = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']) : '';
    $app_root = str_replace('\\', '/', dirname(__DIR__));
    if ($doc_root && strpos($app_root, $doc_root) === 0) {
        $base_dir = rtrim(substr($app_root, strlen($doc_root)), '/');
    } else {
        $base_dir = '';
    }
}

define('BASE_URL', rtrim($protocol . '://' . $host . $base_dir, '/'));

// Waktu zona Indonesia
date_default_timezone_set('Asia/Jakarta');
?>
