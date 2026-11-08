<?php
// app/Models/DB.php - Kết nối Cơ sở dữ liệu tập trung qua PDO (Singleton Pattern)

class DB
{
    private static ?PDO $pdo = null;

    /**
     * Thiết lập kết nối PDO (hữu ích cho testing với SQLite in-memory)
     */
    public static function setConnection(?PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * Khởi tạo hoặc lấy đối tượng PDO kết nối CSDL
     */
    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            $host    = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
            $port    = defined('DB_PORT') ? DB_PORT : '3306';
            $dbname  = defined('DB_NAME') ? DB_NAME : 'phongtroqn';
            $user    = defined('DB_USER') ? DB_USER : 'root';
            $pass    = defined('DB_PASS') ? DB_PASS : '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Dùng native prepared statements của MySQL
            ];

            try {
                self::$pdo = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());

                if (defined('APP_DEBUG') && APP_DEBUG === true) {
                    throw new RuntimeException("Lỗi kết nối CSDL: " . $e->getMessage());
                }

                http_response_code(500);
                require_once __DIR__ . '/../Views/errors/500.php';
                exit;
            }
        }

        return self::$pdo;
    }

    /**
     * Thực thi truy vấn Prepared Statement
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Lấy ID của bản ghi vừa chèn
     */
    public static function lastInsertId(): string|false
    {
        return self::connect()->lastInsertId();
    }

    /**
     * Bắt đầu Database Transaction
     */
    public static function beginTransaction(): bool
    {
        return self::connect()->beginTransaction();
    }

    /**
     * Xác nhận Transaction
     */
    public static function commit(): bool
    {
        return self::connect()->commit();
    }

    /**
     * Hoàn tác Transaction
     */
    public static function rollBack(): bool
    {
        return self::connect()->rollBack();
    }
}

/**
 * Hàm toàn cục thực thi truy vấn thuận tiện cho toàn dự án
 */
if (!function_exists('query')) {
    function query(string $sql, array $params = []): PDOStatement
    {
        return DB::query($sql, $params);
    }
}
