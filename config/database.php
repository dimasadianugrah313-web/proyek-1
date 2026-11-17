<?php
// ============================================================================
// KONFIGURASI DATABASE - LO.LO COFFEE & RESTO INDRAMAYU
// ============================================================================

class Database {
    private static $host = '127.0.0.1';
    private static $db_name = 'proyek 1'; // Database bernama `proyek 1`
    private static $username = 'root';
    private static $password = '';
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => true,
                    PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
                ];

                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4";

                try {
                    self::$conn = new PDO($dsn, self::$username, self::$password, $options);
                } catch (PDOException $e) {
                    // Jika database belum dibuat (Error 1049 Unknown Database), buat otomatis!
                    if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                        $serverPdo = new PDO("mysql:host=" . self::$host . ";charset=utf8mb4", self::$username, self::$password, $options);
                        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . self::$db_name . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                        self::$conn = new PDO($dsn, self::$username, self::$password, $options);
                    } else {
                        throw $e;
                    }
                }

                // Cek apakah tabel utama sudah ada dan data menu lengkap (120 menu), jika belum import dari database.sql
                $needImport = false;
                $checkTable = self::$conn->query("SHOW TABLES LIKE 'menu'");
                if ($checkTable->rowCount() === 0) {
                    $needImport = true;
                } else {
                    $countMenu = (int)self::$conn->query("SELECT COUNT(*) FROM `menu`")->fetchColumn();
                    if ($countMenu < 50) {
                        $needImport = true;
                    }
                }

                if ($needImport) {
                    $sqlFile = __DIR__ . '/../database.sql';
                    if (file_exists($sqlFile)) {
                        $sqlContent = file_get_contents($sqlFile);
                        $sqlContent = preg_replace('/USE `?[^`]+`?;/i', '', $sqlContent);
                        $sqlContent = preg_replace('/CREATE DATABASE IF NOT EXISTS [^;]+;/i', '', $sqlContent);
                        try {
                            self::$conn->exec($sqlContent);
                        } catch (PDOException $ex) {
                            $statements = array_filter(array_map('trim', explode(";\n", $sqlContent)));
                            foreach ($statements as $stmt) {
                                if (!empty($stmt)) {
                                    self::$conn->exec($stmt);
                                }
                            }
                        }
                    }
                }

            } catch (PDOException $e) {
                // Tampilan pesan ramah bila MySQL belum running
                die('<div style="font-family:Segoe UI,sans-serif;max-width:600px;margin:50px auto;padding:24px;background:#fff3cd;border:1px solid #ffeeba;border-radius:8px;color:#856404;">' .
                    '<h3 style="margin-top:0;">Gagal Terhubung ke Database MySQL</h3>' .
                    '<p>Pastikan MySQL di Laragon / XAMPP sudah aktif.</p>' .
                    '<p><small>Detail Error: ' . htmlspecialchars($e->getMessage()) . '</small></p>' .
                    '</div>');
            }
        }
        return self::$conn;
    }
}

// Helper BASE_URL dinamis sesuai direktori proyek di Apache
if (!defined('BASE_URL')) {
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Cari root direktori proyek jika berada di dalam subfolder pages/
    $base_path = preg_replace('#/pages(/.*)?$#', '', $script_dir);
    $base_path = rtrim($base_path, '/');
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    define('BASE_URL', $protocol . $host . $base_path);
}

// Format Rupiah Helper
if (!function_exists('format_rupiah')) {
    function format_rupiah($angka) {
        return 'Rp ' . number_format((float)$angka, 0, ',', '.');
    }
}

// Format Angka / Stok Helper
if (!function_exists('format_angka')) {
    function format_angka($angka) {
        return (floatval($angka) == intval($angka)) ? number_format($angka, 0, ',', '.') : number_format($angka, 2, ',', '.');
    }
}
