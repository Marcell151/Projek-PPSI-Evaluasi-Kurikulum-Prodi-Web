<?php
// includes/auth.php

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect('auth/login.php');
    }
    
    // Cegah akses jika wajib ganti sandi (kecuali sedang di profil, logout, dll)
    if (isset($_SESSION['wajib_ganti_sandi']) && $_SESSION['wajib_ganti_sandi']) {
        $current_script = basename($_SERVER['SCRIPT_NAME']);
        if (!in_array($current_script, ['profil.php', 'logout.php'])) {
            redirect('auth/profil.php');
        }
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['user_role'] !== $role) {
        http_response_code(403);
        exit('Forbidden');
    }
}
?>
