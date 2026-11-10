<?php
// ============================================================================
// DATA MASTER BAHAN BAKU (PEMILIK) - CRUD
// ============================================================================

$page_title = 'Kelola Bahan Baku';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['pemilik']); // Wajib role pemilik

require_once __DIR__ . '/../../classes/BahanBaku.php';
$bahanModel = new BahanBaku();

$error = '';
$edit_data = null;

// Mode Edit jika ada parameter ?edit=ID
if (isset($_GET['edit'])) {
    $edit_data = $bahanModel->getById((int)$_GET['edit']);
}

// Proses Hapus jika ada parameter ?hapus=ID
if (isset($_GET['hapus'])) {
    try {
        $bahanModel->hapus((int)$_GET['hapus']);
        set_flash('success', 'Data berhasil diproses');
        header("Location: " . BASE_URL . "/pages/pemilik/bahan.php");
        exit;
    } catch (Exception $e) {
        set_flash('danger', $e->getMessage());
        header("Location: " . BASE_URL . "/pages/pemilik/bahan.php");
        exit;
    }
}

// Proses Simpan (Tambah / Ubah)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bahan = isset($_POST['id_bahan']) ? (int)$_POST['id_bahan'] : 0;
    $nama_bahan = trim($_POST['nama_bahan'] ?? '');
    $satuan = trim($_POST['satuan'] ?? '');
    $stok_bahan = $_POST['stok_bahan'] ?? '';
    $batas_minimal = $_POST['batas_minimal'] ?? '';

    // Validasi form tidak boleh kosong
    if ($nama_bahan === '' || $satuan === '' || $stok_bahan === '' || $batas_minimal === '') {
        $error = 'Form data tidak boleh kosong';
    } elseif (!is_numeric($stok_bahan) || !is_numeric($batas_minimal) || (float)$stok_bahan < 0 || (float)$batas_minimal < 0) {
        $error = 'Stok dan batas minimal harus berupa angka valid non-negatif.';
    } else {
        if ($id_bahan > 0) {
            // Update
            $bahanModel->ubah($id_bahan, $nama_bahan, $satuan, (float)$stok_bahan, (float)$batas_minimal);
        } else {
            // Insert
            $bahanModel->tambah($nama_bahan, $satuan, (float)$stok_bahan, (float)$batas_minimal);
        }
        set_flash('success', 'Data berhasil diproses');
        header("Location: " . BASE_URL . "/pages/pemilik/bahan.php");
        exit;
    }
}

$daftarBahan = $bahanModel->getAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- FORM TAMBAH / UBAH BAHAN -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <?= $edit_data ? 'Ubah Bahan Baku' : 'Tambah Bahan Baku' ?>
            </div>
            <?php if ($edit_data): ?>
                <a href="<?= BASE_URL ?>/pages/pemilik/bahan.php" class="btn btn-sm btn-secondary">Batal</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id_bahan" value="<?= $edit_data['id_bahan'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nama_bahan" class="form-label">
                        Nama Bahan Baku <span class="required">*</span>
                    </label>
                    <input type="text" id="nama_bahan" name="nama_bahan" class="form-control" 
                           placeholder="Contoh: Kopi Arabika, Susu Segar" required
                           value="<?= htmlspecialchars($edit_data['nama_bahan'] ?? ($_POST['nama_bahan'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label for="satuan" class="form-label">
                        Satuan Ukur <span class="required">*</span>
                    </label>
                    <input type="text" id="satuan" name="satuan" class="form-control" 
                           placeholder="Contoh: gram, ml, pcs, lembar" required
                           value="<?= htmlspecialchars($edit_data['satuan'] ?? ($_POST['satuan'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label for="stok_bahan" class="form-label">
                        Stok Saat Ini <span class="required">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" id="stok_bahan" name="stok_bahan" class="form-control" 
                           placeholder="Contoh: 1000" required
                           value="<?= htmlspecialchars($edit_data['stok_bahan'] ?? ($_POST['stok_bahan'] ?? '0')) ?>">
                </div>

                <div class="form-group">
                    <label for="batas_minimal" class="form-label">
                        Batas Minimal Stok <span class="required">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" id="batas_minimal" name="batas_minimal" class="form-control" 
                           placeholder="Contoh: 200" required
                           value="<?= htmlspecialchars($edit_data['batas_minimal'] ?? ($_POST['batas_minimal'] ?? '100')) ?>">
                    <div class="form-hint">Sistem akan memunculkan peringatan jika stok mencapai atau di bawah angka ini.</div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <?= $edit_data ? 'Perbarui Bahan Baku' : 'Simpan Bahan Baku' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DAFTAR BAHAN BAKU -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Master Data Bahan Baku (<?= count($daftarBahan) ?> Bahan)</div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Bahan</th>
                        <th>Satuan</th>
                        <th>Stok Saat Ini</th>
                        <th>Batas Minimal</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarBahan)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 24px; color: var(--text-muted);">
                                Belum ada data bahan baku.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($daftarBahan as $b): ?>
                            <?php $isMenipis = ((float)$b['stok_bahan'] <= (float)$b['batas_minimal']); ?>
                            <tr class="<?= $isMenipis ? 'row-danger' : '' ?>">
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($b['nama_bahan']) ?></strong></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($b['satuan']) ?></span></td>
                                <td style="font-weight: 700; color: <?= $isMenipis ? 'var(--color-danger)' : 'var(--text-primary)' ?>;">
                                    <?= format_angka($b['stok_bahan']) ?>
                                </td>
                                <td><?= format_angka($b['batas_minimal']) ?></td>
                                <td>
                                    <?php if ($isMenipis): ?>
                                        <span class="badge badge-danger">Menipis</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Aman</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a href="<?= BASE_URL ?>/pages/pemilik/bahan.php?edit=<?= $b['id_bahan'] ?>" class="btn btn-sm btn-secondary">
                                        Ubah
                                    </a>
                                    <a href="<?= BASE_URL ?>/pages/pemilik/bahan.php?hapus=<?= $b['id_bahan'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus bahan baku <?= addslashes(htmlspecialchars($b['nama_bahan'])) ?>?');">
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
