<?php
// includes/helpers.php

function base_url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path) {
    header("Location: " . base_url($path));
    exit;
}

function escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function set_flash_message($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function display_flash_message() {
    $flash = get_flash_message();
    if (!$flash) return '';
    
    $color = $flash['type'] === 'success' ? 'green' : ($flash['type'] === 'error' ? 'red' : 'blue');
    $icon = $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'warning-circle' : 'info');
    
    return '<div class="mb-6 bg-'.$color.'-50 border-l-4 border-'.$color.'-500 p-4 rounded-r-lg shadow-sm">
        <div class="flex items-center">
            <i class="ph ph-'.$icon.' text-'.$color.'-500 text-xl mr-3"></i>
            <p class="text-sm text-'.$color.'-700">'.escape($flash['message']).'</p>
        </div>
    </div>';
}
?>
