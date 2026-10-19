<?php
// admin/kuesioner/bahan_kajian.php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../includes/csrf.php';

require_role('admin');

$page_title = 'Bahan Kajian (Kategori Matkul)';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Token CSRF tidak valid.');
        redirect('admin/kuesioner/bahan_kajian.php');
    }

    $action = $_POST['action'] ?? '';
    
    if ($action === 'tambah') {
        $kode = trim($_POST['kode'] ?? '');
        $nama_id = trim($_POST['nama_id'] ?? '');
        $nama_en = trim($_POST['nama_en'] ?? '');
        $kategori_kompetensi_id = !empty($_POST['kategori_kompetensi_id']) ? $_POST['kategori_kompetensi_id'] : null;
        
        if ($kode && $nama_id) {
            try {
                $stmt = $pdo->prepare("INSERT INTO bahan_kajian (kode, nama_id, nama_en, kategori_kompetensi_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$kode, $nama_id, $nama_en, $kategori_kompetensi_id]);
                set_flash_message('success', 'Bahan Kajian berhasil ditambahkan.');
            } catch (Exception $e) {
                set_flash_message('error', 'Gagal: Kode bahan kajian mungkin sudah ada.');
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $kode = trim($_POST['kode'] ?? '');
        $nama_id = trim($_POST['nama_id'] ?? '');
        $nama_en = trim($_POST['nama_en'] ?? '');
        $kategori_kompetensi_id = !empty($_POST['kategori_kompetensi_id']) ? $_POST['kategori_kompetensi_id'] : null;
        
        if ($id && $kode && $nama_id) {
            try {
                $stmt = $pdo->prepare("UPDATE bahan_kajian SET kode = ?, nama_id = ?, nama_en = ?, kategori_kompetensi_id = ? WHERE id = ?");
                $stmt->execute([$kode, $nama_id, $nama_en, $kategori_kompetensi_id, $id]);
                set_flash_message('success', 'Bahan Kajian berhasil diperbarui.');
            } catch (Exception $e) {
                set_flash_message('error', 'Gagal update: Kode mungkin bentrok dengan data lain.');
            }
        }
    }
    
    redirect('admin/kuesioner/bahan_kajian.php');
}

$stmt = $pdo->query("SELECT bk.*, kk.nama as nama_kategori FROM bahan_kajian bk LEFT JOIN kategori_kompetensi kk ON bk.kategori_kompetensi_id = kk.id ORDER BY bk.kode ASC");
$bk_list = $stmt->fetchAll();

$kategori_stmt = $pdo->query("SELECT id, nama FROM kategori_kompetensi ORDER BY id ASC");
$kategori_list = $kategori_stmt->fetchAll();

require_once '../../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-2xl"><i class="ph ph-folder-open"></i></div>
        <div><h2 class="text-xl font-bold text-gray-900">Bahan Kajian</h2><p class="text-sm text-gray-500">Kelola Bahan Kajian (Kategori Mata Kuliah).</p></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2" id="form-title"><i class="ph ph-plus-circle text-brand-500"></i> Tambah Bahan Kajian</h3>
            <form action="" method="POST" class="space-y-4" id="form-bk">
                <?= csrf_field() ?> 
                <input type="hidden" name="action" value="tambah" id="form-action">
                <input type="hidden" name="id" value="" id="form-id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode BK <span class="text-red-500">*</span></label>
                    <input type="text" name="kode" id="form-kode" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" required placeholder="Cth: BK01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama (ID) <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_id" id="form-nama-id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama (EN)</label>
                    <input type="text" name="nama_en" id="form-nama-en" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Kompetensi Terkait</label>
                    <select name="kategori_kompetensi_id" id="form-kategori" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">-- Tidak ada --</option>
                        <?php foreach($kategori_list as $kat): ?>
                            <option value="<?= $kat['id'] ?>"><?= escape($kat['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="pt-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors" id="btn-submit">Simpan</button>
                    <button type="button" class="hidden bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors" id="btn-cancel" onclick="resetForm()">Batal</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-gray-50 shadow-sm z-10">
                        <tr class="border-b border-gray-200 text-sm text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-4 font-medium">Kode</th>
                            <th class="px-6 py-4 font-medium">Nama (ID & EN)</th>
                            <th class="px-6 py-4 font-medium">Relasi Kompetensi</th>
                            <th class="px-6 py-4 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($bk_list as $bk): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 text-gray-900 font-medium whitespace-nowrap"><?= escape($bk['kode']) ?></td>
                                <td class="px-6 py-3">
                                    <div class="font-medium text-gray-900"><?= escape($bk['nama_id']) ?></div>
                                    <div class="text-xs text-gray-500"><?= escape($bk['nama_en']) ?: '-' ?></div>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600">
                                    <?= $bk['nama_kategori'] ? escape($bk['nama_kategori']) : '<span class="text-gray-400 italic">Tidak ada</span>' ?>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <button type="button" onclick="editData(<?= htmlspecialchars(json_encode($bk)) ?>)" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-2 rounded-lg transition-colors inline-block" title="Edit">
                                        <i class="ph ph-pencil-simple"></i>
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
    document.getElementById('form-title').innerHTML = '<i class="ph ph-pencil-simple text-blue-500"></i> Edit Bahan Kajian';
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = data.id;
    document.getElementById('form-kode').value = data.kode;
    document.getElementById('form-nama-id').value = data.nama_id;
    document.getElementById('form-nama-en').value = data.nama_en || '';
    document.getElementById('form-kategori').value = data.kategori_kompetensi_id || '';
    
    document.getElementById('btn-submit').textContent = 'Update';
    document.getElementById('btn-cancel').classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerHTML = '<i class="ph ph-plus-circle text-brand-500"></i> Tambah Bahan Kajian';
    document.getElementById('form-action').value = 'tambah';
    document.getElementById('form-id').value = '';
    document.getElementById('form-bk').reset();
    
    document.getElementById('btn-submit').textContent = 'Simpan';
    document.getElementById('btn-cancel').classList.add('hidden');
}
</script>

<?php require_once '../../includes/footer.php'; ?>
