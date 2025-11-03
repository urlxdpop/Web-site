<?php

require_once __DIR__ . '/../ScriptsForBD/DBController.php';
header('Content-Type: application/json; charset=utf-8');

// Get product ID from request
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$productId) {
    echo json_encode(['error' => 'No product ID provided']);
    exit;
}

// Connect to database and get product info
$mysqli = DBController::connect();
$stmt = $mysqli->prepare("
    SELECT 
        c.*,
        u.username as author_name,
        u.avatar as author_avatar,
        u.descr as author_desc,
        u.email as author_email
    FROM content c
    LEFT JOIN users u ON c.author = u.id
    WHERE c.id = ?
    LIMIT 1
");

if (!$stmt) {
    $mysqli->close();
    echo json_encode(['error' => 'Database error']);
    exit;
}

$stmt->bind_param('i', $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    $mysqli->close();
    echo json_encode(['error' => 'Product not found']);
    exit;
}

// Get similar products (same type, excluding current)
$stmt = $mysqli->prepare("
    SELECT 
        c.*,
        u.username as author_name,
        u.avatar as author_avatar,
        u.descr as author_desc
    FROM content c
    LEFT JOIN users u ON c.author = u.id
    WHERE c.type = ? AND c.id != ?
    ORDER BY RAND()
    LIMIT 4
");

$similarProducts = [];
if ($stmt) {
    $stmt->bind_param('si', $product['type'], $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $similarProducts[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'mainImage' => $row['mainImage'],
            'type' => $row['type'],
            'price' => $row['price'],
            'description' => $row['description'],
            'author' => [
                'id' => $row['author'],
                'name' => $row['author_name'],
                'avatar' => $row['author_avatar'],
                'desc' => $row['author_desc']
            ]
        ];
    }
    $stmt->close();
}

$mysqli->close();

// Format response
echo json_encode([
    'product' => [
        'id' => $product['id'],
        'title' => $product['title'],
        'mainImage' => $product['mainImage'],
        'type' => $product['type'],
        'price' => $product['price'],
        'description' => $product['description'],
        'images' => json_decode($product['images'], true),
        'author' => [
            'id' => $product['author'],
            'name' => $product['author_name'],
            'desc' => $product['author_desc'],
            'avatar' => $product['author_avatar'],
            'email' => $product['author_email']
        ]
    ],
    'similar' => $similarProducts
]);