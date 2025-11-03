<?php
// Если передан GET-параметр id — вернёт данные для этого id.
// Иначе попытается взять id из cookie user_id.

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../ScriptsForBD/DBController.php';

$id = null;
if (!empty($_GET['id'])) {
    $id = intval($_GET['id']);
} elseif (!empty($_COOKIE['user_id'])) {
    $id = intval($_COOKIE['user_id']);
}

if (!$id) {
    echo json_encode(['error' => 'No user id']);
    exit;
}

$user = DBController::getUserById($id);
if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

$avatar = $user['avatar'] ? $user['avatar'] : '';
echo json_encode([
    'id' => intval($user['id']),
    'username' => $user['username'],
    'email' => $user['email'],
    'avatar' => $avatar,
    'descr' => $user['descr']
]);
exit;
?>