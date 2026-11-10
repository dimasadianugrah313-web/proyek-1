<?php
// ============================================================================
// EKSPOR LAPORAN PENJUALAN KE EXCEL (.XLS) - LO.LO COFFEE & RESTO
// ============================================================================

require_once __DIR__ . '/../../includes/auth.php';
check_auth(['pemilik']); // Wajib role pemilik

require_once __DIR__ . '/../../classes/Penjualan.php';
$penjualanModel = new Penjualan();

$tanggal_awal = isset($_GET['awal']) ? trim($_GET['awal']) : date('Y-m-01');
$tanggal_akhir = isset($_GET['akhir']) ? trim($_GET['akhir']) : date('Y-m-d');

if ($tanggal_awal > $tanggal_akhir) {
    die("Periode tidak valid");
}

$laporanData = $penjualanModel->getLaporan($tanggal_awal, $tanggal_akhir);
$ringkasan = $penjualanModel->getRingkasanLaporan($tanggal_awal, $tanggal_akhir);

$filename = "Laporan_Penjualan_LOLO_" . $tanggal_awal . "_sd_" . $tanggal_akhir . ".xls";

// Set Header Download Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Output format HTML Spreadsheet yang kompatibel dengan Microsoft Excel & LibreOffice
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Penjualan</title>
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
        th { background-color: #3E2723; color: #FFFFFF; border: 1px solid #796658; padding: 8px; text-align: left; }
        td { border: 1px solid #D7CCC8; padding: 6px; }
        .title { font-size: 16pt; font-weight: bold; color: #3E2723; }
        .subtitle { font-size: 11pt; color: #555; }
        .currency { text-align: right; mso-number-format:"\#\,\#\#0"; }
        .number { text-align: center; }
        .total-row { background-color: #F5EFEB; font-weight: bold; }
    </style>
</head>
<body>

<table>
    <tr>
        <td colspan="7" class="title">LO.LO COFFEE & RESTO INDRAMAYU</td>
    </tr>
    <tr>
        <td colspan="7" class="subtitle">LAPORAN REKAPITULASI PENJUALAN</td>
    </tr>
    <tr>
        <td colspan="7">Periode: <?= date('d/m/Y', strtotime($tanggal_awal)) ?> s/d <?= date('d/m/Y', strtotime($tanggal_akhir)) ?></td>
    </tr>
    <tr>
        <td colspan="7">Dicetak pada: <?= date('d/m/Y H:i:s') ?> oleh <?= htmlspecialchars(current_user_name()) ?></td>
    </tr>
    <tr><td colspan="7"></td></tr>
</table>

<table>
    <thead>
        <tr>
            <th style="width: 40px;">No</th>
            <th>No. Transaksi</th>
            <th>Tanggal & Waktu</th>
            <th>Nama Kasir</th>
            <th>Rincian Menu & Porsi</th>
            <th>Total Porsi</th>
            <th>Total Penjualan (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($laporanData)): ?>
            <tr>
                <td colspan="7" style="text-align: center;">Tidak ada transaksi penjualan pada periode ini.</td>
            </tr>
        <?php else: ?>
            <?php $no = 1; foreach ($laporanData as $row): ?>
                <tr>
                    <td class="number"><?= $no++ ?></td>
                    <td>#TRX-<?= str_pad($row['id_penjualan'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($row['nama_kasir']) ?></td>
                    <td><?= htmlspecialchars($row['rincian_menu']) ?></td>
                    <td class="number"><?= $row['total_porsi'] ?></td>
                    <td class="currency"><?= (float)$row['total_harga'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="5" style="text-align: right;">TOTAL KESELURUHAN:</td>
            <td class="number"><?= $ringkasan['total_porsi_terjual'] ?? 0 ?></td>
            <td class="currency"><?= (float)($ringkasan['total_pendapatan'] ?? 0) ?></td>
        </tr>
    </tfoot>
</table>

</body>
</html>
