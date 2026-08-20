<?php
// View: daftar karyawan
//
// Memasukkan header template
// require_once __DIR__ . '/../templates/header.php';
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
require_once __DIR__ . '/../../models/Karyawan.php';
$karyawanModel = new Karyawan();
// $gudangId = $_SESSION['gudang_id'] ?? null;
$karyawans = $karyawanModel->getAll();
?>
<!-- Main Content -->
        <main class="main-content">
            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Daftar Karyawan</h5>
                                <a href="index.php?page=tk" class="btn btn-light btn-sm">
                                    <i class="fas fa-plus me-1"></i>Tambah Karyawan
                                </a>
                            </div>
                            <div class="card-body">
    <?php if (!empty($karyawans)) : ?>
                            <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Gudang</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($karyawans as $k) : ?>
            <tr>
                <td><?= htmlspecialchars($k['id'] ?? '') ?></td>
                <td><?= htmlspecialchars($k['nama'] ?? '') ?></td>
                <td><?= htmlspecialchars($k['role'] ?? '') ?></td>
                <td><?= htmlspecialchars($k['gudang_id'] ?? '') ?></td>
                <td class="actions">
                    <a href="index.php?controller=karyawan&action=edit&id=<?= $k['id'] ?>" class="btn edit>index.php?controller=karyawan&action=hapus&id=<?= $k['id'] ?>" class="btn delete" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <p>Belum ada data karyawan.</p>
    <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

<?php
// Memasukkan footer template
// require_once __DIR__ . '/../templates/footer.php';
?>
