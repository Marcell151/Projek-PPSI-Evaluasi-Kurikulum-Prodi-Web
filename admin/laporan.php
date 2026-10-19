<?php
// admin/laporan.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

require_role('admin');

// Ambil daftar periode
$stmt = $pdo->query("SELECT id, tahun_akademik, status FROM periode_evaluasi ORDER BY id DESC");
$list_periode = $stmt->fetchAll();

$periode_aktif_id = $_GET['periode_id'] ?? null;
if (!$periode_aktif_id && count($list_periode) > 0) {
    foreach ($list_periode as $p) {
        if ($p['status'] === 'buka') {
            $periode_aktif_id = $p['id'];
            break;
        }
    }
    if (!$periode_aktif_id) $periode_aktif_id = $list_periode[0]['id'];
}

$page_title = 'Laporan Evaluasi';
require_once '../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-brand-50 text-brand-500 rounded-full flex items-center justify-center text-3xl">
                <i class="ph ph-printer"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Pusat Laporan</h2>
                <p class="text-gray-500">Unduh data evaluasi kurikulum untuk dianalisis lebih lanjut.</p>
            </div>
        </div>
        
        <form action="" method="GET" class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Periode:</label>
            <select name="periode_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none bg-gray-50">
                <?php foreach ($list_periode as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $periode_aktif_id ? 'selected' : '' ?>>
                        <?= escape($p['tahun_akademik']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    
    <!-- Ekspor Excel -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-t-4 border-t-green-500 flex flex-col h-full">
        <div class="flex-grow">
            <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                <i class="ph ph-file-xls text-green-500 text-2xl"></i> Rekapitulasi Excel
            </h3>
            <p class="text-sm text-gray-600 mb-4">Unduh seluruh data mentah dan rekapitulasi nilai evaluasi (termasuk esai dan skor skala) ke dalam format Microsoft Excel (.xlsx).</p>
            
            <ul class="text-sm text-gray-500 space-y-2 mb-6">
                <li class="flex items-center gap-2"><i class="ph ph-check-circle text-green-500"></i> Detail Jawaban per Responden</li>
                <li class="flex items-center gap-2"><i class="ph ph-check-circle text-green-500"></i> Nilai Rata-rata Mata Kuliah</li>
                <li class="flex items-center gap-2"><i class="ph ph-check-circle text-green-500"></i> Tren Kompetensi Alumni & Perusahaan</li>
            </ul>
        </div>
        
        <a href="<?= base_url('admin/export-excel.php?periode_id=' . $periode_aktif_id) ?>" class="block w-full text-center bg-green-500 hover:bg-green-600 text-white px-5 py-3 rounded-xl font-medium transition-colors shadow-sm">
            <i class="ph ph-download-simple mr-1"></i> Unduh File Excel
        </a>
    </div>

    <!-- Cetak PDF / Print -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-t-4 border-t-red-500 flex flex-col h-full">
        <div class="flex-grow">
            <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center gap-2">
                <i class="ph ph-file-pdf text-red-500 text-2xl"></i> Laporan PDF (Cetak)
            </h3>
            <p class="text-sm text-gray-600 mb-4">Buat laporan siap cetak berisikan ringkasan eksekutif, grafik radar, dan tabel rekapitulasi rata-rata tiap mata kuliah.</p>
            
            <ul class="text-sm text-gray-500 space-y-2 mb-6">
                <li class="flex items-center gap-2"><i class="ph ph-check-circle text-red-500"></i> Ringkasan Eksekutif Sistem</li>
                <li class="flex items-center gap-2"><i class="ph ph-check-circle text-red-500"></i> Grafik Radar Tren Kompetensi</li>
                <li class="flex items-center gap-2"><i class="ph ph-check-circle text-red-500"></i> Format siap cetak (A4)</li>
            </ul>
        </div>
        
        <a href="<?= base_url('admin/cetak-pdf.php?periode_id=' . $periode_aktif_id) ?>" target="_blank" class="block w-full text-center bg-red-500 hover:bg-red-600 text-white px-5 py-3 rounded-xl font-medium transition-colors shadow-sm">
            <i class="ph ph-printer mr-1"></i> Buka Mode Cetak / PDF
        </a>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>
