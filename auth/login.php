<?php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';
require_once '../includes/auth.php';

if (is_logged_in()) {
    if ($_SESSION['user_role'] === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('responden/beranda.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Token CSRF tidak valid. Silakan coba lagi.');
        redirect('auth/login.php');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        set_flash_message('error', 'Email dan kata sandi wajib diisi.');
        redirect('auth/login.php');
    }

    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE email = ? AND aktif = TRUE");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['peran'];
        $_SESSION['user_name'] = $user['nama'];
        $_SESSION['wajib_ganti_sandi'] = $user['wajib_ganti_sandi'];
        
        if ($user['wajib_ganti_sandi']) {
            redirect('auth/profil.php');
        } elseif ($user['peran'] === 'admin') {
            redirect('admin/dashboard.php');
        } else {
            redirect('responden/beranda.php');
        }
    } else {
        set_flash_message('error', 'Email atau kata sandi salah, atau akun Anda dinonaktifkan.');
        redirect('auth/login.php');
    }
}

$page_title = 'Masuk';
require_once '../includes/header.php';
?>

<div class="max-w-md mx-auto mt-12 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-brand-50 text-brand-500 mb-4">
                <i class="ph ph-user text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Selamat Datang</h2>
            <p class="text-sm text-gray-500 mt-2">Masuk untuk melanjutkan ke portal evaluasi.</p>
        </div>
        
        <form action="<?= base_url('auth/login.php') ?>" method="POST" class="space-y-5">
            <?= csrf_field() ?>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ph ph-envelope-simple text-gray-400"></i>
                    </div>
                    <input type="email" id="email" name="email" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-shadow" placeholder="nama@email.com" required autofocus>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
                    <a href="<?= base_url('auth/lupa-sandi.php') ?>" class="text-xs font-medium text-brand-600 hover:text-brand-500">Lupa sandi?</a>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ph ph-lock-key text-gray-400"></i>
                    </div>
                    <input type="password" id="password" name="password" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 sm:text-sm transition-shadow" placeholder="••••••••" required>
                </div>
            </div>
            
            <button type="submit" class="w-full flex justify-center items-center gap-2 py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors mt-2">
                Masuk <i class="ph ph-arrow-right font-bold"></i>
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
