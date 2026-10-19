<?php
session_start();
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$valid_token = false;
$user_id = null;

if (empty($token)) {
    $error = "Token tidak valid atau tidak ditemukan.";
} else {
    $stmt = $pdo->prepare("SELECT id FROM pengguna WHERE reset_token = ? AND reset_token_expired > NOW() AND aktif = 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $valid_token = true;
        $user_id = $user['id'];
    } else {
        $error = "Tautan reset kata sandi telah kedaluwarsa atau tidak valid. Silakan buat tautan baru.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (strlen($password) < 6) {
        $error = "Kata sandi minimal 6 karakter.";
    } elseif ($password !== $password_confirm) {
        $error = "Konfirmasi kata sandi tidak cocok.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE pengguna SET password_hash = ?, reset_token = NULL, reset_token_expired = NULL, wajib_ganti_sandi = 0 WHERE id = ?");
        $stmt->execute([$hash, $user_id]);
        
        $success = "Kata sandi berhasil diubah! Anda dapat masuk sekarang.";
        $valid_token = false; // Sembunyikan form
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <script src="<?= base_url('assets/vendor/phosphor.js') ?>"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 tracking-tight">
            Atur Ulang Kata Sandi
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 border border-gray-100">
            
            <?php if ($error): ?>
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                    <p class="text-sm text-red-700"><?= $error ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-4 bg-green-50 border border-green-200 p-4 rounded-lg">
                    <p class="text-sm text-green-700"><?= $success ?></p>
                </div>
                <div class="mt-6 text-center">
                    <a href="login.php" class="text-sm font-medium text-brand-600 hover:text-brand-500">Menuju Halaman Login</a>
                </div>
            <?php elseif ($valid_token): ?>
                <form class="space-y-6" action="?token=<?= htmlspecialchars($token) ?>" method="POST">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi Baru</label>
                        <input id="password" name="password" type="password" required 
                            class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm py-2.5 border px-3">
                    </div>
                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700">Konfirmasi Kata Sandi</label>
                        <input id="password_confirm" name="password_confirm" type="password" required 
                            class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm py-2.5 border px-3">
                    </div>
                    <div>
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-600 hover:bg-brand-700">
                            Simpan Kata Sandi
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="mt-6 text-center">
                    <a href="lupa-sandi.php" class="text-sm font-medium text-brand-600 hover:text-brand-500">Minta Tautan Baru</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
