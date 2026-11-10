<?php
// ============================================================================
// DASHBOARD PEMILIK - LO.LO COFFEE & RESTO INDRAMAYU
// ============================================================================

$page_title = 'Dashboard Pemilik';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['pemilik']); // Wajib role pemilik

require_once __DIR__ . '/../../classes/Penjualan.php';
require_once __DIR__ . '/../../classes/BahanBaku.php';
require_once __DIR__ . '/../../classes/Menu.php';

$penjualanModel = new Penjualan();
$bahanModel = new BahanBaku();
$menuModel = new Menu();

$statsHariIni = $penjualanModel->getStatistikHariIni();
$totalMenu = $menuModel->countTotal();
$totalBahan = $bahanModel->countTotal();
$bahanMenipis = $bahanModel->getMenipis();
$countMenipis = count($bahanMenipis);
$penjualanTerbaru = $penjualanModel->getRiwayat(10);

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Tombol Aksi Pintas Pemilik -->
<div style="display:flex; justify-content: flex-end; margin-bottom: 14px; gap: 8px;">
    <a href="<?= BASE_URL ?>/pages/pemilik/reset_data.php" class="btn btn-sm btn-secondary" style="border-color: #D7CCC8;">
        Reset / Hapus Data
    </a>
</div>

<!-- KPI / Stat Cards Pemilik -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Total Omset Hari Ini</div>
            <div class="stat-value" style="color: var(--color-success);">
                <?= format_rupiah($statsHariIni['total_pendapatan'] ?? 0) ?>
            </div>
            <div class="stat-subtext">Pendapatan kotor hari ini</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Transaksi Hari Ini</div>
            <div class="stat-value"><?= number_format($statsHariIni['total_transaksi'] ?? 0, 0) ?></div>
            <div class="stat-subtext">Pesanan kasir selesai</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Total Menu Aktif</div>
            <div class="stat-value"><?= number_format($totalMenu, 0) ?></div>
            <div class="stat-subtext">Varian menu terdaftar</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Bahan Baku Menipis</div>
            <div class="stat-value" style="<?= $countMenipis > 0 ? 'color: var(--color-danger);' : '' ?>">
                <?= number_format($countMenipis, 0) ?> / <?= $totalBahan ?>
            </div>
            <div class="stat-subtext"><?= $countMenipis > 0 ? 'Perlu tindakan pengadaan' : 'Stok dalam batas aman' ?></div>
        </div>
    </div>
</div>

<!-- Daftar Notifikasi Stok Menipis -->
<?php if ($countMenipis > 0): ?>
    <div class="card" style="border-left: 4px solid var(--color-danger);">
        <div class="card-header" style="background: #FFF5F5;">
            <div class="card-title" style="color: var(--color-danger);">
                Notifikasi Stok Menipis (Di Bawah / Sama Dengan Batas Minimal)
            </div>
            <a href="<?= BASE_URL ?>/pages/pemilik/opname.php" class="btn btn-sm btn-danger">
                Lakukan Stock Opname
            </a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Bahan Baku</th>
                        <th>Sisa Stok Saat Ini</th>
                        <th>Batas Minimal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($bahanMenipis as $b): ?>
                        <tr class="row-danger">
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($b['nama_bahan']) ?></strong></td>
                            <td style="color: var(--color-danger); font-weight:700;">
                                <?= format_angka($b['stok_bahan']) ?> <?= htmlspecialchars($b['satuan']) ?>
                            </td>
                            <td><?= format_angka($b['batas_minimal']) ?> <?= htmlspecialchars($b['satuan']) ?></td>
                            <td><span class="badge badge-danger">Menipis Segera Restock</span></td>
                            <td>
                                <a href="<?= BASE_URL ?>/pages/pemilik/bahan.php?edit=<?= $b['id_bahan'] ?>" class="btn btn-sm btn-secondary">
                                    Kelola Bahan
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Ringkasan Penjualan Terbaru -->
<div class="card">
    <div class="card-header">
        <div class="card-title">10 Transaksi Penjualan Terakhir</div>
        <a href="<?= BASE_URL ?>/pages/pemilik/laporan.php" class="btn btn-sm btn-outline">
            Buka Rekap Penjualan Lengkap &rarr;
        </a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Waktu</th>
                    <th>Kasir</th>
                    <th>Total Item</th>
                    <th>Total Penjualan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($penjualanTerbaru)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 24px; color: var(--text-muted);">
                            Belum ada riwayat penjualan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($penjualanTerbaru as $p): ?>
                        <tr>
                            <td><strong>#TRX-<?= str_pad($p['id_penjualan'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($p['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($p['nama_kasir']) ?></td>
                            <td><span class="badge badge-secondary"><?= $p['total_item'] ?> Menu</span></td>
                            <td style="font-weight: 700; color: var(--cafe-accent);">
                                <?= format_rupiah($p['total_harga']) ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/pages/kasir/nota.php?id=<?= $p['id_penjualan'] ?>" class="btn btn-sm btn-outline">
                                    Nota
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
