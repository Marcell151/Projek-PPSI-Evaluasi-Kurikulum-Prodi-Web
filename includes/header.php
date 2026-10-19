<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? escape($page_title) . ' - ' : '' ?>Evaluasi Kurikulum</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=' . filemtime(__DIR__ . '/../assets/css/style.css')) ?>">
    <script src="<?= base_url('assets/vendor/phosphor.js') ?>"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; color: #111827; }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <i class="ph ph-graduation-cap text-3xl text-brand-500"></i>
                        <a href="<?= base_url() ?>" class="font-bold text-xl text-gray-900 tracking-tight">Evaluasi<span class="text-brand-500">Kurikulum</span></a>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <?php if (is_logged_in()): ?>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="<?= base_url('admin/dashboard.php') ?>" class="text-gray-600 hover:text-brand-500 font-medium transition-colors"><i class="ph ph-squares-four mr-1"></i>Dashboard</a>
                            <a href="<?= base_url('admin/periode.php') ?>" class="text-gray-600 hover:text-brand-500 font-medium transition-colors"><i class="ph ph-calendar-blank mr-1"></i>Periode</a>
                            <div class="relative group">
                                <button class="text-gray-600 hover:text-brand-500 font-medium transition-colors flex items-center gap-1">
                                    <i class="ph ph-database mr-1"></i> Master Data <i class="ph ph-caret-down text-xs"></i>
                                </button>
                                <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                    <div class="py-2">
                                        <a href="<?= base_url('admin/master/mahasiswa.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-500">Mahasiswa</a>
                                        <a href="<?= base_url('admin/master/dosen.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-500">Dosen</a>
                                        <a href="<?= base_url('admin/master/alumni.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-500">Alumni</a>
                                        <a href="<?= base_url('admin/master/perusahaan.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-500">Perusahaan</a>
                                    </div>
                                </div>
                            </div>
                            <div class="relative group">
                                <button class="text-gray-600 hover:text-brand-500 font-medium transition-colors flex items-center gap-1">
                                    <i class="ph ph-clipboard-text mr-1"></i> Kuesioner <i class="ph ph-caret-down text-xs"></i>
                                </button>
                                <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                    <div class="py-2">
                                        <a href="<?= base_url('admin/kuesioner/matkul.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-500">Bank Mata Kuliah</a>
                                        <a href="<?= base_url('admin/kuesioner/bahan_kajian.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-500">Bahan Kajian</a>
                                        <a href="<?= base_url('admin/kuesioner/kategori_kompetensi.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-500">Kategori Kompetensi</a>
                                        <a href="<?= base_url('admin/kuesioner/pertanyaan.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-500">Bank Pertanyaan</a>
                                    </div>
                                </div>
                            </div>
                            <a href="<?= base_url('admin/tiket.php') ?>" class="text-gray-600 hover:text-brand-500 font-medium transition-colors"><i class="ph ph-lifebuoy mr-1"></i>Tiket</a>
                        <?php else: ?>
                            <a href="<?= base_url('responden/beranda.php') ?>" class="text-gray-600 hover:text-brand-500 font-medium transition-colors"><i class="ph ph-house mr-1"></i>Beranda</a>
                            <a href="<?= base_url('responden/formulir.php') ?>" class="text-gray-600 hover:text-brand-500 font-medium transition-colors"><i class="ph ph-note-pencil mr-1"></i>Formulir</a>
                            <a href="<?= base_url('responden/riwayat.php') ?>" class="text-gray-600 hover:text-brand-500 font-medium transition-colors"><i class="ph ph-clock-counter-clockwise mr-1"></i>Riwayat</a>
                            <a href="<?= base_url('responden/tiket.php') ?>" class="text-gray-600 hover:text-brand-500 font-medium transition-colors"><i class="ph ph-lifebuoy mr-1"></i>Bantuan</a>
                        <?php endif; ?>
                        
                        <div class="relative group border-l border-gray-200 pl-6 ml-2">
                            <button class="flex items-center gap-2 text-gray-700 hover:text-brand-500 font-medium transition-colors">
                                <i class="ph ph-user-circle text-2xl"></i>
                                <span class="text-sm"><?= escape($_SESSION['user_name']) ?></span>
                                <i class="ph ph-caret-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                <div class="py-2">
                                    <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                        <p class="text-sm font-semibold text-gray-900 truncate"><?= escape($_SESSION['user_name']) ?></p>
                                        <p class="text-xs text-gray-500 uppercase tracking-wider mt-0.5"><?= escape($_SESSION['user_role']) ?></p>
                                    </div>
                                    <a href="<?= base_url('auth/profil.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-500 flex items-center gap-2"><i class="ph ph-user text-lg"></i> Profil Saya</a>
                                    <a href="<?= base_url('auth/logout.php') ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 mt-1"><i class="ph ph-sign-out text-lg"></i> Keluar</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= base_url('auth/login.php') ?>" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                            <i class="ph ph-sign-in"></i> Masuk
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center md:hidden">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <i class="ph ph-list text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div class="md:hidden hidden bg-white border-t border-gray-200" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <?php if (is_logged_in()): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="<?= base_url('admin/dashboard.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Dashboard</a>
                        <a href="<?= base_url('admin/periode.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Periode Evaluasi</a>
                        <p class="px-3 py-2 mt-2 text-xs font-semibold text-gray-500 uppercase">Master Data</p>
                        <a href="<?= base_url('admin/master/mahasiswa.php') ?>" class="block pl-6 pr-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Mahasiswa</a>
                        <a href="<?= base_url('admin/master/dosen.php') ?>" class="block pl-6 pr-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Dosen</a>
                        <a href="<?= base_url('admin/master/alumni.php') ?>" class="block pl-6 pr-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Alumni</a>
                        <a href="<?= base_url('admin/master/perusahaan.php') ?>" class="block pl-6 pr-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Perusahaan</a>
                        <p class="px-3 py-2 mt-2 text-xs font-semibold text-gray-500 uppercase">Kuesioner</p>
                        <a href="<?= base_url('admin/kuesioner/matkul.php') ?>" class="block pl-6 pr-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Bank Mata Kuliah</a>
                        <a href="<?= base_url('admin/kuesioner/bahan_kajian.php') ?>" class="block pl-6 pr-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Bahan Kajian</a>
                        <a href="<?= base_url('admin/kuesioner/kategori_kompetensi.php') ?>" class="block pl-6 pr-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Kategori Kompetensi</a>
                        <a href="<?= base_url('admin/kuesioner/pertanyaan.php') ?>" class="block pl-6 pr-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Bank Pertanyaan</a>
                        <a href="<?= base_url('admin/tiket.php') ?>" class="block px-3 py-2 mt-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Manajemen Tiket</a>
                    <?php else: ?>
                        <a href="<?= base_url('responden/beranda.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Beranda</a>
                        <a href="<?= base_url('responden/formulir.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Formulir</a>
                        <a href="<?= base_url('responden/riwayat.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Riwayat</a>
                        <a href="<?= base_url('responden/tiket.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Bantuan (Tiket)</a>
                    <?php endif; ?>
                    <div class="border-t border-gray-200 mt-4 pt-4 pb-2">
                        <div class="px-3">
                            <p class="text-base font-medium text-gray-900"><?= escape($_SESSION['user_name']) ?></p>
                            <p class="text-sm font-medium text-gray-500 uppercase"><?= escape($_SESSION['user_role']) ?></p>
                        </div>
                        <div class="mt-3 space-y-1">
                            <a href="<?= base_url('auth/profil.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-500 hover:bg-gray-50">Profil Saya</a>
                            <a href="<?= base_url('auth/logout.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-red-50">Keluar</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= base_url('auth/login.php') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-brand-600 hover:bg-brand-50">Masuk</a>
                <?php endif; ?>
            </div>
        </div>
            </div>
        </div>
    </nav>
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php $flash = get_flash_message(); ?>
        <?php if ($flash): ?>
            <div class="mb-6 p-4 rounded-lg flex items-start gap-3 <?= $flash['type'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                <i class="ph <?= $flash['type'] === 'success' ? 'ph-check-circle text-green-500' : 'ph-warning-circle text-red-500' ?> text-xl mt-0.5"></i>
                <p class="text-sm font-medium"><?= escape($flash['message']) ?></p>
            </div>
        <?php endif; ?>
