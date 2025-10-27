<?php
// Регистрация пользователя и создание таблицы, если нужно.
define('DB_HOST', 'localhost');
define('DB_PORT', 3306); // измените если нужно
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

// Обработка отправки формы
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

    // Проверить есть ли пользователь с таким email или username
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
    $stmt->bind_param('ss', $email, $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = 'Пользователь с таким Email или именем уже существует';
        $stmt->close();
        header('Location: Reg.html');
        exit;
    }
    $stmt->close();

    // Обработка загрузки аватара (необязательно)
    $avatarPath = ''; // путь для записи в БД (от корня сайта)
    if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../Profile/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fname = basename($_FILES['avatar']['name']);
        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
        // ограничение по расширению (простая проверка)
        $allowed = ['png','jpg','jpeg','gif','webp'];
        if (in_array($ext, $allowed)) {
            $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $uploadDir . $newName;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                // относительный путь от корня сайта
                $avatarPath = 'Profile/avatars/' . $newName;
            }
        }
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Вставляем все поля: username, email, password, avatar, descr
    $ins = $mysqli->prepare("INSERT INTO users (username, email, password, avatar, descr) VALUES (?, ?, ?, ?, ?)");
    if (!$ins) {
        $_SESSION['error'] = 'Ошибка подготовки запроса';
        header('Location: Reg.html');
        exit;
    }
    $ins->bind_param('sssss', $username, $email, $passwordHash, $avatarPath, $descr);
    if ($ins->execute()) {
        $userId = $ins->insert_id;
        // установить сессию и cookie
        $_SESSION['user_id'] = $userId;
        setcookie('user_id', $userId, time() + (86400 * 30), "/"); // 30 дней
        $ins->close();
        header('Location: ../Main.html');
        exit;
    } else {
        $_SESSION['error'] = 'Ошибка регистрации';
        $ins->close();
        header('Location: Reg.html');
        exit;
    }
}

?>
