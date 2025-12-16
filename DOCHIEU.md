# Project PHP Cơ Bản

## Cấu trúc thư mục
- **frontend/**: chứa các file giao diện (view, HTML, CSS, JS).
- **backend/**: chứa các file xử lý logic PHP (đăng nhập, đăng ký, đặt vé...).
- **assets/**: chứa tài nguyên tĩnh (ảnh, CSS, JS).
- **config/**: chứa file cấu hình, ví dụ `config.php` để kết nối database.
- **index.php**: trang chính, load header + footer.
- **header.php**: phần đầu của website (menu, CSS link).
- **footer.php**: phần cuối của website (copyright).

## Cách chạy
1. Import file `database.sql` vào MySQL.
2. Mở file `config/config.php` và sửa thông tin kết nối cho đúng.
3. Chạy `index.php` bằng localhost (XAMPP/WAMP).