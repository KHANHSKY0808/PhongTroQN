<?php
// config/config.php - Cấu hình hệ thống và nạp biến môi trường

require_once __DIR__ . '/../app/Helpers/Env.php';

// Nạp file .env từ thư mục gốc
Env::load(dirname(__DIR__) . '/.env');

if (!defined('DB_CONFIG_INCLUDED')) {
    define('DB_CONFIG_INCLUDED', true);

    // ==== CẤU HÌNH ỨNG DỤNG ====
    define('APP_NAME', env('APP_NAME', 'PhongTroQN'));
    define('APP_ENV', env('APP_ENV', 'development'));
    define('APP_DEBUG', (bool)env('APP_DEBUG', true));

    // ==== CẤU HÌNH DATABASE ====
    define('DB_HOST', env('DB_HOST', '127.0.0.1'));
    define('DB_PORT', env('DB_PORT', '3306'));
    define('DB_NAME', env('DB_NAME', 'phongtroqn'));
    define('DB_USER', env('DB_USER', 'root'));
    define('DB_PASS', env('DB_PASS', ''));

    // ==== CẤU HÌNH URL & ĐƯỜNG DẪN ====
    define('BASE_URL', env('BASE_URL', '/PhongTroQN/public'));
    define('ASSETS_URL', BASE_URL . '/assets');
    define('UPLOADS_URL', env('UPLOADS_URL', 'http://localhost/PhongTroQN'));
    define('UPLOADS_PATH', __DIR__ . '/../uploads');
    define('ROOT', dirname(__DIR__));

    // ==== CẤU HÌNH SMTP MAIL ====
    define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com'));
    define('SMTP_PORT', (int)env('SMTP_PORT', 587));
    define('SMTP_USER', env('SMTP_USER', ''));
    define('SMTP_PASS', env('SMTP_PASS', ''));
    define('SMTP_SECURE', env('SMTP_SECURE', 'tls'));
    define('SMTP_FROM_ADDRESS', env('SMTP_FROM_ADDRESS', 'Phongtroqnu@gmail.com'));
    define('SMTP_FROM_NAME', env('SMTP_FROM_NAME', 'PhongTroQN'));
}

// Biến $conn hỗ trợ tương thích ngược cho các hàm cũ dùng mysqli (kết nối lười / lazy connection)
if (!isset($conn)) {
    $conn = null;
}

function getLegacyMysqli(): ?mysqli {
    global $conn;
    if ($conn instanceof mysqli && @$conn->ping()) {
        return $conn;
    }
    try {
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
        if ($conn->connect_error) {
            error_log("Lỗi kết nối mysqli cũ: " . $conn->connect_error);
            return null;
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Throwable $e) {
        error_log("Lỗi khởi tạo mysqli: " . $e->getMessage());
        return null;
    }
}
