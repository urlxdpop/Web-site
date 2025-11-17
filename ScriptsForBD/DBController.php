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

    private function __construct() {
        $this->CreateUserTableIfNotExists();
        $this->CreateContentTableIfNotExists();
    }

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

    public static function getProducts(int $page = 1, int $limit = 20, ?int $author = null): array {
        $offset = ($page - 1) * $limit;
        $mysqli = self::connect();

        if ($author !== null) {
            $stmt = $mysqli->prepare("
                SELECT c.*, u.username as author_name, u.avatar as author_avatar, u.descr as author_desc 
                FROM content c 
                LEFT JOIN users u ON c.author = u.id 
                WHERE c.author = ?
                ORDER BY c.id DESC 
                LIMIT ? OFFSET ?");
            if (!$stmt) { $mysqli->close(); return []; }
            $stmt->bind_param('iii', $author, $limit, $offset);
        } else {
            $stmt = $mysqli->prepare("
                SELECT c.*, u.username as author_name, u.avatar as author_avatar, u.descr as author_desc 
                FROM content c 
                LEFT JOIN users u ON c.author = u.id 
                ORDER BY c.id DESC 
                LIMIT ? OFFSET ?");
            if (!$stmt) { $mysqli->close(); return []; }
            $stmt->bind_param('ii', $limit, $offset);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'mainImage' => $row['mainImage'],
                'type' => $row['type'],
                'price' => $row['price'],
                'description' => $row['description'],
                'images' => json_decode($row['images'], true),
                'author' => [
                    'id' => $row['author'],
                    'name' => $row['author_name'],
                    'desc' => $row['author_desc'],
                    'avatar' => $row['author_avatar']
                ]
            ];
        }
        $stmt->close();
        $mysqli->close();
        return $products;
    }

    public static function getProductsCount(?int $author = null): int {
        $mysqli = self::connect();
        if ($author !== null) {
            $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM content WHERE author = ?");
            if (!$stmt) { $mysqli->close(); return 0; }
            $stmt->bind_param('i', $author);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $mysqli->close();
            return (int)($res['cnt'] ?? 0);
        } else {
            $result = $mysqli->query("SELECT COUNT(*) as cnt FROM content");
            if (!$result) { $mysqli->close(); return 0; }
            $row = $result->fetch_assoc();
            $mysqli->close();
            return (int)($row['cnt'] ?? 0);
        }
    }

    private function CreateUserTableIfNotExists(): void {
        $mysqli = self::connect();
        $query = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(30) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            avatar VARCHAR(255) DEFAULT '',
            descr TEXT DEFAULT ''
            contacts TEXT DEFAULT ''
            officialCreator TINYINT(1) DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $mysqli->query($query);
        $mysqli->close();
    }

    private function CreateContentTableIfNotExists(): void {
        $mysqli = self::connect();
        $query = "CREATE TABLE IF NOT EXISTS content (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            mainImage VARCHAR(255) DEFAULT '',
            type VARCHAR(30) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            description TEXT DEFAULT '',
            images TEXT DEFAULT '',
            author INT NOT NULL,
            FOREIGN KEY (author) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $mysqli->query($query);
        $mysqli->close();
    }
}
?>