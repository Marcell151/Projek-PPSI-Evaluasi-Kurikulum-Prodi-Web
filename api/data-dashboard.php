<?php
// api/data-dashboard.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$periode_id      = $_GET['periode_id'] ?? null;
$filter_peran    = $_GET['peran'] ?? '';
$filter_kelompok = $_GET['kelompok'] ?? '';
$filter_jenis    = $_GET['jenis'] ?? '';
$filter_tahun    = $_GET['tahun_lulus'] ?? '';

$valid_peran    = ['mahasiswa', 'dosen', 'alumni', 'perusahaan'];
$valid_kelompok = ['prodi', 'fakultas', 'universitas'];
$valid_jenis    = ['wajib', 'pilihan'];

if (!in_array($filter_peran, $valid_peran, true))       $filter_peran = '';
if (!in_array($filter_kelompok, $valid_kelompok, true)) $filter_kelompok = '';
if (!in_array($filter_jenis, $valid_jenis, true))       $filter_jenis = '';
if ($filter_tahun !== '' && !ctype_digit((string)$filter_tahun)) $filter_tahun = '';

// Jika periode_id tidak diberikan, gunakan periode aktif
if (!$periode_id) {
    $stmt = $pdo->query("SELECT id FROM periode_evaluasi WHERE status = 'buka' LIMIT 1");
    $periode = $stmt->fetch();
    $periode_id = $periode ? $periode['id'] : null;
}

// 1. Data Summary Cards & Breakdown
$summary = [
    'total_responden' => 0,
    'pengisian_selesai' => 0,
    'matkul_aktif' => 0,
    'role_breakdown' => ['mahasiswa' => 0, 'alumni' => 0, 'perusahaan' => 0, 'dosen' => 0]
];

if ($filter_peran) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pengguna WHERE peran = ?");
    $stmt->execute([$filter_peran]);
    $summary['total_responden'] = (int)$stmt->fetchColumn();
} else {
    $stmt = $pdo->query("SELECT COUNT(*) FROM pengguna WHERE peran != 'admin'");
    $summary['total_responden'] = (int)$stmt->fetchColumn();
}

if ($periode_id) {
    $stmt = $pdo->prepare("SELECT u.peran, COUNT(pg.id) as cnt FROM pengisian pg JOIN pengguna u ON pg.pengguna_id = u.id WHERE pg.periode_id = ? AND pg.status = 'final' GROUP BY u.peran");
    $stmt->execute([$periode_id]);
    foreach ($stmt->fetchAll() as $r) {
        $summary['role_breakdown'][$r['peran']] = (int)$r['cnt'];
    }

    if ($filter_peran) {
        $summary['pengisian_selesai'] = $summary['role_breakdown'][$filter_peran] ?? 0;
    } else {
        $summary['pengisian_selesai'] = array_sum($summary['role_breakdown']);
    }
} else {
    $stmt = $pdo->query("SELECT COUNT(*) FROM pengisian WHERE status = 'final'");
    $summary['pengisian_selesai'] = (int)$stmt->fetchColumn();
}

$mkWhere = ["aktif = TRUE"];
$mkParams = [];
if ($filter_kelompok) { $mkWhere[] = "kelompok = ?"; $mkParams[] = $filter_kelompok; }
if ($filter_jenis)    { $mkWhere[] = "jenis = ?";    $mkParams[] = $filter_jenis; }
$stmt = $pdo->prepare("SELECT COUNT(*) FROM mata_kuliah WHERE " . implode(' AND ', $mkWhere));
$stmt->execute($mkParams);
$summary['matkul_aktif'] = (int)$stmt->fetchColumn();

// 2. Data Grafik Radar & Tabel Kompetensi (Responsif untuk SEMUA peran!)
$roleStyles = [
    'alumni'     => ['label' => 'Alumni',     'bg' => 'rgba(59, 130, 246, 0.2)',  'border' => 'rgba(59, 130, 246, 1)',  'text' => 'text-blue-600'],
    'perusahaan' => ['label' => 'Perusahaan', 'bg' => 'rgba(12, 161, 78, 0.2)',   'border' => 'rgba(12, 161, 78, 1)',   'text' => 'text-green-600'],
    'mahasiswa'  => ['label' => 'Mahasiswa',  'bg' => 'rgba(147, 51, 234, 0.2)',  'border' => 'rgba(147, 51, 234, 1)',  'text' => 'text-purple-600'],
    'dosen'      => ['label' => 'Dosen',      'bg' => 'rgba(234, 88, 12, 0.2)',   'border' => 'rgba(234, 88, 12, 1)',   'text' => 'text-red-600'],
];

if ($filter_peran === '') {
    $targetRoles = ['alumni', 'perusahaan'];
    $radar_meta = [
        'mode'     => 'compare',
        'title'    => 'Tren Warna Prodi',
        'subtitle' => 'Perbandingan relevansi kompetensi dari sisi Alumni & Perusahaan.',
        'roles'    => $targetRoles,
        'columns'  => ['Alumni', 'Perusahaan'],
    ];
} else {
    $targetRoles = [$filter_peran];
    $titles = [
        'alumni'     => ['Tren Kompetensi — Alumni', 'Kelompok kompetensi yang paling sering digunakan Alumni dalam pekerjaan.'],
        'perusahaan' => ['Tren Kompetensi — Perusahaan', 'Kompetensi lulusan yang paling diprioritaskan oleh Perusahaan Mitra.'],
        'mahasiswa'  => ['Minat Kompetensi — Mahasiswa', 'Bidang kompetensi yang paling ingin diperdalam oleh Mahasiswa.'],
        'dosen'      => ['Prioritas Penguatan — Dosen', 'Bidang kompetensi yang disarankan Dosen untuk diperkuat dalam kurikulum.'],
    ];
    $radar_meta = [
        'mode'     => 'single',
        'title'    => $titles[$filter_peran][0],
        'subtitle' => $titles[$filter_peran][1],
        'roles'    => $targetRoles,
        'columns'  => [$roleStyles[$filter_peran]['label']],
    ];
}

$radar_data = ['labels' => [], 'datasets' => []];
foreach ($targetRoles as $r) {
    $radar_data['datasets'][] = [
        'label'                => $roleStyles[$r]['label'],
        'data'                 => [],
        'backgroundColor'      => $roleStyles[$r]['bg'],
        'borderColor'          => $roleStyles[$r]['border'],
        'pointBackgroundColor' => $roleStyles[$r]['border'],
    ];
}

$tabel_perbandingan = [];

if ($periode_id) {
    $stmt_kategori = $pdo->query("SELECT id, nama FROM kategori_kompetensi ORDER BY id ASC");
    $kategori_all = $stmt_kategori->fetchAll();

    $kompetensi_map = [];
    foreach ($kategori_all as $k) {
        $nama = $k['nama'];
        $kompetensi_map[$nama] = [];
        foreach ($targetRoles as $r) $kompetensi_map[$nama][$r] = 0;
        $radar_data['labels'][] = $nama;
    }

    $rolePlaceholders = implode(',', array_fill(0, count($targetRoles), '?'));
    $radarSql = "SELECT
                k.id, k.nama, u.peran,
                COUNT(jm.opsi_id) as jumlah_user_memilih
            FROM jawaban_multi jm
            JOIN jawaban j ON jm.jawaban_id = j.id
            JOIN opsi_pertanyaan o ON jm.opsi_id = o.id
            JOIN kategori_kompetensi k ON o.kategori_kompetensi_id = k.id
            JOIN pengisian pg ON j.pengisian_id = pg.id
            JOIN pengguna u ON pg.pengguna_id = u.id
            LEFT JOIN profil_pengguna pr ON u.id = pr.pengguna_id
            WHERE pg.periode_id = ?
              AND pg.status = 'final'
              AND u.peran IN ($rolePlaceholders)";
    $radarParams = array_merge([$periode_id], $targetRoles);

    if ($filter_tahun !== '' && in_array('alumni', $targetRoles, true)) {
        $radarSql .= " AND (u.peran != 'alumni' OR pr.tahun_lulus = ?)";
        $radarParams[] = $filter_tahun;
    }
    $radarSql .= " GROUP BY k.id, k.nama, u.peran";

    $stmt = $pdo->prepare($radarSql);
    $stmt->execute($radarParams);
    $hasil_radar = $stmt->fetchAll();

    $totalsPerRole = [];
    foreach ($targetRoles as $r) $totalsPerRole[$r] = 0;

    foreach ($hasil_radar as $row) {
        $nama = $row['nama'];
        $peranRow = $row['peran'];
        if (isset($kompetensi_map[$nama][$peranRow])) {
            $kompetensi_map[$nama][$peranRow] = (int)$row['jumlah_user_memilih'];
            $totalsPerRole[$peranRow] += (int)$row['jumlah_user_memilih'];
        }
    }

    foreach ($radar_data['labels'] as $nama) {
        $rowOut = ['kompetensi' => $nama, 'values' => []];
        foreach ($targetRoles as $idx => $r) {
            $jml = $kompetensi_map[$nama][$r];
            $pct = $totalsPerRole[$r] > 0 ? round(($jml / $totalsPerRole[$r]) * 100, 2) : 0;
            $radar_data['datasets'][$idx]['data'][] = $pct;
            $rowOut['values'][] = [
                'role'   => $r,
                'jumlah' => $jml,
                'pct'    => $pct,
                'color'  => $roleStyles[$r]['text'],
            ];
        }
        $tabel_perbandingan[] = $rowOut;
    }
}

// 3. Top 5 & Bottom 5 Mata Kuliah (filter periode, peran, kelompok, jenis)
$matkul_ranking = ['top' => [], 'bottom' => []];
if ($periode_id) {
    $sql_mk = "SELECT m.kode, m.nama, m.kelompok, m.jenis, AVG(j.nilai_skala) as skor, COUNT(DISTINCT pg.pengguna_id) as jumlah_penilai
               FROM jawaban j
               JOIN pertanyaan pt ON j.pertanyaan_id = pt.id
               JOIN mata_kuliah m ON j.mata_kuliah_id = m.id
               JOIN pengisian pg ON j.pengisian_id = pg.id
               JOIN pengguna u ON pg.pengguna_id = u.id
               WHERE pg.periode_id = ? AND pg.status = 'final'
                 AND j.mata_kuliah_id IS NOT NULL
                 AND j.nilai_skala IS NOT NULL
                 AND pt.untuk_peringkat = 1";
    $params_mk = [$periode_id];
    if ($filter_peran)    { $sql_mk .= " AND u.peran = ?";    $params_mk[] = $filter_peran; }
    if ($filter_kelompok) { $sql_mk .= " AND m.kelompok = ?"; $params_mk[] = $filter_kelompok; }
    if ($filter_jenis)    { $sql_mk .= " AND m.jenis = ?";    $params_mk[] = $filter_jenis; }
    $sql_mk .= " GROUP BY m.id ORDER BY skor DESC, jumlah_penilai DESC";

    $stmt = $pdo->prepare($sql_mk);
    $stmt->execute($params_mk);
    $all_mk = $stmt->fetchAll();

    if (count($all_mk) > 0) {
        $matkul_ranking['top'] = array_slice($all_mk, 0, 5);
        $bottom = array_slice($all_mk, -5);
        usort($bottom, function($a, $b) { return $a['skor'] <=> $b['skor']; });
        $matkul_ranking['bottom'] = $bottom;
    }
}

// 4. Sebaran Posisi/Profesi & Instansi Alumni (khusus jika peran '' atau 'alumni', filter tahun_lulus)
$alumni_instansi = ['labels' => [], 'data' => []];
$alumni_profesi  = ['labels' => [], 'data' => []];

if ($filter_peran === '' || $filter_peran === 'alumni') {
    $sql_alumni = "SELECT p.instansi, p.posisi_pekerjaan
                   FROM profil_pengguna p
                   JOIN pengguna u ON p.pengguna_id = u.id
                   WHERE u.peran = 'alumni'";
    $params_alumni = [];
    if ($filter_tahun !== '') {
        $sql_alumni .= " AND p.tahun_lulus = ?";
        $params_alumni[] = $filter_tahun;
    }
    $stmt = $pdo->prepare($sql_alumni);
    $stmt->execute($params_alumni);

    $instansi_count = [];
    $profesi_count = [];
    foreach ($stmt->fetchAll() as $row) {
        $inst = trim(preg_replace('/\s+/', ' ', strtoupper($row['instansi'] ?? '')));
        $prof = trim(preg_replace('/\s+/', ' ', strtoupper($row['posisi_pekerjaan'] ?? '')));

        if ($inst !== '') $instansi_count[$inst] = ($instansi_count[$inst] ?? 0) + 1;
        if ($prof !== '') $profesi_count[$prof]  = ($profesi_count[$prof] ?? 0) + 1;
    }
    arsort($instansi_count);
    arsort($profesi_count);

    foreach ($instansi_count as $k => $v) { $alumni_instansi['labels'][] = $k; $alumni_instansi['data'][] = $v; }
    foreach ($profesi_count as $k => $v)  { $alumni_profesi['labels'][] = $k;  $alumni_profesi['data'][] = $v; }
}

// 5. Ringkasan Tracer Study Alumni (khusus jika peran '' atau 'alumni')
$tracer = [
    'visible' => ($filter_peran === '' || $filter_peran === 'alumni'),
    'status' => ['labels' => [], 'data' => []],
    'level'  => ['labels' => [], 'data' => []],
    'waktu_tunggu_cepat_pct' => 0,
    'gaji_atas_ump_pct' => 0,
    'sesuai_bidang_pct' => 0,
    'total_alumni_tracer' => 0,
];

if ($periode_id && $tracer['visible']) {
    $fetchTracerGroup = function(string $kode) use ($pdo, $periode_id, $filter_tahun) {
        $sql = "SELECT o.teks_opsi, COUNT(*) as cnt
                FROM jawaban j
                JOIN pertanyaan pt ON j.pertanyaan_id = pt.id
                JOIN opsi_pertanyaan o ON j.opsi_id = o.id
                JOIN pengisian pg ON j.pengisian_id = pg.id
                JOIN pengguna u ON pg.pengguna_id = u.id
                LEFT JOIN profil_pengguna pr ON u.id = pr.pengguna_id
                WHERE pg.periode_id = ? AND pg.status = 'final'
                  AND u.peran = 'alumni' AND pt.kode = ?";
        $params = [$periode_id, $kode];
        if ($filter_tahun !== '') {
            $sql .= " AND pr.tahun_lulus = ?";
            $params[] = $filter_tahun;
        }
        $sql .= " GROUP BY o.id, o.teks_opsi ORDER BY o.urutan ASC";
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    };

    foreach ($fetchTracerGroup('T1') as $r) {
        $tracer['status']['labels'][] = $r['teks_opsi'];
        $tracer['status']['data'][]   = (int)$r['cnt'];
        $tracer['total_alumni_tracer'] += (int)$r['cnt'];
    }
    foreach ($fetchTracerGroup('T4') as $r) {
        if (stripos($r['teks_opsi'], 'Belum bekerja') !== false) continue;
        $tracer['level']['labels'][] = $r['teks_opsi'];
        $tracer['level']['data'][]   = (int)$r['cnt'];
    }

    $t2 = $fetchTracerGroup('T2');
    $t2Total = 0; $t2Cepat = 0;
    foreach ($t2 as $r) {
        if (stripos($r['teks_opsi'], 'Belum bekerja') !== false) continue;
        $t2Total += (int)$r['cnt'];
        if (stripos($r['teks_opsi'], 'Kurang dari 6') !== false) $t2Cepat += (int)$r['cnt'];
    }
    $tracer['waktu_tunggu_cepat_pct'] = $t2Total > 0 ? round(($t2Cepat / $t2Total) * 100, 1) : 0;

    $t3 = $fetchTracerGroup('T3');
    $t3Total = 0; $t3Sesuai = 0;
    foreach ($t3 as $r) {
        if (stripos($r['teks_opsi'], 'Belum bekerja') !== false) continue;
        $t3Total += (int)$r['cnt'];
        if (stripos($r['teks_opsi'], 'Sesuai') === 0) $t3Sesuai += (int)$r['cnt'];
    }
    $tracer['sesuai_bidang_pct'] = $t3Total > 0 ? round(($t3Sesuai / $t3Total) * 100, 1) : 0;

    $t5 = $fetchTracerGroup('T5');
    $t5Total = 0; $t5Atas = 0;
    foreach ($t5 as $r) {
        if (stripos($r['teks_opsi'], 'Belum berpenghasilan') !== false) continue;
        $t5Total += (int)$r['cnt'];
        if (stripos($r['teks_opsi'], 'Di atas UMP') !== false) $t5Atas += (int)$r['cnt'];
    }
    $tracer['gaji_atas_ump_pct'] = $t5Total > 0 ? round(($t5Atas / $t5Total) * 100, 1) : 0;
}

// 6. Rekapitulasi Ulasan & Masukan (filter periode, peran, kelompok, jenis)
$ulasan = [];
if ($periode_id) {
    $sql_ulasan = "
        SELECT DATE_FORMAT(pg.waktu_submit, '%M %Y') as bulan_tahun, u.peran, m.nama as topik, j.teks, j.alasan
        FROM jawaban j
        JOIN pengisian pg ON j.pengisian_id = pg.id
        JOIN pengguna u ON pg.pengguna_id = u.id
        LEFT JOIN mata_kuliah m ON j.mata_kuliah_id = m.id
        WHERE pg.periode_id = ? AND pg.status = 'final'
          AND (j.teks IS NOT NULL OR j.alasan IS NOT NULL)";
    $params_ulasan = [$periode_id];
    if ($filter_peran)    { $sql_ulasan .= " AND u.peran = ?";    $params_ulasan[] = $filter_peran; }
    if ($filter_kelompok) { $sql_ulasan .= " AND (m.kelompok = ? OR j.mata_kuliah_id IS NULL)"; $params_ulasan[] = $filter_kelompok; }
    if ($filter_jenis)    { $sql_ulasan .= " AND (m.jenis = ? OR j.mata_kuliah_id IS NULL)";    $params_ulasan[] = $filter_jenis; }
    $sql_ulasan .= " ORDER BY pg.waktu_submit DESC";

    $stmt = $pdo->prepare($sql_ulasan);
    $stmt->execute($params_ulasan);
    $hasil_ulasan = $stmt->fetchAll();

    foreach ($hasil_ulasan as $u) {
        $konten = '';
        if ($u['alasan']) $konten .= "<strong>Alasan Nilai:</strong> " . escape($u['alasan']) . "<br>";
        if ($u['teks'])   $konten .= "<strong>Saran:</strong> " . escape($u['teks']);

        $bln = str_replace(
            ['January','February','March','April','May','June','July','August','September','October','November','December'],
            ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
            $u['bulan_tahun']
        );
        $peran = $u['peran'];
        if (($summary['role_breakdown'][$peran] ?? 0) < 3) {
            $peran = 'responden';
        }

        $ulasan[] = [
            'tanggal' => $bln,
            'peran' => ucfirst($peran),
            'topik' => $u['topik'] ?: 'Umum/Kompetensi',
            'isi' => $konten
        ];
    }
}

echo json_encode([
    'summary' => $summary,
    'radar_meta' => $radar_meta,
    'radar' => $radar_data,
    'tabel' => $tabel_perbandingan,
    'matkul_ranking' => $matkul_ranking,
    'alumni_instansi' => $alumni_instansi,
    'alumni_profesi' => $alumni_profesi,
    'tracer' => $tracer,
    'ulasan' => $ulasan
]);
?>
