<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'WebSite');

$avatarSrc = '../Profile/profile.png'; // fallback
$userId = $_COOKIE['user_id'] ?? null;

if ($userId) {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if (!$mysqli->connect_error) {
        $stmt = $mysqli->prepare("SELECT avatar FROM users WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->bind_result($dbAvatar);
            if ($stmt->fetch() && $dbAvatar) {
                // avatar в БД хранится как относительный путь от корня, например "Profile/avatars/..."
                $avatarSrc = '../' . ltrim($dbAvatar, '/');
            }
            $stmt->close();
        }
        $mysqli->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="../Style.css">
</head>
<body>
    <header>
        <div class="top-bar">
            <button id="mainBtn" onclick="window.top.location.href='../Main.html'">Рекомендации</button>
            <button id="Save" onclick="window.top.location.href='../Save/Saved.html'">Библиотека</button>
            <?php if ($userId): ?>
                <img id="accountBtn" src="<?php echo htmlspecialchars($avatarSrc, ENT_QUOTES); ?>" alt="Аватар" style="width:36px;height:36px;border-radius:50%;cursor:pointer;border:1px solid #dbdbdb"
                     onclick="window.top.location.href='../Profile/MyProfile.html'">
            <?php else: ?>
                <button id="accountBtn" onclick="window.top.location.href='../Register/Reg.html'">Аккаунт</button>
            <?php endif; ?>
        </div>
    </header>
    <script src="Script.js"></script>
</body>
</html>