<?php

declare(strict_types=1);

require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/controllers/DestinationController.php';
require_once __DIR__ . '/controllers/TourPackageController.php';
require_once __DIR__ . '/controllers/BookingController.php';
require_once __DIR__ . '/controllers/TourController.php';
require_once __DIR__ . '/controllers/TestimonialController.php';
require_once __DIR__ . '/controllers/AdminController.php';

// Basic CORS for API consumption from a JS frontend (adjust/remove as needed)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

Auth::startSession();

$method = $_SERVER['REQUEST_METHOD'];

// Support method override for PUT/PATCH/DELETE from HTML forms: ?_method=PUT
if ($method === 'POST' && !empty($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$basePath = $scriptDir === '' ? '/' : $scriptDir . '/';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (str_starts_with($requestPath, $basePath)) {
    $path = '/' . trim(substr($requestPath, strlen($basePath)), '/');
} else {
    $path = '/' . trim($requestPath, '/');
}

$segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));

// e.g. /tours/5  -> ['tours', '5']
$resource = $segments[0] ?? '';
$param1 = $segments[1] ?? null;
$param2 = $segments[2] ?? null;

function respondNotFound(): void
{
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Route not found']);
    exit;
}

/**
 * Public (no session):
 *   GET  /destinations, /destinations/{id}, /destinations/slug/{slug}
 *   GET  /packages, /packages/{id}
 *   GET  /tours, /tours/{id}, /tours/slug/{slug}
 *   POST /bookings
 *   GET  /bookings/code/{code}
 *   GET  /testimonials  (approved list; ?tour_id= allowed)
 *   POST /testimonials
 *   POST /admins/login
 *   POST /admins/logout
 *
 * Everything else requires $_SESSION['admin_id'].
 */
$action = null;
$public = false;

try {
    switch ($resource) {

        // ---------------- Destinations ----------------
        case 'destinations':
            $controller = new DestinationController();
            if ($param1 === null && $method === 'GET') {
                $action = fn() => $controller->index();
                $public = true;
            } elseif ($param1 === 'slug' && $param2 && $method === 'GET') {
                $action = fn() => $controller->showBySlug($param2);
                $public = true;
            } elseif ($param1 && $method === 'GET') {
                $action = fn() => $controller->show((int) $param1);
                $public = true;
            } elseif ($param1 === null && $method === 'POST') {
                $action = fn() => $controller->store();
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $action = fn() => $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $action = fn() => $controller->destroy((int) $param1);
            }
            break;

        // ---------------- Tour Packages ----------------
        case 'packages':
            $controller = new TourPackageController();
            if ($param1 === null && $method === 'GET') {
                $action = fn() => $controller->index();
                $public = true;
            } elseif ($param1 && $method === 'GET') {
                $action = fn() => $controller->show((int) $param1);
                $public = true;
            } elseif ($param1 === null && $method === 'POST') {
                $action = fn() => $controller->store();
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $action = fn() => $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $action = fn() => $controller->destroy((int) $param1);
            }
            break;

        // ---------------- Tours ----------------
        case 'tours':
            $controller = new TourController();
            if ($param1 === null && $method === 'GET') {
                $action = fn() => $controller->index();
                $public = true;
            } elseif ($param1 === 'slug' && $param2 && $method === 'GET') {
                $action = fn() => $controller->showBySlug($param2);
                $public = true;
            } elseif ($param1 === 'images' && $param2 && $method === 'DELETE') {
                $action = fn() => $controller->deleteImage((int) $param2);
            } elseif ($param1 && $param2 === 'images' && $method === 'POST') {
                $action = fn() => $controller->addImage((int) $param1);
            } elseif ($param1 && $method === 'GET') {
                $action = fn() => $controller->show((int) $param1);
                $public = true;
            } elseif ($param1 === null && $method === 'POST') {
                $action = fn() => $controller->store();
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $action = fn() => $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $action = fn() => $controller->destroy((int) $param1);
            }
            break;

        // ---------------- Bookings ----------------
        case 'bookings':
            $controller = new BookingController();
            if ($param1 === null && $method === 'GET') {
                $action = fn() => $controller->index();
            } elseif ($param1 === 'code' && $param2 && $method === 'GET') {
                $action = fn() => $controller->showByCode($param2);
                $public = true;
            } elseif ($param1 && $param2 === 'status' && $method === 'PATCH') {
                $action = fn() => $controller->updateStatus((int) $param1);
            } elseif ($param1 && $method === 'GET') {
                $action = fn() => $controller->show((int) $param1);
            } elseif ($param1 === null && $method === 'POST') {
                $action = fn() => $controller->store();
                $public = true;
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $action = fn() => $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $action = fn() => $controller->destroy((int) $param1);
            }
            break;

        // ---------------- Testimonials ----------------
        case 'testimonials':
            $controller = new TestimonialController();
            if ($param1 === null && $method === 'GET') {
                $action = fn() => $controller->index();
                $public = empty($_GET['pending']);
            } elseif ($param1 && $param2 === 'approve' && $method === 'PATCH') {
                $action = fn() => $controller->approve((int) $param1);
            } elseif ($param1 && $param2 === 'reject' && $method === 'PATCH') {
                $action = fn() => $controller->reject((int) $param1);
            } elseif ($param1 && $method === 'GET') {
                $action = fn() => $controller->show((int) $param1);
            } elseif ($param1 === null && $method === 'POST') {
                $action = fn() => $controller->store();
                $public = true;
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $action = fn() => $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $action = fn() => $controller->destroy((int) $param1);
            }
            break;

        // ---------------- Admins ----------------
        case 'admins':
            $controller = new AdminController();
            if ($param1 === 'login' && $method === 'POST') {
                $action = fn() => $controller->login();
                $public = true;
            } elseif ($param1 === 'logout' && $method === 'POST') {
                $action = fn() => $controller->logout();
                $public = true;
            } elseif ($param1 === null && $method === 'GET') {
                $action = fn() => $controller->index();
            } elseif ($param1 && $method === 'GET') {
                $action = fn() => $controller->show((int) $param1);
            } elseif ($param1 === null && $method === 'POST') {
                $action = fn() => $controller->store();
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $action = fn() => $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $action = fn() => $controller->destroy((int) $param1);
            }
            break;
    }

    if ($action === null) {
        respondNotFound();
    }

    if (!$public) {
        Auth::requireAdmin();
    }

    $action();
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error'   => $e->getMessage(),
    ]);
}
