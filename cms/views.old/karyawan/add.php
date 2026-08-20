<?php
/**
 * View: Tambah Karyawan Baru
 *
 * Halaman ini menampilkan formulir untuk menambah data karyawan baru.
 */

// Memasukkan header template
// require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/sidebar.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Tambah Karyawan Baru</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['karyawan_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['karyawan_error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['karyawan_error']); ?>
                    <?php endif; ?>

                    <form action="index.php?page=karyawan_save" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="Admin">Admin</option>
                                    <option value="Kasir">Kasir</option>
                                    <option value="Manager">Manager</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gudang_id" class="form-label">Gudang</label>
                                <select class="form-select" id="gudang_id" name="gudang_id">
                                    <option value="">-- Pilih Gudang (Opsional) --</option>
                                    <!-- Data gudang bisa diambil dari database jika diperlukan -->
                                    <?php
                                    require_once __DIR__ . '/../../models/Barang.php';
                                    $barangModel = new Barang();
                                    $gudangs = $barangModel->getAllGudang();
                                    foreach ($gudangs as $g) {
                                        echo "<option value='" . $g['id'] . "'>" . htmlspecialchars($g['nama_gudang']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Password harus diisi untuk karyawan baru.</div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?page=karyawan" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Karyawan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Memasukkan footer template
require_once __DIR__ . '/../templates/footer.php';
?>
