<?php
// ============================================================================
// STRUK / NOTA PENJUALAN - LO.LO COFFEE & RESTO INDRAMAYU
// ============================================================================

require_once __DIR__ . '/../../includes/auth.php';
check_auth(['kasir', 'pemilik']); // Kasir dan Pemilik berhak mencetak nota

require_once __DIR__ . '/../../classes/Penjualan.php';

$id_penjualan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$penjualanModel = new Penjualan();

$penjualan = $penjualanModel->getById($id_penjualan);
if (!$penjualan) {
    die("Transaksi tidak ditemukan.");
}

$detail = $penjualanModel->getDetail($id_penjualan);
$is_new = isset($_GET['new']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Struk #TRX-<?= str_pad($penjualan['id_penjualan'], 5, '0', STR_PAD_LEFT) ?> - LO.LO Coffee & Resto</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .receipt-card {
            max-width: 440px;
            margin: 30px auto;
            background: #FFF;
            padding: 24px;
            border-radius: var(--radius-md);
            border: 1px dashed #C2B4A8;
            box-shadow: var(--shadow-md);
            font-family: 'Courier New', Courier, monospace;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #796658;
            padding-bottom: 14px;
            margin-bottom: 14px;
        }
        .receipt-header h2 {
            font-size: 18px;
            margin-bottom: 4px;
            color: #2A1810;
        }
        .receipt-header p {
            font-size: 12px;
            color: #555;
            margin: 2px 0;
        }
        .receipt-table {
            width: 100%;
            font-size: 12.5px;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .receipt-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .receipt-divider {
            border-top: 1px dashed #796658;
            margin: 10px 0;
        }
        .receipt-footer {
            text-align: center;
            font-size: 11.5px;
            color: #555;
            margin-top: 18px;
            border-top: 1px dashed #796658;
            padding-top: 12px;
        }
        @media print {
            body { background: #FFF !important; }
            .receipt-card { box-shadow: none !important; border: none !important; margin: 0 auto !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body style="background: var(--bg-body);">

<div style="max-width: 440px; margin: 20px auto 0;" class="no-print">
    <?php if ($is_new): ?>
        <div class="alert alert-success" style="text-align: left; margin-bottom: 12px;">
            <strong>Transaksi Berhasil!</strong> Stok bahan baku telah terpotong secara otomatis.
        </div>
    <?php endif; ?>
    <div style="display:flex; justify-content:space-between; gap:10px; margin-bottom: 10px;">
        <a href="<?= BASE_URL ?>/pages/kasir/riwayat_penjualan.php" class="btn btn-secondary" style="flex:1; justify-content:center;">
            &larr; Riwayat Penjualan
        </a>
        <?php if (current_user_role() === 'kasir'): ?>
            <a href="<?= BASE_URL ?>/pages/kasir/penjualan.php" class="btn btn-secondary" style="flex:1; justify-content:center;">
                + Transaksi Baru
            </a>
        <?php endif; ?>
    </div>
    <button type="button" onclick="window.print()" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 15px; padding: 10px;">
        🖨️ Cetak Struk
    </button>
</div>

<div class="receipt-card">
    <div class="receipt-header">
        <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Logo LO.LO" style="width: 60px; height: 60px; object-fit: contain; margin-bottom: 6px;">
        <h2>LO.LO COFFEE & RESTO</h2>
        <p>Jl. Olahraga No. 45, Indramayu</p>
        <p>Telp: (0234) 8765432</p>
    </div>

    <table style="width:100%; font-size:11.5px; margin-bottom: 10px;">
        <tr>
            <td>No: #TRX-<?= str_pad($penjualan['id_penjualan'], 5, '0', STR_PAD_LEFT) ?></td>
            <td style="text-align:right;"><?= date('d/m/Y H:i', strtotime($penjualan['tanggal'])) ?></td>
        </tr>
        <tr>
            <td>Kasir: <?= htmlspecialchars($penjualan['nama_kasir']) ?></td>
            <td style="text-align:right;">Status: LUNAS</td>
        </tr>
    </table>

    <div class="receipt-divider"></div>

    <table class="receipt-table">
        <?php foreach ($detail as $d): ?>
            <tr>
                <td colspan="2"><strong><?= htmlspecialchars($d['nama_menu']) ?></strong></td>
            </tr>
            <tr>
                <td style="padding-left: 10px; color:#555;">
                    <?= $d['porsi'] ?> x @ Rp <?= number_format($d['harga'], 0, ',', '.') ?>
                </td>
                <td style="text-align:right; font-weight:bold;">
                    Rp <?= number_format($d['subtotal'], 0, ',', '.') ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="receipt-divider"></div>

    <table style="width:100%; font-size:14px; font-weight:bold;">
        <tr>
            <td>TOTAL</td>
            <td style="text-align:right;">Rp <?= number_format($penjualan['total_harga'], 0, ',', '.') ?></td>
        </tr>
    </table>

    <div class="receipt-footer">
        <p>Terima kasih atas kunjungan Anda!</p>
        <p>Nikmati Kopi & Sajian Khas LO.LO Resto</p>
    </div>
</div>

<div style="max-width: 440px; margin: 15px auto 30px; text-align: center;" class="no-print">
    <button type="button" onclick="window.print()" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 15px; padding: 10px;">
        🖨️ Cetak Struk
    </button>
</div>

</body>
</html>
