<?php
// responden/formulir.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';

require_login();
if ($_SESSION['user_role'] === 'admin') { redirect('admin/dashboard.php'); }

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

$stmt = $pdo->prepare("SELECT * FROM periode_evaluasi WHERE status = 'buka' LIMIT 1");
$stmt->execute();
$periode = $stmt->fetch();
if (!$periode) { set_flash_message('error', 'Tidak ada periode evaluasi yang sedang dibuka.'); redirect('responden/beranda.php'); }

$stmt = $pdo->prepare("SELECT * FROM pengisian WHERE pengguna_id = ? AND periode_id = ?");
$stmt->execute([$user_id, $periode['id']]);
$pengisian = $stmt->fetch();

if ($pengisian && $pengisian['status'] === 'final') { set_flash_message('success', 'Anda sudah mengumpulkan evaluasi untuk periode ini.'); redirect('responden/beranda.php'); }

if (!$pengisian) {
    $stmt = $pdo->prepare("INSERT INTO pengisian (pengguna_id, periode_id, status, tab_terakhir) VALUES (?, ?, 'draft', 1)");
    $stmt->execute([$user_id, $periode['id']]);
    $pengisian_id = $pdo->lastInsertId();
    $tab_terakhir = 1;
} else {
    $pengisian_id = $pengisian['id'];
    $tab_terakhir = $pengisian['tab_terakhir'];
}

// Ambil pertanyaan (kini mendukung peran 'semua' dan 'default_aktif')
$stmt = $pdo->prepare("SELECT * FROM pertanyaan WHERE (peran_sasaran = ? OR peran_sasaran = 'semua') AND aktif = TRUE AND default_aktif = TRUE ORDER BY urutan ASC");
$stmt->execute([$user_role]);
$pertanyaan_semua = $stmt->fetchAll();
$pertanyaan_matkul = array_filter($pertanyaan_semua, fn($p) => $p['bagian'] === 'matkul');
$pertanyaan_kompetensi = array_filter($pertanyaan_semua, fn($p) => $p['bagian'] === 'kompetensi');

$opsi_semua_raw = $pdo->query("
    SELECT o.*, k.deskripsi as k_desk 
    FROM opsi_pertanyaan o 
    LEFT JOIN kategori_kompetensi k ON o.kategori_kompetensi_id = k.id 
    WHERE o.aktif = TRUE 
    ORDER BY o.urutan ASC
")->fetchAll();
$opsi_map = []; foreach ($opsi_semua_raw as $o) { $opsi_map[$o['pertanyaan_id']][] = $o; }

$stmt = $pdo->prepare("
    SELECT j.*, GROUP_CONCAT(jm.opsi_id) as multi_opsi 
    FROM jawaban j 
    LEFT JOIN jawaban_multi jm ON j.id = jm.jawaban_id 
    WHERE j.pengisian_id = ? 
    GROUP BY j.id
");
$stmt->execute([$pengisian_id]);
$jawaban_tersimpan = $stmt->fetchAll();
$jawaban_map = [];
foreach ($jawaban_tersimpan as $j) {
    if ($j['mata_kuliah_id']) { $jawaban_map['matkul'][$j['mata_kuliah_id']][$j['pertanyaan_id']] = $j; }
    else { $jawaban_map['kompetensi'][$j['pertanyaan_id']] = $j; }
}

// Ambil semua mata kuliah aktif dengan bahan kajiannya
$stmt = $pdo->query("
    SELECT m.*, GROUP_CONCAT(bk.nama_id SEPARATOR ' / ') as nama_bk, GROUP_CONCAT(bk.id SEPARATOR ',') as bk_ids
    FROM mata_kuliah m
    LEFT JOIN mata_kuliah_bk mkb ON m.id = mkb.mata_kuliah_id
    LEFT JOIN bahan_kajian bk ON mkb.bahan_kajian_id = bk.id
    WHERE m.aktif = TRUE
    GROUP BY m.id
    ORDER BY m.semester ASC, m.nama ASC
");
$semua_matkul = $stmt->fetchAll();

// Ambil mata kuliah terpilih
$stmt = $pdo->prepare("SELECT mata_kuliah_id FROM pengisian_matkul WHERE pengisian_id = ?");
$stmt->execute([$pengisian_id]);
$matkul_terpilih = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Ambil list bahan kajian untuk filter
$stmt = $pdo->query("SELECT id, nama_id FROM bahan_kajian ORDER BY id ASC");
$list_bk = $stmt->fetchAll();

$page_title = 'Formulir Evaluasi';
require_once '../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Formulir Evaluasi: <?= escape($periode['tahun_akademik']) ?></h2>
            <p class="text-sm text-gray-500 mt-1">Silakan isi evaluasi dengan jujur dan objektif.</p>
        </div>
        <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">
            <i class="ph ph-cloud-check text-brand-500"></i>
            <span id="save-status" class="text-xs font-medium text-gray-600">Terakhir disimpan: Belum ada</span>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="flex border-b border-gray-200 bg-gray-50">
        <button type="button" class="tab-btn w-1/2 py-4 px-6 text-center font-medium text-sm transition-colors focus:outline-none flex justify-center items-center gap-2 <?= $tab_terakhir == 1 ? 'text-brand-600 bg-white border-b-2 border-brand-500' : 'text-gray-500 hover:text-gray-700' ?>" onclick="switchTab(1)" id="tab-btn-1">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs <?= $tab_terakhir == 1 ? 'bg-brand-100 text-brand-600' : 'bg-gray-200 text-gray-500' ?>">1</div> Evaluasi Mata Kuliah
        </button>
        <button type="button" class="tab-btn w-1/2 py-4 px-6 text-center font-medium text-sm transition-colors focus:outline-none flex justify-center items-center gap-2 <?= $tab_terakhir == 2 ? 'text-brand-600 bg-white border-b-2 border-brand-500' : 'text-gray-500 hover:text-gray-700' ?>" onclick="switchTab(2)" id="tab-btn-2">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs <?= $tab_terakhir == 2 ? 'bg-brand-100 text-brand-600' : 'bg-gray-200 text-gray-500' ?>">2</div> Kompetensi Umum
        </button>
    </div>

    <form id="form-evaluasi" action="<?= base_url('api/submit-final.php') ?>" method="POST" class="p-6">
        <?= csrf_field() ?>
        <input type="hidden" name="pengisian_id" id="pengisian_id" value="<?= $pengisian_id ?>">

        <!-- TAB 1 -->
        <div id="tab-1" class="tab-content" style="<?= $tab_terakhir == 1 ? 'display:block;' : 'display:none;' ?>">
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 text-sm text-blue-800">
                <i class="ph ph-info mr-1"></i> Tidak ingat nama mata kuliahnya? Gunakan filter Bahan Kajian atau cari dengan kata kunci. Centang mata kuliah yang ingin dinilai lalu klik "Mulai Menilai".
                <div class="mt-2 font-medium text-blue-900 border-t border-blue-200 pt-2">
                    <?php 
                    if ($user_role === 'mahasiswa') echo "Nilai berdasarkan pengalaman Anda mengikuti mata kuliah tersebut.";
                    elseif ($user_role === 'alumni') echo "Nilai berdasarkan pengalaman kuliah dan pekerjaan Anda saat ini.";
                    elseif ($user_role === 'perusahaan') echo "Nilai berdasarkan kebutuhan kerja di perusahaan Anda dan kinerja lulusan yang Anda ketahui.";
                    elseif ($user_role === 'dosen') echo "Nilai berdasarkan mata kuliah yang Anda kenal atau ampu.";
                    ?>
                </div>
            </div>

            <!-- Panel Filter -->
            <div id="panel-filter-matkul" class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-1">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pencarian</label>
                        <div class="relative">
                            <i class="ph ph-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
                            <input type="text" id="filter-q" class="w-full pl-9 pr-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500" placeholder="Kode atau nama...">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Semester</label>
                        <select id="filter-semester" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500">
                            <option value="">Semua Semester</option>
                            <?php for($i=1; $i<=8; $i++): ?><option value="<?= $i ?>">Semester <?= $i ?></option><?php endfor; ?>
                            <option value="null">Tanpa Semester / Pilihan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Bahan Kajian</label>
                        <select id="filter-bk" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500">
                            <option value="">Semua Bahan Kajian</option>
                            <?php foreach($list_bk as $bk): ?>
                                <option value="<?= $bk['id'] ?>"><?= escape($bk['nama_id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Jenis</label>
                        <select id="filter-jenis" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500">
                            <option value="">Semua Jenis</option>
                            <option value="wajib">Wajib</option>
                            <option value="pilihan">Pilihan</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 border-t border-gray-200 pt-4">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                        <span class="text-sm font-medium text-gray-700">Kelompokkan menurut:</span>
                        <select id="groupby-select" class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 max-w-full">
                            <option value="semester" <?= $user_role !== 'perusahaan' ? 'selected' : '' ?>>Semester</option>
                            <option value="bk" <?= $user_role === 'perusahaan' ? 'selected' : '' ?>>Bahan Kajian</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-4 justify-between sm:justify-end border-t sm:border-t-0 border-gray-200 pt-3 sm:pt-0">
                        <span class="text-sm font-medium text-brand-600 bg-brand-50 px-3 py-1 rounded-full whitespace-nowrap"><span id="count-selected">0</span> dipilih</span>
                        <button type="button" id="btn-reset-filter" class="text-sm text-gray-500 hover:text-gray-700 font-medium whitespace-nowrap">Reset Filter</button>
                    </div>
                </div>
            </div>

            <!-- Daftar Mata Kuliah (Accordion) -->
            <div id="mk-list-container" class="space-y-4 mb-8"></div>
            
            <div class="flex justify-center border-b border-gray-200 pb-8 mb-8" id="action-mulai-menilai">
                <button type="button" id="btn-mulai-menilai" class="bg-gray-900 hover:bg-black text-white px-8 py-3 rounded-xl font-medium transition-colors shadow-sm flex items-center gap-2" onclick="renderMatkulForm()">
                    <i class="ph ph-check-square-offset text-lg"></i> Mulai Menilai (<span id="count-menilai">0</span>)
                </button>
            </div>

            <!-- Area Formulir Mata Kuliah Terpilih -->
            <div id="selected-matkul-header" class="mb-6 pb-4 border-b border-gray-200 flex justify-between items-center" style="display:none;">
                <h3 class="text-lg font-bold text-gray-900">Penilaian Mata Kuliah Terpilih</h3>
                <button type="button" onclick="kembaliKePilihanMatkul()" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                    <i class="ph ph-arrow-left"></i> Ubah Pilihan Mata Kuliah
                </button>
            </div>
            
            <div id="selected-matkul-container" class="space-y-6"></div>
            
            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end" id="btn-lanjut-tab2" style="display:none;">
                <button type="button" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-xl font-medium transition-colors flex items-center gap-2" onclick="switchTab(2)">Selanjutnya <i class="ph ph-arrow-right"></i></button>
            </div>
        </div>

        <!-- TAB 2 -->
        <div id="tab-2" class="tab-content" style="<?= $tab_terakhir == 2 ? 'display:block;' : 'display:none;' ?>">
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 text-sm text-blue-800">
                <i class="ph ph-info mr-1"></i> Bagian ini berisi pertanyaan umum tentang kurikulum program studi. Pertanyaan bertanda <span class="text-red-500 font-bold">*</span> wajib dijawab.
            </div>
            <div class="space-y-6">
                <?php foreach ($pertanyaan_kompetensi as $p): ?>
                    <div class="p-6 bg-gray-50 border border-gray-200 rounded-2xl">
                        <label class="block text-base font-medium text-gray-900 mb-1">
                            <?= escape($p['teks']) ?> <?php if ($p['wajib']): ?><span class="text-red-500">*</span><?php endif; ?>
                        </label>
                        <?php if ($p['bantuan']): ?>
                            <p class="text-sm text-gray-500 mb-4"><?= escape($p['bantuan']) ?></p>
                        <?php else: ?>
                            <div class="mb-4"></div>
                        <?php endif; ?>
                        
                        <?php $jwb = $jawaban_map['kompetensi'][$p['id']] ?? null; ?>
                        
                        <?php if ($p['tipe'] === 'skala'): ?>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-3">
                                <?php $labels = json_decode($p['label_skala'] ?? '[]', true); ?>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label class="flex flex-col items-center gap-1 cursor-pointer">
                                        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg border border-gray-300 hover:bg-brand-50 transition-colors w-full justify-center text-center">
                                            <input type="radio" name="komp_<?= $p['id'] ?>_skala" value="<?= $i ?>" class="auto-save text-brand-500 focus:ring-brand-500 h-4 w-4" <?= ($jwb['nilai_skala'] ?? '') == $i ? 'checked' : '' ?> <?= $p['wajib'] ? 'required' : '' ?>>
                                            <span class="text-gray-700 font-medium"><?= $i ?></span>
                                        </div>
                                        <?php if(isset($labels[$i-1])): ?>
                                            <span class="text-xs text-gray-500 text-center leading-tight"><?= escape($labels[$i-1]) ?></span>
                                        <?php endif; ?>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <?php if ($p['alasan_jika_rendah'] == 1): ?>
                            <textarea name="komp_<?= $p['id'] ?>_alasan" class="w-full mt-3 p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm auto-save alasan-skala" placeholder="Mohon berikan alasan untuk nilai yang rendah (Wajib jika nilai 1 atau 2, min 10 karakter)" rows="2" style="display: <?= in_array($jwb['nilai_skala'] ?? '', [1,2]) ? 'block' : 'none' ?>;" minlength="10"><?= escape($jwb['alasan'] ?? '') ?></textarea>
                            <?php endif; ?>
                        <?php elseif ($p['tipe'] === 'pilihan_ganda' || $p['tipe'] === 'pilihan_ganda_banyak'): ?>
                            <?php $is_multi = ($p['tipe'] === 'pilihan_ganda_banyak'); ?>
                            <?php $selected_opsi = $jwb ? ($is_multi && $jwb['multi_opsi'] ? explode(',', $jwb['multi_opsi']) : [$jwb['opsi_id']]) : []; ?>
                            <div class="space-y-2 pg-group" data-max="<?= $p['maks_pilihan'] ?? 0 ?>">
                            <?php $opsi_list = $opsi_map[$p['id']] ?? []; foreach ($opsi_list as $opsi): ?>
                                <label class="flex items-start gap-3 p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="<?= $is_multi ? 'checkbox' : 'radio' ?>" name="komp_<?= $p['id'] ?>_opsi<?= $is_multi ? '[]' : '' ?>" value="<?= $opsi['id'] ?>" class="mt-0.5 auto-save text-brand-500 focus:ring-brand-500 h-4 w-4 cb-multi" <?= in_array($opsi['id'], $selected_opsi) ? 'checked' : '' ?> <?= $p['wajib'] && !$is_multi ? 'required' : '' ?>> 
                                    <div>
                                        <span class="text-gray-700 block font-medium"><?= escape($opsi['teks_opsi']) ?></span>
                                        <?php if($opsi['k_desk']): ?><span class="text-xs text-gray-500 block mt-1"><?= escape($opsi['k_desk']) ?></span><?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                            </div>
                        <?php elseif ($p['tipe'] === 'teks'): ?>
                            <textarea name="komp_<?= $p['id'] ?>_teks" class="w-full p-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm auto-save" rows="4" placeholder="Tulis jawaban Anda di sini..." <?= $p['wajib'] ? 'required' : '' ?>><?= escape($jwb['teks'] ?? '') ?></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-between">
                <button type="button" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-6 py-2.5 rounded-xl font-medium transition-colors flex items-center gap-2" onclick="switchTab(1)"><i class="ph ph-arrow-left"></i> Sebelumnya</button>
                <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-8 py-2.5 rounded-xl font-medium transition-colors flex items-center gap-2 shadow-sm" id="btn-submit" onclick="return confirm('Apakah Anda yakin ingin melakukan Submit Final? Jawaban tidak dapat diubah lagi setelah ini.')"><i class="ph ph-paper-plane-tilt"></i> Submit Final</button>
            </div>
        </div>
    </form>
</div>

<template id="tpl-matkul-form">
    <div class="bg-white border border-brand-200 rounded-2xl overflow-hidden shadow-sm matkul-item mb-6" data-id="{id}">
        <div class="bg-brand-50 px-4 md:px-6 py-4 flex flex-col md:flex-row md:justify-between md:items-center border-b border-brand-100 gap-2 md:gap-0">
            <h3 class="font-semibold text-brand-900 flex items-center gap-2 text-sm md:text-base"><i class="ph ph-book-open text-brand-600 flex-shrink-0"></i> <span>{kode} - {nama} - {sks} SKS - Semester {semester}</span> {badge_pilihan}</h3>
            <span class="text-[10px] md:text-xs font-medium bg-gray-200 text-gray-600 px-2 py-1 rounded-md max-w-max">{nama_bk}</span>
        </div>
        <div class="p-4 md:p-6 space-y-6">
            <?php if(empty($pertanyaan_matkul)): ?>
                <p class="text-gray-500 text-sm italic">Tidak ada pertanyaan untuk mata kuliah ini.</p>
            <?php endif; ?>
            <?php foreach ($pertanyaan_matkul as $p): ?>
                <div class="pertanyaan-item" data-pid="<?= $p['id'] ?>" data-syarat="<?= escape($p['syarat_tampil'] ?? '') ?>" <?= $p['syarat_tampil'] ? 'style="display:none;"' : '' ?>>
                    <label class="block text-sm font-medium text-gray-900 mb-1 pertanyaan-teks">
                        <?= escape($p['teks']) ?> <?php if ($p['wajib']): ?><span class="text-red-500 req-star" <?= $p['syarat_tampil'] ? 'style="display:none;"' : '' ?>>*</span><?php endif; ?>
                    </label>
                    <?php if ($p['bantuan']): ?>
                        <p class="text-xs text-gray-500 mb-3"><?= escape($p['bantuan']) ?></p>
                    <?php else: ?>
                        <div class="mb-3"></div>
                    <?php endif; ?>
                    
                    <?php if ($p['tipe'] === 'skala'): ?>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                            <?php $labels = json_decode($p['label_skala'] ?? '[]', true); ?>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="flex flex-col items-center gap-1 cursor-pointer">
                                    <div class="flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-lg border border-gray-200 hover:bg-brand-50 transition-colors w-full justify-center">
                                        <input type="radio" name="matkul_{id}_<?= $p['id'] ?>_skala" value="<?= $i ?>" class="auto-save mk-skala text-brand-500 focus:ring-brand-500 h-4 w-4">
                                        <span class="text-gray-700 text-sm font-medium"><?= $i ?></span>
                                    </div>
                                    <?php if(isset($labels[$i-1])): ?>
                                        <span class="text-xs text-gray-500 text-center leading-tight"><?= escape($labels[$i-1]) ?></span>
                                    <?php endif; ?>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <?php if ($p['alasan_jika_rendah'] == 1): ?>
                        <textarea name="matkul_{id}_<?= $p['id'] ?>_alasan" class="w-full mt-3 p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-500 text-sm auto-save mk-alasan alasan-skala" placeholder="Mohon berikan alasan (Wajib jika nilai 1 atau 2, min 10 karakter)" rows="2" style="display:none;" minlength="10"></textarea>
                        <?php endif; ?>
                    
                    <?php elseif ($p['tipe'] === 'pilihan_ganda' || $p['tipe'] === 'pilihan_ganda_banyak'): ?>
                        <?php $is_multi = ($p['tipe'] === 'pilihan_ganda_banyak'); ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pg-group" data-max="<?= $p['maks_pilihan'] ?? 0 ?>">
                        <?php $opsi_list = $opsi_map[$p['id']] ?? []; foreach ($opsi_list as $opsi): ?>
                            <label class="flex items-start gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-100 transition-colors">
                                <input type="<?= $is_multi ? 'checkbox' : 'radio' ?>" name="matkul_{id}_<?= $p['id'] ?>_opsi<?= $is_multi ? '[]' : '' ?>" value="<?= $opsi['id'] ?>" class="mt-0.5 auto-save text-brand-500 focus:ring-brand-500 h-4 w-4 cb-multi"> 
                                <div>
                                    <span class="text-gray-700 text-sm font-medium"><?= escape($opsi['teks_opsi']) ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                        </div>
                    <?php elseif ($p['tipe'] === 'teks'): ?>
                        <textarea name="matkul_{id}_<?= $p['id'] ?>_teks" class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-500 text-sm auto-save" rows="3"></textarea>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</template>

<script>
const semuaMatkul = <?= json_encode($semua_matkul) ?>;
let selectedMatkulIds = <?= json_encode($matkul_terpilih) ?>.map(id => parseInt(id));
const jawabanMap = <?= json_encode($jawaban_map) ?>;
const pengisianId = <?= $pengisian_id ?>;
const csrfToken = document.querySelector('input[name="csrf_token"]').value;

function updateCounters() {
    document.getElementById('count-selected').textContent = selectedMatkulIds.length;
    document.getElementById('count-menilai').textContent = selectedMatkulIds.length;
}

function renderMatkulList() {
    const q = document.getElementById('filter-q').value.toLowerCase();
    const sem = document.getElementById('filter-semester').value;
    const bk = document.getElementById('filter-bk').value;
    const jenis = document.getElementById('filter-jenis').value;
    const groupBy = document.getElementById('groupby-select').value;
    
    let filtered = semuaMatkul.filter(m => {
        let match = true;
        if(q && !m.nama.toLowerCase().includes(q) && !m.kode.toLowerCase().includes(q)) match = false;
        if(sem) { if(sem === 'null' && m.semester !== null) match = false; else if(sem !== 'null' && m.semester != sem) match = false; }
        if(bk && (!m.bk_ids || !m.bk_ids.split(',').includes(bk))) match = false;
        if(jenis && m.jenis !== jenis) match = false;
        return match;
    });

    let groups = {};
    filtered.forEach(m => {
        let key = "Lainnya";
        if (groupBy === 'semester') {
            key = m.semester ? `Semester ${m.semester}` : 'Tanpa Semester / Pilihan';
        } else {
            key = m.nama_bk || 'Tanpa Bahan Kajian';
        }
        if(!groups[key]) groups[key] = [];
        groups[key].push(m);
    });

    const container = document.getElementById('mk-list-container');
    container.innerHTML = '';
    
    if (Object.keys(groups).length === 0) {
        container.innerHTML = '<div class="p-8 text-center text-gray-500 bg-gray-50 rounded-xl border border-gray-100">Tidak ada mata kuliah yang cocok dengan filter.</div>';
        return;
    }

    Object.keys(groups).sort().forEach(groupName => {
        const div = document.createElement('div');
        div.className = 'border border-gray-200 rounded-xl overflow-hidden';
        div.innerHTML = `
            <div class="bg-gray-100 px-4 py-3 font-semibold text-gray-800 flex justify-between items-center cursor-pointer" onclick="this.nextElementSibling.classList.toggle('hidden')">
                <span>${groupName} (${groups[groupName].length} mata kuliah)</span>
                <i class="ph ph-caret-down text-gray-500"></i>
            </div>
            <div class="divide-y divide-gray-100">
                ${groups[groupName].map(m => {
                    const isChecked = selectedMatkulIds.includes(parseInt(m.id)) ? 'checked' : '';
                    const badgePilihan = m.jenis === 'pilihan' ? '<span class="ml-2 px-1.5 py-0.5 bg-yellow-100 text-yellow-700 text-[10px] rounded uppercase font-bold">Pilihan</span>' : '';
                    const desc = m.deskripsi_singkat ? `<p class="text-xs text-gray-500 mt-1 pl-6">${m.deskripsi_singkat}</p>` : '';
                    return `
                    <label class="flex items-start p-4 hover:bg-blue-50 cursor-pointer transition-colors ${isChecked ? 'bg-blue-50/50' : ''}">
                        <input type="checkbox" value="${m.id}" class="cb-matkul mt-1 mr-3 h-4 w-4 text-brand-500 rounded focus:ring-brand-500 border-gray-300" ${isChecked}>
                        <div class="flex-1">
                            <div class="flex items-center flex-wrap">
                                <span class="font-medium text-gray-900">${m.kode} - ${m.nama}</span>
                                ${badgePilihan}
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">${m.sks} SKS • ${m.nama_bk || '-'}</div>
                            ${desc}
                        </div>
                    </label>
                    `;
                }).join('')}
            </div>
        `;
        container.appendChild(div);
    });
    
    document.querySelectorAll('.cb-matkul').forEach(cb => {
        cb.addEventListener('change', function() {
            const id = parseInt(this.value);
            if (this.checked && !selectedMatkulIds.includes(id)) selectedMatkulIds.push(id);
            else if (!this.checked) selectedMatkulIds = selectedMatkulIds.filter(i => i !== id);
            updateCounters();
            saveDraft();
        });
    });
}

['filter-q', 'filter-semester', 'filter-bk', 'filter-jenis', 'groupby-select'].forEach(id => {
    document.getElementById(id).addEventListener(id === 'filter-q' ? 'input' : 'change', renderMatkulList);
});

document.getElementById('btn-reset-filter').addEventListener('click', () => {
    document.getElementById('filter-q').value = '';
    document.getElementById('filter-semester').value = '';
    document.getElementById('filter-bk').value = '';
    document.getElementById('filter-jenis').value = '';
    renderMatkulList();
});

function renderMatkulForm() {
    if (selectedMatkulIds.length === 0) { alert('Harap pilih minimal 1 mata kuliah terlebih dahulu.'); return; }
    
    const container = document.getElementById('selected-matkul-container');
    container.innerHTML = '';
    const tpl = document.getElementById('tpl-matkul-form').innerHTML;
    
    const selectedObjects = semuaMatkul.filter(m => selectedMatkulIds.includes(parseInt(m.id)));
    
    selectedObjects.forEach(mk => {
        let isPilihan = mk.jenis === 'pilihan' ? '<span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded uppercase font-bold">Pilihan</span>' : '';
        let html = tpl.replace(/{id}/g, mk.id)
                      .replace(/{kode}/g, mk.kode)
                      .replace(/{nama}/g, mk.nama)
                      .replace(/{sks}/g, mk.sks)
                      .replace(/{semester}/g, mk.semester || '-')
                      .replace(/{nama_bk}/g, mk.nama_bk || '-')
                      .replace(/{badge_pilihan}/g, isPilihan);
        const temp = document.createElement('div'); temp.innerHTML = html;
        
        temp.querySelectorAll('.pertanyaan-teks').forEach(el => el.innerHTML = el.innerHTML.replace('{sks}', mk.sks));
        
        if (jawabanMap.matkul && jawabanMap.matkul[mk.id]) {
            Object.keys(jawabanMap.matkul[mk.id]).forEach(pid => {
                const jwb = jawabanMap.matkul[mk.id][pid];
                if (jwb.nilai_skala) {
                    const rb = temp.querySelector(`input[name="matkul_${mk.id}_${pid}_skala"][value="${jwb.nilai_skala}"]`);
                    if(rb) rb.checked = true;
                    if (jwb.nilai_skala <= 2) {
                        const alasan = temp.querySelector(`textarea[name="matkul_${mk.id}_${pid}_alasan"]`);
                        if(alasan) { alasan.style.display = 'block'; alasan.value = jwb.alasan || ''; }
                    }
                }
                if (jwb.opsi_id || jwb.multi_opsi) {
                    const opsis = jwb.multi_opsi ? jwb.multi_opsi.split(',') : [jwb.opsi_id];
                    opsis.forEach(oId => {
                        const rb = temp.querySelector(`input[value="${oId}"]`);
                        if(rb) rb.checked = true;
                    });
                }
                if (jwb.teks) {
                    const tx = temp.querySelector(`textarea[name="matkul_${mk.id}_${pid}_teks"]`);
                    if(tx) tx.value = jwb.teks;
                }
            });
        }
        container.appendChild(temp.firstElementChild);
    });
    
    document.getElementById('mk-list-container').style.display = 'none';
    document.getElementById('action-mulai-menilai').style.display = 'none';
    document.getElementById('panel-filter-matkul').style.display = 'none';
    
    document.getElementById('selected-matkul-header').style.display = 'flex';
    document.getElementById('btn-lanjut-tab2').style.display = 'flex';
    
    attachSkalaLogic(); attachAutoSaveEvents(); checkConditionalLogic();
}

function kembaliKePilihanMatkul() {
    document.getElementById('mk-list-container').style.display = 'block';
    document.getElementById('action-mulai-menilai').style.display = 'flex';
    document.getElementById('panel-filter-matkul').style.display = 'block';
    
    document.getElementById('selected-matkul-header').style.display = 'none';
    document.getElementById('selected-matkul-container').innerHTML = '';
    document.getElementById('btn-lanjut-tab2').style.display = 'none';
}

function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.className = el.className.replace('text-brand-600 bg-white border-b-2 border-brand-500', 'text-gray-500 hover:text-gray-700');
        const badge = el.querySelector('div');
        badge.className = badge.className.replace('bg-brand-100 text-brand-600', 'bg-gray-200 text-gray-500');
    });
    
    document.getElementById('tab-' + tabId).style.display = 'block';
    const activeBtn = document.getElementById('tab-btn-' + tabId);
    activeBtn.className = activeBtn.className.replace('text-gray-500 hover:text-gray-700', 'text-brand-600 bg-white border-b-2 border-brand-500');
    const activeBadge = activeBtn.querySelector('div');
    activeBadge.className = activeBadge.className.replace('bg-gray-200 text-gray-500', 'bg-brand-100 text-brand-600');
    
    saveDraft(tabId);
}

function attachSkalaLogic() {
    document.querySelectorAll('input[type="radio"]').forEach(rb => {
        rb.addEventListener('change', function() {
            if(this.name.includes('_skala')) {
                const nameParts = this.name.split('_'); 
                let textareaName = nameParts[0] === 'matkul' ? `matkul_${nameParts[1]}_${nameParts[2]}_alasan` : `komp_${nameParts[1]}_alasan`;
                const textarea = document.querySelector(`textarea[name="${textareaName}"]`);
                if (textarea) {
                    if (this.value == '1' || this.value == '2') {
                        textarea.style.display = 'block'; textarea.required = true;
                    } else {
                        textarea.style.display = 'none'; textarea.required = false; textarea.value = '';
                    }
                }
            }
        });
    });
}

function checkConditionalLogic() {
    document.querySelectorAll('.matkul-item').forEach(matkulEl => {
        const matkulId = matkulEl.getAttribute('data-id');
        matkulEl.querySelectorAll('.pertanyaan-item').forEach(item => {
            const syarat = item.getAttribute('data-syarat');
            if (syarat) {
                if (syarat === 'A3_BUKAN_OPSI_1') {
                    // Cari input radio untuk pertanyaan_id 21 di matkul ini, wait, A3 is dynamically ID.
                    // Need a better generic logic. 
                    // Tipe A3 Dosen -> ini hardcoded but can be derived from label if we know it.
                    // Assuming A3 Dosen is the only one right now, we find its radio.
                    const rbs = matkulEl.querySelectorAll(`input[type="radio"]`);
                    let a3_val = null;
                    rbs.forEach(r => { 
                        if(r.name.includes('_opsi') && r.checked) {
                            // Cek jika teks opsi parentnya adalah "Sudah sesuai..."
                            const labelText = r.nextElementSibling.textContent;
                            if(labelText.includes("Sudah sesuai")) a3_val = "1";
                            else a3_val = "other";
                        }
                    });
                    if (a3_val === "other" || !a3_val) {
                        item.style.display = 'block';
                        const req = item.querySelector('.req-star');
                        if (req) { req.style.display = 'inline'; }
                        item.querySelectorAll('textarea, input').forEach(el => { if (req) el.required = true; });
                    } else {
                        item.style.display = 'none';
                        const req = item.querySelector('.req-star');
                        if (req) req.style.display = 'none';
                        item.querySelectorAll('textarea, input').forEach(el => el.required = false);
                    }
                }
            }
        });
    });
}

// Max checkbox logic
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('cb-multi')) {
        const group = e.target.closest('.pg-group');
        const max = parseInt(group.getAttribute('data-max'));
        if (max > 0) {
            const checked = group.querySelectorAll('.cb-multi:checked').length;
            if (checked > max) { e.target.checked = false; alert(`Maksimal ${max} pilihan.`); }
        }
    }
});

let saveTimeout;
let autoSaveInterval = setInterval(() => saveDraft(), 30000);

function attachAutoSaveEvents() {
    document.querySelectorAll('.auto-save').forEach(el => {
        el.removeEventListener('input', queueSave); el.removeEventListener('change', saveImmediate);
        if (el.tagName === 'TEXTAREA' || el.type === 'text') el.addEventListener('input', queueSave);
        else el.addEventListener('change', saveImmediate);
    });
}
function queueSave() { clearTimeout(saveTimeout); saveTimeout = setTimeout(() => saveDraft(), 800); }
function saveImmediate() { checkConditionalLogic(); saveDraft(); }

document.addEventListener('focusout', (e) => { if (e.target.classList.contains('auto-save')) saveDraft(); });

function saveDraft(tabId = null) {
    clearTimeout(saveTimeout);
    const formData = new FormData(document.getElementById('form-evaluasi'));
    if (tabId) formData.append('tab_terakhir', tabId);
    formData.append('matkul_terpilih', JSON.stringify(selectedMatkulIds));
    
    document.getElementById('save-status').textContent = 'Menyimpan...';
    document.getElementById('save-status').previousElementSibling.className = 'ph ph-spinner animate-spin text-brand-500';

    fetch(BASE_URL + '/api/simpan-draf.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            const d = new Date();
            document.getElementById('save-status').textContent = 'Tersimpan ' + d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
            document.getElementById('save-status').previousElementSibling.className = 'ph ph-cloud-check text-brand-500';
        }
    }).catch(err => {
        document.getElementById('save-status').textContent = 'Gagal menyimpan';
        document.getElementById('save-status').previousElementSibling.className = 'ph ph-warning-circle text-red-500';
    });
}

document.getElementById('form-evaluasi').addEventListener('submit', function(e) {
    if (selectedMatkulIds.length === 0) { e.preventDefault(); alert('Anda harus memilih minimal 1 mata kuliah.'); switchTab(1); return false; }
    let hasError = false; let minLenError = false;
    document.querySelectorAll('textarea').forEach(tx => {
        if (tx.style.display !== 'none' && tx.required) {
            if (tx.value.trim() === '') { hasError = true; tx.classList.add('border-red-500'); }
            else if (tx.classList.contains('alasan-skala') && tx.value.trim().length < 10) { minLenError = true; tx.classList.add('border-red-500'); }
            else { tx.classList.remove('border-red-500'); }
        }
    });
    if (hasError) { e.preventDefault(); alert('Harap isi alasan untuk nilai skala yang rendah.'); return false; }
    if (minLenError) { e.preventDefault(); alert('Alasan untuk nilai yang rendah minimal 10 karakter.'); return false; }
});

renderMatkulList(); updateCounters();
// Jika sudah ada pilihan matkul, mungkin kita render form langsung (opsional)
if (selectedMatkulIds.length > 0 && <?= $tab_terakhir ?> == 1) {
    // renderMatkulForm();
}
</script>

<?php require_once '../includes/footer.php'; ?>
