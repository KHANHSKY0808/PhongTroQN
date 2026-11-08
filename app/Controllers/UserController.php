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

        // Rate limit: Mỗi lần gửi cách nhau ít nhất 60 giây
        $lastSent = $_SESSION['otp_last_sent'] ?? 0;
        if (time() - $lastSent < 60) {
            $wait = 60 - (time() - $lastSent);
            echo json_encode(['success' => false, 'message' => "Vui lòng chờ {$wait} giây trước khi yêu cầu mã OTP mới!"]);
            exit;
        }

        // Sinh OTP ngẫu nhiên an toàn bằng random_int và lưu dạng hash
        $otp = (string)random_int(100000, 999999);
        $_SESSION['otp_phone_hash']  = password_hash($otp, PASSWORD_DEFAULT);
        $_SESSION['otp_expire']      = time() + 180; // Hiệu lực 3 phút
        $_SESSION['phone_pending']   = $newPhone;
        $_SESSION['otp_attempts']    = 0; // Bộ đếm số lần nhập sai
        $_SESSION['otp_last_sent']   = time();

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

        // 1. Kiểm tra session OTP
        if (!isset($_SESSION['otp_phone_hash']) || !isset($_SESSION['otp_expire'])) {
            $_SESSION['error'] = "Bạn chưa yêu cầu mã OTP!";
            redirect('/tai-khoan?tab=phone');
        }

        // 2. Kiểm tra thời hạn OTP
        if (time() > $_SESSION['otp_expire']) {
            unset($_SESSION['otp_phone_hash'], $_SESSION['otp_expire'], $_SESSION['phone_pending'], $_SESSION['otp_attempts']);
            $_SESSION['error'] = "Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới!";
            redirect('/tai-khoan?tab=phone');
        }

        // 3. Kiểm tra số điện thoại có đúng với số đã xin OTP không (chống bypass phone number)
        if ($newPhone !== ($_SESSION['phone_pending'] ?? '')) {
            $_SESSION['error'] = "Số điện thoại không khớp với số đã yêu cầu mã OTP!";
            redirect('/tai-khoan?tab=phone');
        }

        // 4. Rate limit số lần nhập sai (tối đa 5 lần)
        $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
        if ($_SESSION['otp_attempts'] > 5) {
            unset($_SESSION['otp_phone_hash'], $_SESSION['otp_expire'], $_SESSION['phone_pending'], $_SESSION['otp_attempts']);
            $_SESSION['error'] = "Bạn đã nhập sai mã OTP quá 5 lần. Vui lòng lấy mã mới!";
            redirect('/tai-khoan?tab=phone');
        }

        // 5. Đối chiếu mã OTP qua password_verify
        if (!password_verify($otp, $_SESSION['otp_phone_hash'])) {
            $remaining = 5 - $_SESSION['otp_attempts'];
            $_SESSION['error'] = "Mã OTP không chính xác! Bạn còn {$remaining} lần thử.";
            redirect('/tai-khoan?tab=phone');
        }

        if ($this->model()->updatePhone($userId, $newPhone)) {
            Auth::updateUser(['SoDienThoai' => $newPhone]);
            unset($_SESSION['otp_phone_hash'], $_SESSION['otp_expire'], $_SESSION['phone_pending'], $_SESSION['otp_attempts'], $_SESSION['otp_last_sent']);
            $_SESSION['success'] = "Đổi số điện thoại thành công!";
        } else {
            $_SESSION['error'] = "Cập nhật thất bại. Vui lòng thử lại!";
        }

        redirect('/tai-khoan?tab=phone');
    }
}