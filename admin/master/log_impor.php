<?php
// admin/master/log_impor.php
require_once '../../config/app.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../includes/csrf.php';

require_role('admin');

$page_title = 'Riwayat Log Impor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Token CSRF tidak valid.');
        redirect('admin/master/log_impor.php');
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM log_impor WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Catatan log impor berhasil dihapus.');
        }
    }
    redirect('admin/master/log_impor.php');
}

$stmt = $pdo->query("
    SELECT l.*, u.nama AS nama_admin
    FROM log_impor l
    JOIN pengguna u ON l.admin_id = u.id
    ORDER BY l.waktu DESC, l.id DESC
");
$logs = $stmt->fetchAll();

require_once '../../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-2xl">
            <i class="ph ph-clock-counter-clockwise"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Riwayat Log Impor Massal</h2>
            <p class="text-sm text-gray-500">Daftar riwayat unggah data responden (Excel/CSV) oleh administrator.</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
        <h3 class="font-bold text-gray-900 flex items-center gap-2"><i class="ph ph-list-bullets text-brand-500"></i> Daftar Log Impor</h3>
        <span class="text-sm text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200">Total: <?= count($logs) ?> Riwayat</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-white border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-4 font-medium">Waktu</th>
                    <th class="px-6 py-4 font-medium">Admin</th>
                    <th class="px-6 py-4 font-medium">Nama Berkas</th>
                    <th class="px-6 py-4 font-medium text-center">Berhasil</th>
                    <th class="px-6 py-4 font-medium text-center">Gagal</th>
                    <th class="px-6 py-4 font-medium">Ringkasan</th>
                    <th class="px-6 py-4 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (count($logs) === 0): ?>
                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Belum ada riwayat impor berkas.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-600 whitespace-nowrap"><?= date('d M Y H:i', strtotime($l['waktu'])) ?></td>
                            <td class="px-6 py-4 text-gray-900 font-medium"><?= escape($l['nama_admin']) ?></td>
                            <td class="px-6 py-4 text-gray-700 font-mono text-xs"><?= escape($l['nama_berkas']) ?></td>
                            <td class="px-6 py-4 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700"><?= (int)$l['jumlah_berhasil'] ?></span></td>
                            <td class="px-6 py-4 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $l['jumlah_gagal'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' ?>"><?= (int)$l['jumlah_gagal'] ?></span></td>
                            <td class="px-6 py-4 text-gray-500 text-xs"><?= escape($l['ringkasan_galat'] ?: '-') ?></td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <form action="" method="POST" class="inline-block" onsubmit="return confirm('Hapus catatan log impor ini?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-medium inline-flex items-center gap-1">
                                        <i class="ph ph-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
