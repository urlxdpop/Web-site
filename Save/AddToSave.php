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

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? intval($input['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'No product id']);
    exit;
}

$ok = DBController::addToSaved($uid, $id);
if ($ok) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Cannot add (may be already in buy or other error)']);
}