<?php
// admin/cetak-pdf.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

require_role('admin');

$periode_id = $_GET['periode_id'] ?? null;
if (!$periode_id) {
    die("Periode ID tidak diberikan.");
}

$stmt = $pdo->prepare("SELECT * FROM periode_evaluasi WHERE id = ?");
$stmt->execute([$periode_id]);
$periode = $stmt->fetch();

if (!$periode) {
    die("Periode tidak ditemukan.");
}

// 1. Ambil Summary
$summary = ['mahasiswa' => 0, 'dosen' => 0, 'alumni' => 0, 'perusahaan' => 0];
$stmt = $pdo->prepare("SELECT u.peran, COUNT(p.id) as total FROM pengisian p JOIN pengguna u ON p.pengguna_id = u.id WHERE p.periode_id = ? AND p.status = 'final' GROUP BY u.peran");
$stmt->execute([$periode_id]);
while ($row = $stmt->fetch()) {
    $summary[$row['peran']] = $row['total'];
}

// 2. Data Matkul
$stmt = $pdo->prepare("
    SELECT m.kode, m.nama, m.sks, AVG(j.nilai_skala) as rata_rata
    FROM jawaban j
    JOIN pertanyaan pt ON j.pertanyaan_id = pt.id
    JOIN mata_kuliah m ON j.mata_kuliah_id = m.id
    JOIN pengisian p ON j.pengisian_id = p.id
    WHERE p.periode_id = ? AND p.status = 'final' AND pt.tipe = 'skala'
    GROUP BY m.id
    ORDER BY rata_rata DESC
");
$stmt->execute([$periode_id]);
$rekap_matkul = $stmt->fetchAll();

// 3. Radar Data
$stmt_kategori = $pdo->query("SELECT id, nama FROM kategori_kompetensi ORDER BY id ASC");
$kategori_all = $stmt_kategori->fetchAll();

$radar_data = [];
$kompetensi_map = [];
foreach ($kategori_all as $k) {
    $nama = $k['nama'];
    $kompetensi_map[$nama] = ['alumni' => 0, 'perusahaan' => 0];
    $radar_data['labels'][] = $nama;
}

$sql = "SELECT 
            k.nama, u.peran,
            COUNT(jm.opsi_id) as jumlah_dipilih
        FROM jawaban_multi jm
        JOIN jawaban j ON jm.jawaban_id = j.id
        JOIN opsi_pertanyaan o ON jm.opsi_id = o.id
        JOIN kategori_kompetensi k ON o.kategori_kompetensi_id = k.id
        JOIN pengisian p ON j.pengisian_id = p.id
        JOIN pengguna u ON p.pengguna_id = u.id
        WHERE p.periode_id = ? 
          AND p.status = 'final' 
          AND u.peran IN ('alumni', 'perusahaan')
        GROUP BY k.id, k.nama, u.peran";
        
$stmt = $pdo->prepare($sql);
$stmt->execute([$periode_id]);
$hasil_radar = $stmt->fetchAll();

foreach ($hasil_radar as $row) {
    $nama = $row['nama'];
    if (isset($kompetensi_map[$nama])) {
        $kompetensi_map[$nama][$row['peran']] = (int)$row['jumlah_dipilih'];
    }
}

foreach ($radar_data['labels'] as $nama) {
    $radar_data['datasets'][0]['data'][] = $kompetensi_map[$nama]['alumni'];
    $radar_data['datasets'][1]['data'][] = $kompetensi_map[$nama]['perusahaan'];
}

// 4. Data Ulasan
$sql_ulasan = "
    SELECT DATE(p.waktu_submit) as tanggal, u.peran, m.nama as topik, j.teks, j.alasan
    FROM jawaban j
    JOIN pengisian p ON j.pengisian_id = p.id
    JOIN pengguna u ON p.pengguna_id = u.id
    LEFT JOIN mata_kuliah m ON j.mata_kuliah_id = m.id
    WHERE p.periode_id = ? AND p.status = 'final'
      AND (j.teks IS NOT NULL OR j.alasan IS NOT NULL)
    ORDER BY p.waktu_submit DESC
";
$stmt = $pdo->prepare($sql_ulasan);
$stmt->execute([$periode_id]);
$ulasan_mentah = $stmt->fetchAll();

$ulasan = [];
foreach ($ulasan_mentah as $u) {
    $peran = $u['peran'];
    if (($summary[$peran] ?? 0) < 3) {
        $peran = 'responden';
    }
    $u['peran_final'] = $peran;
    $ulasan[] = $u;
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Evaluasi Kurikulum - <?= escape($periode['tahun_akademik']) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <script src="<?= base_url('assets/vendor/chart.js') ?>"></script>
    <style>
        @media print {
            body { font-size: 12pt; background: #fff !important; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
        .print-container { max-width: 210mm; margin: 0 auto; background: #fff; padding: 20mm; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); min-height: 297mm; }
        @media print { .print-container { box-shadow: none; padding: 0; margin: 0; } }
    </style>
</head>
<body class="py-8">

    <div class="max-w-[210mm] mx-auto mb-4 flex justify-end no-print">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium shadow flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M208,32H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM160,208H96V160h64Zm48,0H176V152a8,8,0,0,0-8-8H88a8,8,0,0,0-8,8v56H48V48H208Z"></path></svg>
            Cetak Laporan (PDF)
        </button>
    </div>

    <div class="print-container">
        
        <!-- Header -->
        <div class="text-center border-b-2 border-gray-900 pb-6 mb-8">
            <h1 class="text-2xl font-bold uppercase tracking-wide">Laporan Evaluasi Kurikulum</h1>
            <h2 class="text-lg font-semibold text-gray-700 mt-1">Program Studi Sistem Informasi</h2>
            <p class="text-gray-500 mt-2">Periode Akademik: <strong><?= escape($periode['tahun_akademik']) ?></strong></p>
        </div>

        <!-- Ringkasan Eksekutif -->
        <div class="mb-10">
            <h3 class="text-lg font-bold border-b border-gray-300 mb-4 pb-1">1. Ringkasan Partisipasi</h3>
            <div class="grid grid-cols-4 gap-4 text-center">
                <div class="border border-gray-200 p-4 rounded-lg bg-gray-50">
                    <p class="text-3xl font-bold text-gray-900"><?= $summary['mahasiswa'] ?></p>
                    <p class="text-sm text-gray-600 mt-1">Mahasiswa</p>
                </div>
                <div class="border border-gray-200 p-4 rounded-lg bg-gray-50">
                    <p class="text-3xl font-bold text-gray-900"><?= $summary['dosen'] ?></p>
                    <p class="text-sm text-gray-600 mt-1">Dosen</p>
                </div>
                <div class="border border-gray-200 p-4 rounded-lg bg-gray-50">
                    <p class="text-3xl font-bold text-gray-900"><?= $summary['alumni'] ?></p>
                    <p class="text-sm text-gray-600 mt-1">Alumni</p>
                </div>
                <div class="border border-gray-200 p-4 rounded-lg bg-gray-50">
                    <p class="text-3xl font-bold text-gray-900"><?= $summary['perusahaan'] ?></p>
                    <p class="text-sm text-gray-600 mt-1">Perusahaan Mitra</p>
                </div>
            </div>
        </div>

        <!-- Radar Chart -->
        <div class="mb-10 page-break">
            <h3 class="text-lg font-bold border-b border-gray-300 mb-4 pb-1">2. Tren Warna Program Studi (Perspektif Eksternal)</h3>
            <p class="text-sm text-gray-600 mb-4">Grafik ini menunjukkan persepsi nilai kompetensi lulusan berdasarkan isian dari Alumni dan Perusahaan Mitra (Skala 1-5).</p>
            
            <div class="flex justify-center" style="height: 400px;">
                <canvas id="pdfRadarChart"></canvas>
            </div>
            
            <table class="w-full text-left border-collapse mt-6 border border-gray-200">
                <thead>
                    <tr class="bg-gray-100 text-sm font-bold">
                        <th class="border border-gray-200 px-4 py-2">Kategori Kompetensi</th>
                        <th class="border border-gray-200 px-4 py-2 text-center w-32">Rata-rata</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($radar_data['labels'] ?? []) === 0): ?>
                        <tr><td colspan="2" class="border border-gray-200 px-4 py-4 text-center text-gray-500">Belum ada data kompetensi.</td></tr>
                    <?php else: ?>
                        <?php foreach ($radar_data['labels'] as $index => $nama): ?>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-sm"><?= escape($nama) ?></td>
                                <td class="border border-gray-200 px-4 py-2 text-center font-semibold text-sm"><?= $radar_data['datasets'][0]['data'][$index] + $radar_data['datasets'][1]['data'][$index] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Rekap Matkul -->
        <div class="mb-8 page-break">
            <h3 class="text-lg font-bold border-b border-gray-300 mb-4 pb-1">3. Rekapitulasi Evaluasi Mata Kuliah</h3>
            <table class="w-full text-left border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100 text-sm font-bold">
                        <th class="border border-gray-200 px-4 py-2 w-24">Kode</th>
                        <th class="border border-gray-200 px-4 py-2">Mata Kuliah</th>
                        <th class="border border-gray-200 px-4 py-2 text-center w-20">SKS</th>
                        <th class="border border-gray-200 px-4 py-2 text-center w-32">Rata-rata Skor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rekap_matkul) === 0): ?>
                        <tr><td colspan="4" class="border border-gray-200 px-4 py-6 text-center text-gray-500">Belum ada data evaluasi mata kuliah.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rekap_matkul as $rm): ?>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-sm font-medium"><?= escape($rm['kode']) ?></td>
                                <td class="border border-gray-200 px-4 py-2 text-sm"><?= escape($rm['nama']) ?></td>
                                <td class="border border-gray-200 px-4 py-2 text-sm text-center"><?= escape($rm['sks']) ?></td>
                                <td class="border border-gray-200 px-4 py-2 text-center font-bold <?= $rm['rata_rata'] >= 4 ? 'text-green-600' : ($rm['rata_rata'] < 3 ? 'text-red-600' : '') ?>">
                                    <?= round($rm['rata_rata'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <p class="text-xs text-gray-500 mt-2">* Skor maksimal adalah 5.00</p>
        </div>
        
        <!-- Rekapitulasi Ulasan -->
        <div class="mb-8 page-break">
            <h3 class="text-lg font-bold border-b border-gray-300 mb-4 pb-1">4. Rekapitulasi Ulasan & Masukan Responden</h3>
            <table class="w-full text-left border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100 text-sm font-bold">
                        <th class="border border-gray-200 px-4 py-2 w-28">Tanggal</th>
                        <th class="border border-gray-200 px-4 py-2 w-24">Peran</th>
                        <th class="border border-gray-200 px-4 py-2 w-48">Topik / Matkul</th>
                        <th class="border border-gray-200 px-4 py-2">Isi Catatan / Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($ulasan) === 0): ?>
                        <tr><td colspan="4" class="border border-gray-200 px-4 py-6 text-center text-gray-500">Belum ada ulasan atau masukan teks.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ulasan as $u): ?>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-sm whitespace-nowrap"><?= escape($u['tanggal']) ?></td>
                                <td class="border border-gray-200 px-4 py-2 text-sm font-medium"><?= escape(ucfirst($u['peran_final'])) ?></td>
                                <td class="border border-gray-200 px-4 py-2 text-sm"><?= escape($u['topik'] ?: 'Umum/Kompetensi') ?></td>
                                <td class="border border-gray-200 px-4 py-2 text-sm">
                                    <?php if ($u['alasan']): ?><strong>Alasan Nilai:</strong> <?= escape($u['alasan']) ?><br><?php endif; ?>
                                    <?php if ($u['teks']): ?><?= escape($u['teks']) ?><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-16 pt-8 border-t border-gray-300 text-sm text-gray-500 text-center">
            Dicetak oleh Sistem Informasi Evaluasi Kurikulum pada <?= date('d M Y, H:i') ?>
        </div>
    </div>

    <script>
        const radarData = <?= json_encode($radar_data) ?>;
        if (radarData && radarData.labels && radarData.labels.length > 0) {
            new Chart(document.getElementById('pdfRadarChart'), {
                type: 'radar',
                data: {
                    labels: radarData.labels,
                    datasets: [
                        {
                            label: 'Alumni',
                            data: radarData.datasets[0].data,
                            backgroundColor: 'rgba(59, 130, 246, 0.2)', // Blue
                            borderColor: 'rgba(59, 130, 246, 1)',
                            pointBackgroundColor: 'rgba(59, 130, 246, 1)'
                        },
                        {
                            label: 'Perusahaan',
                            data: radarData.datasets[1].data,
                            backgroundColor: 'rgba(12, 161, 78, 0.2)', // Green
                            borderColor: 'rgba(12, 161, 78, 1)',
                            pointBackgroundColor: 'rgba(12, 161, 78, 1)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // Penting agar grafik langsung dirender untuk PDF Print
                    scales: {
                        r: { min: 0, ticks: { stepSize: 1 } }
                    }
                }
            });
            
            // Auto trigger print dialog after chart renders
            // setTimeout(() => window.print(), 500);
        }
    </script>
</body>
</html>
