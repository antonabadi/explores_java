<?php

declare(strict_types=1);

require_once __DIR__ . '/controllers/DestinationController.php';
require_once __DIR__ . '/controllers/TourPackageController.php';
require_once __DIR__ . '/controllers/TourController.php';
require_once __DIR__ . '/controllers/BookingController.php';
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

$method = $_SERVER['REQUEST_METHOD'];

// Support method override for PUT/PATCH/DELETE from HTML forms: ?_method=PUT
if ($method === 'POST' && !empty($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

// Strip query string and base path, split into segments
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/cms/'; // change if the app lives in a subdirectory, e.g. '/explores-java/'
$path = '/' . trim(substr($path, strlen($basePath)), '/');
$segments = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));

// e.g. /tours/5  -> ['tours', '5']
$resource = $segments[0] ?? '';
$param1 = $segments[1] ?? null;
$param2 = $segments[2] ?? null;

if($path) {
    // echo $path;
    echo " ";
    // exit;
}

function respondNotFound(): void
{
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Route not found']);
    exit;
}

try {
    switch ($resource) {

        // ---------------- Destinations ----------------
        case 'destinations':
            $controller = new DestinationController();
            if ($param1 === null && $method === 'GET') {
                $controller->index();
            } elseif ($param1 === 'slug' && $param2 && $method === 'GET') {
                $controller->showBySlug($param2);
            } elseif ($param1 && $method === 'GET') {
                $controller->show((int) $param1);
            } elseif ($param1 === null && $method === 'POST') {
                $controller->store();
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $controller->destroy((int) $param1);
            } else {
                respondNotFound();
            }
            break;

        // ---------------- Tour Packages ----------------
        case 'packages':
            $controller = new TourPackageController();
            if ($param1 === null && $method === 'GET') {
                $controller->index();
            } elseif ($param1 && $method === 'GET') {
                $controller->show((int) $param1);
            } elseif ($param1 === null && $method === 'POST') {
                $controller->store();
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $controller->destroy((int) $param1);
            } else {
                respondNotFound();
            }
            break;

        // ---------------- Tours ----------------
        case 'tours':
            $controller = new TourController();
            if ($param1 === null && $method === 'GET') {
                $controller->index();
            } elseif ($param1 === 'slug' && $param2 && $method === 'GET') {
                $controller->showBySlug($param2);
            } elseif ($param1 === 'images' && $param2 && $method === 'DELETE') {
                $controller->deleteImage((int) $param2);
            } elseif ($param1 && $param2 === 'images' && $method === 'POST') {
                $controller->addImage((int) $param1);
            } elseif ($param1 && $method === 'GET') {
                $controller->show((int) $param1);
            } elseif ($param1 === null && $method === 'POST') {
                $controller->store();
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $controller->destroy((int) $param1);
            } else {
                respondNotFound();
            }
            break;

        // ---------------- Bookings ----------------
        case 'bookings':
            $controller = new BookingController();
            if ($param1 === null && $method === 'GET') {
                $controller->index();
            } elseif ($param1 === 'code' && $param2 && $method === 'GET') {
                $controller->showByCode($param2);
            } elseif ($param1 && $param2 === 'status' && $method === 'PATCH') {
                $controller->updateStatus((int) $param1);
            } elseif ($param1 && $method === 'GET') {
                $controller->show((int) $param1);
            } elseif ($param1 === null && $method === 'POST') {
                $controller->store();
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $controller->destroy((int) $param1);
            } else {
                respondNotFound();
            }
            break;

        // ---------------- Testimonials ----------------
        case 'testimonials':
            $controller = new TestimonialController();
            if ($param1 === null && $method === 'GET') {
                $controller->index();
            } elseif ($param1 && $param2 === 'approve' && $method === 'PATCH') {
                $controller->approve((int) $param1);
            } elseif ($param1 && $param2 === 'reject' && $method === 'PATCH') {
                $controller->reject((int) $param1);
            } elseif ($param1 && $method === 'GET') {
                $controller->show((int) $param1);
            } elseif ($param1 === null && $method === 'POST') {
                $controller->store();
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $controller->destroy((int) $param1);
            } else {
                respondNotFound();
            }
            break;

        // ---------------- Admins ----------------
        case 'admins':
            $controller = new AdminController();
            if ($param1 === 'login' && $method === 'POST') {
                $controller->login();
            } elseif ($param1 === 'logout' && $method === 'POST') {
                $controller->logout();
            } elseif ($param1 === null && $method === 'GET') {
                $controller->index();
            } elseif ($param1 && $method === 'GET') {
                $controller->show((int) $param1);
            } elseif ($param1 === null && $method === 'POST') {
                $controller->store();
            } elseif ($param1 && in_array($method, ['PUT', 'PATCH'], true)) {
                $controller->update((int) $param1);
            } elseif ($param1 && $method === 'DELETE') {
                $controller->destroy((int) $param1);
            } else {
                respondNotFound();
            }
            break;

        default:
            respondNotFound();
    }
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error'   => $e->getMessage(),
    ]);
}
