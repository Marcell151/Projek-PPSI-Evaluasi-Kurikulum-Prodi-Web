<?php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tiket_id = $_POST['tiket_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $balasan = $_POST['balasan_admin'] ?? '';
    
    if ($tiket_id && $status) {
        $stmt = $pdo->prepare("UPDATE tiket SET status = ?, balasan_admin = ? WHERE id = ?");
        $stmt->execute([$status, $balasan, $tiket_id]);
        set_flash_message('success', 'Status dan balasan tiket berhasil diperbarui.');
        redirect('admin/tiket.php');
    }
}

// Ambil semua tiket
$stmt = $pdo->query("
    SELECT t.*, u.nama, u.email, u.peran 
    FROM tiket t 
    JOIN pengguna u ON t.pengguna_id = u.id 
    ORDER BY CASE WHEN t.status = 'baru' THEN 1 WHEN t.status = 'diproses' THEN 2 ELSE 3 END, t.dibuat_pada DESC
");
$tikets = $stmt->fetchAll();

$page_title = 'Manajemen Tiket';
require_once '../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-16 h-16 bg-brand-50 text-brand-500 rounded-full flex items-center justify-center text-3xl">
            <i class="ph ph-lifebuoy"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Manajemen Tiket</h2>
            <p class="text-gray-500">Kelola pertanyaan dan keluhan dari responden.</p>
        </div>
    </div>
</div>

<?= display_flash_message() ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <?php if (count($tikets) === 0): ?>
        <div class="p-8 text-center">
            <p class="text-gray-500">Belum ada tiket yang masuk.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4 font-medium">Pengirim</th>
                        <th class="px-6 py-4 font-medium">Subjek & Pesan</th>
                        <th class="px-6 py-4 font-medium">Status & Balasan</th>
                        <th class="px-6 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php foreach ($tikets as $t): ?>
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900"><?= escape($t['nama']) ?></p>
                                <p class="text-xs text-gray-500"><?= escape($t['email']) ?></p>
                                <span class="inline-block mt-1 bg-gray-200 text-gray-700 px-2 py-0.5 rounded text-[10px] uppercase"><?= $t['peran'] ?></span>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="font-semibold text-gray-900 mb-1"><?= escape($t['subjek']) ?></p>
                                <p class="text-gray-600 text-xs line-clamp-3"><?= escape($t['isi']) ?></p>
                                <p class="text-[10px] text-gray-400 mt-2"><?= date('d M Y H:i', strtotime($t['dibuat_pada'])) ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                    $badge = 'bg-gray-100 text-gray-700';
                                    if ($t['status'] === 'diproses') $badge = 'bg-blue-100 text-blue-700';
                                    if ($t['status'] === 'selesai') $badge = 'bg-green-100 text-green-700';
                                ?>
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide <?= $badge ?> mb-2 inline-block"><?= $t['status'] ?></span>
                                <?php if ($t['balasan_admin']): ?>
                                    <p class="text-xs text-blue-800 bg-blue-50 p-2 rounded line-clamp-2" title="<?= escape($t['balasan_admin']) ?>"><?= escape($t['balasan_admin']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="openModal(<?= htmlspecialchars(json_encode($t)) ?>)" class="text-brand-600 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition-colors font-medium text-xs inline-flex items-center gap-1">
                                    <i class="ph ph-pencil-simple"></i> Kelola
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Balas -->
<div id="modalTiket" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-900">Kelola Tiket</h3>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>
        <form action="" method="POST" class="p-6">
            <input type="hidden" name="tiket_id" id="form-tiket-id">
            
            <div class="mb-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                <p class="text-xs text-gray-500 mb-1">Pesan dari <span id="v-nama" class="font-bold text-gray-700"></span></p>
                <p class="text-sm font-semibold text-gray-900" id="v-subjek"></p>
                <p class="text-sm text-gray-700 mt-2 whitespace-pre-wrap" id="v-isi"></p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Ubah Status</label>
                <select name="status" id="form-status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                    <option value="baru">Baru</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Balasan Admin</label>
                <textarea name="balasan_admin" id="form-balasan" rows="4" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm" placeholder="Tuliskan balasan atau solusi di sini..."></textarea>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700">Simpan Pembaruan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(data) {
    document.getElementById('form-tiket-id').value = data.id;
    document.getElementById('v-nama').textContent = data.nama;
    document.getElementById('v-subjek').textContent = data.subjek;
    document.getElementById('v-isi').textContent = data.isi;
    document.getElementById('form-status').value = data.status;
    document.getElementById('form-balasan').value = data.balasan_admin || '';
    
    document.getElementById('modalTiket').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modalTiket').classList.add('hidden');
}
</script>

<?php require_once '../includes/footer.php'; ?>
