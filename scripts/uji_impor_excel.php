<?php
// scripts/uji_impor_excel.php
// Script pengujian otomatis untuk memverifikasi fungsionalitas impor berkas Excel (.xlsx) dan CSV ke database

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/impor_helper.php';

echo "========================================================\n";
echo "   PENGUJIAN FITUR IMPOR EXCEL (.XLSX) & CSV KE DATABASE\n";
echo "========================================================\n\n";

$admin_id = 1; // Administrator Prodi
$default_pass = password_hash('123456', PASSWORD_DEFAULT);

function uji_impor_mahasiswa($pdo, $admin_id, $default_pass, $file_path, $nama_berkas) {
    echo ">> Menguji Impor Mahasiswa dari: $nama_berkas ...\n";
    $rows = baca_file_impor($file_path, $nama_berkas);
    echo "   Total baris terbaca: " . count($rows) . " (termasuk header)\n";

    $berhasil = 0;
    $gagal = 0;

    $pdo->beginTransaction();
    $stmt_user = $pdo->prepare("INSERT INTO pengguna (nama, email, password_hash, peran, wajib_ganti_sandi) VALUES (?, ?, ?, 'mahasiswa', 1)");
    $stmt_profil = $pdo->prepare("INSERT INTO profil_pengguna (pengguna_id, nomor_induk) VALUES (?, ?)");

    for ($i = 1; $i < count($rows); $i++) {
        $nim = trim($rows[$i][0] ?? '');
        $nama = trim($rows[$i][1] ?? '');
        $email = trim($rows[$i][2] ?? '');

        if ($nama && $email) {
            try {
                $stmt_user->execute([$nama, $email, $default_pass]);
                $uid = $pdo->lastInsertId();
                $stmt_profil->execute([$uid, $nim]);
                $berhasil++;
                echo "   [BERHASIL] Mahasiswa: $nama | NIM: $nim | Email: $email (ID: $uid)\n";
            } catch (Exception $e) {
                $gagal++;
                echo "   [GAGAL] $nama ($email): " . $e->getMessage() . "\n";
            }
        }
    }

    $stmt_log = $pdo->prepare("INSERT INTO log_impor (admin_id, nama_berkas, jumlah_berhasil, jumlah_gagal) VALUES (?, ?, ?, ?)");
    $stmt_log->execute([$admin_id, $nama_berkas, $berhasil, $gagal]);
    $log_id = $pdo->lastInsertId();
    $pdo->commit();

    echo "   Hasil: Berhasil = $berhasil, Gagal = $gagal, Log Impor ID = $log_id\n\n";
    return ['berhasil' => $berhasil, 'gagal' => $gagal];
}

function uji_impor_dosen($pdo, $admin_id, $default_pass, $file_path, $nama_berkas) {
    echo ">> Menguji Impor Dosen dari: $nama_berkas ...\n";
    $rows = baca_file_impor($file_path, $nama_berkas);
    echo "   Total baris terbaca: " . count($rows) . " (termasuk header)\n";

    $berhasil = 0;
    $gagal = 0;

    $pdo->beginTransaction();
    $stmt_user = $pdo->prepare("INSERT INTO pengguna (nama, email, password_hash, peran, wajib_ganti_sandi) VALUES (?, ?, ?, 'dosen', 1)");
    $stmt_profil = $pdo->prepare("INSERT INTO profil_pengguna (pengguna_id, nomor_induk) VALUES (?, ?)");

    for ($i = 1; $i < count($rows); $i++) {
        $nidn = trim($rows[$i][0] ?? '');
        $nama = trim($rows[$i][1] ?? '');
        $email = trim($rows[$i][2] ?? '');

        if ($nama && $email) {
            try {
                $stmt_user->execute([$nama, $email, $default_pass]);
                $uid = $pdo->lastInsertId();
                $stmt_profil->execute([$uid, $nidn]);
                $berhasil++;
                echo "   [BERHASIL] Dosen: $nama | NIDN: $nidn | Email: $email (ID: $uid)\n";
            } catch (Exception $e) {
                $gagal++;
                echo "   [GAGAL] $nama ($email): " . $e->getMessage() . "\n";
            }
        }
    }

    $stmt_log = $pdo->prepare("INSERT INTO log_impor (admin_id, nama_berkas, jumlah_berhasil, jumlah_gagal) VALUES (?, ?, ?, ?)");
    $stmt_log->execute([$admin_id, $nama_berkas, $berhasil, $gagal]);
    $pdo->commit();

    echo "   Hasil: Berhasil = $berhasil, Gagal = $gagal\n\n";
    return ['berhasil' => $berhasil, 'gagal' => $gagal];
}

// Jalankan pengujian
$res_mhs_xlsx = uji_impor_mahasiswa($pdo, $admin_id, $default_pass, __DIR__ . '/../template/dummy_impor_mahasiswa.xlsx', 'dummy_impor_mahasiswa.xlsx');
$res_dsn_xlsx = uji_impor_dosen($pdo, $admin_id, $default_pass, __DIR__ . '/../template/dummy_impor_dosen.xlsx', 'dummy_impor_dosen.xlsx');

// Tampilkan verifikasi data di database
echo ">> Verifikasi Data Hasil Impor di Database:\n";
$stmt = $pdo->query("SELECT u.id, u.nama, u.email, u.peran, p.nomor_induk FROM pengguna u LEFT JOIN profil_pengguna p ON u.id = p.pengguna_id WHERE u.email LIKE '%dummy%' ORDER BY u.id ASC");
$dummies = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($dummies as $d) {
    echo "   - ID {$d['id']} | [{$d['peran']}] {$d['nama']} | Email: {$d['email']} | No. Induk: {$d['nomor_induk']}\n";
}

echo "\n>> Log Impor Terakhir:\n";
$logs = $pdo->query("SELECT * FROM log_impor ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($logs as $l) {
    echo "   - Log ID {$l['id']} | File: {$l['nama_berkas']} | Berhasil: {$l['jumlah_berhasil']} | Gagal: {$l['jumlah_gagal']} | Waktu: {$l['waktu']}\n";
}

echo "\nPengujian Berhasil Selesai!\n";

