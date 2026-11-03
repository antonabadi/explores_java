<?php
$pdo    = db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ─── POST Actions ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: uniqueSlug($pdo, 'destination_areas', $name);

        if ($name === '') {
            setFlash('danger', 'Area name is required.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO destination_areas (name, slug, description) VALUES (?, ?, ?)'
            );
            $stmt->execute([$name, $slug, $desc ?: null]);
            setFlash('success', 'Destination area created successfully.');
        }
        redirect('dashboard.php?page=destination_areas');
    }

    if ($action === 'update') {
        $id   = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: uniqueSlug($pdo, 'destination_areas', $name, $id);

        if ($name === '') {
            setFlash('danger', 'Area name is required.');
        } else {
            $stmt = $pdo->prepare(
                'UPDATE destination_areas SET name = ?, slug = ?, description = ? WHERE id = ?'
            );
            $stmt->execute([$name, $slug, $desc ?: null, $id]);
            setFlash('success', 'Destination area updated successfully.');
        }
        redirect('dashboard.php?page=destination_areas');
    }
}

// ─── GET: Delete ───────────────────────────────────────────────────────────────
if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    $check = $pdo->prepare('SELECT COUNT(*) FROM destination_area_map WHERE area_id = ?');
    $check->execute([$id]);
    $linkedCount = (int) $check->fetchColumn();

    if ($linkedCount > 0) {
        setFlash('danger', "Cannot delete area: currently linked to {$linkedCount} destination(s). Remove destination relations first.");
    } else {
        $pdo->prepare('DELETE FROM destination_areas WHERE id = ?')->execute([$id]);
        setFlash('success', 'Destination area deleted.');
    }
    redirect('dashboard.php?page=destination_areas');
}

// ─── Load Areas List with Destination Count ──────────────────────────────────
$areas = $pdo->query(
    'SELECT da.*, COUNT(dam.destination_id) AS destination_count
     FROM destination_areas da
     LEFT JOIN destination_area_map dam ON dam.area_id = da.id
     GROUP BY da.id
     ORDER BY da.name ASC'
)->fetchAll();

// ─── Load Edit Item ────────────────────────────────────────────────────────────
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM destination_areas WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editItem = $stmt->fetch() ?: null;
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Destination Areas</h1>
            <p class="date-indicator">Kelola wilayah & area administratif untuk destinasi wisata</p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="dashboard.php?page=destinations" class="btn-secondary">
                &larr; Back to Destinations
            </a>
            <button type="button" class="btn-primary" data-modal-open="areaModal"
                    <?= $editItem ? '' : 'data-reset-form="areaForm"' ?>>
                + Add Area
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="glass-card filter-bar">
        <div class="tab-headers tab-headers-inline">
            <a href="dashboard.php?page=destinations" class="tab-link">Destinations</a>
            <a href="dashboard.php?page=destination_areas" class="tab-link active">Manage Areas</a>
            <a href="dashboard.php?page=destination_tags" class="tab-link">Manage Tags</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <?php if ($areas): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Area Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th class="text-center">Linked Destinations</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($areas as $area): ?>
                            <tr>
                                <td><?= (int) $area['id'] ?></td>
                                <td>
                                    <span class="fw-bold"><?= e($area['name']) ?></span>
                                </td>
                                <td>
                                    <code class="code-badge"><?= e($area['slug']) ?></code>
                                </td>
                                <td>
                                    <?php if (!empty($area['description'])): ?>
                                        <span class="text-sm"><?= e(mb_strimwidth($area['description'], 0, 90, '...')) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ((int) $area['destination_count'] > 0): ?>
                                        <span class="badge badge-info"><?= (int) $area['destination_count'] ?> destination<?= (int) $area['destination_count'] > 1 ? 's' : '' ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-muted">0 destinations</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="dashboard.php?page=destination_areas&edit=<?= (int) $area['id'] ?>"
                                           class="btn-icon btn-edit" title="Edit Area">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="dashboard.php?page=destination_areas&action=delete&id=<?= (int) $area['id'] ?>"
                                           class="btn-icon btn-delete" title="Delete Area"
                                           data-confirm="Delete area &quot;<?= e($area['name']) ?>&quot;?">
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
            <p class="empty-state">No destination areas found. Click "+ Add Area" to create your first area.</p>
        <?php endif; ?>
    </div>
</main>

<!-- ─── Modal Form: Area ────────────────────────────────────────────────────── -->
<div class="modal-overlay" id="areaModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><?= $editItem ? 'Edit Destination Area' : 'Add Destination Area' ?></h3>
            <button type="button" class="btn-close-modal" data-modal-close>&times;</button>
        </div>
        <form method="post" id="areaForm">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="area_name">Area Name *</label>
                <input type="text" id="area_name" name="name" class="form-control" required
                       placeholder="Contoh: Yogyakarta, Malang & Bromo, Banyuwangi"
                       value="<?= e($editItem['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="area_slug">Slug</label>
                <input type="text" id="area_slug" name="slug" class="form-control"
                       placeholder="Auto-generated if empty (e.g. yogyakarta)"
                       value="<?= e($editItem['slug'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="area_desc">Description</label>
                <textarea id="area_desc" name="description" class="form-control" rows="3"
                          placeholder="Deskripsi singkat wilayah atau cakupan geografis..."><?= e($editItem['description'] ?? '') ?></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-submit"><?= $editItem ? 'Update Area' : 'Create Area' ?></button>
            </div>
        </form>
    </div>
</div>

<?php if ($editItem): ?>
<script>document.addEventListener('DOMContentLoaded', () => openModal('areaModal'));</script>
<?php endif; ?>
