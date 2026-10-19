<?php
// auth/profil.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

// Tidak bisa menggunakan require_login karena ada exception, jadi cek mandiri
if (!is_logged_in()) {
    redirect('auth/login.php');
}

$user_id = $_SESSION['user_id'];
$is_wajib_ganti = $_SESSION['wajib_ganti_sandi'] ?? false;

// Ambil data user & profil
$stmt = $pdo->prepare("
    SELECT u.nama, u.email, u.peran, p.* 
    FROM pengguna u 
    LEFT JOIN profil_pengguna p ON u.id = p.pengguna_id 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$profil = $stmt->fetch();

// Inisiasi row profil jika belum ada
if (!$profil['pengguna_id'] && $profil['nama']) {
    $pdo->prepare("INSERT IGNORE INTO profil_pengguna (pengguna_id) VALUES (?)")->execute([$user_id]);
    $profil['pengguna_id'] = $user_id;
    $profil['alamat'] = '';
    $profil['telepon'] = '';
    $profil['posisi_pekerjaan'] = '';
    $profil['instansi'] = '';
    $profil['foto'] = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    
    if ($aksi === 'ganti_sandi') {
        $pass_lama = $_POST['sandi_lama'] ?? '';
        $pass_baru = $_POST['sandi_baru'] ?? '';
        $pass_konfirm = $_POST['sandi_konfirmasi'] ?? '';
        
        $stmt = $pdo->prepare("SELECT password_hash FROM pengguna WHERE id = ?");
        $stmt->execute([$user_id]);
        $hash_lama = $stmt->fetchColumn();
        
        if (!password_verify($pass_lama, $hash_lama)) {
            set_flash_message('error', 'Kata sandi lama salah.');
        } elseif (strlen($pass_baru) < 6) {
            set_flash_message('error', 'Kata sandi baru minimal 6 karakter.');
        } elseif ($pass_baru !== $pass_konfirm) {
            set_flash_message('error', 'Konfirmasi kata sandi tidak cocok.');
        } else {
            $hash = password_hash($pass_baru, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE pengguna SET password_hash = ?, wajib_ganti_sandi = 0 WHERE id = ?");
            $stmt->execute([$hash, $user_id]);
            $_SESSION['wajib_ganti_sandi'] = false;
            set_flash_message('success', 'Kata sandi berhasil diperbarui.');
            // Reload profil to remove warning
            redirect('auth/profil.php');
        }
    }
    
    if ($aksi === 'update_profil') {
        $alamat = $_POST['alamat'] ?? '';
        $telepon = $_POST['telepon'] ?? '';
        $posisi = $_POST['posisi_pekerjaan'] ?? '';
        $instansi = $_POST['instansi'] ?? '';
        
        $foto_path = $profil['foto'];
        
        // Handle upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['foto']['tmp_name'];
            $name = $_FILES['foto']['name'];
            $size = $_FILES['foto']['size'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            
            if ($size > 2 * 1024 * 1024) {
                set_flash_message('error', 'Ukuran foto maksimal 2 MB.');
                redirect('auth/profil.php');
            } elseif (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                set_flash_message('error', 'Format foto harus JPG atau PNG.');
                redirect('auth/profil.php');
            } else {
                $new_name = 'foto_' . $user_id . '_' . time() . '.' . $ext;
                $dest = '../uploads/foto/' . $new_name;
                if (move_uploaded_file($tmp, $dest)) {
                    // Hapus foto lama
                    if ($foto_path && file_exists('../' . $foto_path)) {
                        unlink('../' . $foto_path);
                    }
                    $foto_path = 'uploads/foto/' . $new_name;
                }
            }
        }
        
        $stmt = $pdo->prepare("UPDATE profil_pengguna SET alamat = ?, telepon = ?, posisi_pekerjaan = ?, instansi = ?, foto = ? WHERE pengguna_id = ?");
        $stmt->execute([$alamat, $telepon, $posisi, $instansi, $foto_path, $user_id]);
        
        set_flash_message('success', 'Profil berhasil diperbarui.');
        redirect('auth/profil.php');
    }
}

$page_title = 'Profil Saya';
require_once '../includes/header.php';
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <?php if ($is_wajib_ganti): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex items-start">
                <i class="ph ph-warning-circle text-red-500 text-2xl mt-0.5 mr-3"></i>
                <div>
                    <h3 class="text-red-800 font-bold">Wajib Ganti Kata Sandi</h3>
                    <p class="text-sm text-red-700 mt-1">Anda masih menggunakan kata sandi bawaan (default). Demi keamanan, silakan ganti kata sandi Anda sekarang sebelum melanjutkan.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?= display_flash_message() ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Sidebar / Foto -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col items-center">
            <div class="w-32 h-32 rounded-full bg-gray-200 overflow-hidden mb-4 border-4 border-white shadow-lg">
                <?php if ($profil['foto']): ?>
                    <img src="<?= base_url($profil['foto']) ?>" alt="Foto" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-brand-100 text-brand-600">
                        <i class="ph ph-user text-5xl"></i>
                    </div>
                <?php endif; ?>
            </div>
            <h2 class="text-xl font-bold text-gray-900 text-center"><?= escape($profil['nama']) ?></h2>
            <p class="text-sm text-gray-500 uppercase tracking-wide mt-1 font-semibold"><?= escape($profil['peran']) ?></p>
            <p class="text-sm text-gray-500 mt-2 text-center break-all"><?= escape($profil['email']) ?></p>
        </div>

        <!-- Form Info & Sandi -->
        <div class="md:col-span-2 space-y-6">
            
            <!-- Ubah Sandi -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                    <i class="ph ph-lock-key text-brand-500 text-xl"></i>
                    <h3 class="font-bold text-gray-900">Ubah Kata Sandi</h3>
                </div>
                <div class="p-6">
                    <form action="" method="POST" class="space-y-4">
                        <input type="hidden" name="aksi" value="ganti_sandi">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Lama</label>
                            <input type="password" name="sandi_lama" required class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
                                <input type="password" name="sandi_baru" required class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" name="sandi_konfirmasi" required class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Perbarui Kata Sandi</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Profil Lengkap -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden <?= $is_wajib_ganti ? 'opacity-50 pointer-events-none' : '' ?>">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                    <i class="ph ph-identification-card text-brand-500 text-xl"></i>
                    <h3 class="font-bold text-gray-900">Informasi Pribadi</h3>
                </div>
                <div class="p-6">
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="aksi" value="update_profil">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telepon / WhatsApp</label>
                                <input type="text" name="telepon" value="<?= escape($profil['telepon']) ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unggah Foto (JPG/PNG, Maks 2MB)</label>
                                <input type="file" name="foto" accept=".jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <?php if (in_array($profil['peran'], ['alumni', 'perusahaan'])): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Instansi / Perusahaan</label>
                                <input type="text" name="instansi" value="<?= escape($profil['instansi']) ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Posisi Pekerjaan</label>
                                <input type="text" name="posisi_pekerjaan" value="<?= escape($profil['posisi_pekerjaan']) ?>" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm">
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm"><?= escape($profil['alamat']) ?></textarea>
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Simpan Profil</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
