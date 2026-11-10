<?php
// ============================================================================
// TRANSAKSI PENJUALAN (KASIR) - LO.LO COFFEE & RESTO INDRAMAYU
// ============================================================================

$page_title = 'Input Transaksi Penjualan';
require_once __DIR__ . '/../../includes/auth.php';
check_auth(['kasir']); // Wajib role kasir

require_once __DIR__ . '/../../classes/Menu.php';
require_once __DIR__ . '/../../classes/Penjualan.php';

$menuModel = new Menu();
$penjualanModel = new Penjualan();
$daftarMenu = $menuModel->getAll();

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_ids = $_POST['menu_id'] ?? [];
    $porsis = $_POST['porsi'] ?? [];

    $items = [];
    if (is_array($menu_ids) && is_array($porsis)) {
        for ($i = 0; $i < count($menu_ids); $i++) {
            $id = (int)$menu_ids[$i];
            $qty = (int)$porsis[$i];
            if ($id > 0 && $qty > 0) {
                $items[] = [
                    'id_menu' => $id,
                    'porsi' => $qty
                ];
            }
        }
    }

    if (empty($items)) {
        $error_msg = 'Form data tidak boleh kosong. Silakan pilih minimal 1 menu.';
    } else {
        $hasil = $penjualanModel->prosesTransaksi(current_user_id(), $items);
        if ($hasil['success']) {
            set_flash('success', $hasil['message']);
            header("Location: " . BASE_URL . "/pages/kasir/nota.php?id=" . $hasil['id_penjualan'] . "&new=1");
            exit;
        } else {
            // Tampilkan pesan kegagalan (misal "Stok bahan baku tidak mencukupi")
            $error_msg = $hasil['message'];
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <div>
            <strong>Transaksi Dibatalkan:</strong> <?= htmlspecialchars($error_msg) ?>
        </div>
    </div>
<?php endif; ?>

<div class="pos-layout">
    <!-- PANEL KIRI: DAFTAR MENU -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Pilih Menu Kopi & Resto</div>
                <input type="text" id="searchMenuInput" placeholder="Cari nama menu..." 
                       class="form-control" style="max-width: 200px; padding: 6px 10px;" onkeyup="filterMenuCards()">
            </div>
            <div class="card-body">
                <div class="menu-card-grid" id="menuGrid">
                    <?php foreach ($daftarMenu as $m): ?>
                        <div class="menu-item-box" data-name="<?= strtolower(htmlspecialchars($m['nama_menu'])) ?>">
                            <div>
                                <div class="menu-item-title"><?= htmlspecialchars($m['nama_menu']) ?></div>
                                <div class="menu-item-price"><?= format_rupiah($m['harga']) ?></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline" 
                                    onclick="addToCart(<?= $m['id_menu'] ?>, '<?= addslashes(htmlspecialchars($m['nama_menu'])) ?>', <?= (float)$m['harga'] ?>)">
                                + Tambah
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PANEL KANAN: KERANJANG PESANAN KASIR -->
    <div>
        <div class="card" style="position: sticky; top: 80px;">
            <div class="card-header" style="background: #FDFBF9;">
                <div class="card-title">Keranjang Pesanan</div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="clearCart()">Kosongkan</button>
            </div>
            <form method="POST" action="" id="formCheckout">
                <div class="card-body" style="padding: 12px;">
                    <div class="table-responsive" style="max-height: 340px; overflow-y:auto;">
                        <table class="table cart-table" id="cartTable">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th style="width: 80px; text-align:center;">Porsi</th>
                                    <th style="text-align:right;">Subtotal</th>
                                    <th style="width: 40px; text-align:center;">&times;</th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody">
                                <tr id="emptyCartRow">
                                    <td colspan="4" style="text-align:center; padding: 30px 10px; color: var(--text-muted);">
                                        Keranjang masih kosong.<br>Klik tombol <strong>+ Tambah</strong> pada menu di samping.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="total-display-box">
                        <div class="label">Total Pembayaran:</div>
                        <div class="amount" id="lblTotalBayar">Rp 0</div>
                    </div>
                </div>

                <div class="card-footer" style="padding: 16px;">
                    <button type="submit" class="btn btn-primary btn-lg" id="btnSubmitPesanan" style="width: 100%;" disabled>
                        Proses Transaksi Penjualan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// State Keranjang Belanja Native
let cart = {};

function addToCart(id, name, price) {
    if (cart[id]) {
        cart[id].porsi += 1;
    } else {
        cart[id] = {
            id: id,
            name: name,
            price: price,
            porsi: 1
        };
    }
    renderCart();
}

function updateQty(id, newQty) {
    const qty = parseInt(newQty);
    if (isNaN(qty) || qty <= 0) {
        delete cart[id];
    } else {
        cart[id].porsi = qty;
    }
    renderCart();
}

function removeFromCart(id) {
    delete cart[id];
    renderCart();
}

function clearCart() {
    cart = {};
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cartTableBody');
    const btnSubmit = document.getElementById('btnSubmitPesanan');
    const lblTotal = document.getElementById('lblTotalBayar');
    
    tbody.innerHTML = '';
    const keys = Object.keys(cart);

    if (keys.length === 0) {
        tbody.innerHTML = `
            <tr id="emptyCartRow">
                <td colspan="4" style="text-align:center; padding: 30px 10px; color: var(--text-muted);">
                    Keranjang masih kosong.<br>Klik tombol <strong>+ Tambah</strong> pada menu di samping.
                </td>
            </tr>
        `;
        lblTotal.textContent = 'Rp 0';
        btnSubmit.disabled = true;
        return;
    }

    let grandTotal = 0;

    keys.forEach(id => {
        const item = cart[id];
        const subtotal = item.price * item.porsi;
        grandTotal += subtotal;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <strong>${item.name}</strong><br>
                <small style="color:var(--text-muted);">@ Rp ${item.price.toLocaleString('id-ID')}</small>
                <input type="hidden" name="menu_id[]" value="${item.id}">
            </td>
            <td style="text-align:center;">
                <input type="number" name="porsi[]" value="${item.porsi}" min="1" 
                       class="cart-qty-input" onchange="updateQty(${item.id}, this.value)">
            </td>
            <td style="text-align:right; font-weight:700;">
                Rp ${subtotal.toLocaleString('id-ID')}
            </td>
            <td style="text-align:center;">
                <button type="button" class="btn btn-sm btn-danger" style="padding:2px 7px; border-radius:50%;" 
                        onclick="removeFromCart(${item.id})">&times;</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    lblTotal.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
    btnSubmit.disabled = false;
}

function filterMenuCards() {
    const query = document.getElementById('searchMenuInput').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.menu-item-box');
    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        card.style.display = name.includes(query) ? 'flex' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
