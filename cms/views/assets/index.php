<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Asset.php';

$assetModel = new Asset();
$pdo = db();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $asset = $assetModel->find($id);
        if ($asset) {
            if (str_starts_with($asset['file_path'], 'assets/images/')) {
                $fullPath = __DIR__ . '/../../../' . $asset['file_path'];
                if (file_exists($fullPath) && is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }
            $assetModel->delete($id);
            setFlash('success', 'Asset gambar berhasil dihapus.');
        } else {
            setFlash('danger', 'Asset tidak ditemukan.');
        }
        redirect('dashboard.php?page=assets');
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $altText = trim($_POST['alt_text'] ?? '');
        $category = trim($_POST['category'] ?? 'general');

        if ($id > 0 && $title !== '') {
            $assetModel->update($id, [
                'title'    => $title,
                'alt_text' => $altText ?: $title,
                'category' => $category,
            ]);
            setFlash('success', 'Metadata asset berhasil diperbarui.');
        } else {
            setFlash('danger', 'Judul asset wajib diisi.');
        }
        redirect('dashboard.php?page=assets');
    }

    if ($action === 'sync') {
        $synced = $assetModel->syncExistingImages();
        setFlash('success', "Berhasil menyinkronkan {$synced} gambar dari direktori assets/images/.");
        redirect('dashboard.php?page=assets');
    }
}

// Query parameters
$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? 'all');
$sort = trim($_GET['sort'] ?? 'newest');
$viewMode = $_GET['view'] ?? 'grid';

$assets = $assetModel->getFilteredAssets($search, $category, $sort);
$stats = $assetModel->getStats();

$categories = [
    'all'         => 'Semua Kategori',
    'general'     => 'Umum / General',
    'destination' => 'Destinasi',
    'tour'        => 'Paket Tour',
    'blog'        => 'Artikel Blog',
    'hero'        => 'Banner / Hero',
];

function formatBytes(int $bytes, int $precision = 2): string {
    if ($bytes <= 0) return '0 B';
    $base = log($bytes, 1024);
    $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[(int) floor($base)];
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Manajemen Asset Gambar</h1>
            <p class="date-indicator">Kelola, cari, dan atur media gambar CMS Explores Java</p>
        </div>
        <div class="header-actions" style="display: flex; gap: 0.75rem; align-items: center;">
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="action" value="sync">
                <button type="submit" class="btn-secondary" title="Pindai gambar di folder assets/images">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    Sync Folder
                </button>
            </form>
            <a href="dashboard.php?page=assets_add" class="btn-primary">
                + Tambah Asset Baru
            </a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Summary Stats -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Asset Gambar</span>
                <div class="stat-icon icon-blue">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['total_count']) ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Penggunaan Storage</span>
                <div class="stat-icon icon-green">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </div>
            </div>
            <div class="stat-value"><?= formatBytes($stats['total_bytes']) ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Kategori Terbanyak</span>
                <div class="stat-icon icon-purple">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                </div>
            </div>
            <div class="stat-value" style="font-size: 1.25rem;">
                <?php
                $topCategory = 'Umum';
                if (!empty($stats['categories'])) {
                    arsort($stats['categories']);
                    $topKey = array_key_first($stats['categories']);
                    $topCategory = ucfirst($topKey) . ' (' . $stats['categories'][$topKey] . ')';
                }
                echo e($topCategory);
                ?>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
        <form method="GET" action="dashboard.php" class="asset-filter-form" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;">
            <input type="hidden" name="page" value="assets">
            <input type="hidden" name="view" value="<?= e($viewMode) ?>">

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; flex: 1; min-width: 280px;">
                <div style="position: relative; flex: 1; min-width: 200px;">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama, file, atau alt text..." value="<?= e($search) ?>" style="padding-left: 2.25rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>

                <select name="category" class="form-control" style="width: auto; min-width: 160px;" onchange="this.form.submit()">
                    <?php foreach ($categories as $catKey => $catLabel): ?>
                        <option value="<?= e($catKey) ?>" <?= $category === $catKey ? 'selected' : '' ?>>
                            <?= e($catLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sort" class="form-control" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Terlama</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Nama (A-Z)</option>
                    <option value="size_desc" <?= $sort === 'size_desc' ? 'selected' : '' ?>>Ukuran Terbesar</option>
                </select>

                <button type="submit" class="btn-secondary">Filter</button>
                <?php if ($search !== '' || $category !== 'all' || $sort !== 'newest'): ?>
                    <a href="dashboard.php?page=assets" class="btn-secondary" style="color: var(--text-muted);">Reset</a>
                <?php endif; ?>
            </div>

            <!-- Layout Switcher -->
            <div class="view-toggle" style="display: flex; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 2px;">
                <a href="dashboard.php?page=assets&view=grid<?= $search ? '&q='.urlencode($search) : '' ?><?= $category !== 'all' ? '&category='.urlencode($category) : '' ?>"
                   class="btn-icon-toggle <?= $viewMode === 'grid' ? 'active' : '' ?>" title="Grid View" style="padding: 0.4rem 0.75rem; border-radius: 4px; display: flex; align-items: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </a>
                <a href="dashboard.php?page=assets&view=table<?= $search ? '&q='.urlencode($search) : '' ?><?= $category !== 'all' ? '&category='.urlencode($category) : '' ?>"
                   class="btn-icon-toggle <?= $viewMode === 'table' ? 'active' : '' ?>" title="Table View" style="padding: 0.4rem 0.75rem; border-radius: 4px; display: flex; align-items: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Assets Display -->
    <?php if (empty($assets)): ?>
        <div class="glass-card text-center" style="padding: 4rem 2rem;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-muted); margin-bottom: 1rem;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            <h3 style="margin-bottom: 0.5rem; font-size: 1.25rem;">Tidak ada asset gambar ditemukan</h3>
            <p class="text-muted" style="margin-bottom: 1.5rem;">Coba sesuaikan pencarian/filter atau unggah gambar baru.</p>
            <a href="dashboard.php?page=assets_add" class="btn-primary">+ Tambah Asset Baru</a>
        </div>
    <?php else: ?>
        <?php if ($viewMode === 'grid'): ?>
            <div class="asset-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1.25rem;">
                <?php foreach ($assets as $asset): ?>
                    <?php
                    $isExternal = str_starts_with($asset['file_path'], 'http://') || str_starts_with($asset['file_path'], 'https://');
                    $src = $isExternal ? $asset['file_path'] : '../' . ltrim($asset['file_path'], '/');
                    ?>
                    <div class="glass-card asset-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;">
                        <div class="asset-thumb-wrapper" style="position: relative; width: 100%; height: 160px; background: #1a1d24; overflow: hidden;">
                            <img src="<?= e($src) ?>" alt="<?= e($asset['alt_text'] ?: $asset['title']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            <span class="badge badge-info" style="position: absolute; top: 8px; right: 8px; font-size: 0.75rem; backdrop-filter: blur(4px); background: rgba(0,0,0,0.6);">
                                <?= e(ucfirst($asset['category'])) ?>
                            </span>
                        </div>
                        <div class="asset-info" style="padding: 1rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h4 class="asset-title" style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($asset['title']) ?>">
                                    <?= e($asset['title']) ?>
                                </h4>
                                <p class="text-muted text-sm" style="margin-bottom: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($asset['filename']) ?>">
                                    <?= e($asset['dimensions'] ? $asset['dimensions'] . ' • ' : '') ?><?= formatBytes((int) $asset['file_size']) ?>
                                </p>
                            </div>
                            <div class="asset-actions" style="display: flex; gap: 0.5rem; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                                <button type="button" class="btn-secondary btn-sm copy-btn" style="flex: 1; font-size: 0.75rem; padding: 0.4rem;" data-url="<?= e($asset['file_path']) ?>" title="Salin URL Gambar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    Copy URL
                                </button>
                                <button type="button" class="btn-secondary btn-sm edit-asset-btn" style="padding: 0.4rem 0.6rem;"
                                        data-id="<?= $asset['id'] ?>"
                                        data-title="<?= e($asset['title']) ?>"
                                        data-alt="<?= e($asset['alt_text']) ?>"
                                        data-category="<?= e($asset['category']) ?>"
                                        data-src="<?= e($src) ?>"
                                        data-path="<?= e($asset['file_path']) ?>"
                                        data-filename="<?= e($asset['filename']) ?>"
                                        data-size="<?= formatBytes((int) $asset['file_size']) ?>"
                                        data-dimensions="<?= e($asset['dimensions'] ?: '-') ?>"
                                        data-created="<?= formatDate($asset['created_at']) ?>"
                                        title="Edit Metadata">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <form method="POST" style="margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus asset gambar ini?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $asset['id'] ?>">
                                    <button type="submit" class="btn-secondary btn-sm" style="padding: 0.4rem 0.6rem; color: var(--color-danger);" title="Hapus Asset">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Table View -->
            <div class="glass-card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Preview</th>
                                <th>Judul & Filename</th>
                                <th>Kategori</th>
                                <th>Dimensi</th>
                                <th>Ukuran</th>
                                <th>Diunggah</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assets as $asset): ?>
                                <?php
                                $isExternal = str_starts_with($asset['file_path'], 'http://') || str_starts_with($asset['file_path'], 'https://');
                                $src = $isExternal ? $asset['file_path'] : '../' . ltrim($asset['file_path'], '/');
                                ?>
                                <tr>
                                    <td>
                                        <img src="<?= e($src) ?>" alt="" style="width: 48px; height: 48px; object-fit: cover; border-radius: var(--radius-sm); background: #1a1d24;">
                                    </td>
                                    <td>
                                        <div class="fw-bold" style="font-size: 0.95rem;"><?= e($asset['title']) ?></div>
                                        <div class="text-muted text-sm"><?= e($asset['filename']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= e(ucfirst($asset['category'])) ?></span>
                                    </td>
                                    <td><?= e($asset['dimensions'] ?: '-') ?></td>
                                    <td><?= formatBytes((int) $asset['file_size']) ?></td>
                                    <td><?= formatDate($asset['created_at']) ?></td>
                                    <td class="text-center">
                                        <div style="display: flex; gap: 0.4rem; justify-content: center;">
                                            <button type="button" class="btn-secondary btn-sm copy-btn" data-url="<?= e($asset['file_path']) ?>" title="Copy URL">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                            </button>
                                            <button type="button" class="btn-secondary btn-sm edit-asset-btn"
                                                    data-id="<?= $asset['id'] ?>"
                                                    data-title="<?= e($asset['title']) ?>"
                                                    data-alt="<?= e($asset['alt_text']) ?>"
                                                    data-category="<?= e($asset['category']) ?>"
                                                    data-src="<?= e($src) ?>"
                                                    data-path="<?= e($asset['file_path']) ?>"
                                                    data-filename="<?= e($asset['filename']) ?>"
                                                    data-size="<?= formatBytes((int) $asset['file_size']) ?>"
                                                    data-dimensions="<?= e($asset['dimensions'] ?: '-') ?>"
                                                    data-created="<?= formatDate($asset['created_at']) ?>"
                                                    title="Edit Metadata">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>
                                            <form method="POST" style="margin: 0;" onsubmit="return confirm('Hapus asset gambar ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $asset['id'] ?>">
                                                <button type="submit" class="btn-secondary btn-sm" style="color: var(--color-danger);" title="Hapus">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<!-- Edit Metadata & Detail Modal -->
<div id="assetEditModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; padding: 1.5rem;">
    <div class="glass-card" style="width: 100%; max-width: 650px; max-height: 90vh; overflow-y: auto; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
            <h3 class="card-title" style="margin: 0;">Detail & Edit Asset Gambar</h3>
            <button type="button" class="btn-secondary btn-sm" onclick="closeAssetModal()" style="padding: 0.25rem 0.5rem;">✕</button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="modalAssetId">

            <div style="display: grid; grid-template-columns: 220px 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div style="background: #1a1d24; border-radius: var(--radius-md); overflow: hidden; padding: 0.5rem; text-align: center;">
                    <img id="modalAssetImg" src="" alt="" style="width: 100%; max-height: 200px; object-fit: contain; border-radius: 4px; display: block; margin-bottom: 0.5rem;">
                    <div id="modalAssetDimensions" class="text-sm text-muted"></div>
                    <div id="modalAssetSize" class="text-sm text-muted"></div>
                </div>

                <div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Judul Asset</label>
                        <input type="text" name="title" id="modalAssetTitle" class="form-control" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Alt Text (Deskripsi Gambar)</label>
                        <input type="text" name="alt_text" id="modalAssetAlt" class="form-control">
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Kategori</label>
                        <select name="category" id="modalAssetCategory" class="form-control">
                            <option value="general">Umum / General</option>
                            <option value="destination">Destinasi</option>
                            <option value="tour">Paket Tour</option>
                            <option value="blog">Artikel Blog</option>
                            <option value="hero">Banner / Hero</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label">URL Asset</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" id="modalAssetUrl" class="form-control" readonly style="background: var(--bg-tertiary);">
                    <button type="button" class="btn-secondary" id="modalCopyBtn">Salin URL</button>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <button type="button" class="btn-secondary" onclick="closeAssetModal()">Batal</button>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy URL functionality
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) {
                navigator.clipboard.writeText(url).then(() => {
                    const originalText = this.innerHTML;
                    this.innerHTML = '✓ Copied!';
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 2000);
                });
            }
        });
    });

    // Edit modal functionality
    document.querySelectorAll('.edit-asset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modalAssetId').value = this.getAttribute('data-id');
            document.getElementById('modalAssetTitle').value = this.getAttribute('data-title');
            document.getElementById('modalAssetAlt').value = this.getAttribute('data-alt');
            document.getElementById('modalAssetCategory').value = this.getAttribute('data-category');
            document.getElementById('modalAssetImg').src = this.getAttribute('data-src');
            document.getElementById('modalAssetUrl').value = this.getAttribute('data-path');
            document.getElementById('modalAssetDimensions').textContent = 'Dimensi: ' + this.getAttribute('data-dimensions');
            document.getElementById('modalAssetSize').textContent = 'Ukuran: ' + this.getAttribute('data-size');
            
            document.getElementById('assetEditModal').style.display = 'flex';
        });
    });

    document.getElementById('modalCopyBtn')?.addEventListener('click', function() {
        const urlInput = document.getElementById('modalAssetUrl');
        urlInput.select();
        navigator.clipboard.writeText(urlInput.value).then(() => {
            this.textContent = '✓ Copied!';
            setTimeout(() => { this.textContent = 'Salin URL'; }, 2000);
        });
    });
});

function closeAssetModal() {
    document.getElementById('assetEditModal').style.display = 'none';
}
</script>
