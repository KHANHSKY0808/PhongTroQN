<?php $title = "Đăng nhập"; ?>
<?php require_once __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/DangNhap.css">

<div class="container">
    <div class="title"><span>Đăng nhập</span></div>
    <br>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="color:#e74c3c; text-align:center; margin-bottom:15px;">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div style="color:#27ae60; text-align:center; margin-bottom:15px;">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/dang-nhap" method="POST">
        <div class="input_wrapper">
            <input type="text" name="email" class="input_field" required>
            <label class="label">Email</label>
        </div>

        <div class="input_wrapper">
            <input type="password" name="pass" class="input_field" required>
            <label class="label">Mật khẩu</label>
        </div>

        <div class="remember-forgot">
            <div class="remeber_me">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Nhớ mật khẩu</label>
            </div>
            <div class="forgot">
                <a href="#">Quên mật khẩu?</a>
            </div>
        </div>

        <div class="button_wrapper">
            <button type="submit" class="login_button">Đăng nhập</button>
        </div>

        <br>
        <div class="register">
            Chưa có tài khoản? <a href="<?= BASE_URL ?>/dang-ky">Đăng ký ngay</a>
        </div>
    </form>
</div>

<div class="footer">
    <p>&copy; Đảm bảo thông tin an toàn và bảo mật!</p>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>