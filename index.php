<?php
// index.php - Main Application Router & Controller
require_once __DIR__ . '/config/db.php';

// Cek apakah pengguna sudah login, jika belum arahkan ke halaman login
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$role = $currentUser['role_key']; // 'pemilik' or 'staf'
$page = $_GET['page'] ?? 'dashboard';

// Define page mapping and metadata
$pages = [
    'dashboard' => [
        'title' => 'Dashboard · LO.LO Coffee & Resto',
        'breadcrumb' => 'Operasional / Dashboard',
        'view' => ($role === 'pemilik') ? 'dashboard_pemilik.php' : 'dashboard_staf.php',
        'allowed_roles' => ['pemilik', 'staf']
    ],
    'bahan_baku' => [
        'title' => 'Bahan Baku · LO.LO Coffee & Resto',
        'breadcrumb' => 'Operasional / Bahan Baku',
        'view' => 'bahan_baku.php',
        'allowed_roles' => ['pemilik']
    ],
    'menu_resep' => [
        'title' => 'Menu & Resep · LO.LO Coffee & Resto',
        'breadcrumb' => 'Operasional / Menu & Resep',
        'view' => 'menu_resep.php',
        'allowed_roles' => ['pemilik']
    ],
    'stok_masuk' => [
        'title' => 'Stok Masuk · LO.LO Coffee & Resto',
        'breadcrumb' => 'Operasional / Stok Masuk',
        'view' => 'stok_masuk.php',
        'allowed_roles' => ['pemilik', 'staf']
    ],
    'stok_keluar' => [
        'title' => 'Stok Keluar · LO.LO Coffee & Resto',
        'breadcrumb' => 'Operasional / Stok Keluar',
        'view' => 'stok_keluar.php',
        'allowed_roles' => ['pemilik', 'staf']
    ],
    'penjualan' => [
        'title' => 'Penjualan · LO.LO Coffee & Resto',
        'breadcrumb' => 'Operasional / Penjualan',
        'view' => 'penjualan.php',
        'allowed_roles' => ['pemilik', 'staf']
    ],
    'pengeluaran' => [
        'title' => 'Pengeluaran · LO.LO Coffee & Resto',
        'breadcrumb' => 'Operasional / Pengeluaran',
        'view' => 'pengeluaran.php',
        'allowed_roles' => ['pemilik', 'staf']
    ],
    'stock_opname' => [
        'title' => 'Stock Opname · LO.LO Coffee & Resto',
        'breadcrumb' => 'Operasional / Stock Opname',
        'view' => 'stock_opname.php',
        'allowed_roles' => ['pemilik']
    ],
    'laporan_bulanan' => [
        'title' => 'Laporan Bulanan · LO.LO Coffee & Resto',
        'breadcrumb' => 'Operasional / Laporan Bulanan',
        'view' => 'laporan_bulanan.php',
        'allowed_roles' => ['pemilik']
    ]
];

// Fallback if page not found
if (!isset($pages[$page])) {
    $page = 'dashboard';
}

$pageConfig = $pages[$page];
$pageTitle = $pageConfig['title'];
$breadcrumb = $pageConfig['breadcrumb'];
$activePage = $page;

// Render layout and view
include __DIR__ . '/views/layout/header.php';
include __DIR__ . '/views/layout/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 min-h-screen bg-[#f8fafc]">
    <?php include __DIR__ . '/views/layout/topbar.php'; ?>

    <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto">
        <?php 
        // Role check
        if (!in_array($role, $pageConfig['allowed_roles'])) {
            ?>
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center max-w-lg mx-auto my-12 shadow-xs">
                <div class="w-12 h-12 bg-amber-50 text-amber-700 rounded-full flex items-center justify-center mx-auto mb-4 border border-amber-200">
                    <i data-lucide="lock" class="w-6 h-6"></i>
                </div>
                <h2 class="text-base font-bold text-slate-900 mb-1">Akses Terbatas untuk Peran Staf</h2>
                <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                    Halaman ini dikhususkan untuk <strong>Pemilik (Dina Pramesti)</strong> guna menjaga kerahasiaan master data dan perhitungan laba.
                </p>
                <div class="flex items-center justify-center space-x-3">
                    <button type="button" onclick="switchRole('pemilik')" class="px-4 py-2 bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors">
                        Beralih ke Pemilik (Dina)
                    </button>
                    <a href="index.php?page=dashboard" class="px-4 py-2 border border-slate-200 text-xs font-semibold rounded-lg text-slate-700 hover:bg-slate-50 transition-colors">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
            <?php
        } else {
            $viewPath = __DIR__ . '/views/' . $pageConfig['view'];
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "<div class='p-4 bg-rose-50 text-rose-700 rounded-lg text-xs'>View file not found: {$pageConfig['view']}</div>";
            }
        }
        ?>
    </main>

    <!-- Page Footer matching mockups -->
    <footer class="px-8 py-4 border-t border-slate-200/80 bg-white text-[11px] text-slate-400 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 no-print">
        <div>LO.LO Coffee & Resto · Indramayu</div>
        <div>Data contoh · September 2026</div>
    </footer>
</div>

<?php 
include __DIR__ . '/views/layout/footer.php'; 
?>
