<?php
require_once '../config/app.php';
require_once '../includes/helpers.php';

session_unset();
session_destroy();
session_start(); // Start a new session for the flash message

set_flash_message('success', 'Anda telah berhasil keluar.');
redirect('auth/login.php');
?>
