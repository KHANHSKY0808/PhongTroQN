<?php
// app/Controllers/UserController.php

class UserController
{
    private $userModel = null; // Không khởi tạo ngay

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Kiểm tra đăng nhập – hỗ trợ cả AJAX và trang thường
        if (!isset($_SESSION['user'])) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
                exit;
            }
            $_SESSION['error'] = "Vui lòng đăng nhập để truy cập trang này!";
            redirect('/dang-nhap');
        }
    }

    // Kiểm tra có phải request AJAX không
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    // Lấy model (lazy load)
    private function model()
    {
        if ($this->userModel === null) {
            $this->userModel = new User();
        }
        return $this->userModel;
    }

    // Trang quản lý tài khoản
    public function profile()
    {
        $user = $_SESSION['user'];
        view('user/profile', ['user' => $user]);
    }

    // Cập nhật họ tên
    public function updateProfile()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/dang-nhap');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/tai-khoan');
        }

        $userId = $_SESSION['user']['UserID'];
        $hoTen  = trim($_POST['hoten'] ?? '');

        if (empty($hoTen)) {
            $_SESSION['error'] = "Vui lòng nhập họ tên!";
            redirect('/tai-khoan?tab=info');
        }

        if ($this->model()->updateName($userId, $hoTen)) {
            $_SESSION['user']['HoTen'] = $hoTen;
            $_SESSION['success'] = "Cập nhật thông tin thành công!";
        } else {
            $_SESSION['error'] = "Cập nhật thất bại!";
        }
        redirect(BASE_URL);
    }

    // Đổi mật khẩu
    public function changePassword()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/dang-nhap');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/tai-khoan?tab=password');
        }

        $userId   = $_SESSION['user']['UserID'];
        $old      = $_POST['old_password'] ?? '';
        $new      = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        $user = $this->model()->findById($userId);

        if (!password_verify($old, $user['MatKhau'])) {
            $_SESSION['error'] = "Mật khẩu cũ không đúng!";
            redirect('/tai-khoan?tab=password');
        }
        if (strlen($new) < 6) {
            $_SESSION['error'] = "Mật khẩu mới phải từ 6 ký tự!";
            redirect('/tai-khoan?tab=password');
        }
        if ($new !== $confirm) {
            $_SESSION['error'] = "Xác nhận mật khẩu không khớp!";
            redirect('/tai-khoan?tab=password');
        }

        if ($this->model()->updatePassword($userId, $new)) {
            $_SESSION['success'] = "Đổi mật khẩu thành công!";
        } else {
            $_SESSION['error'] = "Đổi mật khẩu thất bại!";
        }
        redirect(BASE_URL);
    }

    // Gửi OTP (AJAX)
    public function sendPhoneOTP()
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
            exit;
        }

        $newPhone = trim($_POST['new_phone'] ?? '');

        if (!preg_match('/^0[3-9][0-9]{8}$/', $newPhone)) {
            echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ!']);
            exit;
        }

        if ($this->model()->phoneExistsExcept($newPhone, $_SESSION['user']['UserID'])) {
            echo json_encode(['success' => false, 'message' => 'Số điện thoại đã được sử dụng!']);
            exit;
        }

        $otp = rand(100000, 999999);
        $_SESSION['otp_phone']   = $otp;
        $_SESSION['otp_expire']  = time() + 180;
        $_SESSION['phone_pending'] = $newPhone;

        $email = $_SESSION['user']['Email'];

        if (MailHelper::sendOTP($email, $otp)) {
            echo json_encode([
                'success' => true,
                'message' => "Mã OTP đã được gửi đến email: $email"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể gửi email. Kiểm tra cấu hình SMTP!'
            ]);
        }
        exit;
    }

    // Đổi số điện thoại (POST form)
    public function changePhone()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/dang-nhap');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/tai-khoan?tab=phone');
        }

        $userId   = $_SESSION['user']['UserID'];
        $newPhone = trim($_POST['new_phone'] ?? '');
        $otp      = trim($_POST['otp'] ?? '');

        if (!preg_match('/^0[3-9][0-9]{8}$/', $newPhone)) {
            $_SESSION['error'] = "Số điện thoại không hợp lệ!";
            redirect('/tai-khoan?tab=phone');
        }

        if ($this->model()->phoneExistsExcept($newPhone, $userId)) {
            $_SESSION['error'] = "Số điện thoại đã được sử dụng!";
            redirect('/tai-khoan?tab=phone');
        }

        if (!isset($_SESSION['otp_phone']) || !isset($_SESSION['otp_expire'])) {
            $_SESSION['error'] = "Bạn chưa yêu cầu mã OTP!";
            redirect('/tai-khoan?tab=phone');
        }
        if (time() > $_SESSION['otp_expire']) {
            unset($_SESSION['otp_phone'], $_SESSION['otp_expire'], $_SESSION['phone_pending']);
            $_SESSION['error'] = "Mã OTP đã hết hạn!";
            redirect('/tai-khoan?tab=phone');
        }
        if ($otp != $_SESSION['otp_phone']) {
            $_SESSION['error'] = "Mã OTP không chính xác!";
            redirect('/tai-khoan?tab=phone');
        }

        if ($this->model()->updatePhone($userId, $newPhone)) {
            $_SESSION['user']['SoDienThoai'] = $newPhone;
            unset($_SESSION['otp_phone'], $_SESSION['otp_expire'], $_SESSION['phone_pending']);
            $_SESSION['success'] = "Đổi số điện thoại thành công!";
        } else {
            $_SESSION['error'] = "Cập nhật thất bại. Vui lòng thử lại!";
        }

        redirect('/tai-khoan?tab=phone');
    }
}