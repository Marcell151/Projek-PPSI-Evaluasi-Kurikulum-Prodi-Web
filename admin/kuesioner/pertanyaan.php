<?php
// admin/kuesioner/pertanyaan.php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../includes/csrf.php';

require_role('admin');

$page_title = 'Bank Pertanyaan';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Token CSRF tidak valid.');
        redirect('admin/kuesioner/pertanyaan.php');
    }

    $action = $_POST['action'] ?? '';
    
    if ($action === 'tambah') {
        $teks = trim($_POST['teks'] ?? '');
        $tipe = $_POST['tipe'] ?? '';
        $bagian = $_POST['bagian'] ?? '';
        $peran_sasaran = $_POST['peran_sasaran'] ?? '';
        $wajib = isset($_POST['wajib']) ? 1 : 0;
        $kategori_kompetensi_id = !empty($_POST['kategori_kompetensi_id']) ? $_POST['kategori_kompetensi_id'] : null;
        
        if ($teks && $tipe && $bagian && $peran_sasaran) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO pertanyaan (teks, tipe, bagian, peran_sasaran, kategori_kompetensi_id, wajib) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$teks, $tipe, $bagian, $peran_sasaran, $kategori_kompetensi_id, $wajib]);
            $pertanyaan_id = $pdo->lastInsertId();
            
            if ($tipe === 'pilihan_ganda' && isset($_POST['opsi']) && is_array($_POST['opsi'])) {
                $stmt_opsi = $pdo->prepare("INSERT INTO opsi_pertanyaan (pertanyaan_id, teks_opsi) VALUES (?, ?)");
                foreach ($_POST['opsi'] as $opsi_teks) {
                    $ot = trim($opsi_teks);
                    if ($ot !== '') $stmt_opsi->execute([$pertanyaan_id, $ot]);
                }
            }
            
            $pdo->commit();
            set_flash_message('success', 'Pertanyaan berhasil ditambahkan.');
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $teks = trim($_POST['teks'] ?? '');
        $kategori_kompetensi_id = !empty($_POST['kategori_kompetensi_id']) ? $_POST['kategori_kompetensi_id'] : null;
        $wajib = isset($_POST['wajib']) ? 1 : 0;

        if ($id && $teks) {
            $stmt = $pdo->prepare("UPDATE pertanyaan SET teks = ?, kategori_kompetensi_id = ?, wajib = ? WHERE id = ?");
            $stmt->execute([$teks, $kategori_kompetensi_id, $wajib, $id]);
            set_flash_message('success', 'Pertanyaan berhasil diperbarui.');
        }
    } elseif ($action === 'toggle') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] == 1 ? 0 : 1; 
        $stmt = $pdo->prepare("UPDATE pertanyaan SET aktif = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        set_flash_message('success', 'Status pertanyaan diperbarui.');
    }
    
    redirect('admin/kuesioner/pertanyaan.php');
}

$kategori_stmt = $pdo->query("SELECT * FROM kategori_kompetensi WHERE aktif = TRUE");
$kategori_list = $kategori_stmt->fetchAll();

$stmt = $pdo->query("SELECT p.*, k.nama as nama_kategori FROM pertanyaan p LEFT JOIN kategori_kompetensi k ON p.kategori_kompetensi_id = k.id ORDER BY p.urutan ASC, p.id ASC");
$semua_pertanyaan = $stmt->fetchAll();

// Kelompokkan data untuk Tabs
$pertanyaan_grouped = [
    'mahasiswa' => ['matkul' => [], 'kompetensi' => []],
    'dosen' => ['matkul' => [], 'kompetensi' => []],
    'alumni' => ['matkul' => [], 'kompetensi' => []],
    'perusahaan' => ['matkul' => [], 'kompetensi' => []],
];

foreach ($semua_pertanyaan as $p) {
    if (isset($pertanyaan_grouped[$p['peran_sasaran']][$p['bagian']])) {
        $pertanyaan_grouped[$p['peran_sasaran']][$p['bagian']][] = $p;
    }
}

require_once '../../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-2xl">
                <i class="ph ph-question"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Bank Pertanyaan</h2>
                <p class="text-sm text-gray-500">Kelola kuesioner evaluasi kurikulum berdasarkan peran sasaran.</p>
            </div>
        </div>
        <button type="button" onclick="document.getElementById('modal-tambah').classList.remove('hidden')" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl font-medium transition-colors flex items-center gap-2 shadow-sm">
            <i class="ph ph-plus-circle"></i> Buat Pertanyaan Baru
        </button>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="flex border-b border-gray-200 overflow-x-auto no-scrollbar">
        <?php
        $tabs = ['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'alumni' => 'Alumni', 'perusahaan' => 'Perusahaan'];
        $first = true;
        foreach ($tabs as $key => $label): 
        ?>
        <button type="button" class="tab-btn min-w-[120px] flex-1 py-4 px-6 text-center font-medium text-sm transition-colors focus:outline-none whitespace-nowrap <?= $first ? 'text-brand-600 bg-white border-b-2 border-brand-500' : 'text-gray-500 hover:text-gray-700 bg-gray-50' ?>" onclick="switchTab('<?= $key ?>')" id="tab-btn-<?= $key ?>">
            <?= $label ?>
        </button>
        <?php $first = false; endforeach; ?>
    </div>

    <!-- Tabs Content -->
    <div class="p-6">
        <?php 
        $first = true;
        foreach ($tabs as $key => $label): 
        ?>
        <div id="tab-content-<?= $key ?>" class="tab-content" style="<?= $first ? 'display:block;' : 'display:none;' ?>">
            
            <!-- Section Mata Kuliah -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2 pb-2 border-b border-gray-100">
                    <i class="ph ph-books text-brand-500"></i> Evaluasi Mata Kuliah (<?= count($pertanyaan_grouped[$key]['matkul']) ?> Pertanyaan)
                </h3>
                
                <?php if (count($pertanyaan_grouped[$key]['matkul']) === 0): ?>
                    <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200 text-gray-500 text-sm">Belum ada pertanyaan untuk bagian ini.</div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($pertanyaan_grouped[$key]['matkul'] as $p): ?>
                            <?php renderRowPertanyaan($p); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Section Kompetensi -->
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2 pb-2 border-b border-gray-100">
                    <i class="ph ph-target text-brand-500"></i> Kompetensi Umum (<?= count($pertanyaan_grouped[$key]['kompetensi']) ?> Pertanyaan)
                </h3>
                
                <?php if (count($pertanyaan_grouped[$key]['kompetensi']) === 0): ?>
                    <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200 text-gray-500 text-sm">Belum ada pertanyaan untuk bagian ini.</div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($pertanyaan_grouped[$key]['kompetensi'] as $p): ?>
                            <?php renderRowPertanyaan($p); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
        <?php $first = false; endforeach; ?>
    </div>
</div>

<?php
// Fungsi helper untuk merender baris pertanyaan agar kodenya rapi
function renderRowPertanyaan($p) {
    ?>
    <div class="p-4 border <?= $p['aktif'] ? 'border-gray-200 bg-white' : 'border-gray-100 bg-gray-50 opacity-75' ?> rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all hover:border-brand-300 hover:shadow-sm">
        <div class="flex-1">
            <div class="flex items-start gap-2 mb-1">
                <?php if ($p['wajib']): ?><span class="text-red-500 font-bold mt-0.5" title="Wajib Diisi">*</span><?php endif; ?>
                <p class="text-gray-900 font-medium leading-snug"><?= escape($p['teks']) ?></p>
            </div>
            <div class="flex flex-wrap gap-2 mt-2 ml-4">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                    <i class="ph ph-list-dashes mr-1"></i> Tipe: <?= str_replace('_', ' ', $p['tipe']) ?>
                </span>
                <?php if ($p['nama_kategori']): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                        <i class="ph ph-tag mr-1"></i> <?= escape($p['nama_kategori']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="flex items-center gap-3 sm:border-l sm:border-gray-100 sm:pl-4">
            <!-- Tombol Edit -->
            <button type="button" onclick="bukaModalEdit(<?= $p['id'] ?>, `<?= htmlspecialchars($p['teks'], ENT_QUOTES) ?>`, `<?= $p['kategori_kompetensi_id'] ?>`, <?= $p['wajib'] ?>)" class="p-2 text-gray-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Edit Pertanyaan">
                <i class="ph ph-pencil-simple text-lg"></i>
            </button>
            
            <!-- Toggle Sakelar -->
            <form action="" method="POST" class="m-0 p-0 flex items-center">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <input type="hidden" name="status" value="<?= $p['aktif'] ?>">
                <button type="submit" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 <?= $p['aktif'] ? 'bg-brand-500' : 'bg-gray-300' ?>" title="<?= $p['aktif'] ? 'Matikan' : 'Hidupkan' ?>">
                    <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out <?= $p['aktif'] ? 'translate-x-5' : 'translate-x-0' ?>"></span>
                </button>
            </form>
        </div>
    </div>
    <?php
}
?>

<!-- Modal Tambah Pertanyaan -->
<div id="modal-tambah" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">Buat Pertanyaan Baru</h3>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="ph ph-x text-xl"></i></button>
        </div>
        <div class="p-6 overflow-y-auto">
            <form action="" method="POST" class="space-y-5">
                <?= csrf_field() ?> <input type="hidden" name="action" value="tambah">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teks Pertanyaan <span class="text-red-500">*</span></label>
                    <textarea name="teks" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" rows="3" required placeholder="Tuliskan pertanyaan evaluasi..."></textarea>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sasaran Responden <span class="text-red-500">*</span></label>
                        <select name="peran_sasaran" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" required>
                            <option value="mahasiswa">Mahasiswa</option><option value="dosen">Dosen</option>
                            <option value="alumni">Alumni</option><option value="perusahaan">Perusahaan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bagian Evaluasi <span class="text-red-500">*</span></label>
                        <select name="bagian" id="select-bagian" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" required>
                            <option value="matkul">Evaluasi Mata Kuliah</option>
                            <option value="kompetensi">Evaluasi Kompetensi Umum</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Jawaban <span class="text-red-500">*</span></label>
                        <select name="tipe" id="select-tipe" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" required>
                            <option value="skala">Skala 1-5 (Otomatis)</option>
                            <option value="pilihan_ganda">Pilihan Ganda Khusus</option>
                            <option value="teks">Isian Bebas (Teks)</option>
                        </select>
                    </div>
                    <div id="container-kategori" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Analisis Tren</label>
                        <select name="kategori_kompetensi_id" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                            <option value="">-- Tidak Ditautkan --</option>
                            <?php foreach ($kategori_list as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= escape($k['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div id="container-opsi" class="hidden bg-blue-50 border border-blue-100 p-4 rounded-xl">
                    <label class="block text-sm font-medium text-blue-900 mb-2">Daftar Opsi (Pilihan Ganda)</label>
                    <div id="list-opsi" class="space-y-2">
                        <input type="text" name="opsi[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none" placeholder="Opsi 1">
                        <input type="text" name="opsi[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none" placeholder="Opsi 2">
                    </div>
                    <button type="button" onclick="addOpsi()" class="mt-3 text-sm text-brand-600 font-medium flex items-center gap-1 hover:text-brand-800"><i class="ph ph-plus"></i> Tambah Opsi Lain</button>
                </div>
                
                <div class="flex items-center gap-2 pt-2 border-t border-gray-100 mt-4">
                    <input type="checkbox" name="wajib" id="cb-wajib" value="1" checked class="w-4 h-4 text-brand-500 rounded border-gray-300 focus:ring-brand-500">
                    <label for="cb-wajib" class="text-sm font-medium text-gray-700">Jawaban Wajib Diisi (Required)</label>
                </div>
                
                <div class="pt-4 flex justify-end">
                    <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="mr-3 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl font-medium transition-colors">Batal</button>
                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-xl font-medium transition-colors shadow-sm">Simpan Pertanyaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pertanyaan -->
<div id="modal-edit" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl flex flex-col">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">Edit Pertanyaan</h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="ph ph-x text-xl"></i></button>
        </div>
        <div class="p-6">
            <form action="" method="POST" class="space-y-4">
                <?= csrf_field() ?> 
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit-id">
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800 mb-4 flex gap-3">
                    <i class="ph ph-warning-circle text-xl flex-shrink-0"></i>
                    <p>Perhatian: Mengubah teks pertanyaan yang sudah memiliki jawaban sebelumnya dapat mempengaruhi validitas data laporan historis.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teks Pertanyaan <span class="text-red-500">*</span></label>
                    <textarea name="teks" id="edit-teks" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" rows="3" required></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Analisis Tren</label>
                    <select name="kategori_kompetensi_id" id="edit-kategori" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">-- Tidak Ditautkan --</option>
                        <?php foreach ($kategori_list as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= escape($k['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="wajib" id="edit-wajib" value="1" class="w-4 h-4 text-brand-500 rounded border-gray-300 focus:ring-brand-500">
                    <label for="edit-wajib" class="text-sm font-medium text-gray-700">Jawaban Wajib Diisi (Required)</label>
                </div>
                
                <div class="pt-6 flex justify-end">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="mr-3 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl font-medium transition-colors">Batal</button>
                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-xl font-medium transition-colors shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Logika Tab Sederhana
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.className = el.className.replace('text-brand-600 bg-white border-b-2 border-brand-500', 'text-gray-500 hover:text-gray-700 bg-gray-50');
    });
    
    document.getElementById('tab-content-' + tabId).style.display = 'block';
    const activeBtn = document.getElementById('tab-btn-' + tabId);
    activeBtn.className = activeBtn.className.replace('text-gray-500 hover:text-gray-700 bg-gray-50', 'text-brand-600 bg-white border-b-2 border-brand-500');
}

// Logika Form Dinamis
const selectTipe = document.getElementById('select-tipe');
const containerOpsi = document.getElementById('container-opsi');
const selectBagian = document.getElementById('select-bagian');
const containerKategori = document.getElementById('container-kategori');

selectTipe.addEventListener('change', (e) => {
    if(e.target.value === 'pilihan_ganda') { containerOpsi.classList.remove('hidden'); } 
    else { containerOpsi.classList.add('hidden'); }
});

selectBagian.addEventListener('change', (e) => {
    if(e.target.value === 'kompetensi') { containerKategori.classList.remove('hidden'); } 
    else { containerKategori.classList.add('hidden'); }
});

function addOpsi() {
    const div = document.createElement('input');
    div.type = 'text'; div.name = 'opsi[]';
    div.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none mt-2';
    div.placeholder = 'Opsi Tambahan';
    document.getElementById('list-opsi').appendChild(div);
}

// Logika Modal Edit
function bukaModalEdit(id, teks, kategoriId, wajib) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-teks').value = teks;
    document.getElementById('edit-kategori').value = kategoriId || '';
    document.getElementById('edit-wajib').checked = (wajib == 1);
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>

<?php require_once '../../includes/footer.php'; ?>
