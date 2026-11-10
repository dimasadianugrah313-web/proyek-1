<?php
// ============================================================================
// CLASS PENJUALAN - TRANSAKSI PENJUALAN, DETAIL, & REKAPITULASI
// ============================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Resep.php';
require_once __DIR__ . '/Menu.php';

class Penjualan {
    private $db;
    private $resepModel;
    private $menuModel;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->resepModel = new Resep();
        $this->menuModel = new Menu();
    }

    // Proses transaksi penjualan dalam SATU transaksi database
    public function prosesTransaksi($id_user, array $items) {
        if (empty($items)) {
            return [
                'success' => false,
                'message' => 'Pesanan menu tidak boleh kosong.'
            ];
        }

        // 1. Validasi dan hitung subtotal & total harga
        $total_harga = 0.00;
        $processed_items = [];

        foreach ($items as $item) {
            $id_menu = (int)($item['id_menu'] ?? 0);
            $porsi = (int)($item['porsi'] ?? 0);

            if ($id_menu <= 0 || $porsi <= 0) {
                continue;
            }

            $menu = $this->menuModel->getById($id_menu);
            if (!$menu) {
                return [
                    'success' => false,
                    'message' => 'Menu yang dipilih tidak ditemukan dalam database.'
                ];
            }

            $subtotal = (float)$menu['harga'] * $porsi;
            $total_harga += $subtotal;

            $processed_items[] = [
                'id_menu' => $id_menu,
                'nama_menu' => $menu['nama_menu'],
                'harga' => (float)$menu['harga'],
                'porsi' => $porsi,
                'subtotal' => $subtotal
            ];
        }

        if (empty($processed_items)) {
            return [
                'success' => false,
                'message' => 'Harap masukkan minimal 1 menu dengan porsi lebih dari 0.'
            ];
        }

        // 2. Hitung kebutuhan bahan baku gabungan dan cek ketersediaan stok
        $cek_bahan = $this->resepModel->hitungKebutuhanBahan($processed_items);
        if (!$cek_bahan['status_cukup']) {
            $kurang_detail = [];
            foreach ($cek_bahan['bahan_kurang'] as $bk) {
                $kurang_detail[] = "{$bk['nama_bahan']} (butuh: {$bk['total_butuh']} {$bk['satuan']}, stok ada: {$bk['stok_bahan']} {$bk['satuan']})";
            }
            return [
                'success' => false,
                'message' => 'Stok bahan baku tidak mencukupi: ' . implode('; ', $kurang_detail)
            ];
        }

        // 3. Eksekusi transaksi database (Atomic)
        try {
            $this->db->beginTransaction();

            // Insert ke tabel penjualan
            $stmtPenjualan = $this->db->prepare("
                INSERT INTO penjualan (tanggal, total_harga, id_user) 
                VALUES (NOW(), :total, :id_user)
            ");
            $stmtPenjualan->execute([
                ':total' => $total_harga,
                ':id_user' => (int)$id_user
            ]);
            $id_penjualan = (int)$this->db->lastInsertId();

            // Insert ke tabel detail_penjualan
            $stmtDetail = $this->db->prepare("
                INSERT INTO detail_penjualan (id_penjualan, id_menu, porsi, subtotal) 
                VALUES (:id_penjualan, :id_menu, :porsi, :subtotal)
            ");
            foreach ($processed_items as $item) {
                $stmtDetail->execute([
                    ':id_penjualan' => $id_penjualan,
                    ':id_menu' => $item['id_menu'],
                    ':porsi' => $item['porsi'],
                    ':subtotal' => $item['subtotal']
                ]);
            }

            // Kurangi stok semua bahan baku yang digunakan & catat ke transaksi_stok
            $stmtPotongStok = $this->db->prepare("
                UPDATE bahan_baku 
                SET stok_bahan = stok_bahan - :butuh 
                WHERE id_bahan = :id AND stok_bahan >= :min_butuh
            ");

            $stmtStokKeluar = $this->db->prepare("
                INSERT INTO transaksi_stok (tanggal, jenis_stok, jumlah_stok, id_bahan, id_user) 
                VALUES (NOW(), 'keluar', :jumlah, :id_bahan, :id_user)
            ");

            foreach ($cek_bahan['kebutuhan'] as $b) {
                if ($b['total_butuh'] > 0) {
                    $stmtPotongStok->execute([
                        ':butuh' => $b['total_butuh'],
                        ':id' => $b['id_bahan'],
                        ':min_butuh' => $b['total_butuh']
                    ]);

                    if ($stmtPotongStok->rowCount() === 0) {
                        throw new Exception("Gagal memotong stok untuk {$b['nama_bahan']}. Stok mungkin berubah.");
                    }

                    // Catat ke log transaksi stok
                    $stmtStokKeluar->execute([
                        ':jumlah' => $b['total_butuh'],
                        ':id_bahan' => $b['id_bahan'],
                        ':id_user' => (int)$id_user
                    ]);
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'id_penjualan' => $id_penjualan,
                'total_harga' => $total_harga,
                'message' => 'Transaksi penjualan berhasil'
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

    // Ambil data penjualan header
    public function getById($id_penjualan) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username as nama_kasir 
            FROM penjualan p 
            JOIN pengguna u ON p.id_user = u.id_user 
            WHERE p.id_penjualan = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $id_penjualan]);
        return $stmt->fetch();
    }

    // Ambil rincian detail menu per penjualan
    public function getDetail($id_penjualan) {
        $stmt = $this->db->prepare("
            SELECT dp.*, m.nama_menu, m.harga 
            FROM detail_penjualan dp 
            JOIN menu m ON dp.id_menu = m.id_menu 
            WHERE dp.id_penjualan = :id 
            ORDER BY dp.id_detail ASC
        ");
        $stmt->execute([':id' => $id_penjualan]);
        return $stmt->fetchAll();
    }

    // Ambil riwayat penjualan terbaru
    public function getRiwayat($limit = 50, $id_user = null) {
        $sql = "
            SELECT p.*, u.username as nama_kasir,
                   (SELECT COUNT(*) FROM detail_penjualan dp WHERE dp.id_penjualan = p.id_penjualan) as total_item
            FROM penjualan p
            JOIN pengguna u ON p.id_user = u.id_user
        ";
        $params = [];

        if ($id_user !== null) {
            $sql .= " WHERE p.id_user = :id_user";
            $params[':id_user'] = $id_user;
        }

        $sql .= " ORDER BY p.tanggal DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Laporan rekapitulasi penjualan berdasar rentang tanggal
    public function getLaporan($tanggal_awal, $tanggal_akhir) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username as nama_kasir,
                   GROUP_CONCAT(CONCAT(m.nama_menu, ' (', dp.porsi, 'x)') SEPARATOR ', ') as rincian_menu,
                   SUM(dp.porsi) as total_porsi
            FROM penjualan p
            JOIN pengguna u ON p.id_user = u.id_user
            JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
            JOIN menu m ON dp.id_menu = m.id_menu
            WHERE DATE(p.tanggal) >= :awal AND DATE(p.tanggal) <= :akhir
            GROUP BY p.id_penjualan, p.tanggal, p.total_harga, p.id_user, u.username
            ORDER BY p.tanggal DESC
        ");
        $stmt->execute([
            ':awal' => $tanggal_awal,
            ':akhir' => $tanggal_akhir
        ]);
        return $stmt->fetchAll();
    }

    // Ringkasan metrik laporan
    public function getRingkasanLaporan($tanggal_awal, $tanggal_akhir) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT p.id_penjualan) as total_transaksi,
                COALESCE(SUM(p.total_harga), 0) as total_pendapatan,
                COALESCE(SUM(dp.porsi), 0) as total_porsi_terjual
            FROM penjualan p
            LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
            WHERE DATE(p.tanggal) >= :awal AND DATE(p.tanggal) <= :akhir
        ");
        $stmt->execute([
            ':awal' => $tanggal_awal,
            ':akhir' => $tanggal_akhir
        ]);
        return $stmt->fetch();
    }

    // Data grafik tren penjualan harian
    public function getGrafikTrenHarian($tanggal_awal, $tanggal_akhir) {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(tanggal) as tanggal_hari,
                COUNT(id_penjualan) as jumlah_transaksi,
                COALESCE(SUM(total_harga), 0) as total_omset
            FROM penjualan
            WHERE DATE(tanggal) >= :awal AND DATE(tanggal) <= :akhir
            GROUP BY DATE(tanggal)
            ORDER BY DATE(tanggal) ASC
        ");
        $stmt->execute([
            ':awal' => $tanggal_awal,
            ':akhir' => $tanggal_akhir
        ]);
        return $stmt->fetchAll();
    }

    // Data menu terlaris pada rentang periode
    public function getMenuTerlaris($tanggal_awal, $tanggal_akhir, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT 
                m.nama_menu,
                SUM(dp.porsi) as total_terjual,
                SUM(dp.subtotal) as total_omset
            FROM detail_penjualan dp
            JOIN penjualan p ON dp.id_penjualan = p.id_penjualan
            JOIN menu m ON dp.id_menu = m.id_menu
            WHERE DATE(p.tanggal) >= :awal AND DATE(p.tanggal) <= :akhir
            GROUP BY m.id_menu, m.nama_menu
            ORDER BY total_terjual DESC
            LIMIT " . (int)$limit
        );
        $stmt->execute([
            ':awal' => $tanggal_awal,
            ':akhir' => $tanggal_akhir
        ]);
        return $stmt->fetchAll();
    }

    // Statistik hari ini untuk Dashboard
    public function getStatistikHariIni() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_transaksi,
                COALESCE(SUM(total_harga), 0) as total_pendapatan
            FROM penjualan 
            WHERE DATE(tanggal) = CURDATE()
        ");
        return $stmt->fetch();
    }

    // Statistik hari ini khusus kasir tertentu
    public function getStatistikKasirHariIni($id_user) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_transaksi,
                COALESCE(SUM(total_harga), 0) as total_pendapatan
            FROM penjualan 
            WHERE DATE(tanggal) = CURDATE() AND id_user = :id_user
        ");
        $stmt->execute([':id_user' => (int)$id_user]);
        return $stmt->fetch();
    }
}
