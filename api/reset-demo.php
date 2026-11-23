<?php
// api/reset-demo.php
// Mengembalikan seluruh data database ke kondisi awal demo (database/data_demo_baru.sql)
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$is_ajax = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

$redirect_to = $_POST['redirect_to'] ?? (is_logged_in() && ($_SESSION['user_role'] ?? '') === 'admin' ? 'admin/dashboard.php' : 'auth/login.php');
// Validasi tujuan redirect agar hanya ke halaman internal aplikasi
$allowed_redirects = ['auth/login.php', 'admin/dashboard.php'];
if (!in_array($redirect_to, $allowed_redirects, true)) {
    $redirect_to = 'auth/login.php';
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid.']);
        exit;
    }
    set_flash_message('error', 'Token keamanan tidak valid. Silakan muat ulang halaman dan coba lagi.');
    redirect($redirect_to);
}

$sql_file = dirname(__DIR__) . '/database/data_demo_baru.sql';
if (!file_exists($sql_file) || !is_readable($sql_file)) {
    $msg = 'Berkas database/data_demo_baru.sql tidak ditemukan.';
    if ($is_ajax) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $msg]);
        exit;
    }
    set_flash_message('error', $msg);
    redirect($redirect_to);
}

/**
 * Memecah isi file SQL menjadi daftar statement valid tanpa memotong
 * tanda titik koma (;) yang berada di dalam string kutip tunggal/ganda.
 */
function split_sql_statements(string $sql): array {
    $statements = [];
    $buffer = '';
    $len = strlen($sql);
    $in_single = false;
    $in_double = false;
    $in_line_comment = false;
    $in_block_comment = false;

    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        $next = ($i + 1 < $len) ? $sql[$i + 1] : '';

        if ($in_line_comment) {
            if ($ch === "\n") {
                $in_line_comment = false;
            }
            continue;
        }

        if ($in_block_comment) {
            if ($ch === '*' && $next === '/') {
                $in_block_comment = false;
                $i++;
            }
            continue;
        }

        if (!$in_single && !$in_double) {
            // Komentar baris: -- ... atau # ...
            if (($ch === '-' && $next === '-') || $ch === '#') {
                $in_line_comment = true;
                continue;
            }
            // Komentar blok /* ... */ (kecuali hint eksekusi /*! ... */)
            if ($ch === '/' && $next === '*') {
                $third = ($i + 2 < $len) ? $sql[$i + 2] : '';
                if ($third !== '!') {
                    $in_block_comment = true;
                    $i++;
                    continue;
                }
            }
        }

        if ($ch === "'" && !$in_double) {
            // Cek escape backslash
            $bs = 0;
            for ($j = $i - 1; $j >= 0 && $sql[$j] === '\\'; $j--) {
                $bs++;
            }
            if ($bs % 2 === 0) {
                $in_single = !$in_single;
            }
        } elseif ($ch === '"' && !$in_single) {
            $bs = 0;
            for ($j = $i - 1; $j >= 0 && $sql[$j] === '\\'; $j--) {
                $bs++;
            }
            if ($bs % 2 === 0) {
                $in_double = !$in_double;
            }
        }

        if ($ch === ';' && !$in_single && !$in_double) {
            $stmt = trim($buffer);
            if ($stmt !== '') {
                $statements[] = $stmt;
            }
            $buffer = '';
        } else {
            $buffer .= $ch;
        }
    }

    $tail = trim($buffer);
    if ($tail !== '') {
        $statements[] = $tail;
    }

    return $statements;
}

@set_time_limit(120);

try {
    $raw_sql = file_get_contents($sql_file);
    $statements = split_sql_statements($raw_sql);

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $executed = 0;
    foreach ($statements as $stmt) {
        $upper = strtoupper(ltrim($stmt));
        // Lewati kontrol transaksi manual karena DDL (DROP/CREATE TABLE) melakukan implicit commit di MySQL
        if ($upper === 'START TRANSACTION' || $upper === 'COMMIT' || strpos($upper, '/*!40101 SET ') === 0) {
            continue;
        }
        $pdo->exec($stmt);
        $executed++;
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    // Jika sebelumnya login sebagai responden non-admin, keluar agar sesi sinkron;
    // jika admin, pertahankan sesi admin (id=1 selalu Administrator Prodi).
    if (is_logged_in() && ($_SESSION['user_role'] ?? '') !== 'admin') {
        session_unset();
    }

    $ok_msg = 'Data demo berhasil direset ke kondisi awal (51 akun & data Tracer Study 2024). Akun uji siap digunakan kembali.';
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'executed' => $executed, 'message' => $ok_msg]);
        exit;
    }
    set_flash_message('success', $ok_msg);
    redirect($redirect_to);
} catch (Throwable $e) {
    try { $pdo->exec('SET FOREIGN_KEY_CHECKS = 1'); } catch (Throwable $_) {}
    $err_msg = 'Gagal mereset data demo: ' . $e->getMessage();
    if ($is_ajax) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $err_msg]);
        exit;
    }
    set_flash_message('error', $err_msg);
    redirect($redirect_to);
}
