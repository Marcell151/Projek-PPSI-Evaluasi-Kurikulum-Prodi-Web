<?php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

require_login();
if ($_SESSION['user_role'] === 'admin') {
    redirect('admin/tiket.php');
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjek = $_POST['subjek'] ?? '';
    $isi = $_POST['isi'] ?? '';
    
    if (!empty($subjek) && !empty($isi)) {
        $stmt = $pdo->prepare("INSERT INTO tiket (pengguna_id, subjek, isi) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $subjek, $isi]);
        set_flash_message('success', 'Tiket bantuan berhasil dikirim.');
        redirect('responden/tiket.php');
    } else {
        set_flash_message('error', 'Subjek dan isi pesan wajib diisi.');
    }
}

$stmt = $pdo->prepare("SELECT * FROM tiket WHERE pengguna_id = ? ORDER BY dibuat_pada DESC");
$stmt->execute([$user_id]);
$tikets = $stmt->fetchAll();

$page_title = 'Bantuan / Tiket';
require_once '../includes/header.php';
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-6">
        <i class="ph ph-lifebuoy text-3xl text-brand-500"></i>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Bantuan (Tiket)</h2>
            <p class="text-gray-500 text-sm">Laporkan masalah sistem atau kirim pertanyaan ke Admin.</p>
        </div>
    </div>

    <?= display_flash_message() ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">Buat Tiket Baru</h3>
                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subjek</label>
                        <input type="text" name="subjek" required class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm" placeholder="Contoh: Lupa sandi, Error submit">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pesan / Masalah</label>
                        <textarea name="isi" required rows="4" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm" placeholder="Jelaskan detail masalah Anda..."></textarea>
                    </div>
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Kirim Tiket</button>
                </form>
            </div>
        </div>
        
        <div class="md:col-span-2">
            <div class="space-y-4">
                <?php if (count($tikets) === 0): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                        <i class="ph ph-chat-circle text-4xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500 font-medium">Anda belum pernah membuat tiket.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tikets as $t): ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-gray-900"><?= escape($t['subjek']) ?></h4>
                                    <p class="text-xs text-gray-500 mt-1"><?= date('d M Y H:i', strtotime($t['dibuat_pada'])) ?></p>
                                </div>
                                <?php 
                                    $badge = 'bg-gray-100 text-gray-700';
                                    if ($t['status'] === 'diproses') $badge = 'bg-blue-100 text-blue-700';
                                    if ($t['status'] === 'selesai') $badge = 'bg-green-100 text-green-700';
                                ?>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide <?= $badge ?>"><?= $t['status'] ?></span>
                            </div>
                            <div class="px-6 py-4">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?= escape($t['isi']) ?></p>
                                
                                <?php if ($t['balasan_admin']): ?>
                                    <div class="mt-4 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                                        <p class="text-xs font-bold text-blue-800 mb-1 flex items-center gap-1"><i class="ph ph-headset"></i> Balasan Admin:</p>
                                        <p class="text-sm text-blue-900 whitespace-pre-wrap"><?= escape($t['balasan_admin']) ?></p>
                                        <p class="text-xs text-blue-500 mt-2"><?= date('d M Y H:i', strtotime($t['diperbarui_pada'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
