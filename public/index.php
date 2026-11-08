<?php
// PhongTroQN - Front Controller

// Nạp cấu hình & môi trường
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Helpers/Auth.php';

// Cấu hình hiển thị lỗi theo môi trường
if (defined('APP_DEBUG') && APP_DEBUG === true) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

// Khởi động session với cookie an toàn (HttpOnly, SameSite, Strict)
Auth::startSession();

// Nạp toàn bộ functions, controller và chạy router
require_once __DIR__ . '/../app/functions.php';
