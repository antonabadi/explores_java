<?php

// Simple Router
$page = $_GET['page'] ?? 'home';

// Start output buffering
ob_start();

// Basic routing logic
switch ($page) {
    case 'home':
        include 'views/home.php';
        break;
    case 'destinations':
        include 'views/destinations.php';
        break;
    case 'packages':
        include 'views/packages.php';
        break;
    case 'detail':
        include 'views/package.detail.php';
        break;
    case 'admin':
    case 'cms':
        header('Location: cms/index.php');
        break;
    default:
        include 'views/404.php';
        break;
}

$content = ob_get_clean();

// Include Main Layout (templates/header + content + footer)
include 'views/modules/header.php';
echo $content;
include 'views/modules/footer.php';
