<?php
// ============================================================================
// HEADER TEMPLATE - LO.LO COFFEE & RESTO INDRAMAYU
// ============================================================================

require_once __DIR__ . '/auth.php';
$current_user_name = current_user_name();
$current_user_role = current_user_role();
$page_title = $page_title ?? 'LO.LO Coffee & Resto';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - LO.LO Coffee & Resto</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo.png">
    <link rel="shortcut icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="app-container">
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="btn-toggle-sidebar" id="btnToggleSidebar" aria-label="Menu Navigasi">
                    ☰
                </button>
                <div class="page-title">
                    <h1><?= htmlspecialchars($page_title) ?></h1>
                </div>
            </div>

            <div class="topbar-right">
                <div class="time-badge">
                    <span><?= date('d M Y') ?></span>
                </div>
                <div class="user-info-quick" style="display:flex;align-items:center;gap:10px;">
                    <span class="role-pill role-<?= htmlspecialchars($current_user_role) ?>">
                        <?= strtoupper(htmlspecialchars($current_user_role)) ?>
                    </span>
                    <button type="button" class="btn btn-sm btn-secondary" data-trigger="modal-logout" title="Keluar dari Sistem">
                        Keluar
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <main class="content-body">
            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                    <div style="font-weight:600;margin-right:6px;">
                        <?= $flash['type'] === 'success' ? 'Berhasil:' : ($flash['type'] === 'danger' ? 'Perhatian:' : 'Info:') ?>
                    </div>
                    <div><?= htmlspecialchars($flash['message']) ?></div>
                </div>
            <?php endif; ?>
