<?php
// PhongTroQN - Front Controller

// Nạp cấu hình & môi trường
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Helpers/Logger.php';
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

// Bắt và xử lý tập trung mọi Exception chưa được xử lý
set_exception_handler(function (Throwable $e) {
    Logger::error($e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);

    if (defined('APP_DEBUG') && APP_DEBUG === true) {
        echo "<pre style='background:#fef2f2;color:#991b1b;padding:20px;border:1px solid #f87171;border-radius:8px;'>";
        echo "<b>Fatal Exception:</b> " . htmlspecialchars($e->getMessage()) . "\n";
        echo "<b>In file:</b> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "\n\n";
        echo "<b>Stack Trace:</b>\n" . htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
        exit;
    }

    http_response_code(500);
    if (Auth::isAjax()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi máy chủ nội bộ. Vui lòng thử lại sau!']);
        exit;
    }

    $errorView = __DIR__ . '/../app/Views/errors/500.php';
    if (file_exists($errorView)) {
        require $errorView;
    } else {
        echo "<h1>500 Internal Server Error</h1>";
    }
    exit;
});

// Khởi động session với cookie an toàn (HttpOnly, SameSite, Strict)
Auth::startSession();

// Nạp toàn bộ functions, controller và chạy router
require_once __DIR__ . '/../app/functions.php';
