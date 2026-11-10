<?php
// ============================================================================
// CLASS MENU - PENGELOLAAN DATA MASTER MENU MAKANAN & MINUMAN
// ============================================================================

require_once __DIR__ . '/../config/database.php';

class Menu {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Ambil semua menu
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM menu ORDER BY nama_menu ASC");
        return $stmt->fetchAll();
    }

    // Ambil menu dengan jumlah bahan dalam resepnya
    public function getAllWithResepInfo() {
        $stmt = $this->db->query("
            SELECT m.*, COUNT(r.id_resep) as jumlah_bahan 
            FROM menu m 
            LEFT JOIN resep r ON m.id_menu = r.id_menu 
            GROUP BY m.id_menu, m.nama_menu, m.harga, m.created_at
            ORDER BY m.nama_menu ASC
        ");
        return $stmt->fetchAll();
    }

    // Ambil menu berdasarkan ID
    public function getById($id_menu) {
        $stmt = $this->db->prepare("SELECT * FROM menu WHERE id_menu = :id LIMIT 1");
        $stmt->execute([':id' => $id_menu]);
        return $stmt->fetch();
    }

    // Hitung total menu
    public function countTotal() {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM menu");
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    // Tambah menu baru
    public function tambah($nama_menu, $harga) {
        $stmt = $this->db->prepare("INSERT INTO menu (nama_menu, harga) VALUES (:nama, :harga)");
        return $stmt->execute([
            ':nama' => trim($nama_menu),
            ':harga' => (float)$harga
        ]);
    }

    // Ubah data menu
    public function ubah($id_menu, $nama_menu, $harga) {
        $stmt = $this->db->prepare("UPDATE menu SET nama_menu = :nama, harga = :harga WHERE id_menu = :id");
        return $stmt->execute([
            ':nama' => trim($nama_menu),
            ':harga' => (float)$harga,
            ':id' => (int)$id_menu
        ]);
    }

    // Hapus menu dengan pencegahan jika ada riwayat penjualan
    public function hapus($id_menu) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM detail_penjualan WHERE id_menu = :id");
        $stmt->execute([':id' => $id_menu]);
        if ($stmt->fetch()['cnt'] > 0) {
            throw new Exception("Menu tidak dapat dihapus karena sudah memiliki riwayat Transaksi Penjualan.");
        }

        // Hapus resep terkait terlebih dahulu (cascade sudah ada di DB, tapi kita pastikan)
        $stmt = $this->db->prepare("DELETE FROM resep WHERE id_menu = :id");
        $stmt->execute([':id' => $id_menu]);

        $stmt = $this->db->prepare("DELETE FROM menu WHERE id_menu = :id");
        return $stmt->execute([':id' => $id_menu]);
    }
}
