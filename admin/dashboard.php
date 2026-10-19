<?php
// admin/dashboard.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

require_role('admin');

// Ambil daftar periode untuk filter
$stmt = $pdo->query("SELECT id, tahun_akademik, status FROM periode_evaluasi ORDER BY id DESC");
$list_periode = $stmt->fetchAll();

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
<script src="<?= base_url('assets/vendor/chart.js') ?>"></script>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-brand-50 text-brand-500 rounded-full flex items-center justify-center text-3xl">
                <i class="ph ph-chart-pie-slice"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Dashboard Evaluasi</h2>
                <p class="text-gray-500">Ringkasan hasil evaluasi kurikulum.</p>
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="flex items-center">
                <label class="text-sm font-medium text-gray-700 mr-2">Filter Periode:</label>
                <select id="filter-periode" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none bg-gray-50">
                    <?php foreach ($list_periode as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $periode_aktif_id ? 'selected' : '' ?>>
                            <?= escape($p['tahun_akademik']) ?> <?= $p['status'] === 'buka' ? '(Aktif)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center gap-2 border-l border-gray-200 pl-3">
                <button type="button" onclick="unduhLaporan('pdf')" class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-1 transition-colors border border-red-200">
                    <i class="ph ph-file-pdf"></i> PDF
                </button>
                <button type="button" onclick="unduhLaporan('excel')" class="bg-green-50 hover:bg-green-100 text-green-600 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-1 transition-colors border border-green-200">
                    <i class="ph ph-file-xls"></i> Excel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center text-2xl">
            <i class="ph ph-users"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Responden Akun</p>
            <p class="text-2xl font-bold text-gray-900" id="stat-responden">0</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-50 text-green-500 rounded-lg flex items-center justify-center text-2xl">
            <i class="ph ph-check-square-offset"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Pengisian Selesai</p>
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

<!-- Grafik dan Tabel (Warna Prodi) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    
    <!-- Radar Chart -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 mb-1 flex items-center gap-2"><i class="ph ph-target text-brand-500 text-xl"></i> Tren Warna Prodi</h3>
        <p class="text-sm text-gray-500 mb-6">Penilaian relevansi kompetensi dari sisi Alumni & Perusahaan.</p>
        
        <div class="relative h-80 w-full flex justify-center items-center">
            <canvas id="radarChart"></canvas>
            <div id="radar-empty" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90 hidden">
                <p class="text-gray-500 font-medium"><i class="ph ph-warning-circle"></i> Belum ada data evaluasi di periode ini.</p>
            </div>
        </div>
    </div>

    <!-- Tabel Perbandingan -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 mb-1 flex items-center gap-2"><i class="ph ph-table text-brand-500 text-xl"></i> Tabel Perbandingan Kompetensi</h3>
        <p class="text-sm text-gray-500 mb-6">Persentase pilihan responden.</p>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3 font-medium">Kategori</th>
                        <th class="px-4 py-3 font-medium text-center">Alumni<br><span class="text-gray-400">(Jml & %)</span></th>
                        <th class="px-4 py-3 font-medium text-center">Perusahaan<br><span class="text-gray-400">(Jml & %)</span></th>
                    </tr>
                </thead>
                <tbody id="tabel-kompetensi" class="divide-y divide-gray-100 text-sm">
                    <!-- Data dimuat via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tambahan Metrik Baru: Top/Bottom Matkul & Sebaran Alumni -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <!-- Top 5 & Bottom 5 Mata Kuliah -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="ph ph-ranking text-brand-500 text-xl"></i> Peringkat Mata Kuliah</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Top 5 -->
            <div>
                <h4 class="text-sm font-semibold text-green-600 mb-3 flex items-center gap-1"><i class="ph ph-trend-up"></i> Top 5 Nilai Tertinggi</h4>
                <ul class="space-y-3" id="list-top-matkul">
                    <!-- Loaded via JS -->
                </ul>
            </div>
            <!-- Bottom 5 -->
            <div>
                <h4 class="text-sm font-semibold text-red-600 mb-3 flex items-center gap-1"><i class="ph ph-trend-down"></i> Top 5 Nilai Terendah</h4>
                <ul class="space-y-3" id="list-bottom-matkul">
                    <!-- Loaded via JS -->
                </ul>
            </div>
        </div>
    </div>

    <!-- Sebaran Profesi & Instansi Alumni -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 mb-1 flex items-center gap-2"><i class="ph ph-briefcase text-brand-500 text-xl"></i> Sebaran Alumni</h3>
        <p class="text-xs text-gray-500 mb-4">Berdasarkan profil posisi dan instansi.</p>
        
        <div class="grid grid-cols-2 gap-4">
            <div class="relative h-48 w-full flex flex-col justify-center items-center">
                <span class="text-xs font-semibold text-gray-600 mb-2">Posisi / Profesi</span>
                <canvas id="pieChartProfesi"></canvas>
                <div id="pie-profesi-empty" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90 hidden mt-6">
                    <p class="text-gray-400 font-medium text-xs">Belum ada data</p>
                </div>
            </div>
            <div class="relative h-48 w-full flex flex-col justify-center items-center">
                <span class="text-xs font-semibold text-gray-600 mb-2">Instansi</span>
                <canvas id="pieChartInstansi"></canvas>
                <div id="pie-instansi-empty" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90 hidden mt-6">
                    <p class="text-gray-400 font-medium text-xs">Belum ada data</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Rekapitulasi Ulasan -->
<div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h3 class="font-bold text-gray-900 flex items-center gap-2"><i class="ph ph-chat-text text-brand-500 text-xl"></i> Rekapitulasi Ulasan & Masukan</h3>
            <p class="text-xs text-gray-500">Tanggapan deskriptif dan alasan skor rendah dari responden.</p>
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
            <tbody id="tabel-ulasan" class="divide-y divide-gray-100">
                <!-- Loaded via JS -->
            </tbody>
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
let ulasanData = [];
let currentUlasanPage = 1;
const itemsPerPage = 5;

function renderUlasanTable() {
    const tUlasan = document.getElementById('tabel-ulasan');
    const pControls = document.getElementById('pagination-controls');
    tUlasan.innerHTML = '';
    
    if (!ulasanData || ulasanData.length === 0) {
        tUlasan.innerHTML = `<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada ulasan atau masukan teks.</td></tr>`;
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

function loadDashboardData(periodeId) {
    fetch(BASE_URL + '/api/data-dashboard.php?periode_id=' + periodeId)
        .then(res => res.json())
        .then(data => {
            // Update Summary
            document.getElementById('stat-responden').textContent = data.summary.total_responden;
            document.getElementById('stat-selesai').textContent = data.summary.pengisian_selesai;
            document.getElementById('stat-matkul').textContent = data.summary.matkul_aktif;
            
            const b = data.summary.role_breakdown;
            document.getElementById('stat-role-breakdown').innerHTML = 
                `MHS: <span class="font-semibold text-gray-700">${b.mahasiswa}</span> &bull; 
                 ALM: <span class="font-semibold text-gray-700">${b.alumni}</span> &bull; 
                 PRS: <span class="font-semibold text-gray-700">${b.perusahaan}</span> &bull; 
                 DSN: <span class="font-semibold text-gray-700">${b.dosen}</span>`;

            // Update Radar Chart
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

            // Update Tabel
            const tbody = document.getElementById('tabel-kompetensi');
            tbody.innerHTML = '';
            if (data.tabel.length === 0) {
                tbody.innerHTML = `<tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">Belum ada data evaluasi</td></tr>`;
            } else {
                data.tabel.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50';
                    tr.innerHTML = `
                        <td class="px-4 py-4 font-medium text-gray-900">${row.kompetensi}</td>
                        <td class="px-4 py-4 text-center">
                            <span class="font-bold text-blue-600">${row.alumni_jumlah}</span>
                            <span class="text-xs text-gray-500 ml-1">(${row.alumni_pct}%)</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="font-bold text-green-600">${row.perusahaan_jumlah}</span>
                            <span class="text-xs text-gray-500 ml-1">(${row.perusahaan_pct}%)</span>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            // Update Peringkat Matkul
            const topList = document.getElementById('list-top-matkul');
            const bottomList = document.getElementById('list-bottom-matkul');
            topList.innerHTML = ''; bottomList.innerHTML = '';
            
            if (data.matkul_ranking.top.length === 0) {
                topList.innerHTML = '<li class="text-sm text-gray-500">Belum ada penilaian.</li>';
                bottomList.innerHTML = '<li class="text-sm text-gray-500">Belum ada penilaian.</li>';
            } else {
                data.matkul_ranking.top.forEach((m, idx) => {
                    const limited = m.jumlah_penilai < 3 ? '<span class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded ml-2">Data terbatas</span>' : '';
                    const badge = `<span class="text-[10px] bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded ml-2 uppercase">${m.kelompok}</span>`;
                    topList.innerHTML += `<li class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
                        <span class="text-sm font-medium text-gray-800"><span class="text-gray-400 mr-2">#${idx+1}</span> ${m.nama} ${badge} ${limited}</span>
                        <div class="text-right">
                            <div class="text-sm font-bold text-green-600">${parseFloat(m.skor).toFixed(2)}</div>
                            <div class="text-[10px] text-gray-500">${m.jumlah_penilai} penilai</div>
                        </div>
                    </li>`;
                });
                data.matkul_ranking.bottom.forEach((m, idx) => {
                    const limited = m.jumlah_penilai < 3 ? '<span class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded ml-2">Data terbatas</span>' : '';
                    const badge = `<span class="text-[10px] bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded ml-2 uppercase">${m.kelompok}</span>`;
                    bottomList.innerHTML += `<li class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
                        <span class="text-sm font-medium text-gray-800"><span class="text-gray-400 mr-2">#${idx+1}</span> ${m.nama} ${badge} ${limited}</span>
                        <div class="text-right">
                            <div class="text-sm font-bold text-red-600">${parseFloat(m.skor).toFixed(2)}</div>
                            <div class="text-[10px] text-gray-500">${m.jumlah_penilai} penilai</div>
                        </div>
                    </li>`;
                });
            }

            // Update Pie Chart Profesi
            const ctxProfesi = document.getElementById('pieChartProfesi').getContext('2d');
            const emptyProfesi = document.getElementById('pie-profesi-empty');
            
            let profLabels = data.alumni_profesi.labels;
            let profData = data.alumni_profesi.data;
            let profColors = ['#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#14b8a6'];
            
            if (profLabels.length === 0) {
                profLabels = ['Belum ada data'];
                profData = [1];
                profColors = ['#e5e7eb']; // abu-abu
            }
            emptyProfesi.classList.add('hidden');
            if (window.pieChartProfesiInstance) window.pieChartProfesiInstance.destroy();
            window.pieChartProfesiInstance = new Chart(ctxProfesi, {
                type: 'doughnut',
                data: { labels: profLabels, datasets: [{ data: profData, backgroundColor: profColors }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '60%' }
            });
            
            // Update Pie Chart Instansi
            const ctxInstansi = document.getElementById('pieChartInstansi').getContext('2d');
            const emptyInstansi = document.getElementById('pie-instansi-empty');
            
            let instLabels = data.alumni_instansi.labels;
            let instData = data.alumni_instansi.data;
            let instColors = ['#0CA14E', '#f43f5e', '#6366f1', '#ec4899', '#84cc16'];
            
            if (instLabels.length === 0) {
                instLabels = ['Belum ada data'];
                instData = [1];
                instColors = ['#e5e7eb']; // abu-abu
            }
            emptyInstansi.classList.add('hidden');
            if (window.pieChartInstansiInstance) window.pieChartInstansiInstance.destroy();
            window.pieChartInstansiInstance = new Chart(ctxInstansi, {
                type: 'doughnut',
                data: { labels: instLabels, datasets: [{ data: instData, backgroundColor: instColors }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '60%' }
            });
            
            // Update Tabel Ulasan
            ulasanData = data.ulasan || [];
            currentUlasanPage = 1;
            renderUlasanTable();
        });
}

// Event Listener Filter
document.getElementById('filter-periode').addEventListener('change', function() {
    loadDashboardData(this.value);
});

// Load awal
loadDashboardData(document.getElementById('filter-periode').value);

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
