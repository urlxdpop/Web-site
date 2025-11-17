<?php
// Файл обработки логина
require_once __DIR__ . '/../ScriptsForBD/DBController.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $identifier = trim($_POST['email_or_username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$identifier || !$password) {
        $_SESSION['error'] = 'Заполните все поля';
        header('Location: Login.html');
        exit;
    }

    $user = DBController::getUserByEmailOrUsername($identifier);
    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            setcookie('user_id', $user['id'], time() + 86400 * 30, "/");
            header('Location: ../Main.html');
            exit;
        } else {
            $_SESSION['error'] = 'Неверный пароль';
        }
    } else {
        $_SESSION['error'] = 'Пользователь не найден';
    }
    header('Location: Login.html');
    exit;
}
