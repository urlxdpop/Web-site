
<?php
// Подключаем на страницах, где нужна проверка: если пользователь уже вошёл — перенаправляем.
session_start();

if (!empty($_SESSION['user_id'])) {
    // уже в сессии
    header('Location: ../Main.html');
    exit;
}

// если есть cookie и в БД такой пользователь — восстанавливаем сессию
if (!empty($_COOKIE['user_id'])) {
    $uid = intval($_COOKIE['user_id']);
    // Настройки БД — синхронизируйте с reg.php
    $mysqli = new mysqli('localhost', 'root', '', 'WebSite', 3306);
    if (!$mysqli->connect_errno) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $_SESSION['user_id'] = $uid;
            $stmt->close();
            $mysqli->close();
            header('Location: ../Main.html');
            exit;
        }
        $stmt->close();
        $mysqli->close();
    }
}
// если не авторизован — ничего не делаем
?>