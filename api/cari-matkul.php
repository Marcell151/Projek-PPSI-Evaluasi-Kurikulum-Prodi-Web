<?php
// api/cari-matkul.php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
if ($_SESSION['user_role'] === 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$q = $_GET['q'] ?? '';

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, kode, nama, sks, semester, jenis FROM mata_kuliah WHERE aktif = TRUE AND (kode LIKE ? OR nama LIKE ?) LIMIT 10");
$search = "%{$q}%";
$stmt->execute([$search, $search]);

echo json_encode($stmt->fetchAll());
?>
