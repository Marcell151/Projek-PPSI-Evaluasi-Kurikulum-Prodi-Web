<?php
// admin/dashboard.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';
require_once '../includes/auth.php';

require_role('admin');

// Ambil daftar periode untuk filter
$stmt = $pdo->query("SELECT id, tahun_akademik, status FROM periode_evaluasi ORDER BY id DESC");
$list_periode = $stmt->fetchAll();

// Ambil daftar tahun lulus alumni untuk filter kontekstual Tracer Study
$list_tahun_lulus = $pdo->query("SELECT DISTINCT tahun_lulus FROM profil_pengguna WHERE tahun_lulus IS NOT NULL ORDER BY tahun_lulus DESC")->fetchAll(PDO::FETCH_COLUMN);

// Cari ID periode buka untuk default filter
$periode_aktif_id = null;
foreach ($list_periode as $p) {
    if ($p['status'] === 'buka') {
        $periode_aktif_id = $p['id'];
        break;
    }
}
if (!$periode_aktif_id && count($list_periode) > 0) {
    $periode_aktif_id = $list_periode[0]['id'];
}

$page_title = 'Dashboard Admin';
require_once '../includes/header.php';
?>

<!-- Chart.js -->
<script src="<?= base_url('assets/vendor/chart.js') ?>" onerror="this.onerror=null;this.src='https://cdn.jsdelivr.net/npm/chart.js';"></script>

<!-- Header & 2 Filter Global (1 Baris Rapi) -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-brand-50 text-brand-500 rounded-full flex items-center justify-center text-3xl flex-shrink-0">
                <i class="ph ph-chart-pie-slice"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Dashboard Evaluasi</h2>
                <p class="text-sm text-gray-500">Ringkasan hasil evaluasi kurikulum &amp; Tracer Study Prodi Sistem Informasi.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select id="filter-periode" title="Filter Periode Evaluasi" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none bg-gray-50">
                <?php foreach ($list_periode as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $periode_aktif_id ? 'selected' : '' ?>>
                        <?= escape($p['tahun_akademik']) ?> <?= $p['status'] === 'buka' ? '(Aktif)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="filter-peran" title="Filter Peran Responden" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none bg-gray-50">
                <option value="">Semua Peran</option>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="dosen">Dosen</option>
                <option value="alumni">Alumni</option>
                <option value="perusahaan">Perusahaan</option>
            </select>

            <button type="button" onclick="resetDashboardFilters()" class="bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-1 transition-colors border border-gray-200" title="Reset semua filter">
                <i class="ph ph-arrow-counter-clockwise"></i> Reset
            </button>

            <div class="flex items-center gap-2 border-l border-gray-200 pl-2">
                <button type="button" onclick="unduhLaporan('pdf')" class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-1 transition-colors border border-red-200">
                    <i class="ph ph-file-pdf"></i> PDF
                </button>
                <button type="button" onclick="unduhLaporan('excel')" class="bg-green-50 hover:bg-green-100 text-green-600 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-1 transition-colors border border-green-200">
                    <i class="ph ph-file-xls"></i> Excel
                </button>
            </div>

            <form action="<?= base_url('api/reset-demo.php') ?>" method="POST" class="border-l border-gray-200 pl-2" onsubmit="return confirm('Kembalikan seluruh data ke kondisi awal? Perubahan atau pengisian percobaan terakhir akan direset.');">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_to" value="admin/dashboard.php">
                <button type="submit" class="inline-flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 hover:text-gray-800 rounded-lg transition-colors border border-gray-200 cursor-pointer" style="width: 38px; height: 38px;" title="Kembalikan data awal" aria-label="Kembalikan data awal">
                    <i class="ph ph-arrows-clockwise text-base"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<?= display_flash_message() ?>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center text-2xl">
            <i class="ph ph-users"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium" id="label-stat-responden">Total Responden Akun</p>
            <p class="text-2xl font-bold text-gray-900" id="stat-responden">0</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-50 text-green-500 rounded-lg flex items-center justify-center text-2xl">
            <i class="ph ph-check-square-offset"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium" id="label-stat-selesai">Pengisian Selesai</p>
            <p class="text-2xl font-bold text-gray-900" id="stat-selesai">0</p>
            <p class="text-xs text-gray-500 mt-1" id="stat-role-breakdown"></p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-lg flex items-center justify-center text-2xl">
            <i class="ph ph-books"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Mata Kuliah Aktif</p>
            <p class="text-2xl font-bold text-gray-900" id="stat-matkul">0</p>
        </div>
    </div>
</div>

<!-- Grafik Radar & Tabel Kompetensi (Responsif untuk semua peran) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 mb-1 flex items-center gap-2"><i class="ph ph-target text-brand-500 text-xl"></i> <span id="radar-title">Tren Warna Prodi</span></h3>
        <p class="text-sm text-gray-500 mb-6" id="radar-subtitle">Perbandingan relevansi kompetensi dari sisi Alumni &amp; Perusahaan.</p>

        <div class="relative w-full flex justify-center items-center" style="height: 320px;">
            <canvas id="radarChart"></canvas>
            <div id="radar-empty" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90 hidden">
                <p class="text-gray-500 font-medium"><i class="ph ph-warning-circle"></i> Belum ada data evaluasi di periode ini.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 mb-1 flex items-center gap-2"><i class="ph ph-table text-brand-500 text-xl"></i> Tabel Perbandingan Kompetensi</h3>
        <p class="text-sm text-gray-500 mb-6">Persentase pilihan responden sesuai filter aktif.</p>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead id="thead-kompetensi">
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3 font-medium">Kategori</th>
                        <th class="px-4 py-3 font-medium text-center">Alumni<br><span class="text-gray-400">(Jml &amp; %)</span></th>
                        <th class="px-4 py-3 font-medium text-center">Perusahaan<br><span class="text-gray-400">(Jml &amp; %)</span></th>
                    </tr>
                </thead>
                <tbody id="tabel-kompetensi" class="divide-y divide-gray-100 text-sm">
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Peringkat Mata Kuliah + Filter Lokal (Kelompok & Jenis) -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-gray-100">
        <div>
            <h3 class="font-bold text-gray-900 flex items-center gap-2"><i class="ph ph-ranking text-brand-500 text-xl"></i> Peringkat Mata Kuliah</h3>
            <p class="text-xs text-gray-500 mt-0.5">Berdasarkan rata-rata kelayakan mata kuliah (A1).</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select id="filter-kelompok" title="Filter Kelompok Matkul" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none bg-gray-50">
                <option value="">Semua Kelompok</option>
                <option value="prodi">Prodi</option>
                <option value="fakultas">Fakultas</option>
                <option value="universitas">Universitas</option>
            </select>
            <select id="filter-jenis" title="Filter Jenis Matkul" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none bg-gray-50">
                <option value="">Semua Jenis</option>
                <option value="wajib">Wajib</option>
                <option value="pilihan">Pilihan</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h4 class="text-sm font-semibold text-green-600 mb-3 flex items-center gap-1"><i class="ph ph-trend-up"></i> Top 5 Nilai Tertinggi</h4>
            <ul class="space-y-3" id="list-top-matkul"></ul>
        </div>
        <div>
            <h4 class="text-sm font-semibold text-red-600 mb-3 flex items-center gap-1"><i class="ph ph-trend-down"></i> Top 5 Nilai Terendah</h4>
            <ul class="space-y-3" id="list-bottom-matkul"></ul>
        </div>
    </div>
</div>

<!-- Section Khusus Tracer Study & Sebaran Alumni (otomatis tersembunyi saat filter peran = Mahasiswa/Dosen/Perusahaan) -->
<div id="section-alumni-tracer" class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><i class="ph ph-graduation-cap text-brand-500 text-xl"></i> Ringkasan Tracer Study &amp; Sebaran Alumni</h3>
            <p class="text-xs text-gray-500">Pemetaan lulusan Prodi Sistem Informasi (waktu tunggu, kesesuaian bidang, skala perusahaan, dan profesi).</p>
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-600">Angkatan Lulus:</label>
            <select id="filter-tahun" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none bg-white">
                <option value="">Semua Tahun</option>
                <?php foreach ($list_tahun_lulus as $th): ?>
                    <option value="<?= escape($th) ?>">Lulusan <?= escape($th) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- 3 Indikator KPI Tracer Study -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu Tunggu &lt; 6 Bulan</p>
                <p class="text-2xl font-bold text-green-600 mt-1" id="stat-waktu-tunggu">0%</p>
                <p class="text-xs text-gray-400 mt-0.5">Alumni bekerja (Tracer Study)</p>
            </div>
            <div class="w-11 h-11 bg-green-50 text-green-600 rounded-lg flex items-center justify-center text-2xl">
                <i class="ph ph-clock-countdown"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Kesesuaian Bidang SI/TI</p>
                <p class="text-2xl font-bold text-blue-600 mt-1" id="stat-kesesuaian">0%</p>
                <p class="text-xs text-gray-400 mt-0.5">Selaras latar belakang prodi</p>
            </div>
            <div class="w-11 h-11 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-2xl">
                <i class="ph ph-seal-check"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Gaji Di Atas UMP</p>
                <p class="text-2xl font-bold text-purple-600 mt-1" id="stat-gaji-ump">0%</p>
                <p class="text-xs text-gray-400 mt-0.5">Alumni bekerja &amp; wiraswasta</p>
            </div>
            <div class="w-11 h-11 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-2xl">
                <i class="ph ph-currency-circle-dollar"></i>
            </div>
        </div>
    </div>

    <!-- Bar Charts: Status Pekerjaan & Level Perusahaan -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h4 class="font-bold text-gray-900 mb-1 flex items-center gap-2"><i class="ph ph-chart-bar text-brand-500 text-lg"></i> Status Pekerjaan Alumni</h4>
            <p class="text-xs text-gray-500 mb-4">Distribusi aktivitas lulusan berdasarkan Tracer Study.</p>
            <div class="relative w-full" style="height: 240px;">
                <canvas id="barStatusTracer"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h4 class="font-bold text-gray-900 mb-1 flex items-center gap-2"><i class="ph ph-buildings text-brand-500 text-lg"></i> Level Perusahaan Alumni</h4>
            <p class="text-xs text-gray-500 mb-4">Skala perusahaan tempat alumni bekerja / berwirausaha.</p>
            <div class="relative w-full" style="height: 240px;">
                <canvas id="barLevelTracer"></canvas>
            </div>
        </div>
    </div>

    <!-- Pie Charts: Sebaran Profesi & Instansi Alumni -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h4 class="font-bold text-gray-900 mb-1 flex items-center gap-2"><i class="ph ph-briefcase text-brand-500 text-lg"></i> Sebaran Profesi &amp; Instansi Alumni</h4>
        <p class="text-xs text-gray-500 mb-4">Berdasarkan profil posisi pekerjaan dan instansi alumni.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-semibold text-gray-600 mb-2 text-center">Posisi / Profesi</p>
                <div class="relative w-full" style="height: 240px;">
                    <canvas id="pieChartProfesi"></canvas>
                    <div id="pie-profesi-empty" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90 hidden">
                        <p class="text-gray-400 font-medium text-xs">Belum ada data</p>
                    </div>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-600 mb-2 text-center">Instansi / Perusahaan</p>
                <div class="relative w-full" style="height: 240px;">
                    <canvas id="pieChartInstansi"></canvas>
                    <div id="pie-instansi-empty" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90 hidden">
                        <p class="text-gray-400 font-medium text-xs">Belum ada data</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Rekapitulasi Ulasan + Pencarian Lokal -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4">
        <div>
            <h3 class="font-bold text-gray-900 flex items-center gap-2"><i class="ph ph-chat-text text-brand-500 text-xl"></i> Rekapitulasi Ulasan &amp; Masukan</h3>
            <p class="text-xs text-gray-500">Tanggapan deskriptif dan alasan skor rendah sesuai filter aktif.</p>
        </div>
        <div class="relative w-full sm:w-64">
            <i class="ph ph-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
            <input type="text" id="filter-search-ulasan" placeholder="Cari topik atau kata kunci..." class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none bg-gray-50">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left">
            <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                <tr>
                    <th scope="col" class="px-4 py-3 font-semibold">TANGGAL</th>
                    <th scope="col" class="px-4 py-3 font-semibold">PERAN</th>
                    <th scope="col" class="px-4 py-3 font-semibold">TOPIK / MATKUL</th>
                    <th scope="col" class="px-4 py-3 font-semibold">ISI CATATAN / SARAN</th>
                </tr>
            </thead>
            <tbody id="tabel-ulasan" class="divide-y divide-gray-100"></tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4" id="pagination-controls" style="display:none;">
        <div class="text-sm text-gray-500">
            Menampilkan <span id="page-info-start" class="font-medium text-gray-900">0</span> - <span id="page-info-end" class="font-medium text-gray-900">0</span> dari <span id="page-info-total" class="font-medium text-gray-900">0</span> ulasan
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-prev-page" class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" onclick="changeUlasanPage(-1)">
                Sebelumnya
            </button>
            <button type="button" id="btn-next-page" class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" onclick="changeUlasanPage(1)">
                Selanjutnya
            </button>
        </div>
    </div>
</div>

<script>
let radarChartInstance = null;
let barStatusInstance = null;
let barLevelInstance = null;
let ulasanRawData = [];
let ulasanData = [];
let currentUlasanPage = 1;
const itemsPerPage = 5;

function applyUlasanSearch() {
    const q = (document.getElementById('filter-search-ulasan').value || '').toLowerCase().trim();
    if (!q) {
        ulasanData = ulasanRawData.slice();
    } else {
        ulasanData = ulasanRawData.filter(u =>
            (u.topik || '').toLowerCase().includes(q) ||
            (u.isi || '').toLowerCase().includes(q) ||
            (u.peran || '').toLowerCase().includes(q)
        );
    }
    currentUlasanPage = 1;
    renderUlasanTable();
}

function renderUlasanTable() {
    const tUlasan = document.getElementById('tabel-ulasan');
    const pControls = document.getElementById('pagination-controls');
    tUlasan.innerHTML = '';

    if (!ulasanData || ulasanData.length === 0) {
        tUlasan.innerHTML = `<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada ulasan atau masukan teks sesuai filter.</td></tr>`;
        pControls.style.display = 'none';
        return;
    }

    pControls.style.display = 'flex';

    const totalPages = Math.ceil(ulasanData.length / itemsPerPage);
    if (currentUlasanPage > totalPages) currentUlasanPage = totalPages;
    if (currentUlasanPage < 1) currentUlasanPage = 1;

    const startIdx = (currentUlasanPage - 1) * itemsPerPage;
    const endIdx = Math.min(startIdx + itemsPerPage, ulasanData.length);
    const pageData = ulasanData.slice(startIdx, endIdx);

    pageData.forEach(u => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        tr.innerHTML = `
            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">${u.tanggal}</td>
            <td class="px-4 py-3 text-gray-600 whitespace-nowrap"><span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">${u.peran}</span></td>
            <td class="px-4 py-3 font-medium text-gray-800">${u.topik}</td>
            <td class="px-4 py-3 text-gray-600">${u.isi}</td>
        `;
        tUlasan.appendChild(tr);
    });

    document.getElementById('page-info-start').textContent = startIdx + 1;
    document.getElementById('page-info-end').textContent = endIdx;
    document.getElementById('page-info-total').textContent = ulasanData.length;

    document.getElementById('btn-prev-page').disabled = currentUlasanPage === 1;
    document.getElementById('btn-next-page').disabled = currentUlasanPage === totalPages;
}

function changeUlasanPage(direction) {
    currentUlasanPage += direction;
    renderUlasanTable();
}

function getDashboardQueryString() {
    const params = new URLSearchParams({
        periode_id:  document.getElementById('filter-periode').value,
        peran:       document.getElementById('filter-peran').value,
        kelompok:    document.getElementById('filter-kelompok').value,
        jenis:       document.getElementById('filter-jenis').value,
        tahun_lulus: document.getElementById('filter-tahun').value
    });
    return params.toString();
}

function resetDashboardFilters() {
    document.getElementById('filter-peran').value = '';
    document.getElementById('filter-kelompok').value = '';
    document.getElementById('filter-jenis').value = '';
    document.getElementById('filter-tahun').value = '';
    document.getElementById('filter-search-ulasan').value = '';
    loadDashboardData();
}

function loadDashboardData() {
    const selectedPeran = document.getElementById('filter-peran').value;
    const peranLabels = { mahasiswa: 'Mahasiswa', dosen: 'Dosen', alumni: 'Alumni', perusahaan: 'Perusahaan' };

    fetch(BASE_URL + '/api/data-dashboard.php?' + getDashboardQueryString())
        .then(res => res.json())
        .then(data => {
            // Update Summary
            document.getElementById('label-stat-responden').textContent = selectedPeran
                ? ('Total Akun ' + peranLabels[selectedPeran])
                : 'Total Responden Akun';
            document.getElementById('label-stat-selesai').textContent = selectedPeran
                ? ('Pengisian Selesai (' + peranLabels[selectedPeran] + ')')
                : 'Pengisian Selesai';

            document.getElementById('stat-responden').textContent = data.summary.total_responden;
            document.getElementById('stat-selesai').textContent = data.summary.pengisian_selesai;
            document.getElementById('stat-matkul').textContent = data.summary.matkul_aktif;

            const b = data.summary.role_breakdown;
            if (selectedPeran) {
                document.getElementById('stat-role-breakdown').innerHTML =
                    `Filter aktif: <span class="font-semibold text-brand-600">${peranLabels[selectedPeran]}</span>`;
            } else {
                document.getElementById('stat-role-breakdown').innerHTML =
                    `MHS: <span class="font-semibold text-gray-700">${b.mahasiswa}</span> &bull;
                     ALM: <span class="font-semibold text-gray-700">${b.alumni}</span> &bull;
                     PRS: <span class="font-semibold text-gray-700">${b.perusahaan}</span> &bull;
                     DSN: <span class="font-semibold text-gray-700">${b.dosen}</span>`;
            }

            // Update Judul & Radar Chart (dinamis sesuai peran!)
            const meta = data.radar_meta || { title: 'Tren Warna Prodi', subtitle: '', columns: ['Alumni', 'Perusahaan'] };
            document.getElementById('radar-title').textContent = meta.title;
            document.getElementById('radar-subtitle').textContent = meta.subtitle;

            const ctx = document.getElementById('radarChart').getContext('2d');
            const emptyState = document.getElementById('radar-empty');

            if (data.radar.labels.length === 0) {
                emptyState.classList.remove('hidden');
                if (radarChartInstance) radarChartInstance.destroy();
            } else {
                emptyState.classList.add('hidden');
                if (radarChartInstance) radarChartInstance.destroy();

                radarChartInstance = new Chart(ctx, {
                    type: 'radar',
                    data: data.radar,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                angleLines: { display: true },
                                min: 0,
                                max: 100,
                                ticks: { stepSize: 20, callback: function(value) { return value + '%'; } }
                            }
                        },
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }

            // Update Header & Body Tabel Kompetensi (dinamis 1 atau 2 kolom)
            const thead = document.getElementById('thead-kompetensi');
            let thHtml = `<tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider"><th class="px-4 py-3 font-medium">Kategori</th>`;
            meta.columns.forEach(col => {
                thHtml += `<th class="px-4 py-3 font-medium text-center">${col}<br><span class="text-gray-400">(Jml &amp; %)</span></th>`;
            });
            thHtml += `</tr>`;
            thead.innerHTML = thHtml;

            const tbody = document.getElementById('tabel-kompetensi');
            tbody.innerHTML = '';
            if (data.tabel.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${meta.columns.length + 1}" class="px-4 py-8 text-center text-gray-500">Belum ada data evaluasi</td></tr>`;
            } else {
                data.tabel.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50';
                    let cells = `<td class="px-4 py-4 font-medium text-gray-900">${row.kompetensi}</td>`;
                    row.values.forEach(v => {
                        cells += `<td class="px-4 py-4 text-center">
                            <span class="font-bold ${v.color}">${v.jumlah}</span>
                            <span class="text-xs text-gray-500 ml-1">(${v.pct}%)</span>
                        </td>`;
                    });
                    tr.innerHTML = cells;
                    tbody.appendChild(tr);
                });
            }

            // Update Peringkat Matkul
            const topList = document.getElementById('list-top-matkul');
            const bottomList = document.getElementById('list-bottom-matkul');
            topList.innerHTML = ''; bottomList.innerHTML = '';

            if (data.matkul_ranking.top.length === 0) {
                topList.innerHTML = '<li class="text-sm text-gray-500">Belum ada penilaian untuk filter ini.</li>';
                bottomList.innerHTML = '<li class="text-sm text-gray-500">Belum ada penilaian untuk filter ini.</li>';
            } else {
                data.matkul_ranking.top.forEach((m, idx) => {
                    const limited = m.jumlah_penilai < 3 ? '<span class="text-[10px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded ml-1">Data terbatas</span>' : '';
                    const badge = `<span class="text-[10px] bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded ml-1 uppercase">${m.kelompok}</span>`;
                    topList.innerHTML += `<li class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
                        <span class="text-sm font-medium text-gray-800"><span class="text-gray-400 mr-2">#${idx+1}</span> ${m.nama} ${badge} ${limited}</span>
                        <div class="text-right">
                            <div class="text-sm font-bold text-green-600">${parseFloat(m.skor).toFixed(2)}</div>
                            <div class="text-[10px] text-gray-500">${m.jumlah_penilai} penilai</div>
                        </div>
                    </li>`;
                });
                data.matkul_ranking.bottom.forEach((m, idx) => {
                    const limited = m.jumlah_penilai < 3 ? '<span class="text-[10px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded ml-1">Data terbatas</span>' : '';
                    const badge = `<span class="text-[10px] bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded ml-1 uppercase">${m.kelompok}</span>`;
                    bottomList.innerHTML += `<li class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
                        <span class="text-sm font-medium text-gray-800"><span class="text-gray-400 mr-2">#${idx+1}</span> ${m.nama} ${badge} ${limited}</span>
                        <div class="text-right">
                            <div class="text-sm font-bold text-red-600">${parseFloat(m.skor).toFixed(2)}</div>
                            <div class="text-[10px] text-gray-500">${m.jumlah_penilai} penilai</div>
                        </div>
                    </li>`;
                });
            }

            // Tampilkan / Sembunyikan Section Tracer Study & Sebaran Alumni sesuai filter peran!
            const alumniSection = document.getElementById('section-alumni-tracer');
            if (data.tracer && data.tracer.visible) {
                alumniSection.style.display = 'block';

                document.getElementById('stat-waktu-tunggu').textContent = data.tracer.waktu_tunggu_cepat_pct + '%';
                document.getElementById('stat-kesesuaian').textContent   = data.tracer.sesuai_bidang_pct + '%';
                document.getElementById('stat-gaji-ump').textContent     = data.tracer.gaji_atas_ump_pct + '%';

                // Bar Status Pekerjaan
                const ctxStatus = document.getElementById('barStatusTracer').getContext('2d');
                if (barStatusInstance) barStatusInstance.destroy();
                barStatusInstance = new Chart(ctxStatus, {
                    type: 'bar',
                    data: {
                        labels: data.tracer.status.labels.length ? data.tracer.status.labels : ['Belum ada data'],
                        datasets: [{
                            label: 'Jumlah Alumni',
                            data: data.tracer.status.data.length ? data.tracer.status.data : [0],
                            backgroundColor: ['#0CA14E', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b'],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                    }
                });

                // Bar Level Perusahaan
                const ctxLevel = document.getElementById('barLevelTracer').getContext('2d');
                if (barLevelInstance) barLevelInstance.destroy();
                barLevelInstance = new Chart(ctxLevel, {
                    type: 'bar',
                    data: {
                        labels: data.tracer.level.labels.length ? data.tracer.level.labels : ['Belum ada data'],
                        datasets: [{
                            label: 'Jumlah Alumni',
                            data: data.tracer.level.data.length ? data.tracer.level.data : [0],
                            backgroundColor: ['#ef4444', '#f59e0b', '#0CA14E'],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                    }
                });

                // Pie Profesi
                const ctxProfesi = document.getElementById('pieChartProfesi').getContext('2d');
                const emptyProfesi = document.getElementById('pie-profesi-empty');
                let profLabels = data.alumni_profesi.labels;
                let profData = data.alumni_profesi.data;
                let profColors = ['#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#14b8a6', '#0ea5e9', '#ec4899', '#84cc16', '#f97316', '#6366f1'];
                if (profLabels.length === 0) { profLabels = ['Belum ada data']; profData = [1]; profColors = ['#e5e7eb']; }
                emptyProfesi.classList.add('hidden');
                if (window.pieChartProfesiInstance) window.pieChartProfesiInstance.destroy();
                window.pieChartProfesiInstance = new Chart(ctxProfesi, {
                    type: 'doughnut',
                    data: { labels: profLabels, datasets: [{ data: profData, backgroundColor: profColors }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 11 } } } }, cutout: '55%' }
                });

                // Pie Instansi
                const ctxInstansi = document.getElementById('pieChartInstansi').getContext('2d');
                const emptyInstansi = document.getElementById('pie-instansi-empty');
                let instLabels = data.alumni_instansi.labels;
                let instData = data.alumni_instansi.data;
                let instColors = ['#0CA14E', '#f43f5e', '#6366f1', '#ec4899', '#84cc16', '#06b6d4', '#f59e0b', '#8b5cf6', '#10b981', '#ef4444', '#3b82f6', '#a855f7'];
                if (instLabels.length === 0) { instLabels = ['Belum ada data']; instData = [1]; instColors = ['#e5e7eb']; }
                emptyInstansi.classList.add('hidden');
                if (window.pieChartInstansiInstance) window.pieChartInstansiInstance.destroy();
                window.pieChartInstansiInstance = new Chart(ctxInstansi, {
                    type: 'doughnut',
                    data: { labels: instLabels, datasets: [{ data: instData, backgroundColor: instColors }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 11 } } } }, cutout: '55%' }
                });
            } else {
                alumniSection.style.display = 'none';
            }

            // Update Tabel Ulasan
            ulasanRawData = data.ulasan || [];
            applyUlasanSearch();
        });
}

// Event Listeners untuk Filter Global & Lokal
['filter-periode', 'filter-peran', 'filter-kelompok', 'filter-jenis', 'filter-tahun'].forEach(id => {
    document.getElementById(id).addEventListener('change', loadDashboardData);
});
document.getElementById('filter-search-ulasan').addEventListener('input', applyUlasanSearch);

// Load awal
loadDashboardData();

function unduhLaporan(jenis) {
    const periodeId = document.getElementById('filter-periode').value;
    if (jenis === 'pdf') {
        window.open(BASE_URL + '/admin/cetak-pdf.php?periode_id=' + periodeId, '_blank');
    } else if (jenis === 'excel') {
        window.location.href = BASE_URL + '/admin/export-excel.php?periode_id=' + periodeId;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
