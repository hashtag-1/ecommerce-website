<?php
// Seed2Greens - Submit Review API
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid request. Please try again.']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$rating = (int)($_POST['rating'] ?? 0);
$reviewText = trim($_POST['review'] ?? '');

if (!$name || strlen($name) > 100) {
    echo json_encode(['success' => false, 'message' => 'Please fill all fields correctly.']);
    exit;
}
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Please fill all fields correctly.']);
    exit;
}
if (!$reviewText || strlen($reviewText) > 2000) {
    echo json_encode(['success' => false, 'message' => 'Please fill all fields correctly.']);
    exit;
}

try {
    addReview($name, '', $rating, $reviewText);
    echo json_encode(['success' => true, 'message' => 'Thank you for your review!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to submit review. Please try again.']);
}