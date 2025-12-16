<?php $title = "Đăng ký tài khoản"; ?>
<?php require_once __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/DangKy.css?v=<?= time() ?>">

<div class="auth-wrapper">
    <div class="container">
        <div class="title"><span>Đăng ký tài khoản</span></div>
        <br>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="color:#e74c3c; text-align:center; background:#ffe6e6; padding:10px; border-radius:8px; margin:15px 0;">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="color:#27ae60; text-align:center; background:#d4edda; padding:10px; border-radius:8px; margin:15px 0;">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/dang-ky" method="POST">
            <div class="text-warpper"><input type="text" name="hoten" placeholder="Họ và tên" required></div>
            <div class="text-warpper"><input type="email" name="email" placeholder="Email" required></div>
            <div class="text-warpper"><input type="password" name="pass" placeholder="Mật khẩu" required></div>
            <div class="text-warpper"><input type="password" name="repass" placeholder="Nhập lại mật khẩu" required></div>
            <div class="text-warpper"><input type="tel" name="phone" placeholder="Số điện thoại" required pattern="\d{10,15}"></div>
            <div class="text-warpper"><input type="text" name="address" placeholder="Địa chỉ" required></div>
            <div class="text-warpper"><input type="submit" value="Đăng ký"></div>
        </form>

        <div class="register" style="text-align:center; margin-top:20px;">
            Đã có tài khoản? <a href="<?= BASE_URL ?>/dang-nhap">Đăng nhập</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>