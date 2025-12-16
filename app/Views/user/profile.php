<?php
$user = $data['user'];
$current_tab = $_GET['tab'] ?? 'info';


// Hiển thị thông báo – PHẢI CÓ unset ĐẦU TIÊN
if (isset($_SESSION['success'])) {
    echo "<script>alert('" . $_SESSION['success'] . "'); window.location.href = '" . BASE_URL . "';</script>";
    unset($_SESSION['success']);
    exit;
}
if (isset($_SESSION['error'])) {
    echo "<script>alert('" . $_SESSION['error'] . "');</script>";
    unset($_SESSION['error']); // ← BẮT BUỘC PHẢI CÓ DÒNG NÀY
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản - NhaTroQN.COM</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/footer.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .page-content {
            padding: 20px;
            display: flex;
            justify-content: center;
            flex: 1;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        .tabs {
            display: flex;
            background: #fff;
            border-bottom: 1px solid #eee;
        }
        .tab {
            flex: 1;
            padding: 16px;
            text-align: center;
            font-size: 15px;
            font-weight: 500;
            color: #999;
            cursor: pointer;
            transition: color 0.2s;
        }
        .tab.active {
            color: #ee4d2d;
            position: relative;
        }
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 3px;
            background: #ee4d2d;
            border-radius: 2px;
        }
        .content { padding: 0 24px 32px; }

        .form-section { display: none; }
        .form-section.active { display: block; }

        .header { background: linear-gradient(to bottom, #fafafa, #ffffff); padding: 32px 24px 24px; }
        .avatar-name { display: flex; align-items: center; gap: 16px; }
        .avatar {
            width: 72px; height: 72px; border-radius: 50%; background: #e0e0e0;
            display: flex; align-items: center; justify-content: center;
        }
        .avatar svg { width: 40px; height: 40px; fill: #aaa; }
        .name { font-size: 20px; font-weight: 600; color: #333; }
        .phone { font-size: 14px; color: #666; margin-top: 4px; }

        .btn-change-avatar {
            margin-top: 20px; width: 100%; padding: 14px; background: #f5f5f5;
            border: none; border-radius: 16px; font-size: 15px; font-weight: 500;
            color: #333; cursor: pointer; display: flex; align-items: center;
            justify-content: center; gap: 8px; transition: background 0.2s;
        }
        .btn-change-avatar:hover { background: #ebebeb; }

        .form-group { margin-top: 24px; }
        .label { display: block; font-size: 14px; color: #555; margin-bottom: 8px; font-weight: 500; }
        .input, .input-disabled {
            width: 100%; padding: 14px 16px; border: 1px solid #e0e0e0;
            border-radius: 16px; font-size: 15px;
        }
        .input-disabled { background: #f9f9f9; color: #333; cursor: pointer; }
        .input:focus { outline: none; border-color: #ee4d2d; box-shadow: 0 0 0 2px rgba(238,77,45,0.15); }

        .btn-update {
            margin-top: 32px; width: 100%; padding: 16px; background: #ee4d2d;
            color: white; border: none; border-radius: 50px; font-size: 16px;
            font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(238,77,45,0.3);
        }
        .btn-update:hover { background: #d83924; }

        .change-phone-header { padding: 24px 24px 16px; font-size: 20px; font-weight: 600; color: #333; }
        .input-old { background: #f5f5f5; color: #333; border-color: #e0e0e0; }

        .btn-get-code {
            width: 100%; padding: 14px; background: #ffa500; color: #000;
            border: none; border-radius: 12px; font-size: 16px; font-weight: 600;
            cursor: pointer; margin: 20px 0;
        }
        .btn-get-code:hover { background: #ff8c00; }
        .btn-get-code:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="page-content">
        <div class="container">
        <!-- Tabs -->
        <div class="tabs">
            <div class="tab <?= $current_tab === 'info' ? 'active' : '' ?>" onclick="showTab('info')">Thông tin cá nhân</div>
            <div class="tab <?= $current_tab === 'phone' ? 'active' : '' ?>" onclick="showTab('phone')">Đổi số điện thoại</div>
            <div class="tab <?= $current_tab === 'password' ? 'active' : '' ?>" onclick="showTab('password')">Đổi mật khẩu</div>
        </div>

        <div class="content">
            <!-- 1. Cập nhật họ tên -->
            <div id="info" class="form-section <?= $current_tab === 'info' ? 'active' : '' ?>">
                <form action="<?= BASE_URL ?>/tai-khoan/cap-nhat" method="POST">
                    <div class="header">
                        <div class="avatar-name">
                            <div class="avatar">
                                <svg viewBox="0 0 32 32"><path d="M16 8a5 5 0 100 10 5 5 0 000-10zm-6 18c0-4.67 3.13-8.56 8-9.6-4.87-1.04-8-4.93-8-9.6 0-5.52 4.48-10 10-10s10 4.48 10 10c0 4.67-3.13 8.56-8 9.6 4.87 1.04 8 4.93 8 9.6h-20z"/></svg>
                            </div>
                            <div>
                                <div class="name"><?= e($user['HoTen']) ?></div>
                                <div class="phone"><?= e($user['SoDienThoai'] ?? 'Chưa có') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="label">Số điện thoại</label>
                        <div class="input input-disabled" onclick="showTab('phone')">
                            <?= e($user['SoDienThoai'] ?? 'Chưa đặt') ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label">Tên liên hệ</label>
                        <input type="text" name="hoten" class="input" value="<?= e($user['HoTen']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="label">Email</label>
                        <input type="text" class="input input-disabled" value="<?= e($user['Email']) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label class="label">Mật khẩu</label>
                        <div class="input input-disabled" onclick="showTab('password')">••••••••</div>
                    </div>

                    <button type="submit" class="btn-update">Cập nhật</button>
                </form>
            </div>

            <!-- 2. Đổi số điện thoại -->
        <div id="phone" class="form-section <?= $current_tab === 'phone' ? 'active' : '' ?>">
            <form id="changePhoneForm" action="<?= BASE_URL ?>/tai-khoan/doi-sdt" method="POST">
                <div class="change-phone-header">Thay đổi số điện thoại</div>
                
                <div class="form-group">
                    <label class="label">Số điện thoại cũ</label>
                    <div class="input input-old"><?= e($user['SoDienThoai'] ?? 'Chưa có') ?></div>
                </div>

                <div class="form-group">
                    <label class="label">Số điện thoại mới <span class="text-red-500">*</span></label>
                    <input type="text" name="new_phone" class="input" placeholder="Ví dụ: 0901234567" maxlength="12" required>
                </div>

                <button type="button" id="btnGetCode" class="btn-get-code" onclick="sendOTP()">
                    Lấy mã xác thực
                </button>
                <div id="otpTimer" class="text-sm text-gray-600 mt-2"></div>

                <div class="form-group mt-4">
                    <label class="label">Nhập mã xác thực <span class="text-red-500">*</span></label>
                    <input type="text" name="otp" class="input" placeholder="Nhập mã 6 số" maxlength="6" required>
                </div>

                <button type="submit" class="btn-update">Cập nhật số điện thoại</button>
            </form>
        </div>

            <!-- 3. Đổi mật khẩu -->
            <div id="password" class="form-section <?= $current_tab === 'password' ? 'active' : '' ?>">
                <form action="<?= BASE_URL ?>/tai-khoan/doi-mat-khau" method="POST">
                    <div class="change-phone-header">Thay đổi mật khẩu</div>
                    <div class="form-group">
                        <label class="label">Mật khẩu cũ</label>
                        <input type="password" name="old_password" class="input" required>
                    </div>
                    <div class="form-group">
                        <label class="label">Mật khẩu mới</label>
                        <input type="password" name="new_password" class="input" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label class="label">Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" class="input" required>
                    </div>
                    <button type="submit" class="btn-update">Cập nhật mật khẩu</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.form-section').forEach(f => f.classList.remove('active'));
            document.querySelector(`.tab[onclick="showTab('${tab}')"]`).classList.add('active');
            document.getElementById(tab).classList.add('active');
            history.pushState({}, '', `?tab=${tab}`);
        }


        // Khôi phục tab khi reload
        window.addEventListener('load', () => {
            const tab = new URLSearchParams(location.search).get('tab');
            if (['info','phone','password'].includes(tab)) showTab(tab);
        });
    </script>
<script>
let countdownInterval;

function sendOTP() {
    const newPhone = document.querySelector('input[name="new_phone"]').value.trim();
    const btn = document.getElementById('btnGetCode');
    const timerEl = document.getElementById('otpTimer');

    if (!newPhone) {
        alert('Vui lòng nhập số điện thoại mới!');
        return;
    }
    if (!/^0[3-9][0-9]{8}$/.test(newPhone)) {
        alert('Số điện thoại không hợp lệ!');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Đang gửi...';
    timerEl.textContent = '';

    fetch('<?= BASE_URL ?>/tai-khoan/gui-otp-sdt', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'new_phone=' + encodeURIComponent(newPhone)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Máy chủ lỗi: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            startCountdown(180);
        } else {
            alert(data.message || 'Gửi OTP thất bại!');
            btn.disabled = false;
            btn.textContent = 'Lấy mã xác thực';
        }
    })
    .catch(err => {
        console.error('Lỗi AJAX:', err);
        alert('Lỗi kết nối hoặc server trả về không phải JSON!\nKiểm tra console (F12) để xem chi tiết.');
        btn.disabled = false;
        btn.textContent = 'Lấy mã xác thực';
    });
}

function startCountdown(seconds) {
    const btn = document.getElementById('btnGetCode');
    const timerEl = document.getElementById('otpTimer');
    
    clearInterval(countdownInterval);
    countdownInterval = setInterval(() => {
        if (seconds <= 0) {
            clearInterval(countdownInterval);
            btn.disabled = false;
            btn.textContent = 'Gửi lại mã';
            timerEl.textContent = '';
            return;
        }
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        timerEl.textContent = `Gửi lại sau ${mins}:${secs.toString().padStart(2, '0')}`;
        seconds--;
    }, 1000);
    
    btn.textContent = 'Đã gửi mã';
}
</script>
        </div>
    </div>
    
</body>
</html>