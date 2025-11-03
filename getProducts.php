<?php
require_once __DIR__ . '/ScriptsForBD/DBController.php';
header('Content-Type: application/json; charset=utf-8');

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, min(50, intval($_GET['limit']))) : 20;
$author = isset($_GET['author']) && $_GET['author'] !== '' ? intval($_GET['author']) : null;

$products = DBController::getProducts($page, $limit, $author);
$total = DBController::getProductsCount($author);

echo json_encode([
    'products' => $products,
    'total' => $total,
    'page' => $page,
    'pages' => $limit ? ceil($total / $limit) : 1
]);