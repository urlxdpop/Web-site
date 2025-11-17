<?php
class DBController
{
    private static $cfg = [
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
        'db' => 'WebSite',
        'charset' => 'utf8mb4'
    ];

    public static function CreateTables()
    {
        self::CreateUserTableIfNotExists();
        self::CreateContentTableIfNotExists();
        self::CreateSavedTableIfNotExists();
    }

    public static function connect(): mysqli
    {
        $c = new mysqli(self::$cfg['host'], self::$cfg['user'], self::$cfg['pass'], self::$cfg['db'], self::$cfg['port']);
        if ($c->connect_error) {
            throw new RuntimeException('DB connect error: ' . $c->connect_error);
        }
        $c->set_charset(self::$cfg['charset']);
        return $c;
    }

    public static function getUserById(int $id): ?array
    {
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

    public static function getUserByEmailOrUsername(string $identifier): ?array
    {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email = ? OR username = ? LIMIT 1");
        if (!$stmt) { $mysqli->close(); return null; }
        $stmt->bind_param('ss', $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $mysqli->close();
        if (!$row) return null;
        return ['id' => (int)$row['id'], 'password' => $row['password']];
    }

    public static function userExistsByEmailOrUsername(string $email, string $username): bool
    {
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

    public static function createUser(string $username, string $email, string $passwordHash, string $avatarPath, string $descr): ?int
    {
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

        self::createSavedRowForUser((int)$id);

        return $id;
    }

    public static function createSavedRowForUser(int $userId): bool
    {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("INSERT INTO saved (id_user, saved, buy) VALUES (?, '', '')");
        if (!$stmt) { $mysqli->close(); return false; }
        $stmt->bind_param('i', $userId);
        $ok = $stmt->execute();
        $stmt->close();
        $mysqli->close();
        return (bool)$ok;
    }

    public static function getSavedRow(int $userId): ?array
    {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("SELECT saved, buy FROM saved WHERE id_user = ? LIMIT 1");
        if (!$stmt) { $mysqli->close(); return null; }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        $mysqli->close();
        return $res;
    }

    public static function updateSavedRow(int $userId, string $saved, string $buy): bool
    {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("UPDATE saved SET saved = ?, buy = ? WHERE id_user = ?");
        if (!$stmt) { $mysqli->close(); return false; }
        $stmt->bind_param('ssi', $saved, $buy, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        $mysqli->close();
        return (bool)$ok;
    }

    public static function addToSaved(int $userId, int $productId): bool
    {
        $row = self::getSavedRow($userId);
        if ($row === null) return false;
        $savedStr = $row['saved'] ?? '';
        $buyStr = $row['buy'] ?? '';

        $savedArr = $savedStr === '' ? [] : array_filter(explode('*', $savedStr), 'strlen');
        $buyArr = $buyStr === '' ? [] : array_filter(explode('*', $buyStr), 'strlen');

        if (in_array((string)$productId, $buyArr, true) || in_array($productId, array_map('intval', $buyArr), true)) {
            return false;
        }

        if (!in_array((string)$productId, $savedArr, true) && !in_array($productId, array_map('intval', $savedArr), true)) {
            $savedArr[] = (string)$productId;
            $newSaved = implode('*', $savedArr);
            return self::updateSavedRow($userId, $newSaved, $buyStr);
        }
        return true;
    }

    public static function removeFromSaved(int $userId, int $productId): bool
    {
        $row = self::getSavedRow($userId);
        if ($row === null) return false;
        $savedArr = $row['saved'] === '' ? [] : array_filter(explode('*', $row['saved']), 'strlen');
        $savedArr = array_values(array_filter($savedArr, function($v) use ($productId) { return (int)$v !== $productId; }));
        $newSaved = $savedArr ? implode('*', $savedArr) : '';
        return self::updateSavedRow($userId, $newSaved, $row['buy'] ?? '');
    }

    public static function addToBuy(int $userId, int $productId): bool
    {
        $row = self::getSavedRow($userId);
        if ($row === null) return false;
        $savedArr = $row['saved'] === '' ? [] : array_filter(explode('*', $row['saved']), 'strlen');
        $buyArr = $row['buy'] === '' ? [] : array_filter(explode('*', $row['buy']), 'strlen');

        $savedArr = array_values(array_filter($savedArr, function($v) use ($productId) { return (int)$v !== $productId; }));

        if (!in_array((string)$productId, $buyArr, true) && !in_array($productId, array_map('intval', $buyArr), true)) {
            $buyArr[] = (string)$productId;
        }

        $newSaved = $savedArr ? implode('*', $savedArr) : '';
        $newBuy = $buyArr ? implode('*', $buyArr) : '';
        return self::updateSavedRow($userId, $newSaved, $newBuy);
    }

    public static function insertContent(string $title, string $mainImage, string $type, float $price, string $description, string $imagesJson, int $author): bool
    {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("INSERT INTO content (title, mainImage, type, price, description, images, author) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) { $mysqli->close(); return false; }
        $stmt->bind_param('sssdssi', $title, $mainImage, $type, $price, $description, $imagesJson, $author);
        $ok = $stmt->execute();
        $stmt->close();
        $mysqli->close();
        return (bool)$ok;
    }
    public static function getProductsByIds(array $ids): array
    {
        $ids = array_filter(array_map('intval', $ids));
        if (count($ids) === 0) return [];
        $mysqli = self::connect();
        $in = implode(',', $ids);
        $sql = "
            SELECT c.id, c.title, c.mainImage, c.type, c.price, c.description, c.images, c.author,
                   u.username AS author_name, u.avatar AS author_avatar, u.descr AS author_desc
            FROM content c
            LEFT JOIN users u ON c.author = u.id
            WHERE c.id IN ($in)
        ";
        $res = $mysqli->query($sql);
        $out = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $out[] = [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'mainImage' => $row['mainImage'],
                    'type' => $row['type'],
                    'price' => (float)$row['price'],
                    'description' => $row['description'],
                    'images' => $row['images'] ? json_decode($row['images'], true) : [],
                    'author' => [
                        'id' => (int)$row['author'],
                        'name' => $row['author_name'],
                        'desc' => $row['author_desc'],
                        'avatar' => $row['author_avatar']
                    ]
                ];
            }
            $res->free();
        }
        $mysqli->close();
        $map = [];
        foreach ($out as $p) $map[$p['id']] = $p;
        $ordered = [];
        foreach ($ids as $i) if (isset($map[$i])) $ordered[] = $map[$i];
        return $ordered;
    }
    public static function getProducts(int $page = 1, int $limit = 20, ?int $author = null, ?string $q = null, ?string $category = null, ?string $sort = null): array
    {
        $offset = max(0, ($page - 1) * $limit);
        $mysqli = self::connect();

        $where = [];
        $types = '';
        $params = [];

        if ($author !== null) {
            $where[] = 'c.author = ?';
            $types .= 'i';
            $params[] = $author;
        }

        if ($category !== null && $category !== '' && mb_strtolower($category) !== 'все') {
            $where[] = 'c.type = ?';
            $types .= 's';
            $params[] = $category;
        }

        if ($q !== null && $q !== '') {
            $where[] = '(c.title LIKE ? OR c.description LIKE ? OR u.username LIKE ?)';
            $types .= 'sss';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $orderBy = 'c.id DESC';
        if ($sort === 'title') $orderBy = 'c.title ASC';
        elseif ($sort === 'price_asc') $orderBy = 'c.price ASC';
        elseif ($sort === 'price_desc') $orderBy = 'c.price DESC';
        elseif ($sort === 'author') $orderBy = 'u.username ASC';

        $sql = "
            SELECT c.id, c.title, c.mainImage, c.type, c.price, c.description, c.images, c.author,
                   u.username AS author_name, u.avatar AS author_avatar, u.descr AS author_desc
            FROM content c
            LEFT JOIN users u ON c.author = u.id
            {$whereSql}
            ORDER BY {$orderBy}
            LIMIT ? OFFSET ?
        ";

        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) { $mysqli->close(); return []; }

        $bind_names = [];
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = & $params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);

        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'mainImage' => $row['mainImage'],
                    'type' => $row['type'],
                    'price' => (float)$row['price'],
                    'description' => $row['description'],
                    'images' => $row['images'] ? json_decode($row['images'], true) : [],
                    'author' => [
                        'id' => (int)$row['author'],
                        'name' => $row['author_name'],
                        'desc' => $row['author_desc'],
                        'avatar' => $row['author_avatar']
                    ]
                ];
            }
        }

        $stmt->close();
        $mysqli->close();
        return $products;
    }

    public static function getProductsCount(?int $author = null, ?string $q = null, ?string $category = null): int
    {
        $mysqli = self::connect();

        $where = [];
        $types = '';
        $params = [];

        if ($author !== null) {
            $where[] = 'content.author = ?';
            $types .= 'i';
            $params[] = $author;
        }

        if ($category !== null && $category !== '' && mb_strtolower($category) !== 'все') {
            $where[] = 'content.type = ?';
            $types .= 's';
            $params[] = $category;
        }

        if ($q !== null && $q !== '') {
            $where[] = '(content.title LIKE ? OR content.description LIKE ? OR users.username LIKE ?)';

            $types .= 'sss';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $sql = "SELECT COUNT(*) AS cnt FROM content LEFT JOIN users ON content.author = users.id WHERE " . implode(' AND ', $where);
        } else {
            $sql = $where ? "SELECT COUNT(*) AS cnt FROM content WHERE " . implode(' AND ', $where) : "SELECT COUNT(*) AS cnt FROM content";
        }

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) { $mysqli->close(); return 0; }

        if (count($params) > 0) {
            $bind_names = [];
            $bind_names[] = $types;
            for ($i = 0; $i < count($params); $i++) {
                $bind_names[] = & $params[$i];
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        $mysqli->close();
        return (int)($row['cnt'] ?? 0);
    }

    public static function getProductById(int $id): ?array
    {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("
            SELECT 
                c.id, c.title, c.mainImage, c.type, c.price, c.description, c.images, c.author,
                u.username AS author_name, u.avatar AS author_avatar, u.descr AS author_desc, u.email AS author_email
            FROM content c
            LEFT JOIN users u ON c.author = u.id
            WHERE c.id = ?
            LIMIT 1
        ");
        if (!$stmt) { $mysqli->close(); return null; }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        $mysqli->close();
        if (!$row) return null;
        return [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'mainImage' => $row['mainImage'],
            'type' => $row['type'],
            'price' => (float)$row['price'],
            'description' => $row['description'],
            'images' => $row['images'] ? json_decode($row['images'], true) : [],
            'author' => [
                'id' => (int)$row['author'],
                'name' => $row['author_name'],
                'desc' => $row['author_desc'],
                'avatar' => $row['author_avatar'],
                'email' => $row['author_email']
            ]
        ];
    }

    public static function getSimilarProducts(string $type, int $excludeId, int $limit = 4): array
    {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("
            SELECT 
                c.id, c.title, c.mainImage, c.type, c.price, c.description, c.images, c.author,
                u.username AS author_name, u.avatar AS author_avatar, u.descr AS author_desc
            FROM content c
            LEFT JOIN users u ON c.author = u.id
            WHERE c.type = ? AND c.id != ?
            ORDER BY RAND()
            LIMIT ?
        ");
        if (!$stmt) { $mysqli->close(); return []; }
        $stmt->bind_param('sii', $type, $excludeId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $out = [];
        while ($row = $result->fetch_assoc()) {
            $out[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'mainImage' => $row['mainImage'],
                'type' => $row['type'],
                'price' => (float)$row['price'],
                'description' => $row['description'],
                'images' => $row['images'] ? json_decode($row['images'], true) : [],
                'author' => [
                    'id' => (int)$row['author'],
                    'name' => $row['author_name'],
                    'desc' => $row['author_desc'],
                    'avatar' => $row['author_avatar']
                ]
            ];
        }
        $stmt->close();
        $mysqli->close();
        return $out;
    }

    public static function getAvatarByUser(int $id): string
    {
        $mysqli = self::connect();
        $stmt = $mysqli->prepare("SELECT avatar FROM users WHERE id = ? LIMIT 1");
        if (!$stmt) { $mysqli->close(); return ''; }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        $mysqli->close();
        if ($row && !empty($row['avatar'])) return $row['avatar'];
        return '';
    }

    // --- table creation ---
    private static function CreateUserTableIfNotExists(): void
    {
        $mysqli = self::connect();
        $query = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(60) NOT NULL UNIQUE,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            avatar VARCHAR(255) DEFAULT '',
            descr TEXT DEFAULT '',
            contacts TEXT DEFAULT '',
            officialCreator TINYINT(1) DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $mysqli->query($query);
        $mysqli->close();
    }

    private static function CreateContentTableIfNotExists(): void
    {
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

    private static function CreateSavedTableIfNotExists(): void
    {
        $mysqli = self::connect();
        $query = "CREATE TABLE IF NOT EXISTS saved (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_user INT NOT NULL,
            saved TEXT DEFAULT '',
            buy TEXT DEFAULT '',
            FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $mysqli->query($query);
        $mysqli->close();
    }
}
?>
