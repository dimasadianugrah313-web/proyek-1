<?php
// ============================================================================
// FITUR HAPUS / RESET DATA (KHUSUS PEMILIK) - LO.LO COFFEE & RESTO
// ============================================================================

$page_title = 'Reset Data Sistem';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['pemilik']); // Wajib role pemilik

require_once __DIR__ . '/../../config/database.php';

$db = Database::getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'reset_transaksi') {
            // 1. Kosongkan seluruh riwayat transaksi penjualan & mutasi stok
            $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $db->exec("TRUNCATE TABLE detail_penjualan;");
            $db->exec("TRUNCATE TABLE penjualan;");
            $db->exec("TRUNCATE TABLE transaksi_stok;");
            $db->exec("TRUNCATE TABLE opname;");
            $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

            set_flash('success', 'Semua riwayat transaksi penjualan telah berhasil dihapus bersih! Omset kini Rp 0 dan transaksi kasir baru akan dimulai dari #TRX-00001.');
            header("Location: " . BASE_URL . "/pages/pemilik/dashboard.php");
            exit;

        } elseif ($action === 'reset_awal') {
            // 2. Bersihkan transaksi dan kembalikan stok bahan & menu ke awal
            $sqlFile = __DIR__ . '/../../database.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("File database.sql tidak ditemukan.");
            }

            $sql = file_get_contents($sqlFile);
            $sql = preg_replace('/USE `?[^`]+`?;/i', 'USE `proyek 1`;', $sql);
            $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS [^;]+;/i', '', $sql);

            $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $db->exec($sql);
            // Pastikan tabel transaksi benar-benar kosong bersih 0
            $db->exec("TRUNCATE TABLE detail_penjualan;");
            $db->exec("TRUNCATE TABLE penjualan;");
            $db->exec("TRUNCATE TABLE transaksi_stok;");
            $db->exec("TRUNCATE TABLE opname;");
            $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

            set_flash('success', 'Database berhasil direset bersih ke data master awal tanpa ada riwayat transaksi sebelumnya.');
            header("Location: " . BASE_URL . "/pages/pemilik/dashboard.php");
            exit;

        } elseif ($action === 'reset_total') {
            // 3. Kosongkan total seluruh data (transaksi + menu + bahan baku + resep)
            $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $db->exec("TRUNCATE TABLE detail_penjualan;");
            $db->exec("TRUNCATE TABLE penjualan;");
            $db->exec("TRUNCATE TABLE transaksi_stok;");
            $db->exec("TRUNCATE TABLE opname;");
            $db->exec("TRUNCATE TABLE resep;");
            $db->exec("TRUNCATE TABLE menu;");
            $db->exec("TRUNCATE TABLE bahan_baku;");
            $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

            set_flash('success', 'Seluruh data transaksi, bahan baku, menu, dan resep telah berhasil dikosongkan total.');
            header("Location: " . BASE_URL . "/pages/pemilik/dashboard.php");
            exit;
        } else {
            $error = 'Aksi reset tidak valid.';
        }
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan saat memproses reset data: ' . $e->getMessage();
    }
}

// Cek jumlah data saat ini
$totalPenjualan = $db->query("SELECT COUNT(*) FROM penjualan")->fetchColumn();
$totalMutasiStok = $db->query("SELECT COUNT(*) FROM transaksi_stok")->fetchColumn();
$totalOpname = $db->query("SELECT COUNT(*) FROM opname")->fetchColumn();
$totalMenu = $db->query("SELECT COUNT(*) FROM menu")->fetchColumn();
$totalBahan = $db->query("SELECT COUNT(*) FROM bahan_baku")->fetchColumn();

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="max-width: 900px; margin: 0 auto;">
    <div class="card" style="border-left: 4px solid var(--color-danger);">
        <div class="card-header" style="background: #FFF5F5;">
            <div class="card-title" style="color: var(--color-danger); font-size: 17px;">
                Fitur Reset & Hapus Data Penjualan (Khusus Pemilik)
            </div>
            <a href="<?= BASE_URL ?>/pages/pemilik/dashboard.php" class="btn btn-sm btn-secondary">&larr; Kembali ke Dashboard</a>
        </div>
        <div class="card-body">
            <p style="color: var(--cafe-espresso); font-size: 14.5px; margin-bottom: 16px;">
                Gunakan halaman ini untuk <strong>mengosongkan seluruh riwayat penjualan dan transaksi stok</strong> agar dasbor menjadi Rp 0 dan kasir dapat memulai transaksi baru dari awal.
            </p>

            <div style="background: #FAF8F5; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px 18px; margin-bottom: 24px;">
                <div style="font-weight: 700; color: var(--cafe-mocha); margin-bottom: 8px;">Status Transaksi Tersimpan Saat Ini:</div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; font-size: 13.5px;">
                    <div>• Transaksi Penjualan: <strong style="color: <?= $totalPenjualan > 0 ? 'var(--color-danger)' : 'var(--color-success)' ?>;"><?= $totalPenjualan ?></strong> data</div>
                    <div>• Riwayat Mutasi Stok: <strong><?= $totalMutasiStok ?></strong> data</div>
                    <div>• Riwayat Stock Opname: <strong><?= $totalOpname ?></strong> data</div>
                    <div>• Data Menu Aktif: <strong><?= $totalMenu ?></strong> menu</div>
                    <div>• Data Bahan Baku: <strong><?= $totalBahan ?></strong> bahan</div>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- PILIHAN 1: HAPUS SEMUA TRANSAKSI (UTAMA / PALING SERING DIGUNAKAN) -->
            <div style="border: 2px solid var(--color-danger); border-radius: var(--radius-md); padding: 22px; margin-bottom: 20px; background: #FFF9F8;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 260px;">
                        <span class="badge badge-danger" style="margin-bottom: 8px; font-size: 11px;">REKOMENDASI UTAMA</span>
                        <h3 style="font-size: 17px; color: var(--color-danger); margin-bottom: 6px;">
                            1. Kosongkan Semua Data Transaksi Penjualan (Mulai Toko dari Nol)
                        </h3>
                        <p style="font-size: 13.5px; color: var(--cafe-espresso); margin-bottom: 8px;">
                            Menghapus seluruh riwayat <strong>Penjualan Kasir</strong>, omset hari ini menjadi <strong>Rp 0</strong>, serta mengosongkan riwayat mutasi stok. Penomoran nota kasir baru akan dimulai dari <code>#TRX-00001</code>.
                        </p>
                        <span class="badge badge-success" style="font-size: 11px;">
                            Master Menu Resto, Bahan Baku, dan Resep (BOM) TETAP AMAN TERSIMPAN
                        </span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-danger btn-lg" onclick="bukaModalReset('reset_transaksi', 'Kosongkan Semua Transaksi', 'Apakah Anda yakin mau reset ini? Seluruh riwayat penjualan akan dikosongkan menjadi 0. Data menu dan bahan baku tetap tersimpan.')">
                            Hapus Semua Transaksi Sekarang
                        </button>
                    </div>
                </div>
            </div>

            <!-- PILIHAN 2: KEMBALIKAN KE MASTER AWAL (BERSIH 0 TRANSAKSI) -->
            <div style="border: 1px solid #E2D9D0; border-radius: var(--radius-md); padding: 20px; margin-bottom: 20px; background: #FFF;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 260px;">
                        <h3 style="font-size: 16px; color: var(--cafe-espresso); margin-bottom: 6px;">
                            2. Reset Data Master ke Awal (Stok Normal & Bersih 0 Transaksi)
                        </h3>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">
                            Mengembalikan 10 bahan baku dan 5 menu kopi ke kondisi awal default, dengan <strong>0 transaksi</strong> (omset bersih Rp 0).
                        </p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" onclick="bukaModalReset('reset_awal', 'Reset ke Master Awal', 'Apakah Anda yakin mau reset ini? Database akan dikembalikan ke data master bawaan tanpa ada riwayat penjualan.')">
                            Reset Master ke Awal
                        </button>
                    </div>
                </div>
            </div>

            <!-- PILIHAN 3: KOSONGKAN TOTAL SEMUA DATA (KECUALI AKUN) -->
            <div style="border: 1px solid #E2D9D0; border-radius: var(--radius-md); padding: 20px; background: #FFF;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 260px;">
                        <h3 style="font-size: 16px; color: var(--text-muted); margin-bottom: 6px;">
                            3. Kosongkan Total Semua Data (Termasuk Menu & Bahan Baku)
                        </h3>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">
                            Menghapus seluruh transaksi, menu, bahan baku, dan resep (hanya menyisakan 3 akun login pemilik, chef, dan kasir).
                        </p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" onclick="bukaModalReset('reset_total', 'Kosongkan Total Semua Data', 'Apakah Anda yakin mau reset ini? Semua menu, bahan baku, resep, dan transaksi akan dihapus bersih total.')">
                            Kosongkan Total
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI: APAKAH ANDA YAKIN MAU RESET INI? -->
<div class="modal-overlay" id="modalKonfirmasiReset">
    <div class="modal-content" style="max-width: 440px;">
        <div class="modal-header" style="background: #FFF5F5; border-bottom: 1px solid #FFCDD2;">
            <h3 class="modal-title" id="txtModalResetJudul" style="color: var(--color-danger); font-size: 16px;">
                Konfirmasi Reset
            </h3>
            <button type="button" class="btn-close-modal" data-dismiss="modal">&times;</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" id="hiddenActionReset" value="">
            <div class="modal-body" style="text-align: center; padding: 26px 20px;">
                <p style="font-size: 16px; font-weight: 700; color: var(--cafe-espresso); margin-bottom: 8px;">
                    Apakah Anda yakin mau reset ini?
                </p>
                <p id="txtModalResetDeskripsi" style="font-size: 13px; color: var(--text-muted); line-height: 1.5;">
                    Semua riwayat transaksi penjualan akan dikosongkan.
                </p>
            </div>
            <div class="modal-footer" style="justify-content: center; gap: 12px; background: #FAF8F5; padding: 16px 20px;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="min-width: 90px;">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger" style="min-width: 140px;">
                    Ya, Reset Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalReset(action, judul, deskripsi) {
    document.getElementById('hiddenActionReset').value = action;
    document.getElementById('txtModalResetJudul').textContent = judul;
    document.getElementById('txtModalResetDeskripsi').textContent = deskripsi;
    openModal('modalKonfirmasiReset');
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
