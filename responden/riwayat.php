<?php
// responden/riwayat.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

require_login();
if ($_SESSION['user_role'] === 'admin') {
    redirect('admin/dashboard.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT p.*, pe.tahun_akademik FROM pengisian p JOIN periode_evaluasi pe ON p.periode_id = pe.id WHERE p.pengguna_id = ? AND p.status = 'final' ORDER BY p.waktu_submit DESC");
$stmt->execute([$user_id]);
$riwayat = $stmt->fetchAll();

$page_title = 'Riwayat Evaluasi';
require_once '../includes/header.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><i class="ph ph-clock-counter-clockwise text-brand-500"></i> Riwayat Evaluasi</h2>
    <p class="text-sm text-gray-500 mt-1">Daftar evaluasi kurikulum yang telah Anda selesaikan.</p>
</div>

<?php if (count($riwayat) > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-4 font-medium">Periode Akademik</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium">Waktu Penyelesaian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($riwayat as $r): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900 flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-500"><i class="ph ph-calendar"></i></div>
                            <?= escape($r['tahun_akademik']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="ph ph-check-circle fill-current"></i> Selesai
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-sm">
                            <i class="ph ph-clock mr-1"></i> <?= date('d M Y, H:i', strtotime($r['waktu_submit'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="text-center p-12 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50">
        <i class="ph ph-ghost text-5xl text-gray-300 mb-3"></i>
        <h3 class="text-lg font-medium text-gray-900">Belum Ada Riwayat</h3>
        <p class="text-gray-500 text-sm mt-1">Anda belum memiliki riwayat pengisian evaluasi kurikulum yang selesai (Final).</p>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
