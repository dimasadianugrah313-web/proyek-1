<?php
// ============================================================================
// RIWAYAT PENJUALAN (KASIR) - LO.LO COFFEE & RESTO INDRAMAYU
// ============================================================================

$page_title = 'Riwayat Transaksi Penjualan';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['kasir', 'pemilik']); // Kasir dan Pemilik dapat melihat

require_once __DIR__ . '/../../classes/Penjualan.php';

$penjualanModel = new Penjualan();
// Jika role kasir, tampilkan riwayat kasir bersangkutan
$id_filter_user = (current_user_role() === 'kasir') ? current_user_id() : null;
$riwayat = $penjualanModel->getRiwayat(100, $id_filter_user);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">Daftar Riwayat Transaksi Penjualan</div>
        <?php if (current_user_role() === 'kasir'): ?>
            <a href="<?= BASE_URL ?>/pages/kasir/penjualan.php" class="btn btn-primary btn-sm">
                + Transaksi Baru
            </a>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal & Waktu</th>
                    <th>Kasir</th>
                    <th>Variasi Menu</th>
                    <th>Total Pembayaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayat)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 24px; color: var(--text-muted);">
                            Belum ada riwayat transaksi penjualan tercatat.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($riwayat as $r): ?>
                        <tr>
                            <td><strong>#TRX-<?= str_pad($r['id_penjualan'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($r['nama_kasir']) ?></td>
                            <td><span class="badge badge-secondary"><?= $r['total_item'] ?> Item Menu</span></td>
                            <td style="font-weight: 700; color: var(--cafe-accent);">
                                <?= format_rupiah($r['total_harga']) ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/pages/kasir/nota.php?id=<?= $r['id_penjualan'] ?>" class="btn btn-sm btn-outline">
                                    Lihat / Cetak Struk
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
