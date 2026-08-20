<?php
$pdo = db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$destinations = $pdo->query('SELECT id, name FROM destinations ORDER BY name')->fetchAll();
$packages = $pdo->query('SELECT id, package_name FROM tour_packages ORDER BY package_name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create' || $action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'destination_id'  => (int) ($_POST['destination_id'] ?? 0),
            'package_id'      => (int) ($_POST['package_id'] ?? 0),
            'title'           => trim($_POST['title'] ?? ''),
            'slug'            => trim($_POST['slug'] ?? ''),
            'duration_days'   => (int) ($_POST['duration_days'] ?? 0),
            'duration_nights' => (int) ($_POST['duration_nights'] ?? 0),
            'price'           => (float) ($_POST['price'] ?? 0),
            'group_type'      => $_POST['group_type'] ?? 'private',
            'description'     => trim($_POST['description'] ?? ''),
            'itinerary'       => trim($_POST['itinerary'] ?? ''),
            'facility_included' => trim($_POST['facility_included'] ?? ''),
            'facility_excluded' => trim($_POST['facility_excluded'] ?? ''),
            'is_active'       => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($data['title'] === '' || $data['destination_id'] < 1 || $data['package_id'] < 1) {
            setFlash('danger', 'Title, destination, and package are required.');
        } elseif (!in_array($data['group_type'], ['private', 'join_group'], true)) {
            setFlash('danger', 'Invalid group type.');
        } else {
            $slug = $data['slug'] ?: uniqueSlug($pdo, 'tours', $data['title'], $action === 'update' ? $id : null);

            if ($action === 'create') {
                $stmt = $pdo->prepare(
                    'INSERT INTO tours (destination_id, package_id, title, slug, duration_days, duration_nights,
                     price, group_type, description, itinerary, facility_included, facility_excluded, is_active)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $data['destination_id'], $data['package_id'], $data['title'], $slug,
                    $data['duration_days'], $data['duration_nights'], $data['price'], $data['group_type'],
                    $data['description'], $data['itinerary'], $data['facility_included'] ?: null,
                    $data['facility_excluded'] ?: null, $data['is_active'],
                ]);
                setFlash('success', 'Tour created successfully.');
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE tours SET destination_id = ?, package_id = ?, title = ?, slug = ?,
                     duration_days = ?, duration_nights = ?, price = ?, group_type = ?,
                     description = ?, itinerary = ?, facility_included = ?, facility_excluded = ?, is_active = ?
                     WHERE id = ?'
                );
                $stmt->execute([
                    $data['destination_id'], $data['package_id'], $data['title'], $slug,
                    $data['duration_days'], $data['duration_nights'], $data['price'], $data['group_type'],
                    $data['description'], $data['itinerary'], $data['facility_included'] ?: null,
                    $data['facility_excluded'] ?: null, $data['is_active'], $id,
                ]);
                setFlash('success', 'Tour updated successfully.');
            }
        }
        redirect('dashboard.php?page=tours');
    }
}

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    $pdo->prepare('DELETE FROM tours WHERE id = ?')->execute([$id]);
    setFlash('success', 'Tour deleted.');
    redirect('dashboard.php?page=tours');
}

$filterDest = (int) ($_GET['destination_id'] ?? 0);
$filterStatus = $_GET['status'] ?? '';

$sql = 'SELECT t.*, d.name AS destination_name, p.package_name
        FROM tours t
        JOIN destinations d ON d.id = t.destination_id
        JOIN tour_packages p ON p.id = t.package_id
        WHERE 1=1';
$params = [];

if ($filterDest > 0) {
    $sql .= ' AND t.destination_id = ?';
    $params[] = $filterDest;
}
if ($filterStatus === 'active') {
    $sql .= ' AND t.is_active = 1';
} elseif ($filterStatus === 'inactive') {
    $sql .= ' AND t.is_active = 0';
}
$sql .= ' ORDER BY t.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tours = $stmt->fetchAll();

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM tours WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editItem = $stmt->fetch() ?: null;
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Tours</h1>
            <p class="date-indicator">Manage tour listings and itineraries</p>
        </div>
        <button type="button" class="btn-primary" data-modal-open="tourModal">+ Add Tour</button>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="glass-card filter-bar">
        <form method="get" class="filter-form">
            <input type="hidden" name="page" value="tours">
            <div class="form-group">
                <label>Destination</label>
                <select name="destination_id" class="form-control">
                    <option value="">All destinations</option>
                    <?php foreach ($destinations as $d): ?>
                        <option value="<?= (int) $d['id'] ?>" <?= $filterDest === (int) $d['id'] ? 'selected' : '' ?>>
                            <?= e($d['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $filterStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
            <a href="dashboard.php?page=tours" class="btn-link">Reset</a>
        </form>
    </div>

    <div class="glass-card">
        <?php if ($tours): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Destination</th>
                            <th>Package</th>
                            <th>Duration</th>
                            <th>Group</th>
                            <th class="text-right">Price</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tours as $t): ?>
                            <tr>
                                <td class="fw-bold"><?= e($t['title']) ?></td>
                                <td><?= e($t['destination_name']) ?></td>
                                <td><?= e($t['package_name']) ?></td>
                                <td><?= (int) $t['duration_days'] ?>D / <?= (int) $t['duration_nights'] ?>N</td>
                                <td><?= e(str_replace('_', ' ', ucfirst($t['group_type']))) ?></td>
                                <td class="text-right"><?= formatRupiah($t['price']) ?></td>
                                <td>
                                    <?php if ((int) $t['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-muted">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="dashboard.php?page=tours&edit=<?= (int) $t['id'] ?>"
                                           class="btn-icon btn-edit" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="dashboard.php?page=tours&action=delete&id=<?= (int) $t['id'] ?>"
                                           class="btn-icon btn-delete" title="Delete"
                                           data-confirm="Delete tour &quot;<?= e($t['title']) ?>&quot;?">
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
            <p class="empty-state">No tours found.</p>
        <?php endif; ?>
    </div>
</main>

<div class="modal-overlay" id="tourModal">
    <div class="modal-container modal-xl">
        <div class="modal-header">
            <h3 class="modal-title"><?= $editItem ? 'Edit Tour' : 'Add Tour' ?></h3>
            <button type="button" class="btn-close-modal" data-modal-close>&times;</button>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="tour_title">Title *</label>
                    <input type="text" id="tour_title" name="title" class="form-control" required
                           value="<?= e($editItem['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="tour_slug">Slug</label>
                    <input type="text" id="tour_slug" name="slug" class="form-control"
                           placeholder="Auto-generated if empty"
                           value="<?= e($editItem['slug'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="tour_dest">Destination *</label>
                    <select id="tour_dest" name="destination_id" class="form-control" required>
                        <option value="">Select destination</option>
                        <?php foreach ($destinations as $d): ?>
                            <option value="<?= (int) $d['id'] ?>"
                                <?= ($editItem['destination_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                                <?= e($d['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tour_pkg">Package *</label>
                    <select id="tour_pkg" name="package_id" class="form-control" required>
                        <option value="">Select package</option>
                        <?php foreach ($packages as $p): ?>
                            <option value="<?= (int) $p['id'] ?>"
                                <?= ($editItem['package_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                <?= e($p['package_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tour_days">Duration (Days) *</label>
                    <input type="number" id="tour_days" name="duration_days" class="form-control" min="1" required
                           value="<?= e((string) ($editItem['duration_days'] ?? '1')) ?>">
                </div>
                <div class="form-group">
                    <label for="tour_nights">Duration (Nights) *</label>
                    <input type="number" id="tour_nights" name="duration_nights" class="form-control" min="0" required
                           value="<?= e((string) ($editItem['duration_nights'] ?? '0')) ?>">
                </div>
                <div class="form-group">
                    <label for="tour_price">Price (IDR) *</label>
                    <input type="number" id="tour_price" name="price" class="form-control" min="0" step="1000" required
                           value="<?= e((string) ($editItem['price'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label for="tour_group">Group Type *</label>
                    <select id="tour_group" name="group_type" class="form-control" required>
                        <option value="private" <?= ($editItem['group_type'] ?? '') === 'private' ? 'selected' : '' ?>>Private</option>
                        <option value="join_group" <?= ($editItem['group_type'] ?? '') === 'join_group' ? 'selected' : '' ?>>Join Group</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="tour_desc">Description *</label>
                <textarea id="tour_desc" name="description" class="form-control" rows="3" required><?= e($editItem['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="tour_itinerary">Itinerary *</label>
                <textarea id="tour_itinerary" name="itinerary" class="form-control" rows="4" required><?= e($editItem['itinerary'] ?? '') ?></textarea>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="tour_included">Facilities Included</label>
                    <textarea id="tour_included" name="facility_included" class="form-control" rows="3"><?= e($editItem['facility_included'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="tour_excluded">Facilities Excluded</label>
                    <textarea id="tour_excluded" name="facility_excluded" class="form-control" rows="3"><?= e($editItem['facility_excluded'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="form-group form-check">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                        <?= ($editItem['is_active'] ?? 1) ? 'checked' : '' ?>>
                    Active (visible to customers)
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-submit"><?= $editItem ? 'Update' : 'Create' ?></button>
            </div>
        </form>
    </div>
</div>

<?php if ($editItem): ?>
<script>document.addEventListener('DOMContentLoaded', () => openModal('tourModal'));</script>
<?php endif; ?>
