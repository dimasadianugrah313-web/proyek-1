<?php
// login.php - Halaman Login LO.LO Coffee & Resto
require_once __DIR__ . '/config/db.php';

$error = '';
$selectedRole = $_POST['role'] ?? ($_GET['role'] ?? 'staf');
$username = $_POST['username'] ?? ($selectedRole === 'pemilik' ? 'dina@lolo.id' : 'rani@lolo.id');

// Handle Login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'staf';
    $user = trim($_POST['username'] ?? '');

    if ($role === 'pemilik' || strpos($user, 'dina') !== false) {
        $_SESSION['user_role'] = 'pemilik';
        $_SESSION['user_logged_in'] = true;
    } else {
        $_SESSION['user_role'] = 'staf';
        $_SESSION['user_logged_in'] = true;
    }

    header('Location: index.php?page=dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk · LO.LO Coffee & Resto</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            500: '#be742d',
                            600: '#a85b1a',
                            700: '#9c5417',
                            800: '#7c4013',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col lg:flex-row bg-[#faf8f5] text-slate-800 antialiased selection:bg-amber-100 selection:text-amber-900">

    <!-- SISI KIRI (Branding Gelap Espresso) -->
    <div class="lg:w-5/12 bg-[#251f1a] text-[#ded7ce] p-8 sm:p-12 lg:p-16 flex flex-col justify-between min-h-screen relative overflow-hidden">
        
        <!-- Header Brand -->
        <div>
            <h1 class="text-3xl lg:text-4xl font-black tracking-tight text-white leading-none">LO.LO</h1>
            <p class="text-xs font-bold tracking-widest text-[#a39788] uppercase mt-1.5">COFFEE & RESTO · INDRAMAYU</p>
        </div>

        <!-- Konten Hero Tengah -->
        <div class="my-12 lg:my-0 space-y-6 max-w-md">
            <div>
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-[#c77a33]">
                    RUANG KERJA OPERASIONAL
                </span>
            </div>

            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-[1.15]">
                Stok terkendali.<br>
                Catatan rapi.<br>
                Usaha lebih pasti.
            </h2>

            <p class="text-sm text-[#a89d8e] leading-relaxed">
                Kelola bahan baku dan rekap penjualan dalam satu ruang kerja untuk tim LO.LO.
            </p>

            <!-- Fitur Highlight -->
            <div class="space-y-4 pt-4 text-xs font-medium text-[#c5b9aa]">
                <div class="flex items-center space-x-3.5">
                    <div class="w-8 h-8 rounded-lg bg-[#322a23] border border-[#42372e] flex items-center justify-center text-[#d88d44] shrink-0">
                        <i data-lucide="package" class="w-4 h-4"></i>
                    </div>
                    <span>Pantau bahan, satuan, dan pergerakan stok</span>
                </div>

                <div class="flex items-center space-x-3.5">
                    <div class="w-8 h-8 rounded-lg bg-[#322a23] border border-[#42372e] flex items-center justify-center text-[#d88d44] shrink-0">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </div>
                    <span>Catat kuantitas menu dari kasir lama</span>
                </div>

                <div class="flex items-center space-x-3.5">
                    <div class="w-8 h-8 rounded-lg bg-[#322a23] border border-[#42372e] flex items-center justify-center text-[#d88d44] shrink-0">
                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                    </div>
                    <span>Tinjau rekap bulanan dengan lebih jelas</span>
                </div>
            </div>
        </div>

        <!-- Footer Bawah Kiri -->
        <div class="pt-8 border-t border-[#382f27]">
        </div>
    </div>

    <!-- SISI KANAN (Formulir Login Bersih) -->
    <div class="lg:w-7/12 bg-white flex items-center justify-center p-8 sm:p-12 lg:p-16">
        <div class="max-w-md w-full">
            
            <!-- Tag Akses Internal -->
            <div>
                <span class="inline-block px-3 py-1 bg-[#fff2e2] text-[#b85c17] text-[11px] font-bold uppercase tracking-wider rounded-md border border-[#fde1c3]">
                    AKSES INTERNAL
                </span>
            </div>

            <!-- Judul -->
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-4 tracking-tight">
                Selamat datang kembali
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 mb-8">
                Masuk dengan akun operasional Anda.
            </p>

            <!-- Form -->
            <form method="POST" action="login.php" class="space-y-5">
                
                <!-- Input Username / Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Username atau email</label>
                    <input type="text" name="username" id="inputUsername" required value="<?= htmlspecialchars($username) ?>"
                           class="w-full px-4 py-3 text-xs sm:text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-[#be631c] focus:ring-1 focus:ring-[#be631c] transition-colors text-slate-900">
                </div>

                <!-- Input Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata sandi</label>
                    <input type="password" name="password" id="inputPassword" required value="password123"
                           class="w-full px-4 py-3 text-xs sm:text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-[#be631c] focus:ring-1 focus:ring-[#be631c] transition-colors text-slate-900 font-mono">
                </div>

                <!-- Pilihan Masuk Sebagai (Radio Selector Cards) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Masuk sebagai</label>
                    <div class="grid grid-cols-2 gap-3">
                        
                        <!-- Pilihan 1: Staf -->
                        <label id="cardRoleStaf" class="relative flex items-center space-x-3 p-3.5 rounded-xl border cursor-pointer transition-all <?= $selectedRole === 'staf' ? 'bg-[#fdf4e9] border-[#be631c] shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300' ?>">
                            <input type="radio" name="role" value="staf" <?= $selectedRole === 'staf' ? 'checked' : '' ?> onchange="onRoleChange('staf')" class="sr-only">
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 <?= $selectedRole === 'staf' ? 'border-[#be631c]' : 'border-slate-300' ?>">
                                <div id="dotStaf" class="w-2 h-2 rounded-full <?= $selectedRole === 'staf' ? 'bg-[#be631c]' : 'hidden' ?>"></div>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Staf</span>
                        </label>

                        <!-- Pilihan 2: Pemilik -->
                        <label id="cardRolePemilik" class="relative flex items-center space-x-3 p-3.5 rounded-xl border cursor-pointer transition-all <?= $selectedRole === 'pemilik' ? 'bg-[#fdf4e9] border-[#be631c] shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300' ?>">
                            <input type="radio" name="role" value="pemilik" <?= $selectedRole === 'pemilik' ? 'checked' : '' ?> onchange="onRoleChange('pemilik')" class="sr-only">
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 <?= $selectedRole === 'pemilik' ? 'border-[#be631c]' : 'border-slate-300' ?>">
                                <div id="dotPemilik" class="w-2 h-2 rounded-full <?= $selectedRole === 'pemilik' ? 'bg-[#be631c]' : 'hidden' ?>"></div>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Pemilik</span>
                        </label>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2">Hak akses mengikuti peran yang ditetapkan pemilik.</p>
                </div>

                <!-- Tombol Masuk -->
                <div class="pt-2">
                    <button type="submit" class="w-full bg-[#be631c] hover:bg-[#a65213] text-white py-3.5 px-6 rounded-xl font-bold text-xs sm:text-sm shadow-sm transition-all flex items-center justify-center space-x-2 group">
                        <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                        <span>Masuk</span>
                    </button>
                </div>
            </form>

            <!-- Catatan Keamanan Bawah -->
            <div class="mt-6 space-y-4">
                <div class="flex items-center space-x-2 text-xs text-slate-400">
                    <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                    <span>Gunakan akun pribadi. Jangan bagikan kata sandi Anda.</span>
                </div>

                <div class="text-xs text-slate-500 pt-2">
                    Kesulitan masuk? <a href="#" onclick="alert('Hubungi Dina Pramesti (Pemilik) untuk reset password atau pembaruan akun staf.'); return false;" class="text-slate-700 font-semibold hover:underline">Hubungi pemilik untuk reset akun.</a>
                </div>

                <div class="pt-8 border-t border-slate-100 text-center">
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Aplikasi pendamping, bukan pengganti kasir. Tidak ada pendaftaran publik.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Script Pengubah Peran Interaktif -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });

        function onRoleChange(role) {
            const cardStaf = document.getElementById('cardRoleStaf');
            const cardPemilik = document.getElementById('cardRolePemilik');
            const dotStaf = document.getElementById('dotStaf');
            const dotPemilik = document.getElementById('dotPemilik');
            const inputUser = document.getElementById('inputUsername');

            if (role === 'staf') {
                cardStaf.className = 'relative flex items-center space-x-3 p-3.5 rounded-xl border cursor-pointer transition-all bg-[#fdf4e9] border-[#be631c] shadow-xs';
                cardPemilik.className = 'relative flex items-center space-x-3 p-3.5 rounded-xl border cursor-pointer transition-all bg-white border-slate-200 hover:border-slate-300';
                dotStaf.className = 'w-2 h-2 rounded-full bg-[#be631c]';
                dotPemilik.className = 'w-2 h-2 rounded-full hidden';
                if (!inputUser.value || inputUser.value === 'dina@lolo.id') {
                    inputUser.value = 'rani@lolo.id';
                }
            } else {
                cardPemilik.className = 'relative flex items-center space-x-3 p-3.5 rounded-xl border cursor-pointer transition-all bg-[#fdf4e9] border-[#be631c] shadow-xs';
                cardStaf.className = 'relative flex items-center space-x-3 p-3.5 rounded-xl border cursor-pointer transition-all bg-white border-slate-200 hover:border-slate-300';
                dotPemilik.className = 'w-2 h-2 rounded-full bg-[#be631c]';
                dotStaf.className = 'w-2 h-2 rounded-full hidden';
                if (!inputUser.value || inputUser.value === 'rani@lolo.id') {
                    inputUser.value = 'dina@lolo.id';
                }
            }
        }
    </script>
</body>
</html>
