<?php
// app/Helpers/Csrf.php - Quản lý tạo và xác thực CSRF Token

class Csrf
{
    /**
     * Lấy hoặc tạo mới CSRF token trong session
     */
    public static function token(): string
    {
        Auth::startSession();

        if (empty($_SESSION['csrf_token'])) {
            try {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (Exception $e) {
                $_SESSION['csrf_token'] = md5(uniqid((string)mt_rand(), true));
            }
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Tạo mã HTML input hidden chứa CSRF token
     */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Xác thực token gửi lên từ form hoặc request header
     */
    public static function validate(?string $token = null): bool
    {
        Auth::startSession();

        if (empty($_SESSION['csrf_token'])) {
            return false;
        }

        $sessionToken = $_SESSION['csrf_token'];

        // Nếu không truyền token cụ thể, tìm trong POST hoặc Headers
        if ($token === null) {
            $token = $_POST['csrf_token'] ??
                     $_SERVER['HTTP_X_CSRF_TOKEN'] ??
                     $_SERVER['HTTP_X_XSRF_TOKEN'] ??
                     null;
        }

        if (empty($token) || !is_string($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Kiểm tra CSRF cho các phương thức ghi dữ liệu, nếu thất bại thì dừng chương trình
     */
    public static function verifyOrAbort(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            if (!self::validate()) {
                http_response_code(403);
                if (Auth::isAjax()) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Lỗi bảo mật: CSRF token không hợp lệ hoặc đã hết hạn. Vui lòng tải lại trang!'
                    ]);
                    exit;
                }

                die('<h3>403 Forbidden: Phiên làm việc không hợp lệ (CSRF Token Mismatch). Vui lòng quay lại và tải lại trang!</h3>');
            }
        }
    }
}

// Hàm trợ giúp toàn cục
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}
