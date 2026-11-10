// ============================================================================
// LO.LO COFFEE & RESTO INDRAMAYU - JAVASCRIPT NATIVE
// Pure JS (Tanpa Library/Framework)
// ============================================================================

document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi Mobile Sidebar Toggle
    initSidebar();

    // 2. Inisialisasi Modal Dialog Global
    initModals();

    // 3. Inisialisasi Quick Account Fill (pada halaman login)
    initQuickLogin();
});

// ----------------------------------------------------------------------------
// 1. MOBILE SIDEBAR
// ----------------------------------------------------------------------------
function initSidebar() {
    const toggleBtn = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }
}

// ----------------------------------------------------------------------------
// 2. MODAL SYSTEM (LOGOUT & GENERAL)
// ----------------------------------------------------------------------------
function initModals() {
    // Tombol pembuka modal logout
    const logoutTriggers = document.querySelectorAll('[data-trigger="modal-logout"]');
    const logoutModal = document.getElementById('modalLogout');

    if (logoutModal) {
        logoutTriggers.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                openModal('modalLogout');
            });
        });
    }

    // Tombol penutup modal (Batal / tombol X)
    const closeButtons = document.querySelectorAll('[data-dismiss="modal"]');
    closeButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const modal = btn.closest('.modal-overlay');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });

    // Tutup saat klik di luar kotak modal
    window.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeModal(e.target.id);
        }
    });

    // Tutup modal dengan tombol Escape
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal-overlay.active');
            openModals.forEach(function (m) {
                closeModal(m.id);
            });
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// ----------------------------------------------------------------------------
// 3. QUICK LOGIN HELPER
// ----------------------------------------------------------------------------
function initQuickLogin() {
    const pills = document.querySelectorAll('.account-pill');
    const userInput = document.getElementById('inputUsername');
    const passInput = document.getElementById('inputPassword');

    if (pills.length && userInput && passInput) {
        pills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                const u = pill.getAttribute('data-username');
                const p = pill.getAttribute('data-password');
                userInput.value = u;
                passInput.value = p;
                userInput.focus();
            });
        });
    }
}

// ----------------------------------------------------------------------------
// 4. CHART GENERATOR NATIVE (CANVAS HTML5 TANPA LIBRARY)
// Digunakan pada Laporan Rekapitulasi Penjualan
// ----------------------------------------------------------------------------
function renderSalesBarChart(canvasId, labels, dataValues, chartTitle) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    
    // Sesuaikan resolusi layar retina/high-DPI
    const width = canvas.parentElement.clientWidth;
    const height = 240;
    canvas.width = width * dpr;
    canvas.height = height * dpr;
    canvas.style.width = width + 'px';
    canvas.style.height = height + 'px';
    ctx.scale(dpr, dpr);

    ctx.clearRect(0, 0, width, height);

    if (!dataValues || dataValues.length === 0) {
        ctx.fillStyle = '#796658';
        ctx.font = '14px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Tidak ada data penjualan untuk ditampilkan pada grafik.', width / 2, height / 2);
        return;
    }

    const padding = { top: 30, right: 20, bottom: 40, left: 70 };
    const chartW = width - padding.left - padding.right;
    const chartH = height - padding.top - padding.bottom;

    const maxVal = Math.max(...dataValues, 100000);
    // Bulatkan max ke atas
    const niceMax = Math.ceil(maxVal / 50000) * 50000;

    // Gambar Garis Horizontal Grid
    const gridLines = 4;
    ctx.strokeStyle = '#E2D9D0';
    ctx.lineWidth = 1;
    ctx.fillStyle = '#796658';
    ctx.font = '11px sans-serif';
    ctx.textAlign = 'right';

    for (let i = 0; i <= gridLines; i++) {
        const val = (niceMax / gridLines) * i;
        const y = padding.top + chartH - (chartH / gridLines) * i;

        ctx.beginPath();
        ctx.moveTo(padding.left, y);
        ctx.lineTo(width - padding.right, y);
        ctx.stroke();

        // Label Rupiah singkatan (K / Jt)
        let labelRp = 'Rp 0';
        if (val >= 1000000) {
            labelRp = 'Rp ' + (val / 1000000).toFixed(1) + 'Jt';
        } else if (val > 0) {
            labelRp = 'Rp ' + (val / 1000).toFixed(0) + 'K';
        }
        ctx.fillText(labelRp, padding.left - 10, y + 4);
    }

    // Gambar Bar Batang
    const barCount = dataValues.length;
    const gap = 14;
    const totalGap = gap * (barCount + 1);
    const barWidth = Math.max(12, Math.min(48, (chartW - totalGap) / barCount));

    dataValues.forEach(function (val, idx) {
        const barH = (val / niceMax) * chartH;
        const x = padding.left + gap + idx * (barWidth + gap);
        const y = padding.top + chartH - barH;

        // Gradient Cokelat Oranye
        const grad = ctx.createLinearGradient(x, y, x, y + barH);
        grad.addColorStop(0, '#D97736');
        grad.addColorStop(1, '#8D6E63');

        ctx.fillStyle = grad;
        // Rounded bar top
        const r = Math.min(4, barWidth / 2);
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + barWidth - r, y);
        ctx.quadraticCurveTo(x + barWidth, y, x + barWidth, y + r);
        ctx.lineTo(x + barWidth, y + barH);
        ctx.lineTo(x, y + barH);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.closePath();
        ctx.fill();

        // Label X Axis (Tanggal / Kategori)
        ctx.fillStyle = '#2B1D16';
        ctx.font = '11px sans-serif';
        ctx.textAlign = 'center';
        const labelText = labels[idx] || '';
        // Potong jika terlalu panjang
        const shortLabel = labelText.length > 10 ? labelText.substring(5) : labelText;
        ctx.fillText(shortLabel, x + barWidth / 2, height - padding.bottom + 16);
    });
}
