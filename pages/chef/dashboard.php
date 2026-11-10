<?php
// ============================================================================
// DASHBOARD CHEF - LO.LO COFFEE & RESTO INDRAMAYU
// ============================================================================

$page_title = 'Dashboard Chef';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['chef']); // Wajib role chef

require_once __DIR__ . '/../../classes/BahanBaku.php';
require_once __DIR__ . '/../../classes/TransaksiStok.php';

$bahanModel = new BahanBaku();
$stokModel = new TransaksiStok();

$statsStok = $stokModel->getStatistikHariIni();
$totalBahan = $bahanModel->countTotal();
$bahanMenipis = $bahanModel->getMenipis();
$countMenipis = count($bahanMenipis);
$riwayatTerbaru = $stokModel->getRiwayat(10);

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Ringkasan Statistik Chef -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Total Bahan Baku</div>
            <div class="stat-value"><?= number_format($totalBahan, 0) ?></div>
            <div class="stat-subtext">Jenis bahan terdaftar</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Stok Masuk Hari Ini</div>
            <div class="stat-value"><?= number_format($statsStok['total_masuk'] ?? 0, 0) ?></div>
            <div class="stat-subtext">Transaksi penerimaan</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Stok Keluar Hari Ini</div>
            <div class="stat-value"><?= number_format($statsStok['total_keluar'] ?? 0, 0) ?></div>
            <div class="stat-subtext">Transaksi pemakaian/keluar</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Bahan Baku Menipis</div>
            <div class="stat-value" style="<?= $countMenipis > 0 ? 'color: var(--color-danger);' : '' ?>">
                <?= number_format($countMenipis, 0) ?>
            </div>
            <div class="stat-subtext"><?= $countMenipis > 0 ? 'Perlu pengadaan segera' : 'Semua stok aman' ?></div>
        </div>
    </div>
</div>

<!-- Tombol Aksi Cepat Chef -->
<div style="display:flex; gap:12px; margin-bottom: 24px; flex-wrap: wrap;">
    <a href="<?= BASE_URL ?>/pages/chef/stok_masuk.php" class="btn btn-success btn-lg">
        Input Stok Masuk
    </a>
    <a href="<?= BASE_URL ?>/pages/chef/stok_keluar.php" class="btn btn-primary btn-lg">
        Input Stok Keluar
    </a>
    <a href="<?= BASE_URL ?>/pages/chef/riwayat_stok.php" class="btn btn-secondary btn-lg">
        Lihat Riwayat Stok Lengkap
    </a>
</div>

<!-- Peringatan Bahan Baku Menipis -->
<?php if ($countMenipis > 0): ?>
    <div class="card" style="border-left: 4px solid var(--color-danger);">
        <div class="card-header" style="background: #FFF5F5;">
            <div class="card-title" style="color: var(--color-danger);">
                Peringatan Stok Menipis (Di Bawah Batas Minimal)
            </div>
            <span class="badge badge-danger"><?= $countMenipis ?> Bahan Kritis</span>
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
                        <th>Aksi Cepat</th>
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
                            <td><span class="badge badge-danger">Menipis</span></td>
                            <td>
                                <a href="<?= BASE_URL ?>/pages/chef/stok_masuk.php?id_bahan=<?= $b['id_bahan'] ?>" class="btn btn-sm btn-success">
                                    + Tambah Stok
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Riwayat Transaksi Stok Terbaru -->
<div class="card">
    <div class="card-header">
        <div class="card-title">10 Transaksi Stok Terbaru</div>
        <a href="<?= BASE_URL ?>/pages/chef/riwayat_stok.php" class="btn btn-sm btn-outline">Lihat Semua &rarr;</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Jenis</th>
                    <th>Bahan Baku</th>
                    <th>Jumlah</th>
                    <th>Petugas / User</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayatTerbaru)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 20px; color: var(--text-muted);">
                            Belum ada riwayat transaksi stok.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($riwayatTerbaru as $r): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                            <td>
                                <?php if ($r['jenis_stok'] === 'masuk'): ?>
                                    <span class="badge badge-success">Masuk</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Keluar</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($r['nama_bahan']) ?></strong></td>
                            <td style="font-weight:600;">
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
