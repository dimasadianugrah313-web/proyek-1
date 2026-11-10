<?php
// ============================================================================
// CLASS RESEP - BILL OF MATERIALS (BOM) MENU
// ============================================================================

require_once __DIR__ . '/../config/database.php';

class Resep {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Ambil resep lengkap untuk suatu menu
    public function getByMenu($id_menu) {
        $stmt = $this->db->prepare("
            SELECT r.id_resep, r.id_menu, r.id_bahan, r.takaran,
                   b.nama_bahan, b.satuan, b.stok_bahan, b.batas_minimal
            FROM resep r
            JOIN bahan_baku b ON r.id_bahan = b.id_bahan
            WHERE r.id_menu = :id_menu
            ORDER BY b.nama_bahan ASC
        ");
        $stmt->execute([':id_menu' => $id_menu]);
        return $stmt->fetchAll();
    }

    // Tambah atau update takaran bahan pada menu
    public function simpanBahan($id_menu, $id_bahan, $takaran) {
        // Cek apakah bahan sudah ada di resep menu ini
        $stmt = $this->db->prepare("SELECT id_resep FROM resep WHERE id_menu = :id_menu AND id_bahan = :id_bahan LIMIT 1");
        $stmt->execute([':id_menu' => $id_menu, ':id_bahan' => $id_bahan]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $this->db->prepare("UPDATE resep SET takaran = :takaran WHERE id_resep = :id");
            return $stmt->execute([
                ':takaran' => (float)$takaran,
                ':id' => $existing['id_resep']
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO resep (id_menu, id_bahan, takaran) VALUES (:id_menu, :id_bahan, :takaran)");
            return $stmt->execute([
                ':id_menu' => (int)$id_menu,
                ':id_bahan' => (int)$id_bahan,
                ':takaran' => (float)$takaran
            ]);
        }
    }

    // Hapus satu item bahan dari resep
    public function hapus($id_resep) {
        $stmt = $this->db->prepare("DELETE FROM resep WHERE id_resep = :id");
        return $stmt->execute([':id' => $id_resep]);
    }

    // Hitung total akumulasi kebutuhan bahan baku untuk sejumlah pesanan menu
    // $items = [ ['id_menu' => 1, 'porsi' => 2], ['id_menu' => 2, 'porsi' => 1], ... ]
    public function hitungKebutuhanBahan(array $items) {
        $kebutuhan = []; // id_bahan => [data]

        foreach ($items as $item) {
            $id_menu = (int)$item['id_menu'];
            $porsi = (int)$item['porsi'];
            if ($porsi <= 0) continue;

            $resep_list = $this->getByMenu($id_menu);
            foreach ($resep_list as $r) {
                $id_b = (int)$r['id_bahan'];
                $total_takaran = (float)$r['takaran'] * $porsi;

                if (!isset($kebutuhan[$id_b])) {
                    $kebutuhan[$id_b] = [
                        'id_bahan' => $id_b,
                        'nama_bahan' => $r['nama_bahan'],
                        'satuan' => $r['satuan'],
                        'stok_bahan' => (float)$r['stok_bahan'],
                        'total_butuh' => 0.00
                    ];
                }
                $kebutuhan[$id_b]['total_butuh'] += $total_takaran;
            }
        }

        // Tentukan kecukupan stok
        $status_cukup = true;
        $bahan_kurang = [];

        foreach ($kebutuhan as &$kb) {
            $kb['cukup'] = ($kb['stok_bahan'] >= $kb['total_butuh']);
            if (!$kb['cukup']) {
                $status_cukup = false;
                $bahan_kurang[] = $kb;
            }
        }

        return [
            'status_cukup' => $status_cukup,
            'kebutuhan' => array_values($kebutuhan),
            'bahan_kurang' => $bahan_kurang
        ];
    }
}
