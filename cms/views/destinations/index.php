<?php
$pdo    = db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Referensi data untuk multi-select
$allAreas = $pdo->query('SELECT id, name FROM destination_areas ORDER BY name ASC')->fetchAll();
$allTags  = $pdo->query('SELECT id, name, icon FROM destination_tags ORDER BY name ASC')->fetchAll();
$allPosts = $pdo->query(
    "SELECT id, title FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC, title ASC"
)->fetchAll();

// ─── Helper: sync pivot setelah INSERT / UPDATE ────────────────────────────────
function syncPivot(PDO $pdo, string $table, string $fkCol, int $fkVal, string $relCol, array $ids): void
{
    $pdo->prepare("DELETE FROM {$table} WHERE {$fkCol} = ?")->execute([$fkVal]);
    if (empty($ids)) {
        return;
    }
    $stmt = $pdo->prepare("INSERT IGNORE INTO {$table} ({$fkCol}, {$relCol}) VALUES (?, ?)");
    foreach ($ids as $rid) {
        $stmt->execute([$fkVal, (int) $rid]);
    }
}

function syncBlogPostsPivot(PDO $pdo, int $destinationId, array $postIds): void
{
    $pdo->prepare('DELETE FROM destination_blog_posts WHERE destination_id = ?')->execute([$destinationId]);
    if (empty($postIds)) {
        return;
    }
    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO destination_blog_posts (destination_id, post_id, sort_order) VALUES (?, ?, ?)'
    );
    foreach ($postIds as $order => $postId) {
        $stmt->execute([$destinationId, (int) $postId, $order]);
    }
}

// ─── POST Actions ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'create') {
        $name     = trim($_POST['name'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $slug     = trim($_POST['slug'] ?? '') ?: uniqueSlug($pdo, 'destinations', $name);
        $image    = trim($_POST['image_thumbnail'] ?? '');
        $location = trim($_POST['location_map'] ?? '');
        $areaIds  = (array) ($_POST['area_ids'] ?? []);
        $tagIds   = (array) ($_POST['tag_ids'] ?? []);
        $postIds  = (array) ($_POST['blog_post_ids'] ?? []);

        if ($name === '') {
            setFlash('danger', 'Destination name is required.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO destinations (name, slug, description, image_thumbnail, location_map)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$name, $slug, $desc ?: null, $image ?: null, $location ?: null]);
            $newId = (int) $pdo->lastInsertId();
            syncPivot($pdo, 'destination_area_map', 'destination_id', $newId, 'area_id', $areaIds);
            syncPivot($pdo, 'destination_tag_map',  'destination_id', $newId, 'tag_id',  $tagIds);
            syncBlogPostsPivot($pdo, $newId, $postIds);
            setFlash('success', 'Destination created successfully.');
        }
        redirect('dashboard.php?page=destinations');
    }

    if ($action === 'update') {
        $id       = (int) ($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $slug     = trim($_POST['slug'] ?? '') ?: uniqueSlug($pdo, 'destinations', $name, $id);
        $image    = trim($_POST['image_thumbnail'] ?? '');
        $location = trim($_POST['location_map'] ?? '');
        $areaIds  = (array) ($_POST['area_ids'] ?? []);
        $tagIds   = (array) ($_POST['tag_ids'] ?? []);
        $postIds  = (array) ($_POST['blog_post_ids'] ?? []);

        if ($name === '') {
            setFlash('danger', 'Destination name is required.');
        } else {
            $stmt = $pdo->prepare(
                'UPDATE destinations
                 SET name = ?, slug = ?, description = ?, image_thumbnail = ?, location_map = ?
                 WHERE id = ?'
            );
            $stmt->execute([$name, $slug, $desc ?: null, $image ?: null, $location ?: null, $id]);
            syncPivot($pdo, 'destination_area_map', 'destination_id', $id, 'area_id', $areaIds);
            syncPivot($pdo, 'destination_tag_map',  'destination_id', $id, 'tag_id',  $tagIds);
            syncBlogPostsPivot($pdo, $id, $postIds);
            setFlash('success', 'Destination updated successfully.');
        }
        redirect('dashboard.php?page=destinations');
    }
}

// ─── GET: Delete ───────────────────────────────────────────────────────────────
if ($action === 'delete') {
    $id    = (int) ($_GET['id'] ?? 0);
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

// ─── Load list ─────────────────────────────────────────────────────────────────
$destinations = $pdo->query(
    'SELECT d.*, COUNT(DISTINCT t.id) AS tour_count
     FROM destinations d
     LEFT JOIN tours t ON t.destination_id = d.id AND t.is_active = 1
     GROUP BY d.id
     ORDER BY d.name ASC'
)->fetchAll();

// Ambil area & tag masing-masing destinasi untuk ditampilkan di tabel
$destMeta = [];
if ($destinations) {
    $ids        = implode(',', array_map(fn($r) => (int) $r['id'], $destinations));
    $areaRows   = $pdo->query(
        "SELECT dam.destination_id, da.name
         FROM destination_area_map dam
         JOIN destination_areas da ON da.id = dam.area_id
         WHERE dam.destination_id IN ({$ids})
         ORDER BY da.name ASC"
    )->fetchAll();
    $tagRows    = $pdo->query(
        "SELECT dtm.destination_id, dt.name, dt.icon
         FROM destination_tag_map dtm
         JOIN destination_tags dt ON dt.id = dtm.tag_id
         WHERE dtm.destination_id IN ({$ids})
         ORDER BY dt.name ASC"
    )->fetchAll();

    foreach ($areaRows as $row) {
        $destMeta[$row['destination_id']]['areas'][] = $row['name'];
    }
    foreach ($tagRows as $row) {
        $destMeta[$row['destination_id']]['tags'][] = $row['name'];
    }
}

// ─── Load edit item (dengan pivot data) ───────────────────────────────────────
$editItem          = null;
$editAreaIds       = [];
$editTagIds        = [];
$editBlogPostIds   = [];

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM destinations WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editItem = $stmt->fetch() ?: null;

    if ($editItem) {
        $eid = (int) $editItem['id'];

        $aStmt = $pdo->prepare('SELECT area_id FROM destination_area_map WHERE destination_id = ?');
        $aStmt->execute([$eid]);
        $editAreaIds = $aStmt->fetchAll(PDO::FETCH_COLUMN);

        $tStmt = $pdo->prepare('SELECT tag_id FROM destination_tag_map WHERE destination_id = ?');
        $tStmt->execute([$eid]);
        $editTagIds = $tStmt->fetchAll(PDO::FETCH_COLUMN);

        $bStmt = $pdo->prepare(
            'SELECT post_id FROM destination_blog_posts WHERE destination_id = ? ORDER BY sort_order ASC'
        );
        $bStmt->execute([$eid]);
        $editBlogPostIds = $bStmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Destinations</h1>
            <p class="date-indicator">Manage tour destinations, areas, and activity tags</p>
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
                            <th>Areas</th>
                            <th>Activity Tags</th>
                            <th class="text-center">Tours</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($destinations as $d): ?>
                            <?php $meta = $destMeta[(int) $d['id']] ?? []; ?>
                            <tr>
                                <td><?= (int) $d['id'] ?></td>
                                <td>
                                    <span class="fw-bold"><?= e($d['name']) ?></span><br>
                                    <code class="code-badge"><?= e($d['slug']) ?></code>
                                </td>
                                <td>
                                    <?php if (!empty($meta['areas'])): ?>
                                        <div class="tag-list">
                                            <?php foreach ($meta['areas'] as $area): ?>
                                                <span class="badge badge-info"><?= e($area) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($meta['tags'])): ?>
                                        <div class="tag-list">
                                            <?php foreach ($meta['tags'] as $tag): ?>
                                                <span class="badge badge-muted"><?= e($tag) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
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

<!-- ─── Modal Form ──────────────────────────────────────────────────────────── -->
<div class="modal-overlay" id="destinationModal">
    <div class="modal-container modal-xl">
        <div class="modal-header">
            <h3 class="modal-title"><?= $editItem ? 'Edit Destination' : 'Add Destination' ?></h3>
            <button type="button" class="btn-close-modal" data-modal-close>&times;</button>
        </div>
        <form method="post" id="destinationForm">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
            <?php endif; ?>

            <!-- ── Baris 1: Name & Slug ── -->
            <div class="form-grid">
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
            </div>

            <!-- ── Description ── -->
            <div class="form-group">
                <label for="dest_desc">Description</label>
                <textarea id="dest_desc" name="description" class="form-control" rows="3"><?= e($editItem['description'] ?? '') ?></textarea>
            </div>

            <!-- ── Baris 2: Image & Location Map ── -->
            <div class="form-grid">
                <div class="form-group">
                    <label for="dest_image">Image Thumbnail URL</label>
                    <input type="text" id="dest_image" name="image_thumbnail" class="form-control"
                           placeholder="https://..."
                           value="<?= e($editItem['image_thumbnail'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="dest_map">Location Map URL</label>
                    <input type="text" id="dest_map" name="location_map" class="form-control"
                           placeholder="Google Maps embed URL atau lat,lng"
                           value="<?= e($editItem['location_map'] ?? '') ?>">
                </div>
            </div>

            <!-- ── Area Tags (wilayah) ── -->
            <div class="form-group">
                <label>Area / Wilayah</label>
                <div class="checkbox-grid">
                    <?php foreach ($allAreas as $area): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="area_ids[]" value="<?= (int) $area['id'] ?>"
                                <?= in_array($area['id'], $editAreaIds, false) ? 'checked' : '' ?>>
                            <?= e($area['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ── Activity Tags ── -->
            <div class="form-group">
                <label>Activity Tags</label>
                <div class="checkbox-grid">
                    <?php foreach ($allTags as $tag): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="tag_ids[]" value="<?= (int) $tag['id'] ?>"
                                <?= in_array($tag['id'], $editTagIds, false) ? 'checked' : '' ?>>
                            <?= e($tag['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ── Blog Suggestions (manual) ── -->
            <div class="form-group">
                <label>Blog Post Suggestions
                    <span class="label-hint">(pilih artikel yang relevan untuk ditampilkan ke pengunjung)</span>
                </label>
                <div class="checkbox-grid checkbox-grid-sm">
                    <?php foreach ($allPosts as $post): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="blog_post_ids[]" value="<?= (int) $post['id'] ?>"
                                <?= in_array($post['id'], $editBlogPostIds, false) ? 'checked' : '' ?>>
                            <?= e($post['title']) ?>
                        </label>
                    <?php endforeach; ?>
                    <?php if (!$allPosts): ?>
                        <p class="text-muted" style="margin:0">Belum ada blog post yang dipublish.</p>
                    <?php endif; ?>
                </div>
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
