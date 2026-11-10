<?php
// ============================================================================
// ATUR RESEP / BILL OF MATERIALS (BOM) - PEMILIK
// ============================================================================

$page_title = 'Atur Resep (BOM) Menu';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['pemilik']); // Wajib role pemilik

require_once __DIR__ . '/../../classes/Menu.php';
require_once __DIR__ . '/../../classes/BahanBaku.php';
require_once __DIR__ . '/../../classes/Resep.php';

$menuModel = new Menu();
$bahanModel = new BahanBaku();
$resepModel = new Resep();

$daftarMenu = $menuModel->getAll();
$daftarBahan = $bahanModel->getAll();

$selected_menu_id = isset($_GET['id_menu']) ? (int)$_GET['id_menu'] : ($daftarMenu[0]['id_menu'] ?? 0);
$current_menu = $menuModel->getById($selected_menu_id);

$error = '';

// Proses Hapus Bahan dari Resep
if (isset($_GET['hapus_resep'])) {
    $id_resep = (int)$_GET['hapus_resep'];
    $resepModel->hapus($id_resep);
    set_flash('success', 'Data berhasil diproses');
    header("Location: " . BASE_URL . "/pages/pemilik/resep.php?id_menu=" . $selected_menu_id);
    exit;
}

// Proses Tambah / Update Takaran Bahan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_menu = (int)($_POST['id_menu'] ?? 0);
    $id_bahan = (int)($_POST['id_bahan'] ?? 0);
    $takaran_raw = $_POST['takaran'] ?? '';

    if (empty($id_menu) || empty($id_bahan) || $takaran_raw === '') {
        $error = 'Form data tidak boleh kosong';
    } elseif (!is_numeric($takaran_raw) || (float)$takaran_raw <= 0) {
        $error = 'Takaran harus berupa angka lebih dari 0.';
    } else {
        $resepModel->simpanBahan($id_menu, $id_bahan, (float)$takaran_raw);
        set_flash('success', 'Data berhasil diproses');
        header("Location: " . BASE_URL . "/pages/pemilik/resep.php?id_menu=" . $id_menu);
        exit;
    }
}

$resepCurrent = $current_menu ? $resepModel->getByMenu($selected_menu_id) : [];

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Pilih Menu Target -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px 20px; display:flex; align-items:center; gap: 16px; flex-wrap:wrap;">
        <label for="selectFilterMenu" style="font-weight:700; color:var(--cafe-espresso);">
            Pilih Menu yang Ingin Diatur Resepnya:
        </label>
        <select id="selectFilterMenu" class="form-control" style="max-width: 320px;" 
                onchange="window.location.href = '<?= BASE_URL ?>/pages/pemilik/resep.php?id_menu=' + this.value">
            <?php foreach ($daftarMenu as $m): ?>
                <option value="<?= $m['id_menu'] ?>" <?= ($selected_menu_id === (int)$m['id_menu']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nama_menu']) ?> (<?= format_rupiah($m['harga']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<?php if ($current_menu): ?>
    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
        <!-- FORM TAMBAH BAHAN KE RESEP -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Tambah Komposisi Bahan</div>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="id_menu" value="<?= $current_menu['id_menu'] ?>">

                    <div class="form-group">
                        <label for="id_bahan" class="form-label">
                            Bahan Baku <span class="required">*</span>
                        </label>
                        <select name="id_bahan" id="id_bahan" class="form-control" required onchange="updateResepSatuan()">
                            <option value="">-- Pilih Bahan Baku --</option>
                            <?php foreach ($daftarBahan as $b): ?>
                                <option value="<?= $b['id_bahan'] ?>" data-satuan="<?= htmlspecialchars($b['satuan']) ?>">
                                    <?= htmlspecialchars($b['nama_bahan']) ?> (Satuan: <?= htmlspecialchars($b['satuan']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="takaran" class="form-label">
                            Takaran per Porsi <span class="required">*</span>
                        </label>
                        <div style="display:flex; align-items:center; gap: 10px;">
                            <input type="number" step="0.01" min="0.01" id="takaran" name="takaran" class="form-control" 
                                   placeholder="Contoh: 18 atau 120" required>
                            <span id="txtResepSatuan" style="font-weight:600; color:var(--cafe-mocha); min-width: 50px;">-</span>
                        </div>
                        <div class="form-hint">Jumlah bahan yang terpotong otomatis tiap 1 porsi menu ini terjual.</div>
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Simpan Komposisi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABEL RESEP SAAT INI -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    Resep / BOM: <strong><?= htmlspecialchars($current_menu['nama_menu']) ?></strong>
                </div>
                <span class="badge badge-info"><?= count($resepCurrent) ?> Bahan Terdaftar</span>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Bahan Baku</th>
                            <th>Takaran per Porsi</th>
                            <th>Stok Sistem Saat Ini</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resepCurrent)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 24px; color: var(--text-muted);">
                                    Menu ini belum memiliki komposisi resep bahan baku.<br>
                                    Silakan tambahkan bahan pada formulir di sebelah kiri.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($resepCurrent as $r): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($r['nama_bahan']) ?></strong></td>
                                    <td style="font-weight: 700; color: var(--cafe-accent);">
                                        <?= format_angka($r['takaran']) ?> <?= htmlspecialchars($r['satuan']) ?>
                                    </td>
                                    <td>
                                        <?= format_angka($r['stok_bahan']) ?> <?= htmlspecialchars($r['satuan']) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="<?= BASE_URL ?>/pages/pemilik/resep.php?id_menu=<?= $selected_menu_id ?>&hapus_resep=<?= $r['id_resep'] ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Hapus <?= addslashes(htmlspecialchars($r['nama_bahan'])) ?> dari resep menu ini?');">
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
<?php endif; ?>

<script>
function updateResepSatuan() {
    const sel = document.getElementById('id_bahan');
    const opt = sel.options[sel.selectedIndex];
    const txt = document.getElementById('txtResepSatuan');
    if (sel.value && opt) {
        txt.textContent = opt.getAttribute('data-satuan');
    } else {
        txt.textContent = '-';
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
