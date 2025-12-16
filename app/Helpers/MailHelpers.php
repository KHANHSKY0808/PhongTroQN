<?php
// app/Helpers/MailHelper.php

// ĐƯỜNG DẪN CHÍNH XÁC KHI PHPMailer-master NẰM CÙNG THƯ MỤC
require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper
{
    public static function sendOTP($email, $otp)
    {
        try {
            $mail = new PHPMailer(true);

            // Cấu hình SMTP Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'Phongtroqnu@gmail.com';
            $mail->Password   = 'gnyw gzkh tqjm gxqi';  // App Password của bạn
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Người gửi
            $mail->setFrom('Phongtroqnu@gmail.com', 'PhongTroQN');

            // Người nhận
            $mail->addAddress($email);

            // Nội dung email đẹp
            $mail->isHTML(true);
            $mail->Subject = 'Mã OTP xác thực đổi số điện thoại - PhongTroQN';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <h2 style='color: #ee4d2d;'>PhongTroQN.com</h2>
                    <h3>Mã OTP xác thực đổi số điện thoại</h3>
                    <p>Xin chào,</p>
                    <p>Mã OTP của bạn là:</p>
                    <h1 style='color: red; font-size: 32px; letter-spacing: 5px; text-align: center;'>
                        <strong>$otp</strong>
                    </h1>
                    <p>Mã này có hiệu lực trong <strong>3 phút</strong>.</p>
                    <p>Nếu bạn không yêu cầu đổi số, vui lòng bỏ qua email này.</p>
                    <hr>
                    <small>PhongTroQN - Kênh thông tin phòng trọ Quy Nhơn uy tín nhất</small>
                </div>
            ";

            return $mail->send();

        } catch (Exception $e) {
            error_log("Lỗi gửi OTP: " . $mail->ErrorInfo);
            return false;
        }
    }
}