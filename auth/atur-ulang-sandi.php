<?php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';

if (isset($_SESSION['user_id'])) {
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('responden/beranda.php');
    }
}

$token = trim($_GET['token'] ?? '');
$error = '';
$success = '';
$valid_token = false;
$user_id = null;
$user_name = '';

if (empty($token)) {
    $error = "Token reset kata sandi tidak valid atau tidak disertakan.";
} else {
    $stmt = $pdo->prepare("SELECT id, nama FROM pengguna WHERE reset_token = ? AND reset_token_expired > NOW() AND aktif = 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $valid_token = true;
        $user_id = $user['id'];
        $user_name = $user['nama'];
    } else {
        $error = "Tautan reset kata sandi telah kedaluwarsa atau tidak valid. Silakan ajukan permohonan baru.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Token keamanan tidak valid. Silakan coba lagi.";
    } else {
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        if (strlen($password) < 6) {
            $error = "Kata sandi baru minimal 6 karakter.";
        } elseif ($password !== $password_confirm) {
            $error = "Konfirmasi kata sandi tidak cocok.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE pengguna SET password_hash = ?, reset_token = NULL, reset_token_expired = NULL, wajib_ganti_sandi = 0 WHERE id = ?");
            $stmt->execute([$hash, $user_id]);
            
            $success = "Kata sandi untuk <strong>" . escape($user_name) . "</strong> berhasil diperbarui! Anda dapat langsung masuk dengan sandi baru.";
            $valid_token = false; // Sembunyikan formulir
        }
    }
}

$page_title = 'Atur Ulang Kata Sandi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= defined('APP_NAME') ? escape(APP_NAME) : 'Evaluasi Kurikulum' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=' . filemtime(__DIR__ . '/../assets/css/style.css')) ?>">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/fill/style.css">
    <script src="<?= base_url('assets/vendor/phosphor.js') ?>" onerror="this.onerror=null;this.src='https://unpkg.com/@phosphor-icons/web';"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; color: #111827; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 text-gray-800">

    <div class="max-w-md w-full mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-brand-50 text-brand-500 mb-3">
                <i class="ph ph-key text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Atur Ulang Sandi</h2>
            <p class="text-sm text-gray-500 mt-1">
                <?= $valid_token ? "Membuat kata sandi baru untuk <strong>" . escape($user_name) . "</strong>" : "Buat kata sandi baru untuk akun Anda." ?>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3.5 rounded-r-lg text-sm text-red-700 flex items-start gap-2">
                <i class="ph ph-warning-circle text-lg text-red-500 flex-shrink-0 mt-0.5"></i>
                <div><?= escape($error) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-3.5 rounded-r-lg text-sm text-emerald-800">
                <div class="flex items-start gap-2">
                    <i class="ph ph-check-circle text-lg text-emerald-600 flex-shrink-0 mt-0.5"></i>
                    <div><?= $success ?></div>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="<?= base_url('auth/login.php') ?>" class="w-full inline-flex justify-center items-center gap-2 py-2.5 px-4 rounded-lg text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 transition-colors shadow-sm">
                    Menuju Halaman Masuk <i class="ph ph-arrow-right"></i>
                </a>
            </div>
        <?php elseif ($valid_token): ?>
            <form action="?token=<?= htmlspecialchars($token) ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="ph ph-lock-key text-base"></i>
                        </div>
                        <input type="password" id="password" name="password" minlength="6" required 
                            class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-shadow" placeholder="Minimal 6 karakter" autofocus>
                    </div>
                </div>

                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="ph ph-lock-key text-base"></i>
                        </div>
                        <input type="password" id="password_confirm" name="password_confirm" minlength="6" required 
                            class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-shadow" placeholder="Ulangi kata sandi baru">
                    </div>
                </div>

                <button type="submit" class="w-full flex justify-center items-center gap-2 py-2.5 px-4 rounded-lg text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors mt-2 cursor-pointer shadow-sm">
                    Simpan Kata Sandi <i class="ph ph-check"></i>
                </button>
            </form>
        <?php else: ?>
            <div class="mt-6 text-center space-y-2">
                <a href="<?= base_url('auth/lupa-sandi.php') ?>" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:text-brand-700">
                    <i class="ph ph-arrow-counter-clockwise"></i> Minta Tautan Baru
                </a>
                <div class="block">
                    <a href="<?= base_url('auth/login.php') ?>" class="text-xs text-gray-400 hover:text-gray-600">Kembali ke Halaman Masuk</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
