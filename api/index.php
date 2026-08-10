<?php

// Vercel PHP entry point for the existing Seed 2 Greens application.

// Project root
$root = dirname(__DIR__);

// Get the requested PHP file from the Vercel route.
$requested = $_GET['file'] ?? 'index.php';

// Remove a leading slash if present.
$requested = ltrim($requested, '/');

// Security: only allow PHP files inside the existing project,
// and prevent direct access to configuration/internal files.
$blocked = [
    'config/',
    'includes/',
    'database/',
    'api/'
];

foreach ($blocked as $folder) {
    if (strpos($requested, $folder) === 0) {
        http_response_code(404);
        exit('Not Found');
    }
}

// Only PHP files are allowed through this router.
if (!str_ends_with(strtolower($requested), '.php')) {
    http_response_code(404);
    exit('Not Found');
}

$target = realpath($root . DIRECTORY_SEPARATOR . $requested);

// Make sure the target actually exists and is inside the project.
if (
    $target === false ||
    strpos($target, realpath($root)) !== 0 ||
    !is_file($target)
) {
    http_response_code(404);
    exit('Page Not Found');
}

// Run the existing PHP page from the project root.
chdir($root);
require $target;