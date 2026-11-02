<?php
// logout.php - Sesi logout dan kembali ke halaman login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();
header('Location: login.php');
exit;
