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
require_once __DIR__ . '/../../models/Transaksi.php';
$transaksiModel = new Transaksi();
// $gudangId = $_SESSION['gudang_id'] ?? null;
$members = $transaksiModel->getAllMember();
?>
<!-- Main Content -->
        <main class="main-content">
            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Daftar Member</h5>
                                <a href="index.php?page=tk" class="btn btn-light btn-sm">
                                    <i class="fas fa-plus me-1"></i>Tambah Member
                                </a>
                            </div>
                            <div class="card-body">
    <?php if (!empty($members)) : ?>
                            <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Poin</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $m) : ?>
            <tr>
                <td><?= htmlspecialchars($m['id'] ?? '') ?></td>
                <td><?= htmlspecialchars($m['nama_member'] ?? '') ?></td>
                <td><?= htmlspecialchars($m['telepon'] ?? '') ?></td>
                <td><?= htmlspecialchars($m['poin'] ?? '') ?></td>
                <td class="actions">
                    <a href="index.php?controller=membership&action=edit&id=<?= $m['id'] ?>" class="btn edit">Edit</a>
                    <a href="index.php?controller=membership&action=hapus&id=<?= $m['id'] ?>" class="btn delete" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <p>Belum ada data member.</p>
    <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
