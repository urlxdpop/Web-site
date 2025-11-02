<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

define('DB_HOST','localhost');
define('DB_PORT',3306);
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','WebSite');

$uid = $_SESSION['user_id'] ?? ($_COOKIE['user_id'] ?? null);
if (!$uid || intval($uid) <= 0) {
    header('Location: ../Register/Login.html');
    exit;
}

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($mysqli->connect_error) {
    echo "DB connection error";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $title = trim($_POST['title'] ?? ' ');
    $type = trim($_POST['type'] ?? ' ');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $author = intval($_POST['author'] ?? $uid);

    if (!$title || !$type) {
        $_SESSION['error'] = 'Заполните обязательные поля';
        header('Location: CreatorForm.html');
        exit;
    }

    // папка для изображений
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

    $stmt = $mysqli->prepare("INSERT INTO content (title, mainImage, type, price, description, images, author) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("prepare error: " . $mysqli->error);
        echo "Ошибка подготовки запроса";
        exit;
    }
    $stmt->bind_param('sssdssi', $title, $mainImagePath, $type, $price, $description, $imagesJson, $author);
    $stmt->close();

    $stmt = $mysqli->prepare("INSERT INTO content (title, mainImage, type, price, description, images, author) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssdssi', $title, $mainImagePath, $type, $price, $description, $imagesJson, $author);
    // Note: due to PHP type string, use this fallback - if this fails, try string binding
    $stmt->close();

    // Simpler safe insertion using real_escape as fallback
    $titleEsc = $mysqli->real_escape_string($title);
    $mainImageEsc = $mysqli->real_escape_string($mainImagePath);
    $typeEsc = $mysqli->real_escape_string($type);
    $priceVal = floatval($price);
    $descEsc = $mysqli->real_escape_string($description);
    $imagesEsc = $mysqli->real_escape_string($imagesJson);
    $authorVal = intval($author);

    $sql = "INSERT INTO content (title, mainImage, type, price, description, images, author) VALUES ('{$titleEsc}','{$mainImageEsc}','{$typeEsc}', {$priceVal}, '{$descEsc}', '{$imagesEsc}', {$authorVal})";
    if ($mysqli->query($sql)) {
        $mysqli->close();
        header('Location: ../Profile/MyProfile.html');
        exit;
    } else {
        error_log("insert error: " . $mysqli->error);
        echo "Ошибка сохранения товара";
        $mysqli->close();
        exit;
    }
} else {
    header('Location: CreatorForm.html');
    exit;
}
?>