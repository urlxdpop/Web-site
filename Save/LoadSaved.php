<?php
require_once __DIR__ . '/../ScriptsForBD/DBController.php';
header('Content-Type: application/json; charset=utf-8');
session_start();

$uid = $_SESSION['user_id'] ?? ($_COOKIE['user_id'] ?? null);
if (!$uid || intval($uid) <= 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}
$uid = intval($uid);

$row = DBController::getSavedRow($uid);
$savedIds = [];
$buyIds = [];
if ($row) {
    $savedIds = $row['saved'] === '' ? [] : array_filter(explode('*', $row['saved']), 'strlen');
    $buyIds = $row['buy'] === '' ? [] : array_filter(explode('*', $row['buy']), 'strlen');
    $savedIds = array_map('intval', $savedIds);
    $buyIds = array_map('intval', $buyIds);
}

$savedIds = array_values(array_diff($savedIds, $buyIds));

$allIds = array_values(array_unique(array_merge($savedIds, $buyIds)));
$products = [];
if (count($allIds) > 0) {
    $products = DBController::getProductsByIds($allIds);
    $byId = [];
    foreach ($products as $p) $byId[$p['id']] = $p;
    $savedProducts = [];
    foreach ($savedIds as $id) if (isset($byId[$id])) $savedProducts[] = $byId[$id];
    $buyProducts = [];
    foreach ($buyIds as $id) if (isset($byId[$id])) $buyProducts[] = $byId[$id];
} else {
    $savedProducts = [];
    $buyProducts = [];
}

echo json_encode([
    'saved' => $savedProducts,
    'buy' => $buyProducts
]);