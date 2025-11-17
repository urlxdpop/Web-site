<?php
require_once __DIR__ . '/../ScriptsForBD/DBController.php';
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: ../Main.html');
    exit;
}

if (!empty($_COOKIE['user_id'])) {
    $uid = intval($_COOKIE['user_id']);
    $user = DBController::getUserById($uid);
    if ($user) {
        $_SESSION['user_id'] = $uid;
        header('Location: ../Main.html');
        exit;
    }
}
