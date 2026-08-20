<?php

// Vercel PHP entry point for Seed 2 Greens.

$root = dirname(__DIR__);

$requested = $_GET['file'] ?? 'index.php';
$requested = ltrim($requested, '/');

// Only allow PHP files.
if (!str_ends_with(strtolower($requested), '.php')) {
    http_response_code(404);
    exit('Not Found');
}

// Prevent access to internal files.
$blocked = [
    'config/',
    'includes/',
    'database/',
    'api/index.php',
];

foreach ($blocked as $blocked_path) {
    if ($requested === $blocked_path || strpos($requested, $blocked_path) === 0) {
        http_response_code(404);
        exit('Not Found');
    }
}

$rootReal = realpath($root);
$target = realpath($root . DIRECTORY_SEPARATOR . $requested);

// Make sure the requested file exists and stays inside the project.
if (
    $target === false ||
    $rootReal === false ||
    strpos($target, $rootReal . DIRECTORY_SEPARATOR) !== 0 ||
    !is_file($target)
) {
    http_response_code(404);
    exit('Page Not Found');
}

chdir($root);

require $target;