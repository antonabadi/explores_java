<?php
$pdo = db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: uniqueSlug($pdo, 'destinations', $name);
        $image = trim($_POST['image_thumbnail'] ?? '');

        if ($name === '') {
            setFlash('danger', 'Destination name is required.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO destinations (name, slug, description, image_thumbnail) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$name, $slug, $description ?: null, $image ?: null]);
            setFlash('success', 'Destination created successfully.');
        }
        redirect('dashboard.php?page=destinations');
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: uniqueSlug($pdo, 'destinations', $name, $id);
        $image = trim($_POST['image_thumbnail'] ?? '');

        if ($name === '') {
            setFlash('danger', 'Destination name is required.');
        } else {
            $stmt = $pdo->prepare(
                'UPDATE destinations SET name = ?, slug = ?, description = ?, image_thumbnail = ? WHERE id = ?'
            );
            $stmt->execute([$name, $slug, $description ?: null, $image ?: null, $id]);
            setFlash('success', 'Destination updated successfully.');
        }
        redirect('dashboard.php?page=destinations');
    }
}

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    $check = $pdo->prepare('SELECT COUNT(*) FROM tours WHERE destination_id = ?');
    $check->execute([$id]);
    if ((int) $check->fetchColumn() > 0) {
        setFlash('danger', 'Cannot delete: destination has linked tours.');
    } else {
        $pdo->prepare('DELETE FROM destinations WHERE id = ?')->execute([$id]);
        setFlash('success', 'Destination deleted.');
    }
    redirect('dashboard.php?page=destinations');
}

$destinations = $pdo->query(
    'SELECT d.*, COUNT(t.id) AS tour_count
     FROM destinations d
     LEFT JOIN tours t ON t.destination_id = d.id
     GROUP BY d.id
     ORDER BY d.name ASC'
)->fetchAll();

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM destinations WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editItem = $stmt->fetch() ?: null;
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Destinations</h1>
            <p class="date-indicator">Manage tour destinations across Java</p>
        </div>
        <button type="button" class="btn-primary" data-modal-open="destinationModal"
                <?= $editItem ? '' : 'data-reset-form="destinationForm"' ?>>
            + Add Destination
        </button>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <?php if ($destinations): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th class="text-center">Tours</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($destinations as $d): ?>
                            <tr>
                                <td><?= (int) $d['id'] ?></td>
                                <td class="fw-bold"><?= e($d['name']) ?></td>
                                <td><code class="code-badge"><?= e($d['slug']) ?></code></td>
                                <td class="text-center"><?= (int) $d['tour_count'] ?></td>
                                <td><?= formatDate($d['created_at']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="dashboard.php?page=destinations&edit=<?= (int) $d['id'] ?>"
                                           class="btn-icon btn-edit" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="dashboard.php?page=destinations&action=delete&id=<?= (int) $d['id'] ?>"
                                           class="btn-icon btn-delete" title="Delete"
                                           data-confirm="Delete destination &quot;<?= e($d['name']) ?>&quot;?">
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
            <p class="empty-state">No destinations yet. Add your first destination.</p>
        <?php endif; ?>
    </div>
</main>

<div class="modal-overlay" id="destinationModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <h3 class="modal-title"><?= $editItem ? 'Edit Destination' : 'Add Destination' ?></h3>
            <button type="button" class="btn-close-modal" data-modal-close>&times;</button>
        </div>
        <form method="post" id="destinationForm">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="dest_name">Name *</label>
                <input type="text" id="dest_name" name="name" class="form-control" required
                       value="<?= e($editItem['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="dest_slug">Slug</label>
                <input type="text" id="dest_slug" name="slug" class="form-control"
                       placeholder="Auto-generated if empty"
                       value="<?= e($editItem['slug'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="dest_desc">Description</label>
                <textarea id="dest_desc" name="description" class="form-control" rows="4"><?= e($editItem['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="dest_image">Image URL</label>
                <input type="text" id="dest_image" name="image_thumbnail" class="form-control"
                       value="<?= e($editItem['image_thumbnail'] ?? '') ?>">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-submit"><?= $editItem ? 'Update' : 'Create' ?></button>
            </div>
        </form>
    </div>
</div>

<?php if ($editItem): ?>
<script>document.addEventListener('DOMContentLoaded', () => openModal('destinationModal'));</script>
<?php endif; ?>
