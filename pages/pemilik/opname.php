<?php
// ============================================================================
// STOCK OPNAME (PEMILIK) - PENYESUAIAN STOK FISIK VS SISTEM
// ============================================================================

$page_title = 'Stock Opname';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['pemilik']); // Wajib role pemilik

require_once __DIR__ . '/../../classes/BahanBaku.php';
require_once __DIR__ . '/../../classes/Opname.php';

$bahanModel = new BahanBaku();
$opnameModel = new Opname();
$daftarBahan = $bahanModel->getAll();

$selected_id = isset($_GET['id_bahan']) ? (int)$_GET['id_bahan'] : 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bahan = (int)($_POST['id_bahan'] ?? 0);
    $stok_fisik_raw = $_POST['stok_fisik'] ?? '';

    // Validasi form tidak boleh kosong
    if (empty($id_bahan) || $stok_fisik_raw === '') {
        $error = 'Form data tidak boleh kosong';
    } elseif (!is_numeric($stok_fisik_raw) || (float)$stok_fisik_raw < 0) {
        $error = 'Stok fisik harus berupa angka valid non-negatif.';
    } else {
        $hasil = $opnameModel->sinkronisasiStok($id_bahan, (float)$stok_fisik_raw, current_user_id());
        if ($hasil['success']) {
            set_flash('success', $hasil['message']);
            header("Location: " . BASE_URL . "/pages/pemilik/opname.php");
            exit;
        } else {
            $error = $hasil['message'];
        }
    }
}

$riwayatOpname = $opnameModel->getRiwayat(50);

require_once __DIR__ . '/../../includes/header.php';
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <span>✕</span>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">
    <!-- FORM INPUT STOCK OPNAME -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Input Hasil Hitung Fisik</div>
        </div>
        <div class="card-body">
            <form method="POST" action="" id="formOpname">
                <div class="form-group">
                    <label for="selectOpnameBahan" class="form-label">
                        Pilih Bahan Baku <span class="required">*</span>
                    </label>
                    <select name="id_bahan" id="selectOpnameBahan" class="form-control" required onchange="calcOpnameDiff()">
                        <option value="">-- Pilih Bahan Baku --</option>
                        <?php foreach ($daftarBahan as $b): ?>
                            <option value="<?= $b['id_bahan'] ?>" 
                                    data-satuan="<?= htmlspecialchars($b['satuan']) ?>"
                                    data-stok="<?= $b['stok_bahan'] ?>"
                                    <?= ($selected_id === (int)$b['id_bahan']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['nama_bahan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Stok di Sistem Saat Ini</label>
                    <div style="background: #F5EFEB; padding: 10px 14px; border-radius: var(--radius-sm); font-size: 15px; font-weight: 700; color: var(--cafe-mocha);">
                        <span id="txtStokSistem">0</span> <span id="txtSatuanSistem">-</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputStokFisik" class="form-label">
                        Stok Fisik Nyata di Gudang/Dapur <span class="required">*</span>
                    </label>
                    <div style="display:flex; align-items:center; gap: 10px;">
                        <input type="number" step="0.01" min="0" id="inputStokFisik" name="stok_fisik" 
                               class="form-control" placeholder="Contoh: 2850" required oninput="calcOpnameDiff()">
                        <span id="txtSatuanInput" style="font-weight:600; color:var(--cafe-mocha); min-width: 50px;">-</span>
                    </div>
                </div>

                <!-- Box Preview Selisih -->
                <div id="diffPreviewBox" style="background: #FFF; border: 1px dashed var(--border-color); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 20px;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">
                        Rumus: Selisih = Stok Fisik - Stok Sistem
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-weight: 600;">Status Selisih:</span>
                        <span id="lblStatusSelisih" class="badge badge-secondary">-</span>
                    </div>
                    <div style="font-size: 16px; font-weight: 700; margin-top: 6px; text-align: right;" id="lblNilaiSelisih">
                        0
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    Simpan Penyesuaian Opname
                </button>
            </form>
        </div>
    </div>

    <!-- TABEL RIWAYAT OPNAME -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Riwayat Audit Stock Opname Terakhir</div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Bahan Baku</th>
                        <th>Stok Sistem</th>
                        <th>Stok Fisik</th>
                        <th>Selisih</th>
                        <th>Status</th>
                        <th>Auditor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($riwayatOpname)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 24px; color: var(--text-muted);">
                                Belum ada riwayat stock opname.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($riwayatOpname as $o): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($o['tanggal'])) ?></td>
                                <td><strong><?= htmlspecialchars($o['nama_bahan']) ?></strong></td>
                                <td><?= format_angka($o['stok_sistem']) ?> <?= htmlspecialchars($o['satuan']) ?></td>
                                <td><?= format_angka($o['stok_fisik']) ?> <?= htmlspecialchars($o['satuan']) ?></td>
                                <td style="font-weight:700; color: <?= (float)$o['selisih'] == 0 ? 'var(--color-success)' : ((float)$o['selisih'] < 0 ? 'var(--color-danger)' : 'var(--color-info)') ?>;">
                                    <?= ((float)$o['selisih'] > 0 ? '+' : '') . format_angka($o['selisih']) ?> <?= htmlspecialchars($o['satuan']) ?>
                                </td>
                                <td>
                                    <?php if ((float)$o['selisih'] == 0): ?>
                                        <span class="badge badge-success">Sesuai</span>
                                    <?php elseif ((float)$o['selisih'] < 0): ?>
                                        <span class="badge badge-warning">Selisih Kurang</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Selisih Lebih</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($o['nama_user']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function calcOpnameDiff() {
    const sel = document.getElementById('selectOpnameBahan');
    const opt = sel.options[sel.selectedIndex];
    const fisikInput = document.getElementById('inputStokFisik');
    const txtStok = document.getElementById('txtStokSistem');
    const txtSatuanS = document.getElementById('txtSatuanSistem');
    const txtSatuanI = document.getElementById('txtSatuanInput');
    const lblStatus = document.getElementById('lblStatusSelisih');
    const lblNilai = document.getElementById('lblNilaiSelisih');

    if (!sel.value || !opt) {
        txtStok.textContent = '0';
        txtSatuanS.textContent = '-';
        txtSatuanI.textContent = '-';
        lblStatus.textContent = '-';
        lblStatus.className = 'badge badge-secondary';
        lblNilai.textContent = '0';
        return;
    }

    const sistemStok = parseFloat(opt.getAttribute('data-stok')) || 0;
    const satuan = opt.getAttribute('data-satuan');

    txtStok.textContent = sistemStok.toLocaleString('id-ID');
    txtSatuanS.textContent = satuan;
    txtSatuanI.textContent = satuan;

    if (fisikInput.value !== '') {
        const fisikVal = parseFloat(fisikInput.value) || 0;
        const selisih = fisikVal - sistemStok;

        lblNilai.textContent = (selisih > 0 ? '+' : '') + selisih.toLocaleString('id-ID') + ' ' + satuan;

        if (selisih === 0) {
            lblStatus.textContent = 'Sesuai (0)';
            lblStatus.className = 'badge badge-success';
        } else if (selisih < 0) {
            lblStatus.textContent = 'Selisih Kurang (Fisik < Sistem)';
            lblStatus.className = 'badge badge-warning';
        } else {
            lblStatus.textContent = 'Selisih Lebih (Fisik > Sistem)';
            lblStatus.className = 'badge badge-info';
        }
    }
}
document.addEventListener('DOMContentLoaded', calcOpnameDiff);
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
