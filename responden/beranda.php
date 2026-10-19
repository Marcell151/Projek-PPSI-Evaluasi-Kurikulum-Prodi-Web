<?php
// responden/beranda.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

require_login();
if ($_SESSION['user_role'] === 'admin') {
    redirect('admin/dashboard.php');
}

$stmt = $pdo->prepare("SELECT * FROM periode_evaluasi WHERE status = 'buka' LIMIT 1");
$stmt->execute();
$periode_aktif = $stmt->fetch();

$pengisian = null;
if ($periode_aktif) {
    $stmt2 = $pdo->prepare("SELECT status FROM pengisian WHERE pengguna_id = ? AND periode_id = ?");
    $stmt2->execute([$_SESSION['user_id'], $periode_aktif['id']]);
    $pengisian = $stmt2->fetch();
}

$page_title = 'Beranda';
require_once '../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
    <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 bg-brand-50 text-brand-500 rounded-full flex items-center justify-center text-3xl">
            <i class="ph ph-hand-waving"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Selamat Datang, <?= escape($_SESSION['user_name']) ?>!</h2>
            <p class="text-gray-500">Anda login sebagai: <span class="font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded text-sm"><?= ucfirst(escape($_SESSION['user_role'])) ?></span></p>
        </div>
    </div>
    
    <div class="mt-8 border-t border-gray-100 pt-8">
        <?php if ($periode_aktif): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6 flex gap-4 items-start">
                <i class="ph ph-info text-2xl text-blue-500 mt-0.5"></i>
                <div>
                    <h3 class="font-semibold text-blue-900">Pemberitahuan</h3>
                    <p class="text-blue-800 text-sm mt-1">Evaluasi Kurikulum untuk periode <strong class="font-bold"><?= escape($periode_aktif['tahun_akademik']) ?></strong> sedang dibuka.</p>
                </div>
            </div>
            
            <?php if (!$pengisian || $pengisian['status'] === 'draft'): ?>
                <div class="text-center p-8 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50">
                    <i class="ph ph-clipboard-text text-5xl text-gray-400 mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900">Evaluasi Belum Selesai</h3>
                    <p class="text-gray-500 text-sm mt-1 mb-6 max-w-md mx-auto">Anda belum menyelesaikan evaluasi untuk periode ini. Silakan menuju ke portal formulir untuk <?= $pengisian ? 'melanjutkan' : 'memulai' ?> pengisian.</p>
                    <a href="<?= base_url('responden/formulir.php') ?>" class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-lg font-medium transition-colors">
                        <?= $pengisian ? 'Lanjutkan Pengisian' : 'Mulai Pengisian' ?> <i class="ph ph-arrow-right"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center p-8 border-2 border-dashed border-brand-200 rounded-xl bg-brand-50">
                    <i class="ph ph-check-circle text-5xl text-brand-500 mb-3"></i>
                    <h3 class="text-lg font-medium text-brand-900">Evaluasi Selesai</h3>
                    <p class="text-brand-700 text-sm mt-1 mb-6 max-w-md mx-auto">Terima kasih! Anda telah menyelesaikan evaluasi kurikulum untuk periode ini.</p>
                    <a href="<?= base_url('responden/riwayat.php') ?>" class="inline-flex items-center gap-2 bg-white text-brand-600 border border-brand-200 hover:bg-brand-50 px-6 py-2.5 rounded-lg font-medium transition-colors shadow-sm">
                        <i class="ph ph-clock-counter-clockwise"></i> Lihat Riwayat
                    </a>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex gap-4 items-center">
                <i class="ph ph-warning-circle text-2xl text-amber-500"></i>
                <p class="text-amber-800 font-medium">Saat ini tidak ada periode evaluasi kurikulum yang sedang dibuka.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
