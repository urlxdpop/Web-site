<?php
// Простой endpoint: возвращает JSON данных пользователя.
// Если передан GET-параметр id — вернёт данные для этого id.
// Иначе попытается взять id из cookie user_id.

header('Content-Type: application/json; charset=utf-8');

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'WebSite');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection error']);
    exit;
}

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

$stmt = $mysqli->prepare("SELECT id, username, email, avatar, descr FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$mysqli->close();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// нормализуем avatar 
$avatar = $user['avatar'] ? $user['avatar'] : '';
echo json_encode([
    'id' => intval($user['id']),
    'username' => $user['username'],
    'email' => $user['email'],
    'avatar' => $avatar,
    'descr' => $user['descr']
]);
exit;