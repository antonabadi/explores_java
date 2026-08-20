<?php
// views/templates/sidebar.php
$activePage = $_GET['page'] ?? 'dashboard';
$userRole = $_SESSION['role'] ?? '';
$userNama = $_SESSION['nama'] ?? '';
$userGudang = $_SESSION['nama_gudang'] ?? 'Tanpa Cabang';
?>
<aside class="sidebar">
    <div class="brand-section">
        <div class="brand-logo">P</div>
        <span class="brand-name">POS Demo</span>
    </div>

    <ul class="nav-menu">
        <li class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <a href="index.php?page=dashboard">
                <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
                <span data-i18n="dashboard">Dasbor</span>
            </a>
        </li>

        <?php if (in_array($userRole, ['Admin', 'Kasir'])): ?>
        <li class="nav-item <?= $activePage === 'transaksi' ? 'active' : '' ?>">
            <a href="index.php?page=transaksi">
                <span class="nav-icon"><i class="fa-solid fa-cash-register"></i></span>
                <span data-i18n="transaction">Transaksi Penjualan</span>
            </a>
        </li>
        <li class="nav-item <?= $activePage === 'member' ? 'active' : '' ?>">
            <a href="index.php?page=member">
                <span class="nav-icon"><i class="fa-solid fa-id-card"></i></span>
                <span data-i18n="member">Member</span>
            </a>
        </li>
        <li class="nav-item <?= $activePage === 'invoice' ? 'active' : '' ?>">
            <a href="index.php?page=invoice">
                <span class="nav-icon"><i class="fa-solid fa-receipt"></i></span>
                <span data-i18n="invoice">Invoice</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (in_array($userRole, ['Admin', 'Manajer Gudang'])): ?>
        <li class="nav-item <?= $activePage === 'barang' ? 'active' : '' ?>">
            <a href="index.php?page=barang">
                <span class="nav-icon"><i class="fa-solid fa-box-open"></i></span>
                <span data-i18n="goods_management">Manajemen Barang</span>
            </a>
        </li>
        <li class="nav-item <?= $activePage === 'bundling' ? 'active' : '' ?>">
            <a href="index.php?page=bundling">
                <span class="nav-icon"><i class="fa-solid fa-cubes"></i></span>
                <span data-i18n="bundling">Paket Bundling</span>
            </a>
        </li>
        <li class="nav-item <?= $activePage === 'supplier' ? 'active' : '' ?>">
            <a href="index.php?page=supplier">
                <span class="nav-icon"><i class="fa-solid fa-truck-field"></i></span>
                <span data-i18n="supplier">Supplier</span>
            </a>
        </li>
        <li class="nav-item <?= $activePage === 'gudang' ? 'active' : '' ?>">
            <a href="index.php?page=gudang">
                <span class="nav-icon"><i class="fa-solid fa-warehouse"></i></span>
                <span data-i18n="warehouse">Gudang</span>
            </a>
        </li>
        <li class="nav-item <?= $activePage === 'transfer' ? 'active' : '' ?>">
            <a href="index.php?page=transfer">
                <span class="nav-icon"><i class="fa-solid fa-right-left"></i></span>
                <span data-i18n="stock_transfer">Mutasi Barang</span>
            </a>
        </li>
        <li class="nav-item <?= $activePage === 'opname' ? 'active' : '' ?>">
            <a href="index.php?page=opname">
                <span class="nav-icon"><i class="fa-solid fa-clipboard-check"></i></span>
                <span data-i18n="stock_opname">Stok Opname</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if ($userRole === 'Admin'): ?>
        <li class="nav-item <?= $activePage === 'karyawan' ? 'active' : '' ?>">
            <a href="index.php?page=karyawan">
                <span class="nav-icon"><i class="fa-solid fa-users-gear"></i></span>
                <span data-i18n="employee_management">Manajemen Karyawan</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info-panel">
            <div class="user-avatar">
                <?= strtoupper(substr($userNama, 0, 1)) ?>
            </div>
            <div class="user-details">
                <span class="user-name"><?= htmlspecialchars($userNama) ?></span>
                <span class="user-role"><?= htmlspecialchars($userRole) ?> (<?= htmlspecialchars($userGudang) ?>)</span>
            </div>
        </div>

        <div class="ui-controls">
            <button type="button" id="theme-toggle" class="btn-control">
                <span class="nav-icon"><i class="fa-solid fa-circle-half-stroke"></i></span>
                <span data-i18n="theme_light">Mode Terang</span>
            </button>

            <select id="lang-select" class="btn-control" style="max-width: 90px; text-align-last: center;">
                <option value="id">ID 🇮🇩</option>
                <option value="en">EN 🇬🇧</option>
            </select>
        </div>

        <a href="index.php?page=logout" class="btn-control" style="background-color: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: var(--color-danger); text-align: center; justify-content: center;">
            <i class="fa-solid fa-right-from-bracket"></i> <span data-i18n="logout" style="margin-left: 0.25rem;">Keluar</span>
        </a>
    </div>
</aside>
