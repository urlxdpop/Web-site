<?php
class DBController {
    private static $cfg = [
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
        'db'   => 'WebSite',
        'charset' => 'utf8mb4'
    ];

    public static function connect(): mysqli {
        $c = new mysqli(self::$cfg['host'], self::$cfg['user'], self::$cfg['pass'], self::$cfg['db'], self::$cfg['port']);
        if ($c->connect_error) {
            throw new RuntimeException('DB connect error: ' . $c->connect_error);
        }
        $c->set_charset(self::$cfg['charset']);
        return $c;
    }

    public static function getUserById(int $id): ?array {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("SELECT id, username, email, avatar, descr FROM users WHERE id = ? LIMIT 1");
        if (!$stmt) { $mysqli->close(); return null; }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        $mysqli->close();
        return $res;
    }

    public static function getUserByEmailOrUsername(string $identifier): ?array {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email = ? OR username = ? LIMIT 1");
        if (!$stmt) { $mysqli->close(); return null; }
        $stmt->bind_param('ss', $identifier, $identifier);
        $stmt->execute();
        $stmt->bind_result($id, $passwordHash);
        $got = $stmt->fetch();
        $stmt->close();
        $mysqli->close();
        if (!$got) return null;
        return ['id' => $id, 'password' => $passwordHash];
    }

    public static function userExistsByEmailOrUsername(string $email, string $username): bool {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
        if (!$stmt) { $mysqli->close(); return false; }
        $stmt->bind_param('ss', $email, $username);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        $mysqli->close();
        return $exists;
    }

    public static function createUser(string $username, string $email, string $passwordHash, string $avatarPath, string $descr): ?int {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password, avatar, descr) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) { $mysqli->close(); return null; }
        $stmt->bind_param('sssss', $username, $email, $passwordHash, $avatarPath, $descr);
        if (!$stmt->execute()) {
            $stmt->close();
            $mysqli->close();
            return null;
        }
        $id = $stmt->insert_id;
        $stmt->close();
        $mysqli->close();
        return $id;
    }

    public static function getAvatarByUser(int $id): string {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("SELECT avatar FROM users WHERE id = ? LIMIT 1");
        if (!$stmt) { $mysqli->close(); return ''; }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($avatar);
        $got = $stmt->fetch();
        $stmt->close();
        $mysqli->close();
        if ($got && $avatar) return $avatar;
        return '';
    }

    public static function insertContent(string $title, string $mainImage, string $type, float $price, string $description, string $imagesJson, int $author): bool {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("INSERT INTO content (title, mainImage, type, price, description, images, author) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) { $mysqli->close(); return false; }
        $stmt->bind_param('sssdssi', $title, $mainImage, $type, $price, $description, $imagesJson, $author);
        $ok = $stmt->execute();
        $stmt->close();
        $mysqli->close();
        return (bool)$ok;
    }
}
?>