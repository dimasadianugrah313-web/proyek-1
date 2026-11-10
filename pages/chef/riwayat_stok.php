<?php
// ============================================================================
// RIWAYAT TRANSAKSI STOK - CHEF
// ============================================================================

$page_title = 'Riwayat Transaksi Stok';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['chef']); // Wajib role chef

require_once __DIR__ . '/../../classes/TransaksiStok.php';

$stokModel = new TransaksiStok();
$filter_jenis = isset($_GET['jenis']) && in_array($_GET['jenis'], ['masuk', 'keluar']) ? $_GET['jenis'] : null;
$riwayat = $stokModel->getRiwayat(100, $filter_jenis);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">Log Riwayat Transaksi Stok Bahan Baku</div>
        <div style="display:flex; gap:8px;">
            <a href="<?= BASE_URL ?>/pages/chef/riwayat_stok.php" 
               class="btn btn-sm <?= empty($filter_jenis) ? 'btn-primary' : 'btn-secondary' ?>">
                Semua
            </a>
            <a href="<?= BASE_URL ?>/pages/chef/riwayat_stok.php?jenis=masuk" 
               class="btn btn-sm <?= $filter_jenis === 'masuk' ? 'btn-success' : 'btn-secondary' ?>">
                Stok Masuk
            </a>
            <a href="<?= BASE_URL ?>/pages/chef/riwayat_stok.php?jenis=keluar" 
               class="btn btn-sm <?= $filter_jenis === 'keluar' ? 'btn-warning' : 'btn-secondary' ?>">
                Stok Keluar
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal & Waktu</th>
                    <th>Jenis Transaksi</th>
                    <th>Nama Bahan Baku</th>
                    <th>Jumlah</th>
                    <th>Petugas / User</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayat)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 24px; color: var(--text-muted);">
                            Tidak ada data transaksi stok ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($riwayat as $r): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                            <td>
                                <?php if ($r['jenis_stok'] === 'masuk'): ?>
                                    <span class="badge badge-success">Masuk</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Keluar</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($r['nama_bahan']) ?></strong></td>
                            <td style="font-weight: 700; color: <?= $r['jenis_stok'] === 'masuk' ? 'var(--color-success)' : 'var(--cafe-mocha)' ?>;">
                                <?= ($r['jenis_stok'] === 'masuk' ? '+' : '-') . format_angka($r['jumlah_stok']) ?> <?= htmlspecialchars($r['satuan']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($r['nama_user']) ?>
                                <small style="color:var(--text-muted);"> (<?= htmlspecialchars($r['role']) ?>)</small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
