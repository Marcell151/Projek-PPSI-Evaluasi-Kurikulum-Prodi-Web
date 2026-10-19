<?php
$page_title = 'Dokumentasi Arsitektur Sistem';
require_once 'config/app.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';
require_role('admin');

$diagrams = [
    ['id' => 'usecase', 'title' => 'Use Case Diagram', 'desc' => 'Interaksi menyeluruh antara Aktor dan fitur aplikasi.'],
    ['id' => 'act_auth', 'title' => 'Activity Diagram: Autentikasi (RBAC)', 'desc' => 'Alur pemeriksaan kredensial dan perutean hak akses.'],
    ['id' => 'act_import', 'title' => 'Activity Diagram: Impor Data Pengguna', 'desc' => 'Alur unggah massal Excel dan pembuatan akun otomatis.'],
    ['id' => 'act_instrumen', 'title' => 'Activity Diagram: Manajemen Instrumen', 'desc' => 'Alur penambahan dan soft-delete instrumen.'],
    ['id' => 'act_kuesioner', 'title' => 'Activity Diagram: Kuesioner Bersyarat', 'desc' => 'Alur pengisian kuesioner dengan auto-save dan validasi wajib.'],
    ['id' => 'act_dasbor', 'title' => 'Activity Diagram: Dasbor Analitik', 'desc' => 'Alur agregasi data real-time, penanganan empty-state, dan ekspor.'],
    ['id' => 'erd', 'title' => 'Entity Relationship Diagram (ERD)', 'desc' => 'Skema struktur relasional database logika.']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <!-- Tailwind CSS Lokal (Anti-CDN) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/output.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/phosphor.css') ?>">
    <style>
        .diagram-container {
            display: flex;
            justify-content: center;
            overflow-x: auto;
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .diagram-container img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    <div class="min-h-screen p-8">
        <div class="max-w-6xl mx-auto">
            
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <i class="ph ph-blueprint text-brand-600"></i> Dokumentasi Arsitektur Sistem
                    </h1>
                    <p class="text-gray-500 mt-2">Diagram Use Case, Activity, dan ERD berdasarkan spesifikasi PlantUML.</p>
                </div>
                <a href="<?= base_url('admin/dashboard.php') ?>" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 font-medium text-sm flex items-center gap-2">
                    <i class="ph ph-arrow-left"></i> Kembali ke Dasbor
                </a>
            </div>

            <div class="space-y-12">
                <?php foreach ($diagrams as $index => $diag): ?>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="mb-4">
                        <h2 class="text-xl font-bold text-gray-800">
                            3.3.<?= $index + 1 ?> <?= htmlspecialchars($diag['title']) ?>
                        </h2>
                        <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($diag['desc']) ?></p>
                    </div>
                    <div class="diagram-container">
                        <img src="<?= base_url('assets/img/diagrams/' . $diag['id'] . '.svg') ?>" alt="<?= htmlspecialchars($diag['title']) ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <footer class="mt-12 text-center text-sm text-gray-400 pb-8">
                &copy; <?= date('Y') ?> Evaluasi Kurikulum - Dokumentasi Internal
            </footer>
        </div>
    </div>
</body>
</html>
