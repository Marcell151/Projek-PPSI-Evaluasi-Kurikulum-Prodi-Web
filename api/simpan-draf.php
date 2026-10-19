<?php
// api/simpan-draf.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
if ($_SESSION['user_role'] === 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$pengisian_id = $_POST['pengisian_id'] ?? null;
$tab_terakhir = $_POST['tab_terakhir'] ?? null;

if (!$pengisian_id) {
    echo json_encode(['success' => false, 'message' => 'No session ID']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verifikasi kepemilikan dan status
    $stmt = $pdo->prepare("SELECT * FROM pengisian WHERE id = ? AND pengguna_id = ? AND status = 'draft'");
    $stmt->execute([$pengisian_id, $_SESSION['user_id']]);
    $pengisian = $stmt->fetch();

    if (!$pengisian) {
        throw new Exception("Pengisian tidak valid atau sudah disubmit.");
    }

    $stmt_periode = $pdo->prepare("SELECT status FROM periode_evaluasi WHERE id = ?");
    $stmt_periode->execute([$pengisian['periode_id']]);
    $periode_status = $stmt_periode->fetchColumn();
    if ($periode_status !== 'buka') {
        throw new Exception("Periode evaluasi sudah ditutup.");
    }

    // Update tab_terakhir jika dikirim
    if ($tab_terakhir) {
        $stmt = $pdo->prepare("UPDATE pengisian SET tab_terakhir = ? WHERE id = ?");
        $stmt->execute([$tab_terakhir, $pengisian_id]);
    }

    // Update pengisian_matkul
    if (isset($_POST['matkul_terpilih'])) {
        $matkul_terpilih = json_decode($_POST['matkul_terpilih'], true);
        if (is_array($matkul_terpilih)) {
            // Hapus yang lama, insert yang baru.
            // Lebih baik hapus yang tidak ada di list
            $placeholders = str_repeat('?,', count($matkul_terpilih) - 1) . '?';
            
            if (!empty($matkul_terpilih)) {
                $stmt = $pdo->prepare("DELETE FROM pengisian_matkul WHERE pengisian_id = ? AND mata_kuliah_id NOT IN ($placeholders)");
                $params = array_merge([$pengisian_id], $matkul_terpilih);
                $stmt->execute($params);

                // Insert yang baru (IGNORE agar tidak error duplikat)
                $stmt_insert = $pdo->prepare("INSERT IGNORE INTO pengisian_matkul (pengisian_id, mata_kuliah_id) VALUES (?, ?)");
                foreach ($matkul_terpilih as $mk_id) {
                    $stmt_insert->execute([$pengisian_id, $mk_id]);
                }
            } else {
                $stmt = $pdo->prepare("DELETE FROM pengisian_matkul WHERE pengisian_id = ?");
                $stmt->execute([$pengisian_id]);
            }
        }
    }

    // Upsert Jawaban
    // Format name input: komp_{pertanyaan_id}_{tipe} ATAU matkul_{mata_kuliah_id}_{pertanyaan_id}_{tipe}
    foreach ($_POST as $key => $val) {
        if (str_starts_with($key, 'komp_')) {
            $parts = explode('_', $key); // ['komp', 'id', 'tipe']
            if (count($parts) === 3) {
                $pid = $parts[1];
                $tipe = $parts[2];
                upsertJawaban($pdo, $pengisian_id, $pid, null, $tipe, $val);
            }
        } elseif (str_starts_with($key, 'matkul_')) {
            $parts = explode('_', $key); // ['matkul', 'mk_id', 'pid', 'tipe']
            if (count($parts) === 4) {
                $mk_id = $parts[1];
                $pid = $parts[2];
                $tipe = $parts[3];
                upsertJawaban($pdo, $pengisian_id, $pid, $mk_id, $tipe, $val);
            }
        }
    }
    
    // Update last saved timestamp explicitly just in case (ON UPDATE CURRENT_TIMESTAMP mostly handles this)
    $pdo->prepare("UPDATE pengisian SET terakhir_disimpan = NOW() WHERE id = ?")->execute([$pengisian_id]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function upsertJawaban($pdo, $pengisian_id, $pertanyaan_id, $mata_kuliah_id, $tipe, $val) {
    if ($val === '' || (is_array($val) && empty($val))) return;

    $col = '';
    if ($tipe === 'skala') $col = 'nilai_skala';
    elseif ($tipe === 'opsi' && !is_array($val)) $col = 'opsi_id';
    elseif ($tipe === 'teks') $col = 'teks';
    elseif ($tipe === 'alasan') $col = 'alasan';

    // Cek ada tidak
    if ($mata_kuliah_id) {
        $stmt = $pdo->prepare("SELECT id FROM jawaban WHERE pengisian_id = ? AND pertanyaan_id = ? AND mata_kuliah_id = ?");
        $stmt->execute([$pengisian_id, $pertanyaan_id, $mata_kuliah_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM jawaban WHERE pengisian_id = ? AND pertanyaan_id = ? AND mata_kuliah_id IS NULL");
        $stmt->execute([$pengisian_id, $pertanyaan_id]);
    }
    $exists = $stmt->fetch();
    $j_id = $exists ? $exists['id'] : null;

    if (!$j_id) {
        $sql = "INSERT INTO jawaban (pengisian_id, pertanyaan_id, mata_kuliah_id) VALUES (?, ?, ?)";
        $pdo->prepare($sql)->execute([$pengisian_id, $pertanyaan_id, $mata_kuliah_id]);
        $j_id = $pdo->lastInsertId();
    }

    if ($col) {
        $sql = "UPDATE jawaban SET $col = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$val, $j_id]);
    } elseif ($tipe === 'opsi' && is_array($val)) {
        // Multi opsi
        $pdo->prepare("DELETE FROM jawaban_multi WHERE jawaban_id = ?")->execute([$j_id]);
        $stmt_multi = $pdo->prepare("INSERT INTO jawaban_multi (jawaban_id, opsi_id) VALUES (?, ?)");
        foreach ($val as $o_id) {
            $stmt_multi->execute([$j_id, $o_id]);
        }
    }
}
?>
