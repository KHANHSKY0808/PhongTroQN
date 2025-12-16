<?php
// app/Controllers/AuthController.php
// ĐÃ SỬA HOÀN CHỈNH – KHÔNG CÒN LỖI UNDEFINED VARIABLE

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ==================== ĐĂNG KÝ ====================
    public function register()
    {
        // Chỉ xử lý khi có submit form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // LẤY VÀ LÀM SẠCH DỮ LIỆU TỪ FORM
            $hoten   = trim($_POST['hoten'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $pass    = $_POST['pass'] ?? '';
            $repass  = $_POST['repass'] ?? '';
            $phone   = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');

            // KIỂM TRA RỖNG
            if (empty($hoten) || empty($email) || empty($pass) || empty($phone)) {
                $_SESSION['error'] = "Vui lòng điền đầy đủ các trường bắt buộc!";
            }
            // KIỂM TRA MẬT KHẨU KHỚP
            elseif ($pass !== $repass) {
                $_SESSION['error'] = "Mật khẩu nhập lại không khớp!";
            }
            // KIỂM TRA EMAIL ĐÃ TỒN TẠI
            elseif ($this->userModel->exists('Email', $email)) {
                $_SESSION['error'] = "Email này đã được sử dụng!";
            }
            // KIỂM TRA SỐ ĐIỆN THOẠI ĐÃ TỒN TẠI
            elseif ($this->userModel->exists('SoDienThoai', $phone)) {
                $_SESSION['error'] = "Số điện thoại này đã được đăng ký!";
            }
            // ĐĂNG KÝ THÀNH CÔNG
            else {
                $userId = $this->userModel->register([
                    'hoten'   => $hoten,
                    'email'   => $email,
                    'pass'    => $pass,
                    'phone'   => $phone,
                    'address' => $address
                ]);

                if ($userId) {
                    // Đăng nhập luôn sau khi đăng ký thành công
                    $user = $this->userModel->findByEmail($email);
                    $_SESSION['user'] = $user;
                    $_SESSION['success'] = "Đăng ký thành công! Chào mừng $hoten";
                    redirect('/');
                    return;
                } else {
                    $_SESSION['error'] = "Đăng ký thất bại, vui lòng thử lại!";
                }
            }
        }

        // Luôn hiển thị form (cả khi có lỗi)
        return view('auth/register');
    }

    // ==================== ĐĂNG NHẬP ====================
// ==================== ĐĂNG NHẬP ====================
    public function login()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['pass'] ?? '';

            if (empty($email) || empty($pass)) {
                $_SESSION['error'] = "Vui lòng nhập đầy đủ email và mật khẩu!";
            } else {
                // Lấy user theo email
                $user = $this->userModel->findByEmail($email);

                // Kiểm tra user tồn tại + mật khẩu đúng
                if ($user && password_verify($pass, $user['MatKhau'])) {

                    // Lưu thông tin vào session
                    $_SESSION['user_id']   = $user['UserID'];
                    $_SESSION['user_name'] = $user['HoTen'];
                    $_SESSION['email']     = $user['email'];
                    $_SESSION['is_admin']  = (int)$user['IsAdmin']; // Quan trọng!
                    $_SESSION['user']      = $user; // Thêm dòng này để header.php có thể truy cập

                    $_SESSION['success'] = "Chào mừng trở lại, " . $user['HoTen'] . "!";

                    // Nếu là Admin → vào trang admin
                    if ((int)$user['IsAdmin'] === 1) {
                        redirect('/index.php?page=admin');
                    } else {
                        // Người dùng thường → về trang chủ
                        redirect('/');
                    }
                    exit(); // Chỉ cần exit() một lần ở đây là đủ
                } else {
                    $_SESSION['error'] = "Email hoặc mật khẩu không đúng!";
                }
            }
        }

        // Nếu là GET hoặc có lỗi → hiển thị form login
        return view('auth/login');
    }

    // ==================== ĐĂNG XUẤT ====================
    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();

        // Redirect về trang chủ
        redirect('/index.php');
    }
}