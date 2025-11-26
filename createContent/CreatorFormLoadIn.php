<?php
session_start();
require_once __DIR__ . '/../ScriptsForBD/DBController.php';
header('Content-Type: text/html; charset=utf-8');

$uid = $_SESSION['user_id'] ?? ($_COOKIE['user_id'] ?? null);
if (!$uid || intval($uid) <= 0) {
    header('Location: ../Register/Login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $title = trim($_POST['title'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $author = intval($_POST['author'] ?? $uid);

    if (!$title || !$type) {
        $_SESSION['error'] = 'Заполните обязательные поля';
        header('Location: CreatorForm.html');
        exit;
    }

    $uploadDirRel = 'Content/images/';
    $uploadDir = __DIR__ . '/../' . $uploadDirRel;
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $mainImagePath = '';
    if (!empty($_FILES['mainImage']) && $_FILES['mainImage']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['mainImage']['name'], PATHINFO_EXTENSION));
        $name = time() . '_main_' . bin2hex(random_bytes(5)) . '.' . $ext;
        $dest = $uploadDir . $name;
        if (move_uploaded_file($_FILES['mainImage']['tmp_name'], $dest)) {
            $mainImagePath = $uploadDirRel . $name;
        }
    }

    $imagesArr = [];
    if (!empty($_FILES['images'])) {
        for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
            $name = time() . "_img_{$i}_" . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = $uploadDir . $name;
            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $dest)) {
                $imagesArr[] = $uploadDirRel . $name;
            }
        }
    }

    $imagesJson = json_encode($imagesArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ok = DBController::insertContent($title, $mainImagePath, $type, $price, $description, $imagesJson, $author);
    if ($ok) {
        header('Location: ../Profile/MyProfile.html');
        exit;
    } else {
        error_log("insert content error");
        echo "Ошибка сохранения товара";
        exit;
    }
} else {
    header('Location: CreatorForm.html');
    exit;
}
?>