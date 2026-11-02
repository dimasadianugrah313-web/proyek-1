# LO.LO Coffee & Resto - Aplikasi Pendamping Kasir & Manajemen Operasional

Aplikasi web manajemen operasional F&B modern yang dirancang khusus sebagai pendamping kasir manual untuk kedai kopi dan restoran **LO.LO Coffee & Resto (Indramayu · Sejak 2019)**.

Sistem ini merealisasikan secara presisi seluruh desain antarmuka dan alur operasional:
1. **Ringkasan Operasional (Dashboard Pemilik - Dina Pramesti)**: Metrik KPI pendapatan, HPP, pengeluaran, laba bersih, grafik tren omzet bulanan, rekonsiliasi kasir, peringatan stok menipis, dan jejak audit aktivitas terbaru.
2. **Dashboard Staf (Rani Aprillia)**: Pencatatan shift harian, aksi cepat (input penjualan, stok masuk, stok keluar, pengeluaran), peringatan selisih kasir, ketersediaan bahan, serta panduan SOP.
3. **Master Bahan Baku**: Kelola satuan dasar (gram, mililiter), harga bahan, batas stok minimum, konversi pembelian (kg ke gram), status aman/menipis, dan form edit master.
4. **Kartu Stok / Ledger Pergerakan**: Telusuri pergerakan stok berjalan per bahan (Saldo Awal, Stok Masuk SM-xxx, Pengurangan Resep BOM PJ-xxx, Pemakaian Manual SK-xxx, Selisih Opname OP-xxx), lengkap dengan formula pemeriksaan saldo serta cetak dan ekspor CSV.
5. **Laporan & Rekapitulasi Bulanan**: Laba Kotor, Laba Bersih, rincian per kategori menu (Minuman, Makanan, Cemilan), rincian pos pengeluaran operasional, dan selisih stock opname.
6. **Menu & Resep (Bill of Materials - BOM)**: Formulasi takaran bahan baku per menu untuk kalkulasi HPP otomatis dan pemotongan stok otomatis saat penjualan dikonfirmasi.
7. **Stok Masuk, Stok Keluar, Penjualan & Stock Opname**: Formulir interaktif lengkap dengan kalkulator otomatis dan pembaruan saldo real-time.

---

## 🚀 Cara Menjalankan Aplikasi

Aplikasi dibuat menggunakan **PHP murni (Vanilla PHP)** dengan basis data **SQLite** bawaan yang langsung terisi data contoh (auto-seeded). Tidak membutuhkan instalasi composer/node tambahan!

### Opsi 1: Lewat Laragon (Rekomendasi)
Karena proyek ini berada di `C:\laragon\www\proyek 1`:
1. Buka aplikasi **Laragon** dan pastikan service Apache/Nginx telah berjalan (**Start All**).
2. Buka browser dan kunjungi salah satu URL berikut:
   - **`http://localhost/proyek%201/`**
   - Atau jika Virtual Host aktif: **`http://proyek-1.test`**

### Opsi 2: Lewat PHP Built-in Server
Jalankan perintah berikut di terminal / PowerShell:
```bash
cd "c:\laragon\www\proyek 1"
php -S localhost:8000
```
Lalu buka browser di:
**`http://localhost:8000`**

---

## 👥 Pengalihan Peran (Ruang Kerja)
Anda dapat menguji kedua peran akun langsung melalui tombol **"Ganti"** pada kotak *Ruang Kerja* di sidebar kiri atau lewat modal switcher:
- **Dina Pramesti (Pemilik)**: Memiliki hak penuh melihat Dashboard Laba/Rugi, Master Bahan Baku, Menu & Resep, Laporan Bulanan, Kartu Stok, dan Stock Opname.
- **Rani Aprillia (Staf)**: Dikhususkan untuk input operasional harian (Penjualan, Stok Masuk, Stok Keluar, Pengeluaran) tanpa melihat rahasia master laba/rugi.

---

## 📁 Struktur Berkas

```
proyek 1/
├── index.php                 # Router utama dan pengatur layout
├── database.sqlite           # Basis data SQLite lokal (auto-created & auto-seeded)
├── config/
│   └── db.php                # Koneksi PDO SQLite, skema tabel, dan seeder data contoh
├── api/
│   └── api.php               # Endpoint pemrosesan transaksi & AJAX
├── assets/
│   ├── css/                  # Styling & konfigurasi print
│   └── js/
│       └── app.js            # Interaktivitas UI, kalkulasi, dialog modal, dan ekspor CSV
├── views/
│   ├── layout/
│   │   ├── header.php        # Header HTML, Tailwind CSS, CDN icons & chart
│   │   ├── sidebar.php       # Navigasi samping dinamis sesuai peran
│   │   ├── topbar.php        # Breadcrumbs, profil pengguna, dan notifikasi
│   │   └── footer.php        # Skrip inisialisasi
│   ├── dashboard_pemilik.php # Ringkasan operasional Pemilik (Image 2)
│   ├── dashboard_staf.php    # Dashboard operasional Staf (Image 3)
│   ├── bahan_baku.php        # Master data bahan baku (Image 1)
│   ├── kartu_stok.php        # Ledger kartu stok pergerakan bahan (Image 4)
│   ├── laporan_bulanan.php   # Laporan bulanan & rekap laba (Image 5)
│   ├── menu_resep.php        # Formulasi resep BOM & estimasi HPP
│   ├── stok_masuk.php        # Form penerimaan bahan & konversi kg/liter
│   ├── stok_keluar.php       # Form bahan terbuang / tumpah
│   ├── penjualan.php         # Rekap penjualan & rekonsiliasi kasir
│   ├── pengeluaran.php       # Beban operasional (gaji, sewa, listrik, dll.)
│   └── stock_opname.php      # Penghitungan fisik & penyesuaian selisih
```

---

## 🔄 Mengembalikan Data ke Data Contoh Awal
Jika Anda menambahkan, mengubah, atau menghapus data saat mencoba fitur dan ingin mengembalikannya persis seperti di tangkapan layar, klik ikon **Putar Balik (Rotate CCW)** di samping badge *"Data contoh"* pada bar navigasi atas.
