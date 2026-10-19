<?php
// config/database.php

// Cek apakah aplikasi berjalan di lingkungan lokal (XAMPP) atau di server hosting (Rumahweb)
$http_host = $_SERVER['HTTP_HOST'] ?? '';
$is_local = in_array($http_host, ['localhost', '127.0.0.1'])
    || strpos($http_host, 'localhost:') === 0
    || strpos($http_host, '127.0.0.1:') === 0
    || (php_sapi_name() === 'cli' && empty($http_host));

if ($is_local) {
    // Konfigurasi Database Lokal (XAMPP)
    $db_host = 'localhost';
    $db_name = 'evaluasi_kurikulum';
    $db_user = 'root';
    $db_pass = '';
} else {
    // Konfigurasi Database Hosting (Rumahweb cPanel)
    $db_host = 'localhost';
    $db_name = 'kmww6861_evaluasi_kurikulum';
    $db_user = 'kmww6861_evaluasi_kurikulum';
    $db_pass = 'evaluasi_kurikulum';
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
