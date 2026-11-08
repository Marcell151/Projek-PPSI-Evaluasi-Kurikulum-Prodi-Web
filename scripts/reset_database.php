<?php
// scripts/reset_database.php
// Script untuk mereset database evaluasi_kurikulum kembali ke kondisi awal sebelum pengujian impor

require_once __DIR__ . '/../config/database.php';

$backup_file = __DIR__ . '/../database/backup_sebelum_uji_excel.sql';

if (!file_exists($backup_file)) {
    die("File backup $backup_file tidak ditemukan!\n");
}

echo "Memulai proses reset database evaluasi_kurikulum dari backup...\n";

// Baca isi SQL backup
$sql = file_get_contents($backup_file);

try {
    // Nonaktifkan foreign key checks sementara untuk import bersih
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    // Jalankan batch SQL
    $pdo->exec($sql);
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "Reset database BERHASIL! Database telah kembali ke kondisi awal persis seperti sebelum pengujian.\n";
    
    // Verifikasi jumlah pengguna
    $count = $pdo->query("SELECT count(*) FROM pengguna")->fetchColumn();
    echo "Jumlah pengguna saat ini: $count (kondisi awal: 17 pengguna)\n";
    
} catch (Exception $e) {
    echo "Gagal mereset database: " . $e->getMessage() . "\n";
}

