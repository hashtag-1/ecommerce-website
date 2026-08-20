<?php
// Seed2Greens - Get Reviews API
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

try {
    $reviews = getFeaturedReviews(getFeaturedMode(), 10);
    echo json_encode(['success' => true, 'reviews' => $reviews]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'reviews' => []]);
}