<?php
$pdo = db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $name = trim($_POST['package_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            setFlash('danger', 'Package name is required.');
        } else {
            $exists = $pdo->prepare('SELECT id FROM tour_packages WHERE package_name = ?');
            $exists->execute([$name]);
            if ($exists->fetch()) {
                setFlash('danger', 'Package name already exists.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO tour_packages (package_name, description) VALUES (?, ?)');
                $stmt->execute([$name, $description ?: null]);
                setFlash('success', 'Package created successfully.');
            }
        }
        redirect('dashboard.php?page=packages');
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['package_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            setFlash('danger', 'Package name is required.');
        } else {
            $stmt = $pdo->prepare('UPDATE tour_packages SET package_name = ?, description = ? WHERE id = ?');
            $stmt->execute([$name, $description ?: null, $id]);
            setFlash('success', 'Package updated successfully.');
        }
        redirect('dashboard.php?page=packages');
    }
}

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    $check = $pdo->prepare('SELECT COUNT(*) FROM tours WHERE package_id = ?');
    $check->execute([$id]);
    if ((int) $check->fetchColumn() > 0) {
        setFlash('danger', 'Cannot delete: package has linked tours.');
    } else {
        $pdo->prepare('DELETE FROM tour_packages WHERE id = ?')->execute([$id]);
        setFlash('success', 'Package deleted.');
    }
    redirect('dashboard.php?page=packages');
}

$packages = $pdo->query(
    'SELECT p.*, COUNT(t.id) AS tour_count
     FROM tour_packages p
     LEFT JOIN tours t ON t.package_id = p.id
     GROUP BY p.id
     ORDER BY p.package_name ASC'
)->fetchAll();

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM tour_packages WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editItem = $stmt->fetch() ?: null;
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Tour Packages</h1>
            <p class="date-indicator">Manage tour package categories</p>
        </div>
        <button type="button" class="btn-primary" data-modal-open="packageModal">
            + Add Package
        </button>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <?php if ($packages): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Package Name</th>
                            <th>Description</th>
                            <th class="text-center">Tours</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $p): ?>
                            <tr>
                                <td><?= (int) $p['id'] ?></td>
                                <td class="fw-bold"><?= e($p['package_name']) ?></td>
                                <td class="text-muted"><?= e(mb_strimwidth($p['description'] ?? '-', 0, 60, '...')) ?></td>
                                <td class="text-center"><?= (int) $p['tour_count'] ?></td>
                                <td><?= formatDate($p['created_at']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="dashboard.php?page=packages&edit=<?= (int) $p['id'] ?>"
                                           class="btn-icon btn-edit" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="dashboard.php?page=packages&action=delete&id=<?= (int) $p['id'] ?>"
                                           class="btn-icon btn-delete" title="Delete"
                                           data-confirm="Delete package &quot;<?= e($p['package_name']) ?>&quot;?">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty-state">No packages yet.</p>
        <?php endif; ?>
    </div>
</main>

<div class="modal-overlay" id="packageModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><?= $editItem ? 'Edit Package' : 'Add Package' ?></h3>
            <button type="button" class="btn-close-modal" data-modal-close>&times;</button>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="package_name">Package Name *</label>
                <input type="text" id="package_name" name="package_name" class="form-control" required
                       value="<?= e($editItem['package_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="package_desc">Description</label>
                <textarea id="package_desc" name="description" class="form-control" rows="4"><?= e($editItem['description'] ?? '') ?></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-submit"><?= $editItem ? 'Update' : 'Create' ?></button>
            </div>
        </form>
    </div>
</div>

<?php if ($editItem): ?>
<script>document.addEventListener('DOMContentLoaded', () => openModal('packageModal'));</script>
<?php endif; ?>
