<?php
// app/Controllers/UserController.php

class UserController
{
    private $userModel = null; // Không khởi tạo ngay

    public function __construct()
    {
        Auth::requireLogin();
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
        $user = Auth::user();
        view('user/profile', ['user' => $user]);
    }

    // Cập nhật họ tên
    public function updateProfile()
    {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/tai-khoan?tab=info');
        }

        $userId = Auth::id();
        $hoTen  = trim($_POST['hoten'] ?? '');

        if (empty($hoTen)) {
            $_SESSION['error'] = "Vui lòng nhập họ tên!";
            redirect('/tai-khoan?tab=info');
        }

        if ($this->model()->updateName($userId, $hoTen)) {
            Auth::updateUser(['HoTen' => $hoTen]);
            $_SESSION['success'] = "Cập nhật thông tin thành công!";
        } else {
            $_SESSION['error'] = "Cập nhật thất bại!";
        }
        redirect('/tai-khoan?tab=info');
    }

    // Đổi mật khẩu
    public function changePassword()
    {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/tai-khoan?tab=password');
        }

        $userId   = Auth::id();
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
        redirect('/tai-khoan?tab=password');
    }

    // Gửi OTP (AJAX)
    public function sendPhoneOTP()
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
            exit;
        }

        $userId = Auth::id();
        $user = Auth::user();
        $newPhone = trim($_POST['new_phone'] ?? '');

        if (!preg_match('/^0[3-9][0-9]{8}$/', $newPhone)) {
            echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ!']);
            exit;
        }

        if ($this->model()->phoneExistsExcept($newPhone, $userId)) {
            echo json_encode(['success' => false, 'message' => 'Số điện thoại đã được sử dụng!']);
            exit;
        }

        $otp = rand(100000, 999999);
        $_SESSION['otp_phone']   = $otp;
        $_SESSION['otp_expire']  = time() + 180;
        $_SESSION['phone_pending'] = $newPhone;

        $email = $user['Email'] ?? '';

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
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/tai-khoan?tab=phone');
        }

        $userId   = Auth::id();
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
            Auth::updateUser(['SoDienThoai' => $newPhone]);
            unset($_SESSION['otp_phone'], $_SESSION['otp_expire'], $_SESSION['phone_pending']);
            $_SESSION['success'] = "Đổi số điện thoại thành công!";
        } else {
            $_SESSION['error'] = "Cập nhật thất bại. Vui lòng thử lại!";
        }

        redirect('/tai-khoan?tab=phone');
    }
}