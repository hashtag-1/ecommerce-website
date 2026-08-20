<?php
// Seed2Greens - Search API
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

$search_term = isset($_GET['q']) ? sanitize($_GET['q']) : '';

if (strlen($search_term) < 2) {
    echo json_encode(['results' => []]);
    exit();
}

$products = searchProducts($search_term, null, 5);

$results = [];
foreach ($products as $product) {
    $results[] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'image' => getProductImage($product),
        'category' => $product['category_name']
    ];
}

echo json_encode([
    'results' => $results,
    'count' => count($results)
]);
exit();