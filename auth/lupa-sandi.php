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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Token keamanan tidak valid. Silakan coba lagi.";
    } else {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = "Email wajib diisi.";
        } else {
            $stmt = $pdo->prepare("SELECT id, nama FROM pengguna WHERE email = ? AND aktif = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expired = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $stmt = $pdo->prepare("UPDATE pengguna SET reset_token = ?, reset_token_expired = ? WHERE id = ?");
                $stmt->execute([$token, $expired, $user['id']]);
                
                $reset_url = base_url('auth/atur-ulang-sandi.php?token=' . $token);
                $success = "Tautan atur ulang kata sandi telah berhasil dibuat untuk <strong>" . escape($user['nama']) . "</strong>.<br><br>
                <div class='p-3 bg-white rounded-lg border border-emerald-200 text-xs text-emerald-900 mt-2'>
                    <div class='font-semibold text-emerald-800 mb-1 flex items-center gap-1.5'><i class='ph ph-link text-sm'></i> Tautan Reset Langsung (Mode Demo/Lokal):</div>
                    <a href='{$reset_url}' class='text-brand-600 font-bold hover:underline break-all block'>{$reset_url}</a>
                </div>";
            } else {
                // Untuk keamanan, tampilkan pesan generik jika email tidak ditemukan
                $success = "Jika alamat email Anda terdaftar dan aktif di sistem, tautan atur ulang kata sandi akan diproses.";
            }
        }
    }
}

$page_title = 'Lupa Kata Sandi';
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
                <i class="ph ph-shield-check text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Lupa Kata Sandi?</h2>
            <p class="text-sm text-gray-500 mt-1">Masukkan alamat email terdaftar Anda untuk mengatur ulang kata sandi.</p>
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
                <a href="<?= base_url('auth/login.php') ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                    <i class="ph ph-arrow-left"></i> Kembali ke Halaman Masuk
                </a>
            </div>
        <?php else: ?>
            <form action="<?= base_url('auth/lupa-sandi.php') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="ph ph-envelope-simple text-base"></i>
                        </div>
                        <input type="email" id="email" name="email" class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-shadow" placeholder="nama@email.com" required autofocus>
                    </div>
                </div>

                <button type="submit" class="w-full flex justify-center items-center gap-2 py-2.5 px-4 rounded-lg text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors mt-2 cursor-pointer shadow-sm">
                    Kirim Tautan Reset <i class="ph ph-paper-plane-right"></i>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    Ingat kata sandi Anda? 
                    <a href="<?= base_url('auth/login.php') ?>" class="font-medium text-brand-600 hover:text-brand-700 hover:underline">Masuk di sini</a>
                </p>
                <p class="text-xs text-gray-400 mt-2">
                    Kendala akun? Hubungi Administrator Prodi melalui tiket bantuan.
                </p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
