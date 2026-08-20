<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/Database.php';

function db(): PDO
{
    return Database::getConnection();
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'item';
}

function uniqueSlug(PDO $pdo, string $table, string $name, ?int $excludeId = null): string
{
    $slug = slugify($name);
    $candidate = $slug;
    $counter = 1;

    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ?";
        $params = [$candidate];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $candidate;
        }
        $candidate = $slug . '-' . $counter++;
    }
}

function formatRupiah(float|int|string $amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

function formatDate(?string $date): string
{
    if (!$date) {
        return '-';
    }
    return date('d M Y', strtotime($date));
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function statusBadge(string $status): string
{
    $map = [
        'pending'   => 'badge-warning',
        'confirmed' => 'badge-success',
        'cancelled' => 'badge-danger',
        'completed' => 'badge-info',
    ];
    $class = $map[$status] ?? 'badge-muted';
    return '<span class="badge ' . $class . '">' . e(ucfirst($status)) . '</span>';
}

$page = $_GET['page'] ?? 'dashboard';
$publicPages = ['login'];

if ($page === 'logout') {
    session_unset();
    session_destroy();
    redirect('dashboard.php?page=login');
}

if (!in_array($page, $publicPages, true) && empty($_SESSION['admin_id'])) {
    redirect('dashboard.php?page=login');
}

$views = [
    'dashboard'     => __DIR__ . '/views/dashboard/index.php',
    'destinations'  => __DIR__ . '/views/destinations/index.php',
    'packages'      => __DIR__ . '/views/packages/index.php',
    'tours'         => __DIR__ . '/views/tours/index.php',
    'bookings'      => __DIR__ . '/views/bookings/index.php',
    'testimonials'  => __DIR__ . '/views/testimonials/index.php',
    'login'         => __DIR__ . '/views/login.php',
];

if ($page === 'login') {
    require $views['login'];
    exit;
}

if (!isset($views[$page])) {
    http_response_code(404);
    $pageTitle = '404 Not Found';
    require __DIR__ . '/views/templates/header.php';
    require __DIR__ . '/views/templates/sidebar.php';
    echo '<main class="main-content"><div class="glass-card"><h1 class="page-title">Page not found</h1><p class="text-muted">The page you requested does not exist.</p></div></main>';
    require __DIR__ . '/views/templates/footer.php';
    exit;
}

$pageTitles = [
    'dashboard'    => 'Dashboard',
    'destinations' => 'Destinations',
    'packages'     => 'Tour Packages',
    'tours'        => 'Tours',
    'bookings'     => 'Bookings',
    'testimonials' => 'Testimonials',
];

$pageTitle = $pageTitles[$page] ?? 'Explores Java CMS';
$activePage = $page;
$flash = getFlash();

require __DIR__ . '/views/templates/header.php';
require __DIR__ . '/views/templates/sidebar.php';
require $views[$page];
require __DIR__ . '/views/templates/footer.php';
