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

// Daftar akun demo (termasuk alias lama untuk kompatibilitas QA/UAT)
$demo_accounts = [
    'admin'                => 'admin@machung.ac.id',

    // Mahasiswa (1 Sudah Isi, 1 Draf Save & Recovery, 2 Belum Isi)
    'mahasiswa'            => '322310001@student.machung.ac.id',
    'mahasiswa_carlo'      => '322310001@student.machung.ac.id',
    'mahasiswa_marcell'    => '322310015@student.machung.ac.id',
    'mahasiswa_kevin'      => '322110005@student.machung.ac.id',
    'mahasiswa_william'    => '322310020@student.machung.ac.id',

    // Dosen (1 Sudah Isi, 2 Belum Isi)
    'dosen'                => 'daniel.saputra@machung.ac.id',
    'dosen_yosef'          => 'yosef.setiawan@machung.ac.id',
    'dosen_daniel'         => 'daniel.saputra@machung.ac.id',
    'dosen_ratna'          => 'ratna.kusumawati@machung.ac.id',

    // Alumni (1 Sudah Isi, 2 Belum Isi)
    'alumni'               => 'andi.alumni@gmail.com',
    'alumni_reza'          => 'reza.alumni@yahoo.com',
    'alumni_andi'          => 'andi.alumni@gmail.com',
    'alumni_maya'          => 'maya.alumni@hotmail.com',

    // Perusahaan (1 Sudah Isi, 2 Belum Isi)
    'perusahaan'           => 'hrd@indomarco.co.id',
    'perusahaan_bca'       => 'hr@bca.co.id',
    'perusahaan_indomarco' => 'hrd@indomarco.co.id',
    'perusahaan_gojek'     => 'talent@gojek.com',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Token CSRF tidak valid. Silakan coba lagi.');
        redirect('auth/login.php');
    }

    // 1-Click Demo Login Handler
    $demo_role = $_POST['demo_role'] ?? '';
    if (!empty($demo_role)) {
        if (isset($demo_accounts[$demo_role])) {
            $email = $demo_accounts[$demo_role];
            $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE email = ? AND aktif = TRUE");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['peran'];
                $_SESSION['user_name'] = $user['nama'];
                $_SESSION['wajib_ganti_sandi'] = 0; // Bypass ganti sandi untuk demo

                set_flash_message('success', 'Berhasil masuk sebagai demo: ' . escape($user['nama']) . ' (' . ucfirst($user['peran']) . ')');

                if ($user['peran'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('responden/beranda.php');
                }
            } else {
                set_flash_message('error', 'Akun demo tidak ditemukan di database. Coba tekan Reset Data Demo.');
                redirect('auth/login.php');
            }
        }
    }

    // Login Manual dengan Email & Kata Sandi
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

// Ambil status pengisian periode aktif secara real-time untuk label dropdown demo
$status_by_email = [];
try {
    $periode_aktif_id = $pdo->query("SELECT id FROM periode_evaluasi WHERE status = 'buka' ORDER BY id DESC LIMIT 1")->fetchColumn();
    if ($periode_aktif_id) {
        $stmt_st = $pdo->prepare("
            SELECT u.email, pg.status
            FROM pengguna u
            LEFT JOIN pengisian pg ON pg.pengguna_id = u.id AND pg.periode_id = ?
        ");
        $stmt_st->execute([$periode_aktif_id]);
        foreach ($stmt_st->fetchAll() as $row) {
            $status_by_email[$row['email']] = $row['status'] ?: 'belum';
        }
    }
} catch (Throwable $e) {
    $status_by_email = [];
}

function demo_status_label(string $status): string {
    if ($status === 'final') return 'Sudah Mengisi';
    if ($status === 'draft') return 'Draf / Lanjut Isi';
    return 'Belum Mengisi';
}

$demo_catalog = [
    'admin' => [
        ['key' => 'admin', 'name' => 'Administrator Prodi', 'sub' => 'admin@machung.ac.id', 'status' => ''],
    ],
    'mahasiswa' => [
        ['key' => 'mahasiswa_carlo',   'name' => 'Carlo Imanuel Suryahasilaga',      'sub' => '322310001', 'status' => demo_status_label($status_by_email['322310001@student.machung.ac.id'] ?? 'belum')],
        ['key' => 'mahasiswa_marcell', 'name' => 'Marcell Chandra Kenchana',         'sub' => '322310015', 'status' => demo_status_label($status_by_email['322310015@student.machung.ac.id'] ?? 'belum')],
        ['key' => 'mahasiswa_kevin',   'name' => 'Kevin Christian Dwi Putra Astawa', 'sub' => '322110005', 'status' => demo_status_label($status_by_email['322110005@student.machung.ac.id'] ?? 'belum')],
        ['key' => 'mahasiswa_william', 'name' => 'William Christopher Linardi',      'sub' => '322310020', 'status' => demo_status_label($status_by_email['322310020@student.machung.ac.id'] ?? 'belum')],
    ],
    'dosen' => [
        ['key' => 'dosen_yosef',  'name' => 'Yosef Setiawan',   'sub' => 'Dosen SI', 'status' => demo_status_label($status_by_email['yosef.setiawan@machung.ac.id'] ?? 'belum')],
        ['key' => 'dosen_daniel', 'name' => 'Daniel Saputra',   'sub' => 'Dosen SI', 'status' => demo_status_label($status_by_email['daniel.saputra@machung.ac.id'] ?? 'belum')],
        ['key' => 'dosen_ratna',  'name' => 'Ratna Kusumawati', 'sub' => 'Dosen SI', 'status' => demo_status_label($status_by_email['ratna.kusumawati@machung.ac.id'] ?? 'belum')],
    ],
    'alumni' => [
        ['key' => 'alumni_reza', 'name' => 'Reza Pratama', 'sub' => 'PT Tokopedia',            'status' => demo_status_label($status_by_email['reza.alumni@yahoo.com'] ?? 'belum')],
        ['key' => 'alumni_andi', 'name' => 'Andi Gunawan', 'sub' => 'PT Indomarco Prismatama', 'status' => demo_status_label($status_by_email['andi.alumni@gmail.com'] ?? 'belum')],
        ['key' => 'alumni_maya', 'name' => 'Maya Sari',    'sub' => 'Gojek',                   'status' => demo_status_label($status_by_email['maya.alumni@hotmail.com'] ?? 'belum')],
    ],
    'perusahaan' => [
        ['key' => 'perusahaan_bca',       'name' => 'IT Recruitment Bank BCA',     'sub' => 'Bank BCA',                'status' => demo_status_label($status_by_email['hr@bca.co.id'] ?? 'belum')],
        ['key' => 'perusahaan_indomarco', 'name' => 'HRD PT Indomarco Prismatama', 'sub' => 'PT Indomarco Prismatama', 'status' => demo_status_label($status_by_email['hrd@indomarco.co.id'] ?? 'belum')],
        ['key' => 'perusahaan_gojek',     'name' => 'Talent Acquisition Gojek',    'sub' => 'Gojek',                   'status' => demo_status_label($status_by_email['talent@gojek.com'] ?? 'belum')],
    ],
];

$page_title = 'Masuk';
require_once '../includes/header.php';
?>

<div class="max-w-md mx-auto my-12 bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-brand-50 text-brand-500 mb-3">
            <i class="ph ph-user text-2xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Selamat Datang</h2>
        <p class="text-sm text-gray-500 mt-1">Masuk untuk melanjutkan ke portal evaluasi.</p>
    </div>

    <?= display_flash_message() ?>

    <!-- Form Login Utama -->
    <form action="<?= base_url('auth/login.php') ?>" method="POST" class="space-y-4">
        <?= csrf_field() ?>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="ph ph-envelope-simple text-base"></i>
                </div>
                <input type="email" id="email" name="email" class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-shadow" placeholder="nama@email.com" required autofocus>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
                <a href="<?= base_url('auth/lupa-sandi.php') ?>" class="text-xs font-medium text-brand-600 hover:text-brand-500">Lupa sandi?</a>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="ph ph-lock-key text-base"></i>
                </div>
                <input type="password" id="password" name="password" class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-shadow" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="w-full flex justify-center items-center gap-2 py-2.5 px-4 rounded-lg text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors mt-2 cursor-pointer">
            Masuk <i class="ph ph-arrow-right"></i>
        </button>
    </form>

    <!-- Panel Akses Cepat Demo (Rapi dengan Dropdown Peran & Akun + Reset Demo) -->
    <div class="mt-6 pt-5 border-t border-gray-100">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Akses Cepat Demo</p>
            <form action="<?= base_url('api/reset-demo.php') ?>" method="POST" onsubmit="return confirm('Kembalikan data ke kondisi awal? Pengisian percobaan terakhir akan direset.');">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_to" value="auth/login.php">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-colors cursor-pointer" style="width: 28px; height: 28px;" title="Kembalikan data awal" aria-label="Kembalikan data awal">
                    <i class="ph ph-arrows-clockwise text-sm"></i>
                </button>
            </form>
        </div>

        <form action="<?= base_url('auth/login.php') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="demo-peran" class="block text-xs font-medium text-gray-600 mb-1">Peran</label>
                <select id="demo-peran" onchange="updateDemoAccounts()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs text-gray-800 bg-gray-50 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                    <option value="mahasiswa" selected>Mahasiswa</option>
                    <option value="dosen">Dosen</option>
                    <option value="alumni">Alumni</option>
                    <option value="perusahaan">Perusahaan</option>
                    <option value="admin">Admin Prodi</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="demo-akun" class="block text-xs font-medium text-gray-600 mb-1">Pilih Akun</label>
                <select id="demo-akun" name="demo_role" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs text-gray-800 bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </select>
            </div>

            <button type="submit" class="w-full flex justify-center items-center gap-2 py-2.5 px-4 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-800 border border-gray-200 transition-colors cursor-pointer" style="margin-top: 4px;">
                <i class="ph ph-sign-in text-sm"></i> Masuk sebagai Akun Demo
            </button>
        </form>
    </div>
</div>

<script>
const DEMO_CATALOG = <?= json_encode($demo_catalog, JSON_UNESCAPED_UNICODE) ?>;

function updateDemoAccounts() {
    const peran = document.getElementById('demo-peran').value;
    const selectAkun = document.getElementById('demo-akun');
    const list = DEMO_CATALOG[peran] || [];
    selectAkun.innerHTML = '';

    list.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.key;
        opt.textContent = item.status
            ? `${item.name} — [${item.status}]`
            : `${item.name} (${item.sub})`;
        selectAkun.appendChild(opt);
    });
}

document.addEventListener('DOMContentLoaded', updateDemoAccounts);
</script>

<?php require_once '../includes/footer.php'; ?>
