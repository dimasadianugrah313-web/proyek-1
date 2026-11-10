<?php
// ============================================================================
// CLASS BAHAN BAKU - PENGELOLAAN STOK DAN MASTER DATA BAHAN
// ============================================================================

require_once __DIR__ . '/../config/database.php';

class BahanBaku {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Ambil semua bahan baku
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM bahan_baku ORDER BY nama_bahan ASC");
        return $stmt->fetchAll();
    }

    // Ambil bahan baku berdasarkan ID
    public function getById($id_bahan) {
        $stmt = $this->db->prepare("SELECT * FROM bahan_baku WHERE id_bahan = :id LIMIT 1");
        $stmt->execute([':id' => $id_bahan]);
        return $stmt->fetch();
    }

    // Ambil daftar bahan yang stoknya menipis (stok <= batas_minimal)
    public function getMenipis() {
        $stmt = $this->db->query("
            SELECT * FROM bahan_baku 
            WHERE stok_bahan <= batas_minimal 
            ORDER BY (stok_bahan / NULLIF(batas_minimal, 0)) ASC, nama_bahan ASC
        ");
        return $stmt->fetchAll();
    }

    // Hitung jumlah bahan yang menipis
    public function countMenipis() {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM bahan_baku WHERE stok_bahan <= batas_minimal");
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    // Hitung total seluruh bahan baku
    public function countTotal() {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM bahan_baku");
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    // Tambah data master bahan baku baru
    public function tambah($nama_bahan, $satuan, $stok_bahan, $batas_minimal) {
        $stmt = $this->db->prepare("
            INSERT INTO bahan_baku (nama_bahan, satuan, stok_bahan, batas_minimal) 
            VALUES (:nama, :satuan, :stok, :batas)
        ");
        return $stmt->execute([
            ':nama' => trim($nama_bahan),
            ':satuan' => trim($satuan),
            ':stok' => (float)$stok_bahan,
            ':batas' => (float)$batas_minimal
        ]);
    }

    // Ubah data master bahan baku
    public function ubah($id_bahan, $nama_bahan, $satuan, $stok_bahan, $batas_minimal) {
        $stmt = $this->db->prepare("
            UPDATE bahan_baku 
            SET nama_bahan = :nama, satuan = :satuan, stok_bahan = :stok, batas_minimal = :batas 
            WHERE id_bahan = :id
        ");
        return $stmt->execute([
            ':nama' => trim($nama_bahan),
            ':satuan' => trim($satuan),
            ':stok' => (float)$stok_bahan,
            ':batas' => (float)$batas_minimal,
            ':id' => (int)$id_bahan
        ]);
    }

    // Hapus bahan baku dengan pencegahan foreign key error
    public function hapus($id_bahan) {
        // Cek apakah dipakai di resep
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM resep WHERE id_bahan = :id");
        $stmt->execute([':id' => $id_bahan]);
        if ($stmt->fetch()['cnt'] > 0) {
            throw new Exception("Bahan baku tidak dapat dihapus karena masih digunakan dalam Resep Menu.");
        }

        // Cek apakah ada riwayat transaksi stok
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM transaksi_stok WHERE id_bahan = :id");
        $stmt->execute([':id' => $id_bahan]);
        if ($stmt->fetch()['cnt'] > 0) {
            throw new Exception("Bahan baku tidak dapat dihapus karena memiliki riwayat Transaksi Stok.");
        }

        // Cek opname
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM opname WHERE id_bahan = :id");
        $stmt->execute([':id' => $id_bahan]);
        if ($stmt->fetch()['cnt'] > 0) {
            throw new Exception("Bahan baku tidak dapat dihapus karena memiliki riwayat Stock Opname.");
        }

        $stmt = $this->db->prepare("DELETE FROM bahan_baku WHERE id_bahan = :id");
        return $stmt->execute([':id' => $id_bahan]);
    }

    // Tambah stok bahan (misal dari stok masuk)
    public function tambahStok($id_bahan, $jumlah) {
        $stmt = $this->db->prepare("
            UPDATE bahan_baku 
            SET stok_bahan = stok_bahan + :jumlah 
            WHERE id_bahan = :id
        ");
        return $stmt->execute([
            ':jumlah' => (float)$jumlah,
            ':id' => (int)$id_bahan
        ]);
    }

    // Kurangi stok bahan (misal dari stok keluar atau penjualan)
    public function kurangiStok($id_bahan, $jumlah) {
        $stmt = $this->db->prepare("
            UPDATE bahan_baku 
            SET stok_bahan = stok_bahan - :jumlah 
            WHERE id_bahan = :id AND stok_bahan >= :min_jumlah
        ");
        $stmt->execute([
            ':jumlah' => (float)$jumlah,
            ':id' => (int)$id_bahan,
            ':min_jumlah' => (float)$jumlah
        ]);
        return $stmt->rowCount() > 0;
    }

    // Update stok fisik saat opname
    public function updateStokFisik($id_bahan, $stok_fisik) {
        $stmt = $this->db->prepare("
            UPDATE bahan_baku 
            SET stok_bahan = :stok_fisik 
            WHERE id_bahan = :id
        ");
        return $stmt->execute([
            ':stok_fisik' => (float)$stok_fisik,
            ':id' => (int)$id_bahan
        ]);
    }
}
