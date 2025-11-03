<?php
require_once __DIR__ . '/../ScriptsForBD/DBController.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $descr = trim($_POST['descr'] ?? '');

    if (!$username || !$email || !$password) {
        $_SESSION['error'] = 'Заполните все обязательные поля';
        header('Location: Reg.html');
        exit;
    }

    if (DBController::userExistsByEmailOrUsername($email, $username)) {
        $_SESSION['error'] = 'Пользователь с таким Email или именем уже существует';
        header('Location: Reg.html');
        exit;
    }

    $avatarPath = '';
    if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../Profile/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fname = basename($_FILES['avatar']['name']);
        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
        $allowed = ['png','jpg','jpeg','gif','webp'];
        if (in_array($ext, $allowed)) {
            $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $uploadDir . $newName;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                $avatarPath = 'Profile/avatars/' . $newName;
            }
        }
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $userId = DBController::createUser($username, $email, $passwordHash, $avatarPath, $descr);
    if ($userId) {
        $_SESSION['user_id'] = $userId;
        setcookie('user_id', $userId, time() + (86400 * 30), "/");
        header('Location: ../Main.html');
        exit;
    } else {
        $_SESSION['error'] = 'Ошибка регистрации';
        header('Location: Reg.html');
        exit;
    }
}
?>
