-- ============================================================================
-- DATABASE SISTEM MANAJEMEN STOK BAHAN BAKU & REKAPITULASI PENJUALAN
-- LO.LO COFFEE & RESTO INDRAMAYU
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `proyek 1` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `proyek 1`;

-- 1. TABEL PENGGUNA
DROP TABLE IF EXISTS `opname`;
DROP TABLE IF EXISTS `transaksi_stok`;
DROP TABLE IF EXISTS `detail_penjualan`;
DROP TABLE IF EXISTS `penjualan`;
DROP TABLE IF EXISTS `resep`;
DROP TABLE IF EXISTS `menu`;
DROP TABLE IF EXISTS `bahan_baku`;
DROP TABLE IF EXISTS `pengguna`;

CREATE TABLE `pengguna` (
    `id_user` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('chef', 'kasir', 'pemilik') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABEL BAHAN BAKU
CREATE TABLE `bahan_baku` (
    `id_bahan` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_bahan` VARCHAR(100) NOT NULL,
    `satuan` VARCHAR(20) NOT NULL,
    `stok_bahan` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `batas_minimal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABEL MENU
CREATE TABLE `menu` (
    `id_menu` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_menu` VARCHAR(100) NOT NULL,
    `harga` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABEL RESEP (BILL OF MATERIALS)
CREATE TABLE `resep` (
    `id_resep` INT AUTO_INCREMENT PRIMARY KEY,
    `id_menu` INT NOT NULL,
    `id_bahan` INT NOT NULL,
    `takaran` DECIMAL(10,2) NOT NULL,
    CONSTRAINT `fk_resep_menu` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE CASCADE,
    CONSTRAINT `fk_resep_bahan` FOREIGN KEY (`id_bahan`) REFERENCES `bahan_baku` (`id_bahan`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABEL PENJUALAN
CREATE TABLE `penjualan` (
    `id_penjualan` INT AUTO_INCREMENT PRIMARY KEY,
    `tanggal` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `total_harga` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `id_user` INT NOT NULL,
    CONSTRAINT `fk_penjualan_user` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id_user`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABEL DETAIL PENJUALAN
CREATE TABLE `detail_penjualan` (
    `id_detail` INT AUTO_INCREMENT PRIMARY KEY,
    `id_penjualan` INT NOT NULL,
    `id_menu` INT NOT NULL,
    `porsi` INT NOT NULL,
    `subtotal` DECIMAL(12,2) NOT NULL,
    CONSTRAINT `fk_detail_penjualan` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id_penjualan`) ON DELETE CASCADE,
    CONSTRAINT `fk_detail_menu` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABEL TRANSAKSI STOK
CREATE TABLE `transaksi_stok` (
    `id_transaksi` INT AUTO_INCREMENT PRIMARY KEY,
    `tanggal` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `jenis_stok` ENUM('masuk', 'keluar') NOT NULL,
    `jumlah_stok` DECIMAL(10,2) NOT NULL,
    `id_bahan` INT NOT NULL,
    `id_user` INT NOT NULL,
    CONSTRAINT `fk_stok_bahan` FOREIGN KEY (`id_bahan`) REFERENCES `bahan_baku` (`id_bahan`) ON DELETE RESTRICT,
    CONSTRAINT `fk_stok_user` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id_user`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TABEL OPNAME
CREATE TABLE `opname` (
    `id_opname` INT AUTO_INCREMENT PRIMARY KEY,
    `tanggal` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `stok_sistem` DECIMAL(10,2) NOT NULL,
    `stok_fisik` DECIMAL(10,2) NOT NULL,
    `selisih` DECIMAL(10,2) NOT NULL,
    `id_bahan` INT NOT NULL,
    `id_user` INT NOT NULL,
    CONSTRAINT `fk_opname_bahan` FOREIGN KEY (`id_bahan`) REFERENCES `bahan_baku` (`id_bahan`) ON DELETE RESTRICT,
    CONSTRAINT `fk_opname_user` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id_user`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA AWAL
-- ============================================================================

-- Akun Default (Password: pemilik123, chef123, kasir123)
INSERT INTO `pengguna` (`id_user`, `username`, `password`, `role`) VALUES
(1, 'pemilik', '$2y$10$yDAdrZrXySjm3JS5TWtiduvv8XSGeDJ2KvMS.7vJk.TemAEvu8gkm', 'pemilik'),
(2, 'chef', '$2y$10$SM0c0EnNCjHV9MXANrXF9.yCBu.zNlBwEeJiY.5HjfGQctPCPDV5K', 'chef'),
(3, 'kasir', '$2y$10$0kcki9YtuELbrn.hDzYSq.tgw.b.94IbdmFPgnmuKW9YPAnAxrk1m', 'kasir');

-- Data Bahan Baku Awal
INSERT INTO `bahan_baku` (`id_bahan`, `nama_bahan`, `satuan`, `stok_bahan`, `batas_minimal`) VALUES
(1, 'Biji Kopi Arabika Indramayu', 'gram', 3000.00, 500.00),
(2, 'Susu Segar Full Cream', 'ml', 6000.00, 1000.00),
(3, 'Sirup Gula Aren Asli', 'ml', 3500.00, 600.00),
(4, 'Bubuk Cokelat Premium', 'gram', 1800.00, 300.00),
(5, 'Daun Teh Hitam', 'gram', 1200.00, 200.00),
(6, 'Daging Patty Sapi', 'pcs', 50.00, 10.00),
(7, 'Roti Bun Burger Wijen', 'pcs', 50.00, 10.00),
(8, 'Keju Slice Melted', 'lembar', 60.00, 15.00),
(9, 'Kentang Beku Shoestring', 'gram', 4500.00, 1000.00),
(10, 'Saus Barbeku Racikan', 'ml', 2500.00, 400.00);

-- Data Menu Awal
INSERT INTO `menu` (`id_menu`, `nama_menu`, `harga`) VALUES
(1, 'Kopi Susu Aren LO.LO', 22000.00),
(2, 'Espresso Single Shot', 15000.00),
(3, 'Hot Chocolate Indramayu', 24000.00),
(4, 'LO.LO Beef Burger Special', 38000.00),
(5, 'French Fries Gurih', 18000.00);

-- Data Resep (BOM)
INSERT INTO `resep` (`id_resep`, `id_menu`, `id_bahan`, `takaran`) VALUES
(1, 1, 1, 18.00),
(2, 1, 2, 120.00),
(3, 1, 3, 25.00),
(4, 2, 1, 18.00),
(5, 3, 4, 30.00),
(6, 3, 2, 150.00),
(7, 3, 3, 15.00),
(8, 4, 6, 1.00),
(9, 4, 7, 1.00),
(10, 4, 8, 1.00),
(11, 4, 10, 20.00),
(12, 5, 9, 150.00),
(13, 5, 10, 20.00);

-- Riwayat transaksi penjualan dan stok awal dikosongkan agar siap digunakan langsung

