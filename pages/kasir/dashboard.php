<?php
// ============================================================================
// DASHBOARD KASIR - LO.LO COFFEE & RESTO INDRAMAYU
// ============================================================================

$page_title = 'Dashboard Kasir';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['kasir']); // Wajib role kasir

require_once __DIR__ . '/../../classes/Penjualan.php';
require_once __DIR__ . '/../../classes/Menu.php';

$penjualanModel = new Penjualan();
$menuModel = new Menu();

$statsHariIni = $penjualanModel->getStatistikKasirHariIni(current_user_id());
$riwayatKasir = $penjualanModel->getRiwayat(10, current_user_id());
$totalMenu = $menuModel->countTotal();

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Kartu Ringkasan Tugas Kasir -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Transaksi Kasir Hari Ini</div>
            <div class="stat-value"><?= number_format($statsHariIni['total_transaksi'] ?? 0, 0) ?></div>
            <div class="stat-subtext">Pesanan selesai diproses</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Total Omset Saya Hari Ini</div>
            <div class="stat-value" style="color: var(--color-success);">
                <?= format_rupiah($statsHariIni['total_pendapatan'] ?? 0) ?>
            </div>
            <div class="stat-subtext">Akumulasi penerimaan kasir</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Menu Siap Dijual</div>
            <div class="stat-value"><?= number_format($totalMenu, 0) ?></div>
            <div class="stat-subtext">Pilihan kopi & resto</div>
        </div>
    </div>
</div>

<!-- Banner Aksi Cepat Kasir -->
<div class="card" style="background: linear-gradient(135deg, var(--cafe-espresso) 0%, var(--cafe-mocha) 100%); color:#FFF;">
    <div class="card-body" style="padding: 28px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 6px;">Siap Melayani Pelanggan?</h2>
            <p style="font-size: 13.5px; color: #D7CCC8;">Buka transaksi kasir untuk menginput pesanan kopi dan makanan secara langsung.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/pages/kasir/penjualan.php" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                Buka Transaksi Penjualan Baru
            </a>
        </div>
    </div>
</div>

<!-- Riwayat Penjualan Terakhir oleh Kasir Ini -->
<div class="card">
    <div class="card-header">
        <div class="card-title">10 Transaksi Terakhir Anda</div>
        <a href="<?= BASE_URL ?>/pages/kasir/riwayat_penjualan.php" class="btn btn-sm btn-outline">Lihat Semua &rarr;</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Waktu</th>
                    <th>Total Item</th>
                    <th>Total Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayatKasir)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 24px; color: var(--text-muted);">
                            Belum ada transaksi penjualan yang diproses hari ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($riwayatKasir as $r): ?>
                        <tr>
                            <td><strong>#TRX-<?= str_pad($r['id_penjualan'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                            <td><span class="badge badge-secondary"><?= $r['total_item'] ?> Menu</span></td>
                            <td style="font-weight: 700; color: var(--cafe-accent);">
                                <?= format_rupiah($r['total_harga']) ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/pages/kasir/nota.php?id=<?= $r['id_penjualan'] ?>" class="btn btn-sm btn-outline">
                                    Cetak Nota
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
