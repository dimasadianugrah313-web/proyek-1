<?php
// ============================================================================
// EKSPOR LAPORAN PENJUALAN KE PDF (PRINT READY) - LO.LO COFFEE & RESTO
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan (<?= date('d-m-Y', strtotime($tanggal_awal)) ?> sd <?= date('d-m-Y', strtotime($tanggal_akhir)) ?>) - LO.LO Resto</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo.png">
    <link rel="shortcut icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/img/logo.png">
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #222;
            background: #FFF;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2A1810;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            margin: 0 0 4px 0;
            color: #2A1810;
        }
        .header p {
            margin: 2px 0;
            font-size: 9.5pt;
            color: #555;
        }
        .report-meta {
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        th, td {
            border: 1px solid #CCC;
            padding: 7px 9px;
            font-size: 9.5pt;
        }
        th {
            background-color: #F5EFEB;
            color: #2A1810;
            font-weight: bold;
            text-align: left;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total-row {
            background-color: #FAF5F0;
            font-weight: bold;
        }
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            margin-top: 60px;
            border-bottom: 1px solid #333;
        }
        .no-print-bar {
            background: #2A1810;
            color: #FFF;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn-print {
            background: #C86432;
            color: #FFF;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        @media print {
            .no-print-bar { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <div>
        <strong>Dokumen Siap Cetak / Simpan PDF</strong> &mdash; 
        <span>Gunakan menu browser "Save as PDF" / "Simpan sebagai PDF"</span>
    </div>
    <button type="button" class="btn-print" onclick="window.print()">
        Cetak / Simpan PDF
    </button>
</div>

<div class="header">
    <h1>LO.LO COFFEE & RESTO INDRAMAYU</h1>
    <p>Jl. Olahraga No. 45, Indramayu, Jawa Barat | Telepon: (0234) 8765432</p>
    <p style="font-weight: bold; margin-top: 6px; font-size: 11pt;">LAPORAN REKAPITULASI PENJUALAN</p>
</div>

<div class="report-meta">
    <div>
        <strong>Periode:</strong> <?= date('d F Y', strtotime($tanggal_awal)) ?> s/d <?= date('d F Y', strtotime($tanggal_akhir)) ?>
    </div>
    <div>
        <strong>Waktu Cetak:</strong> <?= date('d/m/Y H:i') ?> WIB
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width: 30px;" class="text-center">No</th>
            <th>No. Transaksi</th>
            <th>Tanggal & Waktu</th>
            <th>Kasir</th>
            <th>Rincian Menu (Porsi)</th>
            <th class="text-center">Total Porsi</th>
            <th class="text-right">Total Pendapatan</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($laporanData)): ?>
            <tr>
                <td colspan="7" class="text-center">Tidak ada transaksi penjualan pada periode ini.</td>
            </tr>
        <?php else: ?>
            <?php $no = 1; foreach ($laporanData as $row): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td>#TRX-<?= str_pad($row['id_penjualan'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($row['nama_kasir']) ?></td>
                    <td><?= htmlspecialchars($row['rincian_menu']) ?></td>
                    <td class="text-center"><?= $row['total_porsi'] ?></td>
                    <td class="text-right"><?= format_rupiah($row['total_harga']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="5" class="text-right">TOTAL PENDAPATAN PERIODE:</td>
            <td class="text-center"><?= $ringkasan['total_porsi_terjual'] ?? 0 ?> porsi</td>
            <td class="text-right" style="color: #2E7D32;"><?= format_rupiah($ringkasan['total_pendapatan'] ?? 0) ?></td>
        </tr>
    </tfoot>
</table>

<div class="signatures">
    <div class="signature-box">
        <p>Mengetahui,</p>
        <p style="margin-top: 6px;">Pemilik LO.LO Coffee & Resto</p>
        <div class="signature-line"></div>
        <p style="margin-top: 6px; font-weight: bold;"><?= htmlspecialchars(current_user_name()) ?></p>
    </div>
    <div class="signature-box">
        <p>Indramayu, <?= date('d F Y') ?></p>
        <p style="margin-top: 6px;">Penanggung Jawab Keuangan</p>
        <div class="signature-line"></div>
        <p style="margin-top: 6px; font-weight: bold;">( Kasir / Admin )</p>
    </div>
</div>

</body>
</html>
