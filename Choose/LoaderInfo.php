<?php
require_once __DIR__ . '/../ScriptsForBD/DBController.php';
header('Content-Type: application/json; charset=utf-8');


$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$productId) {
    echo json_encode(['error' => 'No product ID provided']);
    exit;
}


$product = DBController::getProductById($productId);
if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit;
}

$similar = DBController::getSimilarProducts($product['type'], $productId, 4);

echo json_encode([
    'product' => $product,
    'similar' => $similar
]);