<?php
// ============================================================================
// CLASS TRANSAKSI STOK - KELOLA STOK MASUK DAN KELUAR (CHEF)
// ============================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BahanBaku.php';

class TransaksiStok {
    private $db;
    private $bahanModel;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->bahanModel = new BahanBaku();
    }

    // Input Stok Masuk
    public function stokMasuk($id_bahan, $jumlah_stok, $id_user) {
        $jumlah = (float)$jumlah_stok;
        if ($jumlah <= 0) {
            return [
                'success' => false,
                'message' => 'Jumlah stok tidak boleh kosong, nol, atau minus.'
            ];
        }

        $bahan = $this->bahanModel->getById($id_bahan);
        if (!$bahan) {
            return [
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan.'
            ];
        }

        try {
            $this->db->beginTransaction();

            // Tambah stok bahan
            $stmtUpdate = $this->db->prepare("
                UPDATE bahan_baku 
                SET stok_bahan = stok_bahan + :jumlah 
                WHERE id_bahan = :id
            ");
            $stmtUpdate->execute([
                ':jumlah' => $jumlah,
                ':id' => (int)$id_bahan
            ]);

            // Catat transaksi stok masuk
            $stmtInsert = $this->db->prepare("
                INSERT INTO transaksi_stok (tanggal, jenis_stok, jumlah_stok, id_bahan, id_user) 
                VALUES (NOW(), 'masuk', :jumlah, :id_bahan, :id_user)
            ");
            $stmtInsert->execute([
                ':jumlah' => $jumlah,
                ':id_bahan' => (int)$id_bahan,
                ':id_user' => (int)$id_user
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Data berhasil diproses'
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ];
        }
    }

    // Input Stok Keluar
    public function stokKeluar($id_bahan, $jumlah_stok, $id_user) {
        $jumlah = (float)$jumlah_stok;
        if ($jumlah <= 0) {
            return [
                'success' => false,
                'message' => 'Jumlah stok tidak boleh kosong, nol, atau minus.'
            ];
        }

        $bahan = $this->bahanModel->getById($id_bahan);
        if (!$bahan) {
            return [
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan.'
            ];
        }

        // Validasi ketersediaan stok
        if ((float)$bahan['stok_bahan'] < $jumlah) {
            return [
                'success' => false,
                'message' => 'Stok bahan baku tidak mencukupi'
            ];
        }

        try {
            $this->db->beginTransaction();

            // Kurangi stok bahan
            $stmtUpdate = $this->db->prepare("
                UPDATE bahan_baku 
                SET stok_bahan = stok_bahan - :jumlah 
                WHERE id_bahan = :id AND stok_bahan >= :min_jumlah
            ");
            $stmtUpdate->execute([
                ':jumlah' => $jumlah,
                ':id' => (int)$id_bahan,
                ':min_jumlah' => $jumlah
            ]);

            if ($stmtUpdate->rowCount() === 0) {
                throw new Exception("Stok bahan baku tidak mencukupi.");
            }

            // Catat transaksi stok keluar
            $stmtInsert = $this->db->prepare("
                INSERT INTO transaksi_stok (tanggal, jenis_stok, jumlah_stok, id_bahan, id_user) 
                VALUES (NOW(), 'keluar', :jumlah, :id_bahan, :id_user)
            ");
            $stmtInsert->execute([
                ':jumlah' => $jumlah,
                ':id_bahan' => (int)$id_bahan,
                ':id_user' => (int)$id_user
            ]);

            $this->db->commit();

            // Cek apakah stok baru <= batas minimal untuk notifikasi stok menipis
            $stok_baru = (float)$bahan['stok_bahan'] - $jumlah;
            $peringatan = '';
            if ($stok_baru <= (float)$bahan['batas_minimal']) {
                $peringatan = " Perhatian: Stok " . htmlspecialchars($bahan['nama_bahan']) . " saat ini menipis (" . format_angka($stok_baru) . " " . htmlspecialchars($bahan['satuan']) . ", batas minimal: " . format_angka($bahan['batas_minimal']) . " " . htmlspecialchars($bahan['satuan']) . ")!";
            }

            return [
                'success' => true,
                'stok_menipis' => ($stok_baru <= (float)$bahan['batas_minimal']),
                'message' => 'Data berhasil diproses' . ($peringatan ? '.' . $peringatan : '')
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ];
        }
    }

    // Ambil riwayat mutasi stok
    public function getRiwayat($limit = 50, $jenis = null) {
        $sql = "
            SELECT ts.*, b.nama_bahan, b.satuan, u.username as nama_user, u.role
            FROM transaksi_stok ts
            JOIN bahan_baku b ON ts.id_bahan = b.id_bahan
            JOIN pengguna u ON ts.id_user = u.id_user
        ";
        $params = [];

        if ($jenis !== null && in_array($jenis, ['masuk', 'keluar'])) {
            $sql .= " WHERE ts.jenis_stok = :jenis";
            $params[':jenis'] = $jenis;
        }

        $sql .= " ORDER BY ts.tanggal DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Statistik stok hari ini
    public function getStatistikHariIni() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_transaksi,
                SUM(CASE WHEN jenis_stok = 'masuk' THEN 1 ELSE 0 END) as total_masuk,
                SUM(CASE WHEN jenis_stok = 'keluar' THEN 1 ELSE 0 END) as total_keluar
            FROM transaksi_stok
            WHERE DATE(tanggal) = CURDATE()
        ");
        return $stmt->fetch();
    }
}
