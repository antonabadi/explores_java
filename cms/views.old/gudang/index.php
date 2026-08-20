<?php
// View: daftar member
//
require_once __DIR__ . '/../templates/sidebar.php';

// Memastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi controller dan handle action jika ada
require_once __DIR__ . '/../../controllers/KaryawanController.php';
$action = $_GET['action'] ?? '';
if ($action === 'delete') {
    $controller = new KaryawanController();
    $controller->handleDeleteKaryawan();
}

// Ambil data barang
require_once __DIR__ . '/../../models/Barang.php';
$barangModel = new Barang();
// $gudangId = $_SESSION['gudang_id'] ?? null;
$gudangs = $barangModel->getAllGudang();
?>
<!-- Main Content -->
        <main class="main-content">
            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Daftar Gudang</h5>
                                <a href="index.php?page=gudang" class="btn btn-light btn-sm">
                                    <i class="fas fa-plus me-1"></i>Tambah Gudang
                                </a>
                            </div>
                            <div class="card-body">
    <?php if (!empty($gudangs)) : ?>
                            <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Lokasi</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gudangs as $g) : ?>
            <tr>
                <td><?= htmlspecialchars($g['id'] ?? '') ?></td>
                <td><?= htmlspecialchars($g['nama_gudang'] ?? '') ?></td>
                <td><?= htmlspecialchars($g['lokasi'] ?? '') ?></td>
                <td class="actions">
                    <a href="index.php?controller=gudang&action=edit&id=<?= $g['id'] ?>" class="btn edit">Edit</a>
                    <a href="index.php?controller=gudang&action=hapus&id=<?= $g['id'] ?>" class="btn delete" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <p>Belum ada data gudang.</p>
    <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
