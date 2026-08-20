<?php
$pdo = db();

$stats = [
    'destinations' => (int) $pdo->query('SELECT COUNT(*) FROM destinations')->fetchColumn(),
    'tours'        => (int) $pdo->query('SELECT COUNT(*) FROM tours')->fetchColumn(),
    'bookings'     => (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn(),
    'pending'      => (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'revenue'      => (float) $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status IN ('confirmed','completed')")->fetchColumn(),
    'testimonials' => (int) $pdo->query('SELECT COUNT(*) FROM testimonials WHERE is_approved = 0')->fetchColumn(),
];

$recentBookings = $pdo->query(
    'SELECT b.*, t.title AS tour_title
     FROM bookings b
     LEFT JOIN tours t ON t.id = b.tour_id
     ORDER BY b.created_at DESC
     LIMIT 8'
)->fetchAll();

$monthlyRevenue = $pdo->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
            SUM(total_price) AS total
     FROM bookings
     WHERE status IN ('confirmed','completed')
     GROUP BY month
     ORDER BY month DESC
     LIMIT 6"
)->fetchAll();
$monthlyRevenue = array_reverse($monthlyRevenue);

$topTours = $pdo->query(
    'SELECT t.title, COUNT(b.id) AS booking_count, COALESCE(SUM(b.total_price), 0) AS revenue
     FROM tours t
     LEFT JOIN bookings b ON b.tour_id = t.id
     GROUP BY t.id
     ORDER BY booking_count DESC
     LIMIT 5'
)->fetchAll();
?>

<main class="main-content">
    <div class="header-bar">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="date-indicator">Welcome back, <?= e($_SESSION['admin_username'] ?? 'Admin') ?></p>
        </div>
        <span class="date-indicator"><?= date('l, d F Y') ?></span>
    </div>

    <?php if ($flash): ?>
        <div class="alert-box alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="glass-card stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Destinations</span>
                <span class="stat-value"><?= $stats['destinations'] ?></span>
            </div>
        </div>
        <div class="glass-card stat-card">
            <div class="stat-icon success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Active Tours</span>
                <span class="stat-value"><?= $stats['tours'] ?></span>
            </div>
        </div>
        <div class="glass-card stat-card">
            <div class="stat-icon warning">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Bookings</span>
                <span class="stat-value"><?= $stats['bookings'] ?></span>
            </div>
        </div>
        <div class="glass-card stat-card">
            <div class="stat-icon danger">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Pending Bookings</span>
                <span class="stat-value"><?= $stats['pending'] ?></span>
            </div>
        </div>
        <div class="glass-card stat-card">
            <div class="stat-icon success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Revenue</span>
                <span class="stat-value stat-value-sm"><?= formatRupiah($stats['revenue']) ?></span>
            </div>
        </div>
        <div class="glass-card stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Pending Reviews</span>
                <span class="stat-value"><?= $stats['testimonials'] ?></span>
            </div>
        </div>
    </div>

    <div class="report-grid">
        <div class="glass-card chart-card">
            <h2 class="card-title">Monthly Revenue</h2>
            <div class="chart-container">
                <canvas id="revenueChart" height="260"
                    data-labels='<?= e(json_encode(array_column($monthlyRevenue, 'month'))) ?>'
                    data-values='<?= e(json_encode(array_map('floatval', array_column($monthlyRevenue, 'total')))) ?>'>
                </canvas>
            </div>
        </div>

        <div class="glass-card report-table-card">
            <h2 class="card-title">Top Tours</h2>
            <?php if ($topTours): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th class="text-center">Bookings</th>
                                <th class="text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topTours as $tour): ?>
                                <tr>
                                    <td><?= e($tour['title']) ?></td>
                                    <td class="text-center"><?= (int) $tour['booking_count'] ?></td>
                                    <td class="text-right"><?= formatRupiah($tour['revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="empty-state">No tour data yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="glass-card" style="margin-top: 1.5rem;">
        <div class="card-header-row">
            <h2 class="card-title">Recent Bookings</h2>
            <a href="dashboard.php?page=bookings" class="btn-link">View all</a>
        </div>
        <?php if ($recentBookings): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Customer</th>
                            <th>Tour</th>
                            <th>Date</th>
                            <th class="text-right">Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td><code class="code-badge"><?= e($b['booking_code']) ?></code></td>
                                <td><?= e($b['customer_name']) ?></td>
                                <td><?= e($b['tour_title'] ?? '-') ?></td>
                                <td><?= formatDate($b['booking_date']) ?></td>
                                <td class="text-right"><?= formatRupiah($b['total_price']) ?></td>
                                <td><?= statusBadge($b['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty-state">No bookings yet.</p>
        <?php endif; ?>
    </div>
</main>
