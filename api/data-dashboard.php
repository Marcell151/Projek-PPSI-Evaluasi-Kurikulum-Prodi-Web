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

$periode_id = $_GET['periode_id'] ?? null;

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

$stmt = $pdo->query("SELECT COUNT(*) FROM pengguna WHERE peran != 'admin'");
$summary['total_responden'] = $stmt->fetchColumn();

if ($periode_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pengisian WHERE periode_id = ? AND status = 'final'");
    $stmt->execute([$periode_id]);
    $summary['pengisian_selesai'] = $stmt->fetchColumn();
    
    // Breakdown
    $stmt = $pdo->prepare("SELECT u.peran, COUNT(pg.id) as cnt FROM pengisian pg JOIN pengguna u ON pg.pengguna_id = u.id WHERE pg.periode_id = ? AND pg.status = 'final' GROUP BY u.peran");
    $stmt->execute([$periode_id]);
    foreach ($stmt->fetchAll() as $r) {
        $summary['role_breakdown'][$r['peran']] = (int)$r['cnt'];
    }
} else {
    $stmt = $pdo->query("SELECT COUNT(*) FROM pengisian WHERE status = 'final'");
    $summary['pengisian_selesai'] = $stmt->fetchColumn();
}

$stmt = $pdo->query("SELECT COUNT(*) FROM mata_kuliah WHERE aktif = TRUE");
$summary['matkul_aktif'] = $stmt->fetchColumn();

// 2. Data Grafik Radar (Tren Warna Prodi - Frekuensi Pilihan B7/B8)
$radar_data = [
    'labels' => [],
    'datasets' => [
        [
            'label' => 'Alumni',
            'data' => [],
            'backgroundColor' => 'rgba(59, 130, 246, 0.2)', // Blue
            'borderColor' => 'rgba(59, 130, 246, 1)',
            'pointBackgroundColor' => 'rgba(59, 130, 246, 1)',
        ],
        [
            'label' => 'Perusahaan',
            'data' => [],
            'backgroundColor' => 'rgba(12, 161, 78, 0.2)', // Green
            'borderColor' => 'rgba(12, 161, 78, 1)',
            'pointBackgroundColor' => 'rgba(12, 161, 78, 1)',
        ]
    ]
];

$tabel_perbandingan = [];

if ($periode_id) {
    // Ambil total responden final per peran (alumni & perusahaan) untuk persentase
    $total_alumni = $summary['role_breakdown']['alumni'] ?? 0;
    $total_perusahaan = $summary['role_breakdown']['perusahaan'] ?? 0;

    // Ambil semua kategori kompetensi agar radar chart selalu memiliki titik
    $stmt_kategori = $pdo->query("SELECT id, nama FROM kategori_kompetensi ORDER BY id ASC");
    $kategori_all = $stmt_kategori->fetchAll();
    
    $kompetensi_map = [];
    foreach ($kategori_all as $k) {
        $nama = $k['nama'];
        $kompetensi_map[$nama] = ['alumni' => 0, 'perusahaan' => 0];
        $radar_data['labels'][] = $nama;
    }

    $sql = "SELECT 
                k.id, k.nama, u.peran,
                COUNT(jm.opsi_id) as jumlah_user_memilih
            FROM jawaban_multi jm
            JOIN jawaban j ON jm.jawaban_id = j.id
            JOIN pertanyaan pt ON j.pertanyaan_id = pt.id
            JOIN opsi_pertanyaan o ON jm.opsi_id = o.id
            JOIN kategori_kompetensi k ON o.kategori_kompetensi_id = k.id
            JOIN pengisian pg ON j.pengisian_id = pg.id
            JOIN pengguna u ON pg.pengguna_id = u.id
            WHERE pg.periode_id = ? 
              AND pg.status = 'final' 
              AND u.peran IN ('alumni', 'perusahaan')
              AND pt.penanda = 'WARNA_PRODI'
            GROUP BY k.id, k.nama, u.peran";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$periode_id]);
    $hasil_radar = $stmt->fetchAll();

    $total_pilihan_alumni = 0;
    $total_pilihan_perusahaan = 0;
    foreach ($hasil_radar as $row) {
        $nama = $row['nama'];
        if (isset($kompetensi_map[$nama])) {
            $kompetensi_map[$nama][$row['peran']] = (int)$row['jumlah_user_memilih'];
            if ($row['peran'] === 'alumni') $total_pilihan_alumni += (int)$row['jumlah_user_memilih'];
            if ($row['peran'] === 'perusahaan') $total_pilihan_perusahaan += (int)$row['jumlah_user_memilih'];
        }
    }

    foreach ($radar_data['labels'] as $nama) {
        $pct_alumni = $total_pilihan_alumni > 0 ? round(($kompetensi_map[$nama]['alumni'] / $total_pilihan_alumni) * 100, 2) : 0;
        $pct_perusahaan = $total_pilihan_perusahaan > 0 ? round(($kompetensi_map[$nama]['perusahaan'] / $total_pilihan_perusahaan) * 100, 2) : 0;

        $radar_data['datasets'][0]['data'][] = $pct_alumni;
        $radar_data['datasets'][1]['data'][] = $pct_perusahaan;
        
        $tabel_perbandingan[] = [
            'kompetensi' => $nama,
            'alumni_jumlah' => $kompetensi_map[$nama]['alumni'],
            'alumni_pct' => $pct_alumni,
            'perusahaan_jumlah' => $kompetensi_map[$nama]['perusahaan'],
            'perusahaan_pct' => $pct_perusahaan
        ];
    }
}

// 3. Top 5 & Bottom 5 Mata Kuliah
$matkul_ranking = ['top' => [], 'bottom' => []];
if ($periode_id) {
    // Ambil rata-rata nilai_skala HANYA untuk pertanyaan bertanda untuk_peringkat dari setiap matkul, plus jumlah penilai
    $sql_mk = "SELECT m.kode, m.nama, m.kelompok, AVG(j.nilai_skala) as skor, COUNT(DISTINCT pg.pengguna_id) as jumlah_penilai
               FROM jawaban j
               JOIN pertanyaan pt ON j.pertanyaan_id = pt.id
               JOIN mata_kuliah m ON j.mata_kuliah_id = m.id
               JOIN pengisian pg ON j.pengisian_id = pg.id
               WHERE pg.periode_id = ? AND pg.status = 'final' 
                 AND j.mata_kuliah_id IS NOT NULL 
                 AND j.nilai_skala IS NOT NULL
                 AND pt.untuk_peringkat = 1
               GROUP BY m.id
               ORDER BY skor DESC";
    $stmt = $pdo->prepare($sql_mk);
    $stmt->execute([$periode_id]);
    $all_mk = $stmt->fetchAll();
    
    if (count($all_mk) > 0) {
        $matkul_ranking['top'] = array_slice($all_mk, 0, 5);
        $bottom = array_slice($all_mk, -5);
        // Sort bottom from lowest to highest
        usort($bottom, function($a, $b) { return $a['skor'] <=> $b['skor']; });
        $matkul_ranking['bottom'] = $bottom;
    }
}

// 4. Sebaran Posisi/Profesi & Instansi Alumni
$alumni_instansi = ['labels' => [], 'data' => []];
$alumni_profesi = ['labels' => [], 'data' => []];

$sql_alumni = "SELECT p.instansi, p.posisi_pekerjaan
               FROM profil_pengguna p 
               JOIN pengguna u ON p.pengguna_id = u.id 
               WHERE u.peran = 'alumni'";
$stmt = $pdo->query($sql_alumni);
$instansi_count = [];
$profesi_count = [];

foreach ($stmt->fetchAll() as $row) {
    $inst = trim(preg_replace('/\s+/', ' ', strtoupper($row['instansi'] ?? '')));
    $prof = trim(preg_replace('/\s+/', ' ', strtoupper($row['posisi_pekerjaan'] ?? '')));
    
    if ($inst != '') {
        $instansi_count[$inst] = ($instansi_count[$inst] ?? 0) + 1;
    }
    if ($prof != '') {
        $profesi_count[$prof] = ($profesi_count[$prof] ?? 0) + 1;
    }
}

foreach ($instansi_count as $k => $v) { $alumni_instansi['labels'][] = $k; $alumni_instansi['data'][] = $v; }
foreach ($profesi_count as $k => $v) { $alumni_profesi['labels'][] = $k; $alumni_profesi['data'][] = $v; }

// 5. Rekapitulasi Ulasan & Masukan
$ulasan = [];
if ($periode_id) {
    $sql_ulasan = "
        SELECT DATE_FORMAT(pg.waktu_submit, '%M %Y') as bulan_tahun, u.peran, m.nama as topik, j.teks, j.alasan
        FROM jawaban j
        JOIN pengisian pg ON j.pengisian_id = pg.id
        JOIN pengguna u ON pg.pengguna_id = u.id
        LEFT JOIN mata_kuliah m ON j.mata_kuliah_id = m.id
        WHERE pg.periode_id = ? AND pg.status = 'final'
          AND (j.teks IS NOT NULL OR j.alasan IS NOT NULL)
        ORDER BY pg.waktu_submit DESC
    ";
    $stmt = $pdo->prepare($sql_ulasan);
    $stmt->execute([$periode_id]);
    $hasil_ulasan = $stmt->fetchAll();
    
    foreach ($hasil_ulasan as $u) {
        $konten = '';
        if ($u['alasan']) $konten .= "<strong>Alasan Nilai:</strong> " . escape($u['alasan']) . "<br>";
        if ($u['teks']) $konten .= "<strong>Saran:</strong> " . escape($u['teks']);
        
        // Terjemahan sederhana bulan
        $bln = str_replace(['January','February','March','April','May','June','July','August','September','October','November','December'],
                           ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'], $u['bulan_tahun']);
        // R24: Anonimkan peran jika < 3
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
    'radar' => $radar_data,
    'tabel' => $tabel_perbandingan,
    'matkul_ranking' => $matkul_ranking,
    'alumni_instansi' => $alumni_instansi,
    'alumni_profesi' => $alumni_profesi,
    'ulasan' => $ulasan
]);
?>
