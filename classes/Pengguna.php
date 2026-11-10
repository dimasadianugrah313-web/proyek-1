<?php
// ============================================================================
// CLASS PENGGUNA - MODEL & OTENTIKASI USER
// ============================================================================

require_once __DIR__ . '/../config/database.php';

class Pengguna {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Cari user berdasarkan username
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM pengguna WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    // Cari user berdasarkan ID
    public function getById($id_user) {
        $stmt = $this->db->prepare("SELECT id_user, username, role, created_at FROM pengguna WHERE id_user = :id LIMIT 1");
        $stmt->execute([':id' => $id_user]);
        return $stmt->fetch();
    }

    // Otentikasi login dengan password_verify
    public function authenticate($username, $password) {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Ambil semua pengguna
    public function getAll() {
        $stmt = $this->db->query("SELECT id_user, username, role, created_at FROM pengguna ORDER BY role ASC, username ASC");
        return $stmt->fetchAll();
    }
}
