<?php
// ============================================================================
// INPUT STOK KELUAR - CHEF
// ============================================================================

$page_title = 'Input Stok Keluar';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['chef']); // Wajib role chef

require_once __DIR__ . '/../../classes/BahanBaku.php';
require_once __DIR__ . '/../../classes/TransaksiStok.php';

$bahanModel = new BahanBaku();
$stokModel = new TransaksiStok();
$daftarBahan = $bahanModel->getAll();

$selected_id = isset($_GET['id_bahan']) ? (int)$_GET['id_bahan'] : 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bahan = (int)($_POST['id_bahan'] ?? 0);
    $jumlah_raw = $_POST['jumlah_stok'] ?? '';

    // Validasi form tidak boleh kosong
    if (empty($id_bahan) || $jumlah_raw === '') {
        $error = 'Form data tidak boleh kosong';
    } elseif (!is_numeric($jumlah_raw) || (float)$jumlah_raw <= 0) {
        $error = 'Jumlah stok tidak boleh kosong, nol, atau minus.';
    } else {
        $result = $stokModel->stokKeluar($id_bahan, (float)$jumlah_raw, current_user_id());
        if ($result['success']) {
            $flash_type = !empty($result['stok_menipis']) ? 'warning' : 'success';
            set_flash($flash_type, $result['message']);
            header("Location: " . BASE_URL . "/pages/chef/stok_keluar.php?id_bahan=" . $id_bahan);
            exit;
        } else {
            // Tampilkan error (termasuk "Stok bahan baku tidak mencukupi")
            $error = $result['message'];
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="max-width: 720px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Form Pemakaian / Stok Keluar Bahan Baku</div>
            <a href="<?= BASE_URL ?>/pages/chef/dashboard.php" class="btn btn-sm btn-secondary">&larr; Kembali</a>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="formStokKeluar">
                <div class="form-group">
                    <label for="selectBahanKeluar" class="form-label">
                        Pilih Bahan Baku <span class="required">*</span>
                    </label>
                    <select name="id_bahan" id="selectBahanKeluar" class="form-control" required onchange="updateBahanInfoKeluar()">
                        <option value="">-- Pilih Bahan Baku --</option>
                        <?php foreach ($daftarBahan as $b): ?>
                            <option value="<?= $b['id_bahan'] ?>" 
                                    data-satuan="<?= htmlspecialchars($b['satuan']) ?>"
                                    data-stok="<?= $b['stok_bahan'] ?>"
                                    data-minimal="<?= $b['batas_minimal'] ?>"
                                    <?= ($selected_id === (int)$b['id_bahan']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['nama_bahan']) ?> (Tersedia: <?= format_angka($b['stok_bahan']) ?> <?= htmlspecialchars($b['satuan']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Box Status Stok Bahan Terpilih -->
                <div id="infoStokBoxKeluar" style="display:none; background: #FAF8F5; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 12px 16px; margin-bottom: 16px;">
                    <div style="font-size: 13px; color: var(--text-muted);">Informasi Ketersediaan Bahan:</div>
                    <div style="display:flex; gap: 20px; margin-top: 6px; flex-wrap: wrap;">
                        <div>Stok Saat Ini: <strong id="lblStokKeluar" style="color:var(--cafe-mocha);">-</strong></div>
                        <div>Batas Minimal: <strong id="lblBatasKeluar">-</strong></div>
                        <div>Satuan: <strong id="lblSatuanKeluar">-</strong></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputJumlahKeluar" class="form-label">
                        Jumlah Pemakaian / Stok Keluar <span class="required">*</span>
                    </label>
                    <div style="display:flex; align-items:center; gap: 10px;">
                        <input type="number" step="0.01" min="0.01" name="jumlah_stok" id="inputJumlahKeluar" 
                               class="form-control" placeholder="Contoh: 150" required>
                        <span id="txtSatuanInputKeluar" style="font-weight:600; color:var(--cafe-mocha); min-width: 60px;">-</span>
                    </div>
                    <div class="form-hint">Jumlah tidak boleh melebihi stok yang tersedia di sistem saat ini.</div>
                </div>

                <div style="margin-top: 24px; display:flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                        Simpan Stok Keluar
                    </button>
                    <a href="<?= BASE_URL ?>/pages/chef/dashboard.php" class="btn btn-secondary btn-lg">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateBahanInfoKeluar() {
    const sel = document.getElementById('selectBahanKeluar');
    const opt = sel.options[sel.selectedIndex];
    const box = document.getElementById('infoStokBoxKeluar');
    const txtSatuan = document.getElementById('txtSatuanInputKeluar');

    if (sel.value && opt) {
        box.style.display = 'block';
        document.getElementById('lblStokKeluar').textContent = parseFloat(opt.getAttribute('data-stok')).toLocaleString('id-ID') + ' ' + opt.getAttribute('data-satuan');
        document.getElementById('lblBatasKeluar').textContent = parseFloat(opt.getAttribute('data-minimal')).toLocaleString('id-ID') + ' ' + opt.getAttribute('data-satuan');
        document.getElementById('lblSatuanKeluar').textContent = opt.getAttribute('data-satuan');
        txtSatuan.textContent = opt.getAttribute('data-satuan');
    } else {
        box.style.display = 'none';
        txtSatuan.textContent = '-';
    }
}
document.addEventListener('DOMContentLoaded', updateBahanInfoKeluar);
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
