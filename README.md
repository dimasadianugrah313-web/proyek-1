# Aplikasi Web Manajemen Stok Bahan Baku dan Rekapitulasi Penjualan
## LO.LO Coffee & Resto Indramayu

Aplikasi web terintegrasi untuk pengelolaan stok bahan baku berbasis Resep/Bill of Materials (BOM), pencatatan transaksi penjualan kasir, stock opname fisik, serta rekapitulasi penjualan berkala.

---

### 1. Spesifikasi Teknologi
- **Frontend**: HTML5, CSS3 Native (Tema Kafe Cokelat Tua `#2A1810` & Oranye Kecokelatan `#C86432`), Vanilla JavaScript (Tanpa Framework/Library UI).
- **Backend**: PHP Native 8.x (Berorientasi Objek / OOP), PDO & Prepared Statements.
- **Database**: MySQL / MariaDB (InnoDb dengan Relasi Foreign Key & Transaksi ACID).
- **Web Server**: Apache (Laragon / XAMPP).

---

### 2. Akun Uji Coba Default

| Role | Username | Password | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Pemilik** | `pemilik` | `pemilik123` | Kelola Bahan Baku, Menu, Resep (BOM), Stock Opname, Laporan Rekapitulasi & Cetak/Ekspor, Notifikasi Stok Menipis. |
| **Chef** | `chef` | `chef123` | Input Stok Masuk, Input Stok Keluar, Cek Sisa Bahan, Riwayat Transaksi Stok. |
| **Kasir** | `kasir` | `kasir123` | Input Transaksi Penjualan Multi-Menu, Pengurangan Stok Otomatis, Cetak Nota Struk. |

---

### 3. Petunjuk Instalasi Cepat (Laragon / XAMPP)

1. **Jalankan Apache & MySQL**:
   - Pada Laragon, klik **Start All**.
   - Atau pada XAMPP Control Panel, klik **Start** pada modul Apache dan MySQL.

2. **Import Database**:
   - Buka phpMyAdmin di browser: `http://localhost/phpmyadmin/` atau HeidiSQL di Laragon.
   - Buat database baru bernama `lolo_resto` (atau langsung import file SQL).
   - Import file `database.sql` yang berada di root direktori proyek:
     `c:\laragon\www\proyek 1\database.sql`
   - Semua tabel, relasi, dan data seed awal akan dibuat secara otomatis.

3. **Buka Aplikasi di Browser**:
   - URL: `http://localhost/proyek%201/` atau `http://localhost/proyek 1/`
   - Masukkan username dan password sesuai akun uji di atas.

---

### 4. Struktur Direktori Proyek

```
proyek 1/
├── config/
│   └── database.php             # Konfigurasi koneksi PDO & helper URL
├── includes/
│   ├── auth.php                 # Otentikasi sesi & pembatasan role di server
│   ├── header.php               # Navigasi atas, alert notifikasi, topbar
│   ├── sidebar.php              # Navigasi menu dinamis berdasarkan role
│   └── footer.php               # Penutup layout & modal konfirmasi logout
├── classes/
│   ├── Pengguna.php             # Model pengguna & otentikasi password_verify
│   ├── BahanBaku.php            # Model CRUD & manipulasi stok bahan baku
│   ├── Menu.php                 # Model CRUD data master menu resto
│   ├── Resep.php                # Model BOM & kalkulasi kebutuhan bahan
│   ├── Penjualan.php            # Transaksi penjualan kasir & rekap laporan
│   ├── TransaksiStok.php        # Input stok masuk/keluar & validasi chef
│   └── Opname.php               # Stock opname fisik & penyesuaian selisih
├── pages/
│   ├── chef/
│   │   ├── dashboard.php        # Dashboard chef & alert bahan menipis
│   │   ├── stok_masuk.php       # Form penambahan stok bahan
│   │   ├── stok_keluar.php      # Form pemakaian/pengurangan stok bahan
│   │   └── riwayat_stok.php     # Riwayat log mutasi stok bahan
│   ├── kasir/
│   │   ├── dashboard.php        # Dashboard kasir & ringkasan penjualan
│   │   ├── penjualan.php        # POS Kasir (multi-menu & validasi stok BOM)
│   │   ├── riwayat_penjualan.php# Riwayat penjualan kasir
│   │   └── nota.php             # Struk nota transaksi siap cetak
│   ├── pemilik/
│   │   ├── dashboard.php        # Dashboard pemilik & KPI penjualan
│   │   ├── bahan.php            # CRUD Master Bahan Baku
│   │   ├── menu.php             # CRUD Master Menu Resto
│   │   ├── resep.php            # Pengaturan Resep (BOM) per Menu
│   │   ├── opname.php           # Audit Stock Opname & penyesuaian selisih
│   │   ├── laporan.php          # Laporan Rekapitulasi & Grafik Native Canvas
│   │   ├── export_excel.php     # Ekspor format spreadsheet Excel (.xls)
│   │   └── export_pdf.php       # Tampilan cetak A4 / simpan ke PDF
│   └── logout.php               # Pembersih sesi dan redirect login
├── assets/
│   ├── css/
│   │   └── style.css            # Tema Kafe Native CSS (Dark brown & warm orange)
│   └── js/
│       └── main.js              # Native JS: Chart Canvas, POS, Modal, Drawer
├── database.sql                 # Struktur database + Foreign Keys + Data Awal
└── index.php                    # Halaman Login
```
