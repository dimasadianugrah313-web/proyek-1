<?php
// ============================================================================
// DATA MASTER MENU (PEMILIK) - CRUD
// ============================================================================

$page_title = 'Kelola Menu Resto';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['pemilik']); // Wajib role pemilik

require_once __DIR__ . '/../../classes/Menu.php';
$menuModel = new Menu();

$error = '';
$edit_data = null;

// Mode Edit jika ada parameter ?edit=ID
if (isset($_GET['edit'])) {
    $edit_data = $menuModel->getById((int)$_GET['edit']);
}

// Proses Hapus jika ada parameter ?hapus=ID
if (isset($_GET['hapus'])) {
    try {
        $menuModel->hapus((int)$_GET['hapus']);
        set_flash('success', 'Data berhasil diproses');
        header("Location: " . BASE_URL . "/pages/pemilik/menu.php");
        exit;
    } catch (Exception $e) {
        set_flash('danger', $e->getMessage());
        header("Location: " . BASE_URL . "/pages/pemilik/menu.php");
        exit;
    }
}

// Proses Simpan (Tambah / Ubah)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_menu = isset($_POST['id_menu']) ? (int)$_POST['id_menu'] : 0;
    $nama_menu = trim($_POST['nama_menu'] ?? '');
    $raw_harga = $_POST['harga'] ?? '';
    // Bersihkan titik, koma, spasi agar menjadi angka murni
    $clean_harga = preg_replace('/[^0-9]/', '', $raw_harga);

    // Validasi form tidak boleh kosong
    if ($nama_menu === '' || $clean_harga === '') {
        $error = 'Form data tidak boleh kosong';
    } elseif (!is_numeric($clean_harga) || (float)$clean_harga < 0) {
        $error = 'Harga menu harus berupa nominal angka valid dan tidak boleh minus.';
    } else {
        $harga = (float)$clean_harga;
        if ($id_menu > 0) {
            $menuModel->ubah($id_menu, $nama_menu, $harga);
        } else {
            $menuModel->tambah($nama_menu, $harga);
        }
        set_flash('success', 'Data berhasil diproses');
        header("Location: " . BASE_URL . "/pages/pemilik/menu.php");
        exit;
    }
}

$daftarMenu = $menuModel->getAllWithResepInfo();

// Format harga awal untuk form jika sedang edit
$harga_input_val = '';
if ($edit_data) {
    $harga_input_val = number_format($edit_data['harga'], 0, ',', '.');
} elseif (isset($_POST['harga'])) {
    $harga_input_val = htmlspecialchars($_POST['harga']);
}

require_once __DIR__ . '/../../includes/header.php';
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- FORM TAMBAH / UBAH MENU -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <?= $edit_data ? 'Ubah Menu' : 'Tambah Menu Baru' ?>
            </div>
            <?php if ($edit_data): ?>
                <a href="<?= BASE_URL ?>/pages/pemilik/menu.php" class="btn btn-sm btn-secondary">Batal</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id_menu" value="<?= $edit_data['id_menu'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nama_menu" class="form-label">
                        Nama Menu <span class="required">*</span>
                    </label>
                    <input type="text" id="nama_menu" name="nama_menu" class="form-control" 
                           placeholder="Contoh: Kopi Tubruk LO.LO" required
                           value="<?= htmlspecialchars($edit_data['nama_menu'] ?? ($_POST['nama_menu'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label for="harga" class="form-label">
                        Harga Jual (Rp) <span class="required">*</span>
                    </label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <span style="position: absolute; left: 12px; font-weight: 600; color: var(--cafe-mocha);">Rp</span>
                        <input type="text" inputmode="numeric" id="harga" name="harga" class="form-control" 
                               placeholder="Contoh: 25.000" required style="padding-left: 38px;"
                               value="<?= $harga_input_val ?>" oninput="formatRupiahInput(this)">
                    </div>
                    <div class="form-hint">Ketik nominal harga, titik pemisah ribuan otomatis terpasang (contoh: 25.000).</div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <?= $edit_data ? 'Perbarui Menu' : 'Simpan Menu Baru' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DAFTAR MENU -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Master Data Menu Makanan & Minuman (<?= count($daftarMenu) ?> Menu)</div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Menu</th>
                        <th>Harga Jual</th>
                        <th>Komposisi Resep (BOM)</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarMenu)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 24px; color: var(--text-muted);">
                                Belum ada data menu.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($daftarMenu as $m): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($m['nama_menu']) ?></strong></td>
                                <td style="font-weight: 700; color: var(--cafe-accent);">
                                    <?= format_rupiah($m['harga']) ?>
                                </td>
                                <td>
                                    <?php if ($m['jumlah_bahan'] > 0): ?>
                                        <a href="<?= BASE_URL ?>/pages/pemilik/resep.php?id_menu=<?= $m['id_menu'] ?>" class="badge badge-success" style="text-decoration:none;">
                                            <?= $m['jumlah_bahan'] ?> Bahan Baku
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>/pages/pemilik/resep.php?id_menu=<?= $m['id_menu'] ?>" class="badge badge-warning" style="text-decoration:none;">
                                            Belum Ada Resep
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a href="<?= BASE_URL ?>/pages/pemilik/resep.php?id_menu=<?= $m['id_menu'] ?>" class="btn btn-sm btn-outline">
                                        Atur Resep
                                    </a>
                                    <a href="<?= BASE_URL ?>/pages/pemilik/menu.php?edit=<?= $m['id_menu'] ?>" class="btn btn-sm btn-secondary">
                                        Ubah
                                    </a>
                                    <a href="<?= BASE_URL ?>/pages/pemilik/menu.php?hapus=<?= $m['id_menu'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus menu <?= addslashes(htmlspecialchars($m['nama_menu'])) ?>?');">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function formatRupiahInput(input) {
    let clean = input.value.replace(/[^0-9]/g, '');
    if (clean) {
        input.value = new Intl.NumberFormat('id-ID').format(clean);
    } else {
        input.value = '';
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
