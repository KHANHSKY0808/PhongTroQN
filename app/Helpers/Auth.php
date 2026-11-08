<?php
// app/Helpers/Auth.php - Quản lý phiên đăng nhập và phân quyền tập trung

class Auth
{
    /**
     * Khởi tạo session an toàn nếu chưa được bật
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                ini_set('session.cookie_httponly', '1');
                ini_set('session.use_only_cookies', '1');
                ini_set('session.cookie_samesite', 'Lax');
                if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                    ini_set('session.cookie_secure', '1');
                }
            }
            @session_start();
        }
    }

    /**
     * Kiểm tra người dùng đã đăng nhập chưa
     */
    public static function check(): bool
    {
        self::startSession();
        return !empty($_SESSION['user_id']) && !empty($_SESSION['user']);
    }

    /**
     * Lấy ID người dùng hiện tại
     */
    public static function id(): ?int
    {
        self::startSession();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Lấy toàn bộ thông tin người dùng hiện tại
     */
    public static function user(): ?array
    {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Kiểm tra người dùng có quyền quản trị (Admin) không
     */
    public static function isAdmin(): bool
    {
        self::startSession();
        return self::check() && !empty($_SESSION['is_admin']) && (int)$_SESSION['is_admin'] === 1;
    }

    /**
     * Thiết lập phiên đăng nhập đồng bộ và chống Session Fixation
     */
    public static function login(array $user): void
    {
        self::startSession();
        // Tái tạo Session ID để chống Session Fixation (chỉ khi chưa gửi headers)
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        $userId = (int)$user['UserID'];
        $isAdmin = (int)($user['IsAdmin'] ?? 0);

        $_SESSION['user_id']   = $userId;
        $_SESSION['user_name'] = $user['HoTen'] ?? '';
        $_SESSION['email']     = $user['Email'] ?? ($user['email'] ?? '');
        $_SESSION['is_admin']  = $isAdmin;
        $_SESSION['user']      = $user;
    }

    /**
     * Cập nhật thông tin user trong session
     */
    public static function updateUser(array $newData): void
    {
        self::startSession();
        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            $_SESSION['user'] = array_merge($_SESSION['user'], $newData);
            if (isset($newData['HoTen'])) {
                $_SESSION['user_name'] = $newData['HoTen'];
            }
            if (isset($newData['Email'])) {
                $_SESSION['email'] = $newData['Email'];
            }
            if (isset($newData['IsAdmin'])) {
                $_SESSION['is_admin'] = (int)$newData['IsAdmin'];
            }
        }
    }

    /**
     * Đăng xuất và xóa sạch session
     */
    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];

        if (ini_get("session.use_cookies") && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Yêu cầu đăng nhập, nếu chưa đăng nhập thì chuyển hướng hoặc báo lỗi
     */
    public static function requireLogin(string $redirectUrl = '/dang-nhap'): void
    {
        if (!self::check()) {
            if (self::isAjax()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục!']);
                exit;
            }

            $_SESSION['error'] = 'Vui lòng đăng nhập để tiếp tục!';
            $_SESSION['redirect_after_login'] = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            redirect($redirectUrl);
            exit;
        }
    }

    /**
     * Yêu cầu quyền Admin, nếu không thì chặn truy cập
     */
    public static function requireAdmin(): void
    {
        self::requireLogin('/dang-nhap');

        if (!self::isAdmin()) {
            if (self::isAjax()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập khu vực này!']);
                exit;
            }

            $_SESSION['error'] = 'Bạn không có quyền quản trị!';
            redirect('/');
            exit;
        }
    }

    /**
     * Kiểm tra có phải AJAX request không
     */
    public static function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
