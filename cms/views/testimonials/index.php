<?php
$pdo = db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$tours = $pdo->query('SELECT id, title FROM tours ORDER BY title')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create' || $action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $rating = (int) ($_POST['rating'] ?? 0);
        $reviewText = trim($_POST['review_text'] ?? '');
        $tourId = $_POST['tour_id'] ?? '';
        $tourId = $tourId !== '' ? (int) $tourId : null;
        $isApproved = isset($_POST['is_approved']) ? 1 : 0;
        $photo = trim($_POST['customer_photo'] ?? '');

        if ($customerName === '' || $reviewText === '' || $rating < 1 || $rating > 5) {
            setFlash('danger', 'Name, review text, and rating (1-5) are required.');
        } else {
            if ($action === 'create') {
                $stmt = $pdo->prepare(
                    'INSERT INTO testimonials (tour_id, customer_name, customer_email, rating, review_text, customer_photo, is_approved)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $tourId, $customerName, $customerEmail ?: null, $rating,
                    $reviewText, $photo ?: null, $isApproved,
                ]);
                setFlash('success', 'Testimonial created.');
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE testimonials SET tour_id = ?, customer_name = ?, customer_email = ?,
                     rating = ?, review_text = ?, customer_photo = ?, is_approved = ? WHERE id = ?'
                );
                $stmt->execute([
                    $tourId, $customerName, $customerEmail ?: null, $rating,
                    $reviewText, $photo ?: null, $isApproved, $id,
                ]);
                setFlash('success', 'Testimonial updated.');
            }
        }
        redirect('dashboard.php?page=testimonials');
    }

    if ($action === 'approve') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE testimonials SET is_approved = 1 WHERE id = ?')->execute([$id]);
        setFlash('success', 'Testimonial approved.');
        redirect('dashboard.php?page=testimonials');
    }

    if ($action === 'reject') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE testimonials SET is_approved = 0 WHERE id = ?')->execute([$id]);
        setFlash('success', 'Testimonial rejected.');
        redirect('dashboard.php?page=testimonials');
    }
}

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    $pdo->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
    setFlash('success', 'Testimonial deleted.');
    redirect('dashboard.php?page=testimonials');
}

$filter = $_GET['filter'] ?? 'all';
$sql = 'SELECT tm.*, t.title AS tour_title
        FROM testimonials tm
        LEFT JOIN tours t ON t.id = tm.tour_id
        WHERE 1=1';
if ($filter === 'pending') {
    $sql .= ' AND tm.is_approved = 0';
} elseif ($filter === 'approved') {
    $sql .= ' AND tm.is_approved = 1';
}
$sql .= ' ORDER BY tm.created_at DESC';
$testimonials = $pdo->query($sql)->fetchAll();

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM testimonials WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editItem = $stmt->fetch() ?: null;
}

function starRating(int $rating): string
{
    $html = '<span class="star-rating" aria-label="' . $rating . ' stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating ? '★' : '☆';
    }
    return $html . '</span>';
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Testimonials</h1>
            <p class="date-indicator">Moderate customer reviews and ratings</p>
        </div>
        <button type="button" class="btn-primary" data-modal-open="testimonialModal">+ Add Testimonial</button>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="glass-card filter-bar">
        <div class="tab-headers tab-headers-inline">
            <a href="dashboard.php?page=testimonials" class="tab-link <?= $filter === 'all' ? 'active' : '' ?>">All</a>
            <a href="dashboard.php?page=testimonials&filter=pending" class="tab-link <?= $filter === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="dashboard.php?page=testimonials&filter=approved" class="tab-link <?= $filter === 'approved' ? 'active' : '' ?>">Approved</a>
        </div>
    </div>

    <div class="testimonial-grid">
        <?php if ($testimonials): ?>
            <?php foreach ($testimonials as $tm): ?>
                <div class="glass-card testimonial-card">
                    <div class="testimonial-header">
                        <div>
                            <div class="fw-bold"><?= e($tm['customer_name']) ?></div>
                            <?php if ($tm['customer_email']): ?>
                                <div class="text-muted text-sm"><?= e($tm['customer_email']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="testimonial-meta">
                            <?= starRating((int) $tm['rating']) ?>
                            <?php if ((int) $tm['is_approved']): ?>
                                <span class="badge badge-success">Approved</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($tm['tour_title']): ?>
                        <div class="text-sm text-muted" style="margin-bottom: 0.5rem;">Tour: <?= e($tm['tour_title']) ?></div>
                    <?php endif; ?>
                    <p class="testimonial-text"><?= e($tm['review_text']) ?></p>
                    <div class="testimonial-footer">
                        <span class="text-muted text-sm"><?= formatDate($tm['created_at']) ?></span>
                        <div class="btn-group">
                            <?php if (!(int) $tm['is_approved']): ?>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="id" value="<?= (int) $tm['id'] ?>">
                                    <button type="submit" class="btn-icon btn-success" title="Approve">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="id" value="<?= (int) $tm['id'] ?>">
                                    <button type="submit" class="btn-icon btn-warning" title="Unapprove">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="dashboard.php?page=testimonials&edit=<?= (int) $tm['id'] ?>"
                               class="btn-icon btn-edit" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <a href="dashboard.php?page=testimonials&action=delete&id=<?= (int) $tm['id'] ?>"
                               class="btn-icon btn-delete" title="Delete"
                               data-confirm="Delete this testimonial?">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="glass-card empty-state-card">
                <p class="empty-state">No testimonials found.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal-overlay" id="testimonialModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <h3 class="modal-title"><?= $editItem ? 'Edit Testimonial' : 'Add Testimonial' ?></h3>
            <button type="button" class="btn-close-modal" data-modal-close>&times;</button>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="tm_name">Customer Name *</label>
                    <input type="text" id="tm_name" name="customer_name" class="form-control" required
                           value="<?= e($editItem['customer_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="tm_email">Email</label>
                    <input type="email" id="tm_email" name="customer_email" class="form-control"
                           value="<?= e($editItem['customer_email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="tm_tour">Tour</label>
                    <select id="tm_tour" name="tour_id" class="form-control">
                        <option value="">No specific tour</option>
                        <?php foreach ($tours as $t): ?>
                            <option value="<?= (int) $t['id'] ?>"
                                <?= ($editItem['tour_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                                <?= e($t['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tm_rating">Rating *</label>
                    <select id="tm_rating" name="rating" class="form-control" required>
                        <?php for ($r = 5; $r >= 1; $r--): ?>
                            <option value="<?= $r ?>" <?= ($editItem['rating'] ?? 5) == $r ? 'selected' : '' ?>>
                                <?= $r ?> Star<?= $r > 1 ? 's' : '' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="tm_review">Review Text *</label>
                <textarea id="tm_review" name="review_text" class="form-control" rows="4" required><?= e($editItem['review_text'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="tm_photo">Photo URL</label>
                <input type="text" id="tm_photo" name="customer_photo" class="form-control"
                       value="<?= e($editItem['customer_photo'] ?? '') ?>">
            </div>
            <div class="form-group form-check">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_approved" value="1"
                        <?= ($editItem['is_approved'] ?? 0) ? 'checked' : '' ?>>
                    Approved (visible on website)
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
<script>document.addEventListener('DOMContentLoaded', () => openModal('testimonialModal'));</script>
<?php endif; ?>
