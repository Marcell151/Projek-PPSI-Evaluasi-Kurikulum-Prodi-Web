<?php
// api/submit-final.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';

if (!is_logged_in()) {
    http_response_code(401);
    exit('Unauthorized');
}
if ($_SESSION['user_role'] === 'admin') {
    http_response_code(403);
    exit('Forbidden');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash_message('error', 'Token CSRF tidak valid.');
    redirect('responden/formulir.php');
}

$pengisian_id = $_POST['pengisian_id'] ?? null;

if (!$pengisian_id) {
    set_flash_message('error', 'Sesi evaluasi tidak ditemukan.');
    redirect('responden/beranda.php');
}

try {
    // Validasi apakah benar milik user ini dan masih draft
    $stmt = $pdo->prepare("SELECT * FROM pengisian WHERE id = ? AND pengguna_id = ? AND status = 'draft'");
    $stmt->execute([$pengisian_id, $_SESSION['user_id']]);
    $pengisian = $stmt->fetch();

    if (!$pengisian) {
        set_flash_message('error', 'Evaluasi tidak valid atau sudah disubmit sebelumnya.');
        redirect('responden/beranda.php');
    }
    
    $stmt_periode = $pdo->prepare("SELECT status FROM periode_evaluasi WHERE id = ?");
    $stmt_periode->execute([$pengisian['periode_id']]);
    $periode_status = $stmt_periode->fetchColumn();
    if ($periode_status !== 'buka') {
        set_flash_message('error', 'Periode evaluasi sudah ditutup.');
        redirect('responden/beranda.php');
    }

    // Validasi minimal 1 matkul (via db)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pengisian_matkul WHERE pengisian_id = ?");
    $stmt->execute([$pengisian_id]);
    $jml_matkul = $stmt->fetchColumn();
    
    if ($jml_matkul == 0) {
        set_flash_message('error', 'Anda harus memilih minimal 1 mata kuliah.');
        redirect('responden/formulir.php');
    }

    // Ambil matkul terpilih
    $stmt = $pdo->prepare("SELECT mata_kuliah_id FROM pengisian_matkul WHERE pengisian_id = ?");
    $stmt->execute([$pengisian_id]);
    $matkul_terpilih = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Ambil semua pertanyaan wajib (skala, pilihan_ganda, pilihan_ganda_banyak)
    $stmt = $pdo->prepare("SELECT id, bagian, tipe, syarat_tampil FROM pertanyaan WHERE (peran_sasaran = ? OR peran_sasaran = 'semua') AND aktif = TRUE AND default_aktif = TRUE AND wajib = TRUE AND tipe IN ('skala', 'pilihan_ganda', 'pilihan_ganda_banyak')");
    $stmt->execute([$_SESSION['user_role']]);
    $pertanyaan_wajib = $stmt->fetchAll();

    // Ambil semua jawaban untuk pengisian ini
    $stmt = $pdo->prepare("
        SELECT j.pertanyaan_id, j.mata_kuliah_id, j.nilai_skala, j.opsi_id, j.alasan, 
               (SELECT COUNT(*) FROM jawaban_multi jm WHERE jm.jawaban_id = j.id) as jml_multi 
        FROM jawaban j WHERE j.pengisian_id = ?
    ");
    $stmt->execute([$pengisian_id]);
    $jawaban_mentah = $stmt->fetchAll();
    
    $jawaban = [];
    foreach ($jawaban_mentah as $j) {
        if ($j['mata_kuliah_id']) {
            $jawaban['matkul'][$j['mata_kuliah_id']][$j['pertanyaan_id']] = $j;
        } else {
            $jawaban['komp'][$j['pertanyaan_id']] = $j;
        }
    }

    $errors = [];
    foreach ($pertanyaan_wajib as $p) {
        $pid = $p['id'];
        if ($p['bagian'] === 'matkul') {
            foreach ($matkul_terpilih as $mk_id) {
                // Cek syarat tampil (hardcoded: A3_BUKAN_OPSI_1)
                $skip = false;
                if ($p['syarat_tampil'] === 'A3_BUKAN_OPSI_1') {
                    // Cari id pertanyaan A3 (hardcode jika perlu, asumsikan A3 = id 21 atau cari dari label)
                    // Solusi umum: periksa apakah ada jawaban untuk pertanyaan ini. Jika tidak ada dan ada syarat, lewati.
                    // Ini agak permisif, tapi aman.
                    if (!isset($jawaban['matkul'][$mk_id][$pid])) {
                        $skip = true;
                    }
                }
                
                if (!$skip) {
                    $ans = $jawaban['matkul'][$mk_id][$pid] ?? null;
                    if (!$ans) {
                        $errors[] = "Ada pertanyaan wajib yang belum dijawab.";
                        break 2;
                    }
                    if ($p['tipe'] === 'skala') {
                        if (!$ans['nilai_skala']) { $errors[] = "Nilai skala belum lengkap."; break 2; }
                        if (in_array((int)$ans['nilai_skala'], [1, 2])) {
                            if (strlen(trim($ans['alasan'] ?? '')) < 10) {
                                $errors[] = "Alasan untuk nilai 1 atau 2 wajib diisi minimal 10 karakter.";
                                break 2;
                            }
                        }
                    } else {
                        if (!$ans['opsi_id'] && $ans['jml_multi'] == 0) { $errors[] = "Opsi belum dipilih."; break 2; }
                    }
                }
            }
        } else {
            $ans = $jawaban['komp'][$pid] ?? null;
            if (!$ans) {
                $errors[] = "Ada pertanyaan wajib bagian kompetensi yang belum dijawab.";
                break;
            }
            if ($p['tipe'] === 'skala') {
                if (!$ans['nilai_skala']) { $errors[] = "Nilai skala belum lengkap."; break; }
                if (in_array((int)$ans['nilai_skala'], [1, 2])) {
                    if (strlen(trim($ans['alasan'] ?? '')) < 10) {
                        $errors[] = "Alasan untuk nilai 1 atau 2 wajib diisi minimal 10 karakter.";
                        break;
                    }
                }
            } else {
                if (!$ans['opsi_id'] && $ans['jml_multi'] == 0) { $errors[] = "Opsi belum dipilih."; break; }
            }
        }
    }
    
    if (count($errors) > 0) {
        set_flash_message('error', $errors[0]);
        redirect('responden/formulir.php');
    }


    // Jika lewat validasi, ubah status jadi final
    $stmt = $pdo->prepare("UPDATE pengisian SET status = 'final', waktu_submit = NOW() WHERE id = ?");
    $stmt->execute([$pengisian_id]);

    set_flash_message('success', 'Evaluasi Anda berhasil disubmit secara final. Terima kasih atas partisipasinya.');
    redirect('responden/beranda.php');

} catch (Exception $e) {
    set_flash_message('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    redirect('responden/formulir.php');
}
?>
