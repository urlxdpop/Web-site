
<?php
// Файл обработки логина
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'WebSite');

session_start();

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($mysqli->connect_error) {
    http_response_code(500);
    echo "DB connection error: " . $mysqli->connect_error;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $identifier = trim($_POST['email_or_username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$identifier || !$password) {
        $_SESSION['error'] = 'Заполните все поля';
        header('Location: Login.html');
        exit;
    }

    $stmt = $mysqli->prepare("SELECT id, password FROM Users WHERE email = ? OR username = ? LIMIT 1");
    $stmt->bind_param('ss', $identifier, $identifier);
    $stmt->execute();
    $stmt->bind_result($id, $hash);
    if ($stmt->fetch()) {
        if (password_verify($password, $hash)) {
            $_SESSION['user_id'] = $id;
            setcookie('user_id', $id, time() + (86400 * 30), "/");
            $stmt->close();
            header('Location: ../Main.html');
            exit;
        } else {
            $_SESSION['error'] = 'Неверный пароль';
        }
    } else {
        $_SESSION['error'] = 'Пользователь не найден';
    }
    $stmt->close();
    header('Location: Login.html');
    exit;
}
?>