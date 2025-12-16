<?php
// app/Models/DB.php

// SỬA DÒNG NÀY THÀNH CHÍNH XÁC:

class DB {
    private static $pdo = null;

    public static function connect() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Lỗi kết nối CSDL: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

function query($sql, $params = []) {
    $stmt = DB::connect()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}