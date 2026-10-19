<?php
// admin/kuesioner/matkul.php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../includes/csrf.php';

require_role('admin');

$page_title = 'Bank Mata Kuliah';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Token CSRF tidak valid.');
        redirect('admin/kuesioner/matkul.php');
    }

    $action = $_POST['action'] ?? '';
    
    if ($action === 'tambah' || $action === 'edit') {
        $kode = trim($_POST['kode'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $semester = !empty($_POST['semester']) ? (int)$_POST['semester'] : null;
        $sks = (int)($_POST['sks'] ?? 0);
        $jenis = $_POST['jenis'] ?? 'wajib';
        $kelompok = $_POST['kelompok'] ?? 'prodi';
        $kurikulum = trim($_POST['kurikulum'] ?? '2024');
        $bk_list = $_POST['bahan_kajian'] ?? []; // Array of bahan_kajian_id
        
        if ($kode && $nama && $sks > 0) {
            try {
                $pdo->beginTransaction();
                
                if ($action === 'tambah') {
                    $stmt = $pdo->prepare("INSERT INTO mata_kuliah (kode, nama, semester, sks, jenis, kelompok, kurikulum, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)");
                    $stmt->execute([$kode, $nama, $semester, $sks, $jenis, $kelompok, $kurikulum]);
                    $matkul_id = $pdo->lastInsertId();
                    
                    if (!empty($bk_list) && is_array($bk_list)) {
                        $stmt_bk = $pdo->prepare("INSERT INTO mata_kuliah_bk (mata_kuliah_id, bahan_kajian_id) VALUES (?, ?)");
                        foreach ($bk_list as $bk_id) {
                            $stmt_bk->execute([$matkul_id, $bk_id]);
                        }
                    }
                    $msg = 'Mata kuliah berhasil ditambahkan.';
                } else {
                    $id = $_POST['id'] ?? 0;
                    $stmt = $pdo->prepare("UPDATE mata_kuliah SET kode=?, nama=?, semester=?, sks=?, jenis=?, kelompok=?, kurikulum=? WHERE id=?");
                    $stmt->execute([$kode, $nama, $semester, $sks, $jenis, $kelompok, $kurikulum, $id]);
                    
                    // Update BK mapping: delete old, insert new
                    $pdo->prepare("DELETE FROM mata_kuliah_bk WHERE mata_kuliah_id = ?")->execute([$id]);
                    if (!empty($bk_list) && is_array($bk_list)) {
                        $stmt_bk = $pdo->prepare("INSERT INTO mata_kuliah_bk (mata_kuliah_id, bahan_kajian_id) VALUES (?, ?)");
                        foreach ($bk_list as $bk_id) {
                            $stmt_bk->execute([$id, $bk_id]);
                        }
                    }
                    $msg = 'Mata kuliah berhasil diperbarui.';
                }
                
                $pdo->commit();
                set_flash_message('success', $msg);
            } catch (Exception $e) {
                $pdo->rollBack();
                set_flash_message('error', 'Gagal: Kode mata kuliah mungkin sudah ada.');
            }
        }
    } elseif ($action === 'toggle') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] == 1 ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE mata_kuliah SET aktif = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        set_flash_message('success', 'Status mata kuliah diperbarui.');
    }
    
    redirect('admin/kuesioner/matkul.php');
}

// Ambil semua matkul
$stmt = $pdo->query("SELECT * FROM mata_kuliah ORDER BY semester ASC, kode ASC");
$matkul_list = $stmt->fetchAll();

// Ambil semua bahan kajian
$stmt_bk = $pdo->query("SELECT id, kode, nama_id FROM bahan_kajian ORDER BY kode ASC");
$semua_bk = $stmt_bk->fetchAll();

// Ambil relasi mata_kuliah_bk (untuk edit)
$stmt_relasi = $pdo->query("SELECT mata_kuliah_id, bahan_kajian_id FROM mata_kuliah_bk");
$relasi = $stmt_relasi->fetchAll();

// Susun array relasi per matkul
$matkul_bks = [];
foreach ($relasi as $r) {
    $matkul_bks[$r['mata_kuliah_id']][] = $r['bahan_kajian_id'];
}

// Inject relasi ke dalam matkul_list agar mudah diload ke JS
foreach ($matkul_list as &$mk) {
    $mk['bk_ids'] = $matkul_bks[$mk['id']] ?? [];
}
unset($mk);

require_once '../../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-2xl"><i class="ph ph-books"></i></div>
        <div><h2 class="text-xl font-bold text-gray-900">Bank Mata Kuliah</h2><p class="text-sm text-gray-500">Kelola daftar mata kuliah, atur parameter, dan kaitkan dengan Bahan Kajian.</p></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
    
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2" id="form-title"><i class="ph ph-plus-circle text-brand-500"></i> Tambah Matkul</h3>
            <form action="" method="POST" class="space-y-4" id="form-matkul">
                <?= csrf_field() ?> 
                <input type="hidden" name="action" value="tambah" id="form-action">
                <input type="hidden" name="id" value="" id="form-id">
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Kode Matkul <span class="text-red-500">*</span></label>
                    <input type="text" name="kode" id="form-kode" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" required placeholder="Cth: MK01">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nama Matkul <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="form-nama" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Semester</label>
                        <input type="number" name="semester" id="form-semester" min="1" max="14" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">SKS <span class="text-red-500">*</span></label>
                        <input type="number" name="sks" id="form-sks" min="1" max="10" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Jenis</label>
                        <select name="jenis" id="form-jenis" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-brand-500 outline-none">
                            <option value="wajib">Wajib</option>
                            <option value="pilihan">Pilihan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Kurikulum</label>
                        <input type="text" name="kurikulum" id="form-kurikulum" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" value="2024">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Kelompok</label>
                    <select name="kelompok" id="form-kelompok" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="prodi">Prodi</option>
                        <option value="fakultas">Fakultas</option>
                        <option value="universitas">Universitas</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-2">Bahan Kajian Terkait</label>
                    <div class="max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-2 space-y-2 bg-gray-50">
                        <?php foreach($semua_bk as $bk): ?>
                        <label class="flex items-start gap-2 cursor-pointer p-1 hover:bg-gray-100 rounded">
                            <input type="checkbox" name="bahan_kajian[]" value="<?= $bk['id'] ?>" class="mt-0.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500 bk-checkbox">
                            <span class="text-xs text-gray-700 font-medium"><?= escape($bk['kode']) ?> - <span class="font-normal text-gray-500"><?= escape($bk['nama_id']) ?></span></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors" id="btn-submit">Simpan</button>
                    <button type="button" class="hidden bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors" id="btn-cancel" onclick="resetForm()">Batal</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="lg:col-span-3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto max-h-[700px] overflow-y-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="sticky top-0 bg-gray-50 shadow-sm z-10">
                        <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-3 font-medium text-center">Smstr</th>
                            <th class="px-4 py-3 font-medium">Kode</th>
                            <th class="px-4 py-3 font-medium">Nama Mata Kuliah</th>
                            <th class="px-4 py-3 font-medium text-center">SKS</th>
                            <th class="px-4 py-3 font-medium">Jenis & Kelompok</th>
                            <th class="px-4 py-3 font-medium text-center">Status</th>
                            <th class="px-4 py-3 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($matkul_list as $mk): ?>
                            <tr class="hover:bg-gray-50 transition-colors <?= $mk['aktif'] ? '' : 'opacity-60 bg-gray-50' ?>">
                                <td class="px-4 py-3 text-center text-gray-500 font-medium"><?= $mk['semester'] ?: '-' ?></td>
                                <td class="px-4 py-3 text-brand-600 font-medium"><?= escape($mk['kode']) ?></td>
                                <td class="px-4 py-3 text-gray-900 font-medium">
                                    <?= escape($mk['nama']) ?>
                                    <?php if(!empty($mk['bk_ids'])): ?>
                                    <div class="mt-1 text-xs text-gray-500 flex flex-wrap gap-1">
                                        <?php foreach($mk['bk_ids'] as $bid): 
                                            // Cari nama BK
                                            $bk_kode = '';
                                            foreach($semua_bk as $sbk) {
                                                if($sbk['id'] == $bid) { $bk_kode = $sbk['kode']; break; }
                                            }
                                        ?>
                                            <span class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-600"><?= escape($bk_kode) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-700 text-center font-medium"><?= escape($mk['sks']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full <?= $mk['jenis'] == 'wajib' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' ?>"><?= ucfirst(escape($mk['jenis'])) ?></span>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 inline-block mt-1"><?= ucfirst(escape($mk['kelompok'])) ?></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <form action="" method="POST" class="inline-block m-0 p-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= $mk['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $mk['aktif'] ?>">
                                        
                                        <button type="submit" class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 <?= $mk['aktif'] ? 'bg-brand-500' : 'bg-gray-200' ?>">
                                            <span aria-hidden="true" class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out <?= $mk['aktif'] ? 'translate-x-4' : 'translate-x-0' ?>"></span>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" onclick="editData(<?= htmlspecialchars(json_encode($mk)) ?>)" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1.5 rounded transition-colors inline-block" title="Edit">
                                        <i class="ph ph-pencil-simple text-base"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function editData(data) {
    document.getElementById('form-title').innerHTML = '<i class="ph ph-pencil-simple text-blue-500"></i> Edit Matkul';
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = data.id;
    document.getElementById('form-kode').value = data.kode;
    document.getElementById('form-nama').value = data.nama;
    document.getElementById('form-semester').value = data.semester || '';
    document.getElementById('form-sks').value = data.sks;
    document.getElementById('form-jenis').value = data.jenis;
    document.getElementById('form-kelompok').value = data.kelompok;
    document.getElementById('form-kurikulum').value = data.kurikulum || '2024';
    
    // Reset all checkboxes first
    document.querySelectorAll('.bk-checkbox').forEach(cb => cb.checked = false);
    
    // Check the ones that match
    if(data.bk_ids && data.bk_ids.length > 0) {
        data.bk_ids.forEach(bid => {
            const cb = document.querySelector(`.bk-checkbox[value="${bid}"]`);
            if(cb) cb.checked = true;
        });
    }
    
    document.getElementById('btn-submit').textContent = 'Update';
    document.getElementById('btn-cancel').classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerHTML = '<i class="ph ph-plus-circle text-brand-500"></i> Tambah Matkul';
    document.getElementById('form-action').value = 'tambah';
    document.getElementById('form-id').value = '';
    document.getElementById('form-matkul').reset();
    
    document.getElementById('btn-submit').textContent = 'Simpan';
    document.getElementById('btn-cancel').classList.add('hidden');
}
</script>

<?php require_once '../../includes/footer.php'; ?>
