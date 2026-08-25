<?php

declare(strict_types=1);

// -------------------------------------------------------------
// Unified Front Controller & Router
// -------------------------------------------------------------

// Calculate base path and current request path
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$basePath = $scriptDir === '' ? '/' : $scriptDir . '/';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (str_starts_with($requestPath, $basePath)) {
    $path = '/' . trim(substr($requestPath, strlen($basePath)), '/');
} else {
    $path = '/' . trim($requestPath, '/');
}

$segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
$firstSegment = $segments[0] ?? '';

// Check if request is for CMS or API
if ($firstSegment === 'cms' || $firstSegment === 'api') {
    // Route request through CMS Router
    require_once __DIR__ . '/cms/index.php';
    exit;
}

// -------------------------------------------------------------
// Frontend Public Routing
// -------------------------------------------------------------
$page = $firstSegment === '' ? 'home' : $firstSegment;

ob_start();

switch ($page) {
    case 'home':
        include __DIR__ . '/views/home.php';
        break;
    case 'blog':
        include __DIR__ . '/views/blog.php';
        break;
    case 'blog-detail':
        include __DIR__ . '/views/blog.detail.php';
        break;
    case 'destinations':
        include __DIR__ . '/views/destinations.php';
        break;
    case 'packages':
        include __DIR__ . '/views/packages.php';
        break;
    case 'detail':
        include __DIR__ . '/views/package.detail.php';
        break;
    case 'admin':
        header('Location: cms');
        exit;
    default:
        include __DIR__ . '/views/404.php';
        break;
}

$content = ob_get_clean();

// Render Main Layout (Header + Content + Footer)
include __DIR__ . '/views/modules/header.php';
echo $content;
include __DIR__ . '/views/modules/footer.php';


