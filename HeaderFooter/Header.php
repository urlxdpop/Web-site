<?php
require_once __DIR__ . '/../ScriptsForBD/DBController.php';
session_start();

$avatarSrc = '../Profile/profile.png';
$userId = $_COOKIE['user_id'] ?? null;

if ($userId) {
    $avatar = DBController::getAvatarByUser(intval($userId));
    if ($avatar) {
        $avatarSrc = '../' . ltrim($avatar, '/');
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
                <img id="accountBtn" src="<?php echo htmlspecialchars($avatarSrc, ENT_QUOTES); ?>" alt="Аватар"
                     onclick="window.top.location.href='../Profile/MyProfile.html'">
            <?php else: ?>
                <button id="accountBtn" onclick="window.top.location.href='../Register/Reg.html'">Аккаунт</button>
            <?php endif; ?>
        </div>
    </header>
    <script src="Script.js"></script>
</body>
</html>