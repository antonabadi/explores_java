<?php
$pdo    = db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ─── POST Actions ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: uniqueSlug($pdo, 'destination_tags', $name);

        if ($name === '') {
            setFlash('danger', 'Tag name is required.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO destination_tags (name, slug, icon) VALUES (?, ?, ?)'
            );
            $stmt->execute([$name, $slug, $icon ?: null]);
            setFlash('success', 'Activity tag created successfully.');
        }
        redirect('dashboard.php?page=destination_tags');
    }

    if ($action === 'update') {
        $id   = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: uniqueSlug($pdo, 'destination_tags', $name, $id);

        if ($name === '') {
            setFlash('danger', 'Tag name is required.');
        } else {
            $stmt = $pdo->prepare(
                'UPDATE destination_tags SET name = ?, slug = ?, icon = ? WHERE id = ?'
            );
            $stmt->execute([$name, $slug, $icon ?: null, $id]);
            setFlash('success', 'Activity tag updated successfully.');
        }
        redirect('dashboard.php?page=destination_tags');
    }
}

// ─── GET: Delete ───────────────────────────────────────────────────────────────
if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    $check = $pdo->prepare('SELECT COUNT(*) FROM destination_tag_map WHERE tag_id = ?');
    $check->execute([$id]);
    $linkedCount = (int) $check->fetchColumn();

    if ($linkedCount > 0) {
        setFlash('danger', "Cannot delete tag: currently linked to {$linkedCount} destination(s). Remove destination relations first.");
    } else {
        $pdo->prepare('DELETE FROM destination_tags WHERE id = ?')->execute([$id]);
        setFlash('success', 'Activity tag deleted.');
    }
    redirect('dashboard.php?page=destination_tags');
}

// ─── Load Tags List with Destination Count ───────────────────────────────────
$tags = $pdo->query(
    'SELECT dt.*, COUNT(dtm.destination_id) AS destination_count
     FROM destination_tags dt
     LEFT JOIN destination_tag_map dtm ON dtm.tag_id = dt.id
     GROUP BY dt.id
     ORDER BY dt.name ASC'
)->fetchAll();

// ─── Load Edit Item ────────────────────────────────────────────────────────────
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM destination_tags WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editItem = $stmt->fetch() ?: null;
}

// Icon Presets for Activity Tags
$presetIcons = [
    '🌿 Nature'      => '🌿',
    '🏖️ Beach'       => '🏖️',
    '🥾 Hiking'      => '🥾',
    '🏛️ Culture'     => '🏛️',
    '🍜 Culinary'    => '🍜',
    '📷 Photo'       => '📷',
    '⚡ Adventure'   => '⚡',
    '👨‍👩‍👧 Family'      => '👨‍👩‍👧',
    '🌊 Waterfall'   => '🌊',
    '⛺ Camping'     => '⛺',
    '🤿 Snorkeling'  => '🤿',
    '🚲 Cycling'     => '🚲',
    '🛍️ Shopping'    => '🛍️',
    '⛩️ Heritage'    => '⛩️',
    '🌅 Sunrise'     => '🌅',
    '🛶 Rafting'     => '🛶',
    '🌋 Volcano'     => '🌋',
    '🏷️ General'     => '🏷️',
];
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Activity Tags</h1>
            <p class="date-indicator">Kelola kategori aktivitas & tema pengalaman destinasi wisata</p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="dashboard.php?page=destinations" class="btn-secondary">
                &larr; Back to Destinations
            </a>
            <button type="button" class="btn-primary" data-modal-open="tagModal"
                    <?= $editItem ? '' : 'data-reset-form="tagForm"' ?>>
                + Add Tag
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="glass-card filter-bar">
        <div class="tab-headers tab-headers-inline">
            <a href="dashboard.php?page=destinations" class="tab-link">Destinations</a>
            <a href="dashboard.php?page=destination_areas" class="tab-link">Manage Areas</a>
            <a href="dashboard.php?page=destination_tags" class="tab-link active">Manage Tags</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <?php if ($tags): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tag Name</th>
                            <th>Icon</th>
                            <th>Slug</th>
                            <th class="text-center">Linked Destinations</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tags as $tag): ?>
                            <tr>
                                <td><?= (int) $tag['id'] ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-size: 1.25rem; line-height: 1;"><?= e($tag['icon'] ?: '🏷️') ?></span>
                                        <span class="fw-bold"><?= e($tag['name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <code class="code-badge"><?= e($tag['icon'] ?: '—') ?></code>
                                </td>
                                <td>
                                    <code class="code-badge"><?= e($tag['slug']) ?></code>
                                </td>
                                <td class="text-center">
                                    <?php if ((int) $tag['destination_count'] > 0): ?>
                                        <span class="badge badge-info"><?= (int) $tag['destination_count'] ?> destination<?= (int) $tag['destination_count'] > 1 ? 's' : '' ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-muted">0 destinations</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="dashboard.php?page=destination_tags&edit=<?= (int) $tag['id'] ?>"
                                           class="btn-icon btn-edit" title="Edit Tag">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="dashboard.php?page=destination_tags&action=delete&id=<?= (int) $tag['id'] ?>"
                                           class="btn-icon btn-delete" title="Delete Tag"
                                           data-confirm="Delete tag &quot;<?= e($tag['name']) ?>&quot;?">
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
            <p class="empty-state">No activity tags found. Click "+ Add Tag" to create your first activity tag.</p>
        <?php endif; ?>
    </div>
</main>

<!-- ─── Modal Form: Tag ─────────────────────────────────────────────────────── -->
<div class="modal-overlay" id="tagModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><?= $editItem ? 'Edit Activity Tag' : 'Add Activity Tag' ?></h3>
            <button type="button" class="btn-close-modal" data-modal-close>&times;</button>
        </div>
        <form method="post" id="tagForm">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="tag_name">Tag Name *</label>
                <input type="text" id="tag_name" name="name" class="form-control" required
                       placeholder="Contoh: Hiking, Culture, Beach, Culinary"
                       value="<?= e($editItem['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="tag_slug">Slug</label>
                <input type="text" id="tag_slug" name="slug" class="form-control"
                       placeholder="Auto-generated if empty (e.g. hiking)"
                       value="<?= e($editItem['slug'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="tag_icon">Icon / Emoji</label>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                    <input type="text" id="tag_icon" name="icon" class="form-control" style="width: 120px; font-size: 1.25rem; text-align: center;"
                           placeholder="🏷️"
                           value="<?= e($editItem['icon'] ?? '') ?>">
                    <span class="text-sm text-muted">Ketik emoji atau pilih preset di bawah:</span>
                </div>

                <!-- Quick emoji buttons -->
                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 0.5rem;">
                    <?php foreach ($presetIcons as $label => $emo): ?>
                        <button type="button" class="badge badge-muted"
                                style="cursor: pointer; border: 1px solid var(--border-color); font-size: 0.85rem; padding: 0.3rem 0.55rem; background: var(--bg-tertiary);"
                                onclick="document.getElementById('tag_icon').value = '<?= $emo ?>'">
                            <?= $label ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-submit"><?= $editItem ? 'Update Tag' : 'Create Tag' ?></button>
            </div>
        </form>
    </div>
</div>

<?php if ($editItem): ?>
<script>document.addEventListener('DOMContentLoaded', () => openModal('tagModal'));</script>
<?php endif; ?>
