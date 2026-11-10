<?php
// ============================================================================
// SIDEBAR PER ROLE - LO.LO COFFEE & RESTO INDRAMAYU
// ============================================================================

$current_script = basename($_SERVER['SCRIPT_NAME']);
$role = current_user_role();
?>
<aside class="sidebar" id="appSidebar">
    <!-- Brand Info -->
    <div class="sidebar-brand">
        <div class="brand-icon">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Logo LO.LO Coffee & Resto">
        </div>
        <div class="brand-text">
            <h2>LO.LO COFFEE</h2>
            <span>Resto Indramayu</span>
        </div>
    </div>

    <!-- Active User Card -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <?= strtoupper(substr(current_user_name(), 0, 1)) ?>
        </div>
        <div class="user-info">
            <div class="username"><?= htmlspecialchars(current_user_name()) ?></div>
            <span class="role-pill role-<?= htmlspecialchars($role) ?>">
                <?= strtoupper(htmlspecialchars($role)) ?>
            </span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <ul class="sidebar-menu">
        <?php if ($role === 'pemilik'): ?>
            <li class="menu-category">Utama</li>
            <li>
                <a href="<?= BASE_URL ?>/pages/pemilik/dashboard.php" class="<?= $current_script === 'dashboard.php' ? 'active' : '' ?>">
                    <span>Dashboard Pemilik</span>
                </a>
            </li>

            <li class="menu-category">Data Master & Resep</li>
            <li>
                <a href="<?= BASE_URL ?>/pages/pemilik/bahan.php" class="<?= $current_script === 'bahan.php' ? 'active' : '' ?>">
                    <span>Data Bahan Baku</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/pages/pemilik/menu.php" class="<?= $current_script === 'menu.php' ? 'active' : '' ?>">
                    <span>Data Menu</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/pages/pemilik/resep.php" class="<?= $current_script === 'resep.php' ? 'active' : '' ?>">
                    <span>Atur Resep (BOM)</span>
                </a>
            </li>

            <li class="menu-category">Inventori & Laporan</li>
            <li>
                <a href="<?= BASE_URL ?>/pages/pemilik/opname.php" class="<?= $current_script === 'opname.php' ? 'active' : '' ?>">
                    <span>Stock Opname</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/pages/pemilik/laporan.php" class="<?= in_array($current_script, ['laporan.php', 'export_excel.php', 'export_pdf.php']) ? 'active' : '' ?>">
                    <span>Rekap Penjualan</span>
                </a>
            </li>

            <li class="menu-category">Pengaturan Sistem</li>
            <li>
                <a href="<?= BASE_URL ?>/pages/pemilik/reset_data.php" class="<?= $current_script === 'reset_data.php' ? 'active' : '' ?>">
                    <span>Reset / Hapus Data</span>
                </a>
            </li>

        <?php elseif ($role === 'chef'): ?>
            <li class="menu-category">Utama</li>
            <li>
                <a href="<?= BASE_URL ?>/pages/chef/dashboard.php" class="<?= $current_script === 'dashboard.php' ? 'active' : '' ?>">
                    <span>Dashboard Chef</span>
                </a>
            </li>

            <li class="menu-category">Manajemen Stok</li>
            <li>
                <a href="<?= BASE_URL ?>/pages/chef/stok_masuk.php" class="<?= $current_script === 'stok_masuk.php' ? 'active' : '' ?>">
                    <span>Input Stok Masuk</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/pages/chef/stok_keluar.php" class="<?= $current_script === 'stok_keluar.php' ? 'active' : '' ?>">
                    <span>Input Stok Keluar</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/pages/chef/riwayat_stok.php" class="<?= $current_script === 'riwayat_stok.php' ? 'active' : '' ?>">
                    <span>Riwayat Transaksi Stok</span>
                </a>
            </li>

        <?php elseif ($role === 'kasir'): ?>
            <li class="menu-category">Utama</li>
            <li>
                <a href="<?= BASE_URL ?>/pages/kasir/dashboard.php" class="<?= $current_script === 'dashboard.php' ? 'active' : '' ?>">
                    <span>Dashboard Kasir</span>
                </a>
            </li>

            <li class="menu-category">Transaksi Penjualan</li>
            <li>
                <a href="<?= BASE_URL ?>/pages/kasir/penjualan.php" class="<?= $current_script === 'penjualan.php' ? 'active' : '' ?>">
                    <span>Input Transaksi Penjualan</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/pages/kasir/riwayat_penjualan.php" class="<?= in_array($current_script, ['riwayat_penjualan.php', 'nota.php']) ? 'active' : '' ?>">
                    <span>Riwayat Penjualan</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Footer Sidebar: Tombol Logout -->
    <div class="sidebar-footer">
        <button type="button" class="btn-sidebar-logout" data-trigger="modal-logout">
            <span>Keluar Sistem</span>
        </button>
    </div>
</aside>
