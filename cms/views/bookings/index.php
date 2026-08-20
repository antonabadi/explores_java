<?php
$pdo = db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$tours = $pdo->query(
    'SELECT id, title, price FROM tours WHERE is_active = 1 ORDER BY title'
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $tourId = (int) ($_POST['tour_id'] ?? 0);
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $bookingDate = $_POST['booking_date'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'pending';

        if ($tourId < 1 || $customerName === '' || $customerEmail === '' || $quantity < 1 || $bookingDate === '') {
            setFlash('danger', 'Please fill all required fields.');
        } elseif (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            setFlash('danger', 'Invalid email address.');
        } elseif (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
            setFlash('danger', 'Invalid status.');
        } else {
            $tourStmt = $pdo->prepare('SELECT price FROM tours WHERE id = ?');
            $tourStmt->execute([$tourId]);
            $tour = $tourStmt->fetch();
            if (!$tour) {
                setFlash('danger', 'Tour not found.');
            } else {
                $totalPrice = (float) $tour['price'] * $quantity;
                $bookingCode = 'EJ-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));

                $stmt = $pdo->prepare(
                    'INSERT INTO bookings (tour_id, booking_code, customer_name, customer_email, customer_phone,
                     quantity, total_price, booking_date, status, notes)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $tourId, $bookingCode, $customerName, $customerEmail, $customerPhone,
                    $quantity, $totalPrice, $bookingDate, $status, $notes ?: null,
                ]);
                setFlash('success', "Booking created: {$bookingCode}");
            }
        }
        redirect('dashboard.php?page=bookings');
    }

    if ($action === 'update_status') {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
            setFlash('danger', 'Invalid status.');
        } else {
            $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?')->execute([$status, $id]);
            setFlash('success', 'Booking status updated.');
        }
        redirect('dashboard.php?page=bookings');
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $bookingDate = $_POST['booking_date'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'pending';

        $booking = $pdo->prepare('SELECT tour_id FROM bookings WHERE id = ?');
        $booking->execute([$id]);
        $bookingRow = $booking->fetch();

        if (!$bookingRow) {
            setFlash('danger', 'Booking not found.');
        } else {
            $tourStmt = $pdo->prepare('SELECT price FROM tours WHERE id = ?');
            $tourStmt->execute([$bookingRow['tour_id']]);
            $tour = $tourStmt->fetch();
            $totalPrice = (float) ($tour['price'] ?? 0) * $quantity;

            $stmt = $pdo->prepare(
                'UPDATE bookings SET customer_name = ?, customer_email = ?, customer_phone = ?,
                 quantity = ?, total_price = ?, booking_date = ?, status = ?, notes = ? WHERE id = ?'
            );
            $stmt->execute([
                $customerName, $customerEmail, $customerPhone, $quantity,
                $totalPrice, $bookingDate, $status, $notes ?: null, $id,
            ]);
            setFlash('success', 'Booking updated.');
        }
        redirect('dashboard.php?page=bookings');
    }
}

if ($action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    $pdo->prepare('DELETE FROM bookings WHERE id = ?')->execute([$id]);
    setFlash('success', 'Booking deleted.');
    redirect('dashboard.php?page=bookings');
}

$filterStatus = $_GET['status'] ?? '';
$sql = 'SELECT b.*, t.title AS tour_title, t.price AS tour_price
        FROM bookings b
        LEFT JOIN tours t ON t.id = b.tour_id
        WHERE 1=1';
$params = [];
if ($filterStatus !== '' && in_array($filterStatus, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
    $sql .= ' AND b.status = ?';
    $params[] = $filterStatus;
}
$sql .= ' ORDER BY b.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editItem = $stmt->fetch() ?: null;
}
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Bookings</h1>
            <p class="date-indicator">Manage customer tour reservations</p>
        </div>
        <button type="button" class="btn-primary" data-modal-open="bookingModal">+ New Booking</button>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="glass-card filter-bar">
        <div class="tab-headers tab-headers-inline">
            <a href="dashboard.php?page=bookings" class="tab-link <?= $filterStatus === '' ? 'active' : '' ?>">All</a>
            <a href="dashboard.php?page=bookings&status=pending" class="tab-link <?= $filterStatus === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="dashboard.php?page=bookings&status=confirmed" class="tab-link <?= $filterStatus === 'confirmed' ? 'active' : '' ?>">Confirmed</a>
            <a href="dashboard.php?page=bookings&status=completed" class="tab-link <?= $filterStatus === 'completed' ? 'active' : '' ?>">Completed</a>
            <a href="dashboard.php?page=bookings&status=cancelled" class="tab-link <?= $filterStatus === 'cancelled' ? 'active' : '' ?>">Cancelled</a>
        </div>
    </div>

    <div class="glass-card">
        <?php if ($bookings): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Customer</th>
                            <th>Tour</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Total</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><code class="code-badge"><?= e($b['booking_code']) ?></code></td>
                                <td>
                                    <div class="fw-bold"><?= e($b['customer_name']) ?></div>
                                    <div class="text-muted text-sm"><?= e($b['customer_email']) ?></div>
                                </td>
                                <td><?= e($b['tour_title'] ?? '-') ?></td>
                                <td class="text-center"><?= (int) $b['quantity'] ?></td>
                                <td class="text-right"><?= formatRupiah($b['total_price']) ?></td>
                                <td><?= formatDate($b['booking_date']) ?></td>
                                <td><?= statusBadge($b['status']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="dashboard.php?page=bookings&edit=<?= (int) $b['id'] ?>"
                                           class="btn-icon btn-edit" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <?php if ($b['status'] === 'pending'): ?>
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="btn-icon btn-success" title="Confirm">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="dashboard.php?page=bookings&action=delete&id=<?= (int) $b['id'] ?>"
                                           class="btn-icon btn-delete" title="Delete"
                                           data-confirm="Delete booking <?= e($b['booking_code']) ?>?">
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
            <p class="empty-state">No bookings found.</p>
        <?php endif; ?>
    </div>
</main>

<div class="modal-overlay" id="bookingModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <h3 class="modal-title"><?= $editItem ? 'Edit Booking' : 'New Booking' ?></h3>
            <button type="button" class="btn-close-modal" data-modal-close>&times;</button>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
            <?php endif; ?>

            <?php if (!$editItem): ?>
                <div class="form-group">
                    <label for="booking_tour">Tour *</label>
                    <select id="booking_tour" name="tour_id" class="form-control" required>
                        <option value="">Select tour</option>
                        <?php foreach ($tours as $t): ?>
                            <option value="<?= (int) $t['id'] ?>" data-price="<?= (float) $t['price'] ?>">
                                <?= e($t['title']) ?> — <?= formatRupiah($t['price']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="booking_name">Customer Name *</label>
                    <input type="text" id="booking_name" name="customer_name" class="form-control" required
                           value="<?= e($editItem['customer_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="booking_email">Email *</label>
                    <input type="email" id="booking_email" name="customer_email" class="form-control" required
                           value="<?= e($editItem['customer_email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="booking_phone">Phone *</label>
                    <input type="text" id="booking_phone" name="customer_phone" class="form-control" required
                           value="<?= e($editItem['customer_phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="booking_qty">Quantity *</label>
                    <input type="number" id="booking_qty" name="quantity" class="form-control" min="1" required
                           value="<?= e((string) ($editItem['quantity'] ?? '1')) ?>">
                </div>
                <div class="form-group">
                    <label for="booking_date">Booking Date *</label>
                    <input type="date" id="booking_date" name="booking_date" class="form-control" required
                           value="<?= e($editItem['booking_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="form-group">
                    <label for="booking_status">Status</label>
                    <select id="booking_status" name="status" class="form-control">
                        <?php foreach (['pending', 'confirmed', 'cancelled', 'completed'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($editItem['status'] ?? 'pending') === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="booking_notes">Notes</label>
                <textarea id="booking_notes" name="notes" class="form-control" rows="3"><?= e($editItem['notes'] ?? '') ?></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-submit"><?= $editItem ? 'Update' : 'Create' ?></button>
            </div>
        </form>
    </div>
</div>

<?php if ($editItem): ?>
<script>document.addEventListener('DOMContentLoaded', () => openModal('bookingModal'));</script>
<?php endif; ?>
