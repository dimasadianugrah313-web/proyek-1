<?php
// ============================================================================
// LAPORAN REKAPITULASI PENJUALAN (PEMILIK)
// ============================================================================

$page_title = 'Rekapitulasi Penjualan';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['pemilik']); // Wajib role pemilik

require_once __DIR__ . '/../../classes/Penjualan.php';
$penjualanModel = new Penjualan();

// Default filter: 30 hari terakhir sampai hari ini
$default_awal = date('Y-m-01');
$default_akhir = date('Y-m-d');

$tanggal_awal = isset($_GET['tanggal_awal']) ? trim($_GET['tanggal_awal']) : $default_awal;
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? trim($_GET['tanggal_akhir']) : $default_akhir;

$error_periode = '';
$laporanData = [];
$ringkasan = ['total_transaksi' => 0, 'total_pendapatan' => 0, 'total_porsi_terjual' => 0];
$trenHarian = [];

// Validasi periode: tanggal_awal <= tanggal_akhir
if ($tanggal_awal > $tanggal_akhir) {
    $error_periode = 'Periode tidak valid';
} else {
    $laporanData = $penjualanModel->getLaporan($tanggal_awal, $tanggal_akhir);
    $ringkasan = $penjualanModel->getRingkasanLaporan($tanggal_awal, $tanggal_akhir);
    $trenHarian = $penjualanModel->getGrafikTrenHarian($tanggal_awal, $tanggal_akhir);
}

// Format data untuk canvas grafik native
$chartLabels = [];
$chartValues = [];
foreach ($trenHarian as $t) {
    $chartLabels[] = date('d/m', strtotime($t['tanggal_hari']));
    $chartValues[] = (float)$t['total_omset'];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- KOP CETAK RESMI (HANYA MUNCUL SAAT CETAK / PRINT) -->
<div class="print-letterhead">
    <h2>LO.LO COFFEE & RESTO INDRAMAYU</h2>
    <p>Jl. Olahraga No. 45, Indramayu, Jawa Barat | Telp: (0234) 8765432</p>
    <p style="font-weight: bold; margin-top: 6px;">
        LAPORAN REKAPITULASI PENJUALAN
    </p>
    <p>Periode: <?= date('d/m/Y', strtotime($tanggal_awal)) ?> s/d <?= date('d/m/Y', strtotime($tanggal_akhir)) ?></p>
</div>

<!-- FORM FILTER PERIODE & TOMBOL EKSPOR -->
<div class="card no-print">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="" class="filter-form" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:16px;">
            <div style="flex:1; min-width: 180px;">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control" 
                       value="<?= htmlspecialchars($tanggal_awal) ?>" required>
            </div>

            <div style="flex:1; min-width: 180px;">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control" 
                       value="<?= htmlspecialchars($tanggal_akhir) ?>" required>
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary">
                    Tampilkan Filter
                </button>
                <a href="<?= BASE_URL ?>/pages/pemilik/laporan.php" class="btn btn-secondary">
                    Reset
                </a>
            </div>

            <div style="margin-left:auto; display:flex; gap:8px; flex-wrap:wrap;">
                <button type="button" onclick="window.print()" class="btn btn-success">
                    Cetak (Print)
                </button>
                <a href="<?= BASE_URL ?>/pages/pemilik/export_excel.php?awal=<?= urlencode($tanggal_awal) ?>&akhir=<?= urlencode($tanggal_akhir) ?>" 
                   class="btn btn-outline">
                    Ekspor Excel
                </a>
                <a href="<?= BASE_URL ?>/pages/pemilik/export_pdf.php?awal=<?= urlencode($tanggal_awal) ?>&akhir=<?= urlencode($tanggal_akhir) ?>" 
                   target="_blank" class="btn btn-outline">
                    Ekspor PDF
                </a>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($error_periode)): ?>
    <div class="alert alert-danger">
        <span><?= htmlspecialchars($error_periode) ?>: Tanggal awal tidak boleh lebih besar dari tanggal akhir.</span>
    </div>
<?php else: ?>

    <!-- STATS RINGKASAN PERIODE -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-value" style="color: var(--color-success);">
                    <?= format_rupiah($ringkasan['total_pendapatan'] ?? 0) ?>
                </div>
                <div class="stat-subtext">Omset selama periode ini</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Jumlah Transaksi</div>
                <div class="stat-value"><?= number_format($ringkasan['total_transaksi'] ?? 0, 0) ?></div>
                <div class="stat-subtext">Total nota penjualan kasir</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Total Porsi Terjual</div>
                <div class="stat-value"><?= number_format($ringkasan['total_porsi_terjual'] ?? 0, 0) ?></div>
                <div class="stat-subtext">Akumulasi seluruh menu</div>
            </div>
        </div>
    </div>

    <!-- GRAFIK TREN PENJUALAN NATIVE CANVAS (NO LIBRARY) -->
    <div class="card no-print">
        <div class="card-header">
            <div class="card-title">Grafik Tren Pendapatan Penjualan Harian</div>
            <span style="font-size:12px; color:var(--text-muted);">
                Periode: <?= date('d/m/Y', strtotime($tanggal_awal)) ?> - <?= date('d/m/Y', strtotime($tanggal_akhir)) ?>
            </span>
        </div>
        <div class="card-body">
            <div class="chart-canvas-wrapper">
                <canvas id="salesChartCanvas"></canvas>
            </div>
        </div>
    </div>

    <!-- TABEL REKAP DETAIL PENJUALAN -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                Rincian Transaksi Penjualan (<?= count($laporanData) ?> Transaksi)
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. Transaksi</th>
                        <th>Tanggal & Waktu</th>
                        <th>Kasir</th>
                        <th>Rincian Menu & Porsi</th>
                        <th>Total Porsi</th>
                        <th style="text-align:right;">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporanData)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 28px; color: var(--text-muted);">
                                Tidak ada transaksi penjualan pada periode yang dipilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($laporanData as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong>#TRX-<?= str_pad($row['id_penjualan'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($row['nama_kasir']) ?></td>
                                <td style="max-width: 320px;">
                                    <small><?= htmlspecialchars($row['rincian_menu']) ?></small>
                                </td>
                                <td><span class="badge badge-secondary"><?= $row['total_porsi'] ?> porsi</span></td>
                                <td style="text-align:right; font-weight:700; color:var(--cafe-accent);">
                                    <?= format_rupiah($row['total_harga']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#F5EFEB; font-weight:bold;">
                        <td colspan="5" style="text-align:right;">TOTAL KESELURUHAN:</td>
                        <td><?= number_format($ringkasan['total_porsi_terjual'] ?? 0, 0) ?> porsi</td>
                        <td style="text-align:right; color:var(--color-success); font-size:15px;">
                            <?= format_rupiah($ringkasan['total_pendapatan'] ?? 0) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Script Native Chart -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = <?= json_encode($chartLabels) ?>;
        const dataValues = <?= json_encode($chartValues) ?>;
        renderSalesBarChart('salesChartCanvas', labels, dataValues, 'Pendapatan Harian');
    });
    </script>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
