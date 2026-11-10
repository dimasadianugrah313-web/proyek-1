<?php
// ============================================================================
// OTORISASI & SESI - LO.LO COFFEE & RESTO INDRAMAYU
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Cek apakah pengguna sudah login dan memiliki role yang diizinkan
function check_auth(array $allowed_roles = []) {
    // 1. Jika belum login, arahkan ke halaman login
    if (!isset($_SESSION['id_user']) || !isset($_SESSION['role'])) {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }

    $current_role = $_SESSION['role'];

    // 2. Jika role tidak termasuk yang diizinkan, arahkan ke dashboard masing-masing
    if (!empty($allowed_roles) && !in_array($current_role, $allowed_roles)) {
        set_flash('danger', 'Akses ditolak. Anda telah dialihkan ke dashboard sesuai hak akses Anda.');
        switch ($current_role) {
            case 'chef':
                header("Location: " . BASE_URL . "/pages/chef/dashboard.php");
                break;
            case 'kasir':
                header("Location: " . BASE_URL . "/pages/kasir/dashboard.php");
                break;
            case 'pemilik':
                header("Location: " . BASE_URL . "/pages/pemilik/dashboard.php");
                break;
            default:
                header("Location: " . BASE_URL . "/index.php");
                break;
        }
        exit;
    }
}

// Helper getter sesi
function current_user_id() {
    return $_SESSION['id_user'] ?? null;
}

function current_user_name() {
    return $_SESSION['username'] ?? 'Pengguna';
}

function current_user_role() {
    return $_SESSION['role'] ?? '';
}

// Flash messages
function set_flash($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}
