<?php
/**
 * KASIR
 *
 */

require_once __DIR__ . "/../templates/sidebar.php";

// Memastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi controller dan handle action jika ada
require_once __DIR__ . '/../../controllers/BarangController.php';
$action = $_GET['action'] ?? '';
if ($action === 'delete') {
    $controller = new BarangController();
    $controller->handleDeleteBarang();
}

// Ambil data barang
require_once __DIR__ . '/../../models/Barang.php';
$barangModel = new Barang();
$gudangId = $_SESSION['gudang_id'] ?? null;
$barangs = $barangModel->getAll($gudangId);
// var_dump($gudangId);
?>
<div class="container-fluid mt-3" id="pos-cashier-container">
        <div class="row">

            <!-- BAGIAN KIRI: DAFTAR PRODUK -->
            <div class="col-md-7 col-lg-8">
                <!-- Bar Pencarian & Kategori -->
                <div class="row g-2 mb-3">
                    <div class="col-md-6 position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input id="pos-search" type="text" class="form-control" placeholder="Cari produk atau scan barcode...">
                        </div>
                        <div id="search-dropdown" class="search-results-dropdown" style="display: none;"></div>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" name="tipe" onchange="this.form.submit()">
                            <option value="">Semua Tipe</option>
                            <?php
                            $tipes = $barangModel->getTipeByGudang($gudangId);
                            foreach ($tipes as $t) {
                                echo "<option value='" . htmlspecialchars($t) . "'>" . htmlspecialchars($t) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Grid Produk (Scrollable) -->
                <div class="product-container pe-2">
                    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-lg-4 g-3">

                        <?php foreach ($barangs as $b): ?>
                                                <div class="col">
                                                    <div class="card h-100 product-card shadow-sm">
                                                        <div class="card-body text-center d-flex flex-column justify-content-between">
                                                            <h6 class="card-title text-truncate"><?php echo htmlspecialchars($b['nama_barang']); ?></h6>
                                                            <p class="card-text text-primary fw-bold mb-0">Rp <?php echo number_format($b['harga_jual'], 0, ',', '.'); ?></p>
                                                            <small class="text-muted block mb-2">Stok: <?php echo isset($b['stok_aktual']) ? $b['stok_aktual'] : (isset($b['total_stok']) ? $b['total_stok'] : 0); ?></small>
                                                            <button class="btn btn-sm btn-outline-primary w-100" onclick="addToCart({
                                                                type: 'barang',
                                                                id: <?php echo intval($b['id']); ?>,
                                                                barcode: '<?php echo htmlspecialchars($b['barcode'], ENT_QUOTES, 'UTF-8'); ?>',
                                                                nama: '<?php echo htmlspecialchars($b['nama_barang'], ENT_QUOTES, 'UTF-8'); ?>',
                                                                harga: <?php echo floatval($b['harga_jual']); ?>,
                                                                stok: <?php echo intval(isset($b['stok_aktual']) ? $b['stok_aktual'] : (isset($b['total_stok']) ? $b['total_stok'] : 0)); ?>
                                                            })"><i class="bi bi-plus-lg"></i> Tambah</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- BAGIAN KANAN: KERANJANG BELANJA & PEMBAYARAN -->
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-sm h-100" style="height: calc(100vh - 100px) !important; display: flex; flex-direction: column;">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-cart3 me-2"></i>Keranjang</span>
                        <button class="btn btn-sm btn-link text-danger p-0 text-decoration-none" onclick="cart = []; renderCart();">Bersihkan</button>
                    </div>

                    <!-- Daftar Item di Keranjang (Scrollable) -->
                    <div class="card-body p-0 overflow-auto" style="flex-grow: 1;">
                        <table class="table table-hover align-middle mb-0 pos-table">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th>Harga</th>
                                    <th style="width: 100px;">Qty</th>
                                    <th>Total</th>
                                    <th style="width: 50px; text-align: center;"></th>
                                </tr>
                            </thead>
                            <tbody id="cart-table-body">
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem;" data-i18n="empty_cart">
                                        Keranjang kosong
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Input Detail Transaksi & Member -->
                    <div class="card-body border-top bg-light p-3">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="form-group-sm">
                                    <label for="member-select">Member</label>
                                    <select id="member-select" class="form-select select-sm">
                                        <option value="">Non-Member</option>
                                        <?php
                                        // Query active members
                                        $db = Database::getConnection();
                                        $stmtMember = $db->query("SELECT * FROM member WHERE status_aktif = TRUE ORDER BY nama_member ASC");
                                        $members = $stmtMember->fetchAll();
                                        foreach ($members as $m) {
                                            echo "<option value='" . htmlspecialchars($m['id']) . "'>" . htmlspecialchars($m['nama_member']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group-sm">
                                    <label for="discount-input">Diskon (Rp)</label>
                                    <input id="discount-input" type="number" class="form-control input-sm" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group-sm">
                                    <label for="payment-method">Metode Bayar</label>
                                    <select id="payment-method" class="form-select select-sm">
                                        <option value="Tunai">Tunai</option>
                                        <option value="Debit">Debit</option>
                                        <option value="Kredit">Kredit</option>
                                        <option value="QRIS">QRIS</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group-sm">
                                    <label for="cash-input">Bayar Tunai (Rp)</label>
                                    <input id="cash-input" type="number" class="form-control input-sm" placeholder="Uang diterima...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan Total & Tombol Bayar -->
                    <div class="card-footer bg-white p-3">
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span id="sum-subtotal">Rp 0</span>
                            </div>
                            <div class="summary-row">
                                <span>Diskon</span>
                                <span id="sum-discount">Rp 0</span>
                            </div>
                            <div class="summary-row">
                                <span>Kembalian</span>
                                <span id="sum-change">Rp 0</span>
                            </div>
                            <div class="summary-row total-row">
                                <span>Total</span>
                                <span id="sum-total">Rp 0</span>
                            </div>
                        </div>
                        <div class="payment-section mt-3">
                            <button id="btn-checkout" class="btn btn-primary w-100 btn-checkout"><i class="bi bi-cash"></i> Bayar / Selesai</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

<!-- Receipt Modal -->
<div id="receipt-modal" class="modal-overlay">
    <div class="modal-container" id="print-receipt-modal">
        <div class="modal-header">
            <h5 class="modal-title">Struk Pembayaran</h5>
            <button type="button" class="btn-close-modal" id="btn-close-receipt">&times;</button>
        </div>
        <div class="modal-body">
            <div id="receipt-print-content">
                <!-- Konten Struk akan dimasukkan oleh JS -->
            </div>
            <div class="mt-4 text-end">
                <button type="button" class="btn btn-secondary me-2" id="btn-close-receipt-secondary" onclick="document.getElementById('receipt-modal').classList.remove('active')">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-print-receipt"><i class="bi bi-printer"></i> Cetak Struk</button>
            </div>
        </div>
    </div>
</div>
