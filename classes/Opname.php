<?php
// ============================================================================
// CLASS OPNAME - STOCK OPNAME FISIK VS SISTEM (PEMILIK)
// ============================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BahanBaku.php';

class Opname {
    private $db;
    private $bahanModel;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->bahanModel = new BahanBaku();
    }

    // Hitung selisih: selisih = stok fisik - stok sistem
    public function hitungSelisih($stok_fisik, $stok_sistem) {
        return (float)$stok_fisik - (float)$stok_sistem;
    }

    // Simpan riwayat stock opname ke database (Q-009)
    public function simpanRiwayat($id_bahan, $id_user, $stok_sistem, $stok_fisik, $selisih) {
        $stmtInsert = $this->db->prepare("
            INSERT INTO opname (tanggal, stok_sistem, stok_fisik, selisih, id_bahan, id_user) 
            VALUES (NOW(), :sistem, :fisik, :selisih, :id_bahan, :id_user)
        ");
        return $stmtInsert->execute([
            ':sistem' => (float)$stok_sistem,
            ':fisik' => (float)$stok_fisik,
            ':selisih' => (float)$selisih,
            ':id_bahan' => (int)$id_bahan,
            ':id_user' => (int)$id_user
        ]);
    }

    // Menjalankan stock opname dan menyinkronkan stok sistem dengan stok fisik (Algo-004)
    public function sinkronisasiStok($id_bahan, $stok_fisik_input, $id_user) {
        if ($stok_fisik_input === '' || $stok_fisik_input === null) {
            return [
                'success' => false,
                'message' => 'Form data tidak boleh kosong'
            ];
        }

        $stok_fisik = (float)$stok_fisik_input;
        if ($stok_fisik < 0) {
            return [
                'success' => false,
                'message' => 'Stok fisik tidak boleh bernilai negatif.'
            ];
        }

        $bahan = $this->bahanModel->getById($id_bahan);
        if (!$bahan) {
            return [
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan.'
            ];
        }

        $stok_sistem = (float)$bahan['stok_bahan'];
        // Rumus Stock Opname: selisih = stok fisik - stok sistem
        $selisih = $this->hitungSelisih($stok_fisik, $stok_sistem);

        try {
            $this->db->beginTransaction();

            // Simpan riwayat opname
            $this->simpanRiwayat($id_bahan, $id_user, $stok_sistem, $stok_fisik, $selisih);

            // Jika selisih != 0, sesuaikan stok_bahan di database ke stok fisik
            if ($selisih != 0) {
                $this->bahanModel->sinkronisasiStok($id_bahan, $stok_fisik);
            }

            $this->db->commit();

            return [
                'success' => true,
                'selisih' => $selisih,
                'status' => ($selisih == 0) ? 'Sesuai' : 'Penyesuaian',
                'message' => 'Sinkronisasi stok berhasil'
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

    // Alias untuk backward compatibility
    public function prosesOpname($id_bahan, $stok_fisik_input, $id_user) {
        return $this->sinkronisasiStok($id_bahan, $stok_fisik_input, $id_user);
    }

    // Ambil riwayat stock opname
    public function getRiwayat($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT o.*, b.nama_bahan, b.satuan, u.username as nama_user 
            FROM opname o
            JOIN bahan_baku b ON o.id_bahan = b.id_bahan
            JOIN pengguna u ON o.id_user = u.id_user
            ORDER BY o.tanggal DESC 
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
