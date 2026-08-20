<?php
// View: daftar barang
//
// Memasukkan sidebar
require_once __DIR__ . '/../templates/sidebar.php';

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
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                
                <!-- Notifikasi Status -->
                <?php if (isset($_SESSION['barang_success'])) : ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['barang_success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['barang_success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['barang_error'])) : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['barang_error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['barang_error']); ?>
                <?php endif; ?>

                <!-- Card Daftar Barang -->
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-box-open me-2"></i>Daftar Barang</h5>
                        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['Admin', 'Manajer Gudang'])) : ?>
                            <a href="index.php?page=tb" class="btn btn-light btn-sm fw-bold">
                                <i class="fas fa-plus me-1"></i>Tambah Barang
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($barangs)) : ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Barcode</th>
                                            <th>Nama Barang</th>
                                            <th>Brand / Tipe</th>
                                            <th>Supplier</th>
                                            <th class="text-end">Harga Beli</th>
                                            <th class="text-end">Harga Jual</th>
                                            <th class="text-center">Stok</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($barangs as $b) : ?>
                                            <tr>
                                                <td><?= htmlspecialchars($b['id'] ?? '') ?></td>
                                                <td>
                                                    <span class="badge bg-secondary font-monospace py-2 px-3">
                                                        <i class="fa-solid fa-barcode me-1"></i><?= htmlspecialchars($b['barcode'] ?? '') ?>
                                                    </span>
                                                </td>
                                                <td class="fw-bold"><?= htmlspecialchars($b['nama_barang'] ?? '') ?></td>
                                                <td>
                                                    <span class="text-muted">
                                                        <?= htmlspecialchars($b['brand'] ?? '-') ?> / <?= htmlspecialchars($b['tipe'] ?? '-') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <i class="fa-solid fa-truck-field text-primary me-1"></i>
                                                    <?= htmlspecialchars($b['nama_supplier'] ?? 'Tanpa Supplier') ?>
                                                </td>
                                                <td class="text-end">
                                                    Rp <?= number_format($b['harga_beli'] ?? 0, 0, ',', '.') ?>
                                                </td>
                                                <td class="text-end text-success fw-bold">
                                                    Rp <?= number_format($b['harga_jual'] ?? 0, 0, ',', '.') ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $stok = isset($b['stok_aktual']) ? intval($b['stok_aktual']) : intval($b['total_stok'] ?? 0);
                                            $stokMinimal = intval($b['stok_minimal'] ?? 0);
                                            if ($stok <= $stokMinimal) {
                                                $badgeClass = 'bg-danger';
                                            } elseif ($stok <= $stokMinimal + 5) {
                                                $badgeClass = 'bg-warning text-dark';
                                            } else {
                                                $badgeClass = 'bg-success';
                                            }
                                            ?>
                                                    <span class="badge <?= $badgeClass ?> px-3 py-2" title="Stok Minimal: <?= $stokMinimal ?>">
                                                        <?= $stok ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="index.php?page=tb&id=<?= $b['id'] ?>" class="btn btn-outline-warning" title="Edit Barang">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <a href="index.php?page=barang&action=delete&id=<?= $b['id'] ?>" 
                                                           class="btn btn-outline-danger" 
                                                           onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?')" 
                                                           title="Hapus Barang">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fa-3x mb-3 text-secondary"></i>
                                <p class="mb-0">Belum ada data barang.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
