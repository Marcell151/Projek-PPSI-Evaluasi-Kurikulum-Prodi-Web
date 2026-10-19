<?php
// admin/periode.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Token CSRF tidak valid.');
        redirect('admin/periode.php');
    }

    $action = $_POST['action'] ?? '';
    
    if ($action === 'tambah') {
        $tahun = trim($_POST['tahun_akademik'] ?? '');
        if ($tahun) {
            $stmt = $pdo->prepare("INSERT INTO periode_evaluasi (tahun_akademik, status) VALUES (?, 'draft')");
            $stmt->execute([$tahun]);
            set_flash_message('success', 'Periode baru berhasil ditambahkan sebagai Draft.');
        }
    } elseif ($action === 'buka') {
        $id = $_POST['id'] ?? 0;
        $pdo->beginTransaction();
        $pdo->query("UPDATE periode_evaluasi SET status = 'tutup' WHERE status = 'buka'");
        $stmt = $pdo->prepare("UPDATE periode_evaluasi SET status = 'buka' WHERE id = ?");
        $stmt->execute([$id]);
        $pdo->commit();
        set_flash_message('success', 'Periode berhasil dibuka. Periode lain otomatis ditutup.');
    } elseif ($action === 'tutup') {
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("UPDATE periode_evaluasi SET status = 'tutup' WHERE id = ?");
        $stmt->execute([$id]);
        set_flash_message('success', 'Periode berhasil ditutup.');
    }
    
    redirect('admin/periode.php');
}

$stmt = $pdo->query("SELECT * FROM periode_evaluasi ORDER BY dibuat_pada DESC");
$periode_list = $stmt->fetchAll();

$page_title = 'Manajemen Periode Evaluasi';
require_once '../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><i class="ph ph-calendar-blank text-brand-500"></i> Manajemen Periode</h2>
            <p class="text-sm text-gray-500 mt-1">Hanya boleh ada 1 periode berstatus Buka.</p>
        </div>
        
        <form action="" method="POST" class="flex items-center gap-3 bg-gray-50 p-2 rounded-xl border border-gray-200">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="tambah">
            <input type="text" name="tahun_akademik" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none w-48" placeholder="Misal: 2026/2027 Ganjil" required>
            <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 whitespace-nowrap">
                <i class="ph ph-plus-circle"></i> Tambah
            </button>
        </form>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-4 font-medium">Tahun Akademik</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($periode_list as $p): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-gray-900 font-medium"><?= escape($p['tahun_akademik']) ?></td>
                        <td class="px-6 py-4">
                            <?php if ($p['status'] === 'buka'): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Buka
                                </span>
                            <?php elseif ($p['status'] === 'tutup'): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Tutup
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Draft
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form action="" method="POST" class="inline-block">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <?php if ($p['status'] !== 'buka'): ?>
                                    <input type="hidden" name="action" value="buka">
                                    <button type="submit" onclick="return confirm('Buka periode ini?')" class="text-brand-600 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">Buka</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="tutup">
                                    <button type="submit" onclick="return confirm('Tutup periode ini?')" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">Tutup</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
