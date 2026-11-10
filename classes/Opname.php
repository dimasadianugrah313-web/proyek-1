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

    // Proses input opname bahan baku
    public function prosesOpname($id_bahan, $stok_fisik_input, $id_user) {
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
        $selisih = $stok_sistem - $stok_fisik; // Sesuai rumus: selisih = stok_sistem - stok_fisik

        try {
            $this->db->beginTransaction();

            // Simpan riwayat opname
            $stmtInsert = $this->db->prepare("
                INSERT INTO opname (tanggal, stok_sistem, stok_fisik, selisih, id_bahan, id_user) 
                VALUES (NOW(), :sistem, :fisik, :selisih, :id_bahan, :id_user)
            ");
            $stmtInsert->execute([
                ':sistem' => $stok_sistem,
                ':fisik' => $stok_fisik,
                ':selisih' => $selisih,
                ':id_bahan' => (int)$id_bahan,
                ':id_user' => (int)$id_user
            ]);

            // Jika selisih != 0, sesuaikan stok_bahan di database ke stok fisik
            if ($selisih != 0) {
                $this->bahanModel->updateStokFisik($id_bahan, $stok_fisik);
            }

            $this->db->commit();

            $status_teks = ($selisih == 0) ? "Sesuai" : "Tersesuaikan (Selisih: " . format_angka($selisih) . " " . htmlspecialchars($bahan['satuan']) . ")";

            return [
                'success' => true,
                'selisih' => $selisih,
                'status' => ($selisih == 0) ? 'Sesuai' : 'Penyesuaian',
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
