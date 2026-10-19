<?php
// admin/master/dosen.php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../includes/csrf.php';

require_role('admin');

$page_title = 'Master Dosen';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Token CSRF tidak valid.');
        redirect('admin/master/dosen.php');
    }

    $action = $_POST['action'] ?? '';
    $default_password = '$2y$10$h412JiSNLScjfMjlAIS6beOg643fBa6Vy3EljKyZGs0R57LRsivOO';

    if ($action === 'tambah_satu') {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nidn = trim($_POST['nidn'] ?? '');

        if ($nama && $email) {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO pengguna (nama, email, password_hash, peran) VALUES (?, ?, ?, 'dosen')");
                $stmt->execute([$nama, $email, $default_password]);
                $user_id = $pdo->lastInsertId();

                $stmt2 = $pdo->prepare("INSERT INTO profil_pengguna (pengguna_id, nomor_induk) VALUES (?, ?)");
                $stmt2->execute([$user_id, $nidn]);
                
                $pdo->commit();
                set_flash_message('success', 'Dosen berhasil ditambahkan.');
            } catch (Exception $e) {
                $pdo->rollBack();
                set_flash_message('error', 'Gagal: Email mungkin sudah terdaftar.');
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nidn = trim($_POST['nidn'] ?? '');
        
        if ($nama && $email && $id) {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE pengguna SET nama = ?, email = ? WHERE id = ?");
                $stmt->execute([$nama, $email, $id]);
                
                $stmt2 = $pdo->prepare("UPDATE profil_pengguna SET nomor_induk = ? WHERE pengguna_id = ?");
                $stmt2->execute([$nidn, $id]);
                
                $pdo->commit();
                set_flash_message('success', 'Data Dosen berhasil diperbarui.');
            } catch (Exception $e) {
                $pdo->rollBack();
                set_flash_message('error', 'Gagal memperbarui: Email mungkin bentrok.');
            }
        }
    } elseif ($action === 'impor_excel') {
        $raw_default_password = $_POST['default_password'] ?? '123456';
        $default_password = password_hash($raw_default_password, PASSWORD_DEFAULT);
        
        if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file_excel']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['xls', 'xlsx', 'csv'])) {
                set_flash_message('error', 'Format file harus xls, xlsx, atau csv.');
                redirect('admin/master/dosen.php');
            }
            if (file_exists('../../vendor/autoload.php')) {
                require_once '../../vendor/autoload.php';
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();
                    $berhasil = 0; $gagal = 0;
                    $pdo->beginTransaction();
                    $stmt_user = $pdo->prepare("INSERT INTO pengguna (nama, email, password_hash, peran) VALUES (?, ?, ?, 'dosen')");
                    $stmt_profil = $pdo->prepare("INSERT INTO profil_pengguna (pengguna_id, nomor_induk) VALUES (?, ?)");
                    for ($i = 1; $i < count($rows); $i++) {
                        $nidn = trim($rows[$i][0] ?? '');
                        $nama = trim($rows[$i][1] ?? '');
                        $email = trim($rows[$i][2] ?? '');
                        if ($nama && $email) {
                            try {
                                $stmt_user->execute([$nama, $email, $default_password]);
                                $user_id = $pdo->lastInsertId();
                                $stmt_profil->execute([$user_id, $nidn]);
                                $berhasil++;
                            } catch (Exception $e) { $gagal++; }
                        }
                    }
                    $stmt_log = $pdo->prepare("INSERT INTO log_impor (admin_id, nama_berkas, jumlah_berhasil, jumlah_gagal) VALUES (?, ?, ?, ?)");
                    $stmt_log->execute([$_SESSION['user_id'], $_FILES['file_excel']['name'], $berhasil, $gagal]);
                    $pdo->commit();
                    set_flash_message('success', "Impor selesai. Berhasil: $berhasil, Gagal: $gagal.");
                } catch (Exception $e) { set_flash_message('error', 'Gagal membaca file: ' . $e->getMessage()); }
            } else { set_flash_message('error', 'Library PhpSpreadsheet belum terinstall.'); }
        }
    }
    redirect('admin/master/dosen.php');
}

$stmt = $pdo->query("SELECT u.id, u.nama, u.email, p.nomor_induk FROM pengguna u LEFT JOIN profil_pengguna p ON u.id = p.pengguna_id WHERE u.peran = 'dosen' ORDER BY u.nama ASC");
$dosen_list = $stmt->fetchAll();

require_once '../../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-2xl"><i class="ph ph-chalkboard-teacher"></i></div>
            <div><h2 class="text-xl font-bold text-gray-900">Master Data Dosen</h2><p class="text-sm text-gray-500">Kelola data dosen.</p></div>
        </div>
        <button type="button" onclick="document.getElementById('modal-tambah').classList.remove('hidden')" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
            <i class="ph ph-plus-circle"></i> Tambah / Impor
        </button>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
        <h3 class="font-bold text-gray-900 flex items-center gap-2"><i class="ph ph-list-bullets text-brand-500"></i> Daftar Dosen</h3>
        <span class="text-sm text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200">Total: <?= count($dosen_list) ?> Dosen</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white border-b border-gray-200 text-sm text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-4 font-medium">NIDN</th><th class="px-6 py-4 font-medium">Nama Lengkap</th>
                    <th class="px-6 py-4 font-medium">Email</th><th class="px-6 py-4 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (count($dosen_list) === 0): ?><tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data dosen.</td></tr><?php else: ?>
                    <?php foreach ($dosen_list as $dsn): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-900 font-medium"><?= escape($dsn['nomor_induk'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-gray-700"><?= escape($dsn['nama']) ?></td>
                            <td class="px-6 py-4 text-gray-500"><?= escape($dsn['email']) ?></td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" onclick="bukaModalEdit(<?= $dsn['id'] ?>, '<?= htmlspecialchars(escape($dsn['nama'])) ?>', '<?= htmlspecialchars(escape($dsn['email'])) ?>', '<?= htmlspecialchars(escape($dsn['nomor_induk'] ?? '')) ?>')" class="text-brand-600 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors inline-flex items-center gap-1"><i class="ph ph-pencil-simple"></i> Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-tambah" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white z-10">
            <h3 class="text-lg font-bold text-gray-900">Tambah Dosen Baru</h3>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="ph ph-x text-xl"></i></button>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center gap-2 border-b border-gray-100 pb-2"><i class="ph ph-user-plus text-brand-500"></i> Tambah Manual</h4>
                    <form action="" method="POST" class="space-y-4">
                        <?= csrf_field() ?> <input type="hidden" name="action" value="tambah_satu">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">NIDN</label><input type="text" name="nidn" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-brand-500"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label><input type="text" name="nama" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-brand-500" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label><input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-brand-500" required></div>
                        <div class="pt-2"><button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Simpan Dosen</button></div>
                    </form>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center gap-2 border-b border-gray-100 pb-2"><i class="ph ph-file-xls text-green-500"></i> Impor Massal Excel</h4>
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-4 text-sm text-blue-800 flex justify-between items-start gap-4">
                        <div>
                            <strong>Format Kolom CSV:</strong><br>Kolom A: NIDN<br>Kolom B: Nama Lengkap (Wajib)<br>Kolom C: Email (Wajib)
                        </div>
                        <a href="<?= base_url('template/template_impor_dosen.csv') ?>" download class="bg-white border border-blue-200 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap flex items-center gap-1 shadow-sm transition-colors">
                            <i class="ph ph-download-simple"></i> Unduh Format
                        </a>
                    </div>
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <?= csrf_field() ?> <input type="hidden" name="action" value="impor_excel">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Default (Bawaan)</label>
                            <input type="text" name="default_password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 outline-none" value="123456" required>
                        </div>
                        <div><input type="file" name="file_excel" accept=".xlsx,.xls,.csv" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100" required></div>
                        <div class="pt-2"><button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex justify-center items-center gap-2"><i class="ph ph-upload-simple"></i> Unggah & Impor</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-edit" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center"><h3 class="text-lg font-bold text-gray-900">Edit Data Dosen</h3><button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="ph ph-x text-xl"></i></button></div>
        <div class="p-6">
            <form action="" method="POST" class="space-y-4">
                <?= csrf_field() ?> <input type="hidden" name="action" value="edit"> <input type="hidden" name="id" id="edit-id">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">NIDN</label><input type="text" name="nidn" id="edit-nidn" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-brand-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label><input type="text" name="nama" id="edit-nama" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-brand-500" required></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label><input type="email" name="email" id="edit-email" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-brand-500" required></div>
                <div class="pt-4 flex justify-end gap-2"><button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Batal</button><button type="submit" class="bg-brand-500 text-white px-4 py-2 rounded-lg text-sm font-medium">Simpan Perubahan</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function bukaModalEdit(id, nama, email, nidn) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-nidn').value = nidn;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>

<?php require_once '../../includes/footer.php'; ?>
