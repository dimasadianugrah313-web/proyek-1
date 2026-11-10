<?php
// ============================================================================
// HALAMAN LOGIN - LO.LO COFFEE & RESTO INDRAMAYU
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Pengguna.php';

// Jika pengguna sudah login, arahkan langsung ke dashboard sesuai role
if (isset($_SESSION['id_user']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'chef':
            header("Location: " . BASE_URL . "/pages/chef/dashboard.php");
            exit;
        case 'kasir':
            header("Location: " . BASE_URL . "/pages/kasir/dashboard.php");
            exit;
        case 'pemilik':
            header("Location: " . BASE_URL . "/pages/pemilik/dashboard.php");
            exit;
    }
}

$error_message = '';
$success_message = '';

if (isset($_GET['logout'])) {
    $success_message = 'Anda telah berhasil keluar dari sistem.';
}

// Proses form login saat submit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Form data tidak boleh kosong';
    } else {
        $penggunaModel = new Pengguna();
        $user = $penggunaModel->authenticate($username, $password);

        if ($user) {
            // Keamanan: regenerasi ID sesi saat login sukses
            session_regenerate_id(true);

            $_SESSION['id_user'] = (int)$user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Arahkan ke dashboard sesuai role
            switch ($user['role']) {
                case 'chef':
                    header("Location: " . BASE_URL . "/pages/chef/dashboard.php");
                    exit;
                case 'kasir':
                    header("Location: " . BASE_URL . "/pages/kasir/dashboard.php");
                    exit;
                case 'pemilik':
                    header("Location: " . BASE_URL . "/pages/pemilik/dashboard.php");
                    exit;
                default:
                    $error_message = 'Role pengguna tidak valid.';
                    break;
            }
        } else {
            // Sesuai aturan bisnis: tampilkan pesan tepat
            $error_message = 'Username/password salah';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LO.LO Coffee & Resto Indramayu</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="login-body">

<div class="login-card">
    <div class="login-header">
        <div class="login-brand-logo">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Logo LO.LO Coffee & Resto">
        </div>
        <h2>LO.LO COFFEE & RESTO</h2>
        <p>Sistem Manajemen Stok & Penjualan</p>
    </div>

    <div class="login-body-content">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <span><?= htmlspecialchars($error_message) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <span><?= htmlspecialchars($success_message) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <div class="form-group">
                <label for="inputUsername" class="form-label">Username</label>
                <input type="text" id="inputUsername" name="username" class="form-control" 
                       placeholder="Masukkan username" required autofocus
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label for="inputPassword" class="form-label">Password</label>
                <input type="password" id="inputPassword" name="password" class="form-control" 
                       placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                Masuk ke Sistem
            </button>
        </form>

        <!-- Akun Demo Cepat untuk Pengujian Pengguna -->
        <div class="login-quick-accounts">
            <h4>Akun Uji Coba Cepat (Klik untuk Mengisi):</h4>
            <div class="account-pill-list">
                <button type="button" class="account-pill" data-username="pemilik" data-password="pemilik123">
                    <strong>Pemilik:</strong> pemilik123
                </button>
                <button type="button" class="account-pill" data-username="chef" data-password="chef123">
                    <strong>Chef:</strong> chef123
                </button>
                <button type="button" class="account-pill" data-username="kasir" data-password="kasir123">
                    <strong>Kasir:</strong> kasir123
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
