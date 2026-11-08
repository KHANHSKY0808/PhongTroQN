# Changelog

Toàn bộ các thay đổi kỹ thuật quan trọng của dự án **PhongTroQN** được ghi lại tại đây theo chuẩn [Keep a Changelog](https://keepachangelog.com/vi/1.0.0/) và [Semantic Versioning](https://semver.org/).

---

## [2.0.0] - 2026-11-08

### 🛡️ Bảo Mật (Security)
- **Upload an toàn**:
  - Viết lại hàm `upload()` trong `app/functions.php`: kiểm tra đuôi file theo whitelist (`jpg`, `jpeg`, `png`, `webp`, `mp4`, `webm`), xác minh MIME type thực tế bằng `finfo_file()` (chống giả mạo header file).
  - Giới hạn kích thước file tải lên: tối đa 5MB cho hình ảnh, 30MB cho video.
  - Tên file sinh ngẫu nhiên mã hóa an toàn qua `bin2hex(random_bytes(16))` chống ghi đè và Directory Traversal.
  - Tạo cấu hình `uploads/.htaccess` chặn toàn bộ quyền thực thi script PHP/CGI (`engine off`, `RemoveHandler`, `deny from all` cho script).
- **Quản lý bí mật & Cấu hình môi trường (.env)**:
  - Tách toàn bộ thông tin nhạy cảm (DB credentials, SMTP password, Secret keys) khỏi codebase vào file `.env`.
  - Cung cấp file `.env.example` mẫu chuẩn hóa.
  - Cập nhật `.gitignore` loại trừ hoàn toàn `.env` và các biến thể môi trường.
  - Xóa bỏ file cấu hình lỗi `.evn` khỏi Git history.
- **Bảo vệ CSRF (Cross-Site Request Forgery)**:
  - Xây dựng helper `Csrf` (`app/Helpers/Csrf.php`) sinh token bảo mật bằng `random_bytes(32)`.
  - Thiết lập middleware `Csrf::verifyOrAbort()` chặn toàn bộ request HTTP mutating (`POST`, `PUT`, `DELETE`).
  - Tích hợp thẻ `csrf_field()` vào toàn bộ các form HTML (`DangTin`, `Edit-Post`, `login`, `register`, `profile`, `report modal`).
  - Tích hợp header `X-CSRF-Token` vào các endpoint AJAX (`api/favorite/toggle`, `gui-otp-sdt`, `admin/delete-post`).
- **Thống nhất Session & Phân quyền (Auth Middleware)**:
  - Xây dựng helper `Auth` (`app/Helpers/Auth.php`) quản lý session tập trung: `check()`, `id()`, `user()`, `isAdmin()`, `login()`, `logout()`.
  - Thiết lập thuộc tính an toàn cho Session Cookie: `HttpOnly=true`, `SameSite=Lax`, `Secure` khi chạy HTTPS.
  - Gọi `session_regenerate_id(true)` ngay khi đăng nhập để chống tấn công Session Fixation.
  - Thống nhất cơ chế session, loại bỏ phân mảnh giữa `$_SESSION['user']`, `$_SESSION['user_id']` và `$_SESSION['is_admin']`.
- **Chống IDOR (Insecure Direct Object References)**:
  - Kiểm tra quyền sở hữu bài đăng tại `PostController::update()` và `PostController::delete()`: chỉ cho phép chủ sở hữu bài đăng (`UserID`) hoặc Quản trị viên (`isAdmin`) thao tác.
  - Chuyển toàn bộ thao tác xóa bài viết từ HTTP `GET` (`/xoa-tin/{id}`) sang HTTP `POST` kèm mã CSRF token.
- **Bảo mật OTP & Rate Limiting**:
  - Nâng cấp sinh mã OTP từ `rand()` sang `random_int(100000, 999999)` chuẩn mật mã học.
  - Lưu mã OTP dưới dạng băm `password_hash()` trong Session, xác thực bằng `password_verify()`.
  - Giới hạn tần suất gửi OTP: tối thiểu 60 giây giữa các lần gửi.
  - Giới hạn số lần thử OTP: khóa sau 5 lần nhập sai.
  - Bổ sung cơ chế Rate Limit phòng chống Brute-force tại form đăng nhập (khóa tạm 60 giây sau 5 lần thất bại liên tiếp).

---

### ⚡ Hiệu Năng & Tối Ưu (Performance)
- **Lọc bài đăng trực tiếp bằng SQL**:
  - Loại bỏ hoàn toàn cơ chế cũ (tải toàn bộ tin đăng vào RAM và lặp mảng PHP lọc chuỗi).
  - Viết lại phương thức `Post::getFilteredPosts()` và `Post::countFilteredPosts()` xây dựng mệnh đề `WHERE` động bằng Prepared Statements.
  - Hỗ trợ đầy đủ các tiêu chí lọc: khoảng giá (`GiaPhong`), diện tích (`DienTich`), loại phòng, khu vực quận/huyện (`huyen`), tỉnh/thành (`tinh`).
- **Phân trang dữ liệu (Pagination)**:
  - Phân trang chuẩn SQL sử dụng `LIMIT` và `OFFSET` (12 tin/trang).
  - Bổ sung thanh phân trang Pagination UI trên giao diện `home/index.php`, tự động duy trì các tham số truy vấn của bộ lọc trên URL.
- **Tối ưu hóa Index Cơ sở dữ liệu**:
  - Bổ sung các chỉ mục tổ hợp trong CSDL: `idx_post_filter (TrangThai, GiaPhong, DienTich, PostID)`, `idx_post_user (UserID)`, `idx_baocao_status (DaXuLy)`.

---

### 🏗️ Kiến Trúc & Tái Cấu Trúc (Refactoring & Architecture)
- **Tích hợp Composer & Autoload PSR-4**:
  - Khởi tạo `composer.json` quản lý thư viện tập trung: `vlucas/phpdotenv: ^5.8`, `phpmailer/phpmailer: ^6.12`, `phpunit/phpunit: ^9.6`.
  - Cấu hình PSR-4 autoload cho namespace `App\` và classmap cho các Controllers, Models, Helpers.
  - Loại bỏ thư mục mã nguồn sao chép thủ công `app/Helpers/PHPMailer-master/` (69 files).
- **Thống nhất PDO & Xóa bỏ mysqli**:
  - Chuẩn hóa kết nối CSDL qua `DB` PDO Singleton (`app/Models/DB.php`): hỗ trợ port, charset `utf8mb4`, `ATTR_EMULATE_PREPARES => false`, `ERRMODE_EXCEPTION`.
  - Hỗ trợ transactions (`beginTransaction`, `commit`, `rollBack`) và Dependency Injection cho unit testing (`DB::setConnection()`).
  - Gỡ bỏ hoàn toàn biến toàn cục `$conn` mysqli khỏi `config/config.php`.
- **Validation tập trung & Flash Messages**:
  - Xây dựng lớp `Validator` (`app/Helpers/Validator.php`) với các rules: `required`, `numeric`, `min`, `max`, `min_len`, `max_len`, `email`, `phone`, `latitude`, `longitude`, `video_url`.
  - Whitelist domain video: chỉ chấp nhận liên kết từ YouTube và TikTok.
  - Thay thế toàn bộ mã `alert()` JavaScript và chuyển hướng bằng hệ thống Flash Message chuyên nghiệp (`setFlash()`, `getFlash()`, `hasFlash()`).
- **Ghi log tập trung & Xử lý lỗi theo môi trường**:
  - Xây dựng `Logger` (`app/Helpers/Logger.php`) ghi log theo chuẩn `[DATE] [LEVEL]: Message` vào `storage/logs/app.log`.
  - Thiết lập `set_exception_handler` hiển thị trang lỗi 500 thân thiện khi ở chế độ `production` và chi tiết stack trace khi `APP_DEBUG=true`.
  - Xóa bỏ file nhạy cảm `public/test-routing.php`.
- **Dọn dẹp View Templates**:
  - Sửa lỗi HTML lồng nhau trong `app/Views/partials/footer.php` (loại bỏ lặp `<html><head><body>`).
  - Chuẩn hóa đường dẫn tài nguyên tĩnh `<?= ASSETS_URL ?>/...`.
  - Bổ sung ghi chú minh họa cho các logo thanh toán.
  - Xóa bỏ các view rác mồ côi không dùng: `favorites/favorites.php`, `favorites/index.php`, `filter.php`, `filterResult.php`, `partials/user-actions.php`.

---

### 🧪 Kiểm Thử Tự Động (Testing)
- Cấu hình `phpunit.xml` và `tests/bootstrap.php`.
- Viết 21 ca kiểm thử đơn vị (74 assertions) đạt tỷ lệ vượt qua 100%:
  - `HelperFunctionsTest`: kiểm thử hàm lọc dấu tiếng Việt `vn_str_filter()`, định dạng tiền tệ `money()`, chống XSS `e()`, session flash messages.
  - `ValidatorTest`: kiểm thử các validation rules, regex SĐT Việt Nam, tọa độ bản đồ, whitelist video URL.
  - `CsrfTest`: kiểm thử sinh token, render input field và xác thực token.
  - `AuthTest`: kiểm thử logic phân quyền, trạng thái khách/admin, bảo vệ session khi đăng nhập/đăng xuất.
  - `DatabaseModelTest`: kiểm thử các thao tác CSDL của `User` và `Post` model thông qua SQLite in-memory database độc lập.

---

### 🐳 DevOps & CI/CD
- **Containerization**:
  - `Dockerfile`: xây dựng image PHP 8.2 Apache tối ưu, cài sẵn extensions (`pdo_mysql`, `gd`, `zip`, `fileinfo`), kích hoạt `mod_rewrite`, trỏ DocumentRoot vào `public/`.
  - `docker-compose.yml`: thiết lập 3 services gồm `app` (PHP 8.2), `db` (MySQL 8.0 tự nạp schema và seed data), `phpmyadmin` (GUI quản lý).
- **CI Pipeline**:
  - `.github/workflows/ci.yml`: tự động hóa quy trình kiểm thử với ma trận PHP (8.0, 8.1, 8.2), kiểm tra cú pháp PHP toàn dự án (`php -l`), kiểm tra tính hợp lệ của composer và chạy PHPUnit test suite trên mỗi commit.

