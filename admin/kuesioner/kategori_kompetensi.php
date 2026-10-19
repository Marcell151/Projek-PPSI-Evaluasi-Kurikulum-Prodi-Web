<?php
// admin/kuesioner/kategori_kompetensi.php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../includes/csrf.php';

require_role('admin');

$page_title = 'Kategori Kompetensi';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Token CSRF tidak valid.');
        redirect('admin/kuesioner/kategori_kompetensi.php');
    }

    $action = $_POST['action'] ?? '';
    
    if ($action === 'tambah') {
        $nama = trim($_POST['nama'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        
        if ($nama) {
            $stmt = $pdo->prepare("INSERT INTO kategori_kompetensi (nama, deskripsi, aktif) VALUES (?, ?, TRUE)");
            $stmt->execute([$nama, $deskripsi]);
            set_flash_message('success', 'Kategori Kompetensi berhasil ditambahkan.');
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $nama = trim($_POST['nama'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        
        if ($id && $nama) {
            $stmt = $pdo->prepare("UPDATE kategori_kompetensi SET nama = ?, deskripsi = ? WHERE id = ?");
            $stmt->execute([$nama, $deskripsi, $id]);
            set_flash_message('success', 'Kategori Kompetensi berhasil diperbarui.');
        }
    } elseif ($action === 'toggle') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] == 1 ? 0 : 1; 
        $stmt = $pdo->prepare("UPDATE kategori_kompetensi SET aktif = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        set_flash_message('success', 'Status diperbarui.');
    }
    
    redirect('admin/kuesioner/kategori_kompetensi.php');
}

$stmt = $pdo->query("SELECT * FROM kategori_kompetensi ORDER BY id ASC");
$kategori_list = $stmt->fetchAll();

require_once '../../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-2xl"><i class="ph ph-target"></i></div>
        <div><h2 class="text-xl font-bold text-gray-900">Kategori Kompetensi</h2><p class="text-sm text-gray-500">Kelola kategori kompetensi lulusan.</p></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2" id="form-title"><i class="ph ph-plus-circle text-brand-500"></i> Tambah Kategori</h3>
            <form action="" method="POST" class="space-y-4" id="form-kategori">
                <?= csrf_field() ?> 
                <input type="hidden" name="action" value="tambah" id="form-action">
                <input type="hidden" name="id" value="" id="form-id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="form-nama" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" required placeholder="Cth: K1 Data Analitik">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" id="form-deskripsi" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
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
                            <th class="px-6 py-4 font-medium">Nama Kategori</th>
                            <th class="px-6 py-4 font-medium">Deskripsi</th>
                            <th class="px-6 py-4 font-medium text-center">Status</th>
                            <th class="px-6 py-4 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($kategori_list as $kat): ?>
                            <tr class="hover:bg-gray-50 transition-colors <?= $kat['aktif'] ? '' : 'opacity-60 bg-gray-50' ?>">
                                <td class="px-6 py-3 text-gray-900 font-medium whitespace-nowrap"><?= escape($kat['nama']) ?></td>
                                <td class="px-6 py-3 text-gray-700 text-sm"><?= escape($kat['deskripsi']) ?></td>
                                <td class="px-6 py-3 text-center">
                                    <form action="" method="POST" class="inline-block m-0 p-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= $kat['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $kat['aktif'] ?>">
                                        
                                        <button type="submit" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 <?= $kat['aktif'] ? 'bg-brand-500' : 'bg-gray-200' ?>" role="switch" aria-checked="<?= $kat['aktif'] ? 'true' : 'false' ?>">
                                            <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out <?= $kat['aktif'] ? 'translate-x-5' : 'translate-x-0' ?>"></span>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <button type="button" onclick="editData(<?= htmlspecialchars(json_encode($kat)) ?>)" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-2 rounded-lg transition-colors inline-block" title="Edit">
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
    document.getElementById('form-title').innerHTML = '<i class="ph ph-pencil-simple text-blue-500"></i> Edit Kategori';
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = data.id;
    document.getElementById('form-nama').value = data.nama;
    document.getElementById('form-deskripsi').value = data.deskripsi || '';
    
    document.getElementById('btn-submit').textContent = 'Update';
    document.getElementById('btn-cancel').classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerHTML = '<i class="ph ph-plus-circle text-brand-500"></i> Tambah Kategori';
    document.getElementById('form-action').value = 'tambah';
    document.getElementById('form-id').value = '';
    document.getElementById('form-kategori').reset();
    
    document.getElementById('btn-submit').textContent = 'Simpan';
    document.getElementById('btn-cancel').classList.add('hidden');
}
</script>

<?php require_once '../../includes/footer.php'; ?>
