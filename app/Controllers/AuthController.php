<?php
// app/Controllers/AuthController.php - Xử lý đăng ký, đăng nhập và đăng xuất

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ==================== ĐĂNG KÝ ====================
    public function register()
    {
        // Nếu đã đăng nhập thì về trang chủ
        if (Auth::check()) {
            redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hoten   = trim($_POST['hoten'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $pass    = $_POST['pass'] ?? '';
            $repass  = $_POST['repass'] ?? '';
            $phone   = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');

            // Validate dữ liệu
            if (empty($hoten) || empty($email) || empty($pass) || empty($phone)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ các trường bắt buộc!";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Định dạng email không hợp lệ!";
            } elseif (strlen($pass) < 6) {
                $_SESSION['error'] = "Mật khẩu phải chứa ít nhất 6 ký tự!";
            } elseif ($pass !== $repass) {
                $_SESSION['error'] = "Mật khẩu nhập lại không khớp!";
            } elseif ($this->userModel->exists('Email', $email)) {
                $_SESSION['error'] = "Email này đã được sử dụng!";
            } elseif ($this->userModel->exists('SoDienThoai', $phone)) {
                $_SESSION['error'] = "Số điện thoại này đã được đăng ký!";
            } else {
                $userId = $this->userModel->register([
                    'hoten'   => $hoten,
                    'email'   => $email,
                    'pass'    => $pass,
                    'phone'   => $phone,
                    'address' => $address
                ]);

                if ($userId) {
                    $user = $this->userModel->findByEmail($email);
                    if ($user) {
                        Auth::login($user);
                    }
                    $_SESSION['success'] = "Đăng ký thành công! Chào mừng $hoten";
                    redirect('/');
                    return;
                } else {
                    $_SESSION['error'] = "Đăng ký thất bại, vui lòng thử lại!";
                }
            }
        }

        return view('auth/register');
    }

    // ==================== ĐĂNG NHẬP ====================
    public function login()
    {
        // Nếu đã đăng nhập thì về trang thích hợp
        if (Auth::check()) {
            redirect(Auth::isAdmin() ? '/index.php?page=admin' : '/');
        }

        // Rate limit: Kiểm tra trạng thái khóa tạm thời
        $lockUntil = $_SESSION['login_locked_until'] ?? 0;
        if (time() < $lockUntil) {
            $wait = $lockUntil - time();
            $_SESSION['error'] = "Bạn đã thử đăng nhập sai quá nhiều lần. Vui lòng chờ {$wait} giây!";
            return view('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['pass'] ?? '';

            if (empty($email) || empty($pass)) {
                $_SESSION['error'] = "Vui lòng nhập đầy đủ email và mật khẩu!";
            } else {
                $user = $this->userModel->findByEmail($email);

                if ($user && password_verify($pass, $user['MatKhau'])) {
                    // Reset bộ đếm đăng nhập sai
                    unset($_SESSION['login_fail_attempts'], $_SESSION['login_locked_until']);

                    // Thiết lập phiên đăng nhập đồng bộ
                    Auth::login($user);

                    $_SESSION['success'] = "Chào mừng trở lại, " . ($user['HoTen'] ?? '') . "!";

                    // Điều hướng lại trang người dùng đang muốn vào trước đó (nếu có)
                    $redirectUrl = $_SESSION['redirect_after_login'] ?? null;
                    unset($_SESSION['redirect_after_login']);

                    if (Auth::isAdmin()) {
                        redirect($redirectUrl ?: '/index.php?page=admin');
                    } else {
                        redirect($redirectUrl ?: '/');
                    }
                    exit;
                } else {
                    $attempts = ($_SESSION['login_fail_attempts'] ?? 0) + 1;
                    $_SESSION['login_fail_attempts'] = $attempts;

                    if ($attempts >= 5) {
                        $_SESSION['login_locked_until'] = time() + 60; // Khóa 60 giây
                        $_SESSION['login_fail_attempts'] = 0;
                        $_SESSION['error'] = "Bạn đã nhập sai 5 lần liên tiếp. Vui lòng thử lại sau 60 giây!";
                    } else {
                        $remaining = 5 - $attempts;
                        $_SESSION['error'] = "Email hoặc mật khẩu không đúng! Còn {$remaining} lần thử.";
                    }
                }
            }
        }

        return view('auth/login');
    }

    // ==================== ĐĂNG XUẤT ====================
    public function logout()
    {
        Auth::logout();
        redirect('/');
    }
}
