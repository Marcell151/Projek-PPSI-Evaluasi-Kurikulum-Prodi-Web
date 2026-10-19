<?php
session_start();
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = "Email wajib diisi.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM pengguna WHERE email = ? AND aktif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expired = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $pdo->prepare("UPDATE pengguna SET reset_token = ?, reset_token_expired = ? WHERE id = ?");
            $stmt->execute([$token, $expired, $user['id']]);
            
            // Karena ini sistem lokal tanpa SMTP, kita pura-pura mengirim dan langsung menampilkan link reset
            $success = "Tautan reset kata sandi telah dibuat. <br><br><b>Pemberitahuan Sistem:</b> Karena layanan email SMTP sedang dimatikan, silakan gunakan tautan berikut secara manual (atau hubungi Admin via Tiket): <br><br> <a href='".BASE_URL."/auth/atur-ulang-sandi.php?token=$token' class='text-brand-600 font-bold break-all'>".BASE_URL."/auth/atur-ulang-sandi.php?token=$token</a>";
        } else {
            // Demi keamanan, jangan beri tahu jika email tidak ada
            $success = "Jika email Anda terdaftar, instruksi reset akan dikirim.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <script src="<?= base_url('assets/vendor/phosphor.js') ?>"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .bg-login { background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'#0ea5e9\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E'); }
    </style>
</head>
<body class="bg-gray-50 bg-login font-sans antialiased text-gray-800 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <div class="w-16 h-16 bg-brand-600 rounded-2xl flex items-center justify-center shadow-lg transform rotate-3">
                <i class="ph ph-shield-check text-4xl text-white -rotate-3"></i>
            </div>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 tracking-tight">
            Lupa Kata Sandi?
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Masukkan email Anda untuk mengatur ulang kata sandi.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 border border-gray-100">
            
            <?php if ($error): ?>
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="ph ph-warning-circle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?= $error ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-4 bg-green-50 border border-green-200 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="ph ph-check-circle text-green-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700"><?= $success ?></p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <a href="login.php" class="text-sm font-medium text-brand-600 hover:text-brand-500">Kembali ke Login</a>
                </div>
            <?php else: ?>
                <form class="space-y-6" action="" method="POST">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="ph ph-envelope-simple text-gray-400 text-lg"></i>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                class="pl-10 block w-full border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm py-2.5 border" 
                                placeholder="nama@kampus.ac.id">
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors">
                            Kirim Tautan Reset
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Ingat kata sandi? 
                        <a href="login.php" class="font-medium text-brand-600 hover:text-brand-500 transition-colors">Masuk di sini</a>
                    </p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-500">
                        Jika Anda tidak dapat menggunakan fitur ini, silakan buat Tiket Bantuan ke Admin.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
