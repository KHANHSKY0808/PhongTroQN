# KIẾN TRÚC HỆ THỐNG DỰ ÁN PHONGTROQN

> **Tài liệu đặc tả kiến trúc kỹ thuật (Technical Architecture Document)**  
> **Dự án**: PhongTroQN - Nền tảng tìm kiếm và đăng tin phòng trọ Quy Nhơn  
> **Công nghệ**: PHP 8.x thuần (Custom MVC + Match Router), MySQL (PDO), PHPMailer, Leaflet/OpenStreetMap, jQuery.

---

## MỤC LỤC
1. [Tổng quan Kiến trúc Hệ thống](#1-tổng-quan-kiến-trúc-hệ-thống)
2. [Sơ đồ Định tuyến & Điểm cuối (Route Map)](#2-sơ-đồ-định-tuyến--điểm-cuối-route-map)
3. [Luồng Nghiệp vụ Chính (Business Flowcharts)](#3-luồng-nghiệp-vụ-chính-business-flowcharts)
   - 3.1. Luồng Xác thực & Phân quyền (Auth & Session)
   - 3.2. Luồng Đăng tin & Tải lên tệp (Post Creation & Uploads)
   - 3.3. Luồng Quản lý tin & Cập nhật/Xóa tin
   - 3.4. Luồng Tìm kiếm & Lọc dữ liệu (Filter Flow)
   - 3.5. Luồng Yêu thích (Favorite Toggle)
   - 3.6. Luồng Báo cáo vi phạm (Report Flow)
   - 3.7. Luồng Quản trị Admin (Dashboard, Reports, Users)
   - 3.8. Luồng Đổi SĐT qua OTP Email
4. [Mô hình Thực thể & Dữ liệu (ERD & Database Schema)](#4-mô-hình-thực-thể--dữ-liệu-erd--database-schema)
   - 4.1. Sơ đồ Quan hệ Thực thể (ERD)
   - 4.2. Đặc tả Chi tiết các Bảng (Suy diễn từ Codebase SQL)
   - 4.3. Các câu hỏi / Điểm chưa rõ về CSDL cần xác nhận
5. [Đánh giá & Audit Codebase Hiện tại](#5-đánh-giá--audit-codebase-hiện-tại)

---

## 1. TỔNG QUAN KIẾN TRÚC HỆ THỐNG

Dự án áp dụng mô hình **MVC tự xây dựng (Custom Lightweight MVC)** kết hợp mô hình **Front Controller**:

```
                       HTTP Request
                            │
                            ▼
                   [public/.htaccess]
                            │ (Rewrite all non-asset URLs)
                            ▼
                    [public/index.php] (Front Controller)
                            │
               ┌────────────┴────────────┐
               ▼                         ▼
      [config/config.php]       [app/functions.php]
     (Env & DB Constants)      (Auto-require, Helpers, Router)
                                         │
                         ┌───────────────┴───────────────┐
                         ▼                               ▼
                 [app/Controllers]                [app/Models]
                  - AuthController                 - DB (PDO singleton)
                  - HomeController                 - User
                  - PostController                 - Post
                  - FavoriteController             - Favorite
                  - UserController                 - hinhanh
                  - AdminController                      │
                         │                               ▼
                         │                        [MySQL Database]
                         ▼
                    [app/Views]
                  - home/, post/, user/
                  - admin/, auth/, errors/
```

- **Front Controller (`public/index.php`)**: Điểm vào duy nhất tiếp nhận HTTP request từ Web Server (Apache qua rewrite rule tại `public/.htaccess`).
- **Core Engine & Router (`app/functions.php`)**:
  - Tự động nạp toàn bộ Controllers và Models bằng hàm `glob()`.
  - Cung cấp các hàm helper toàn cục: `view()`, `redirect()`, `upload()`, `money()`, `e()`, `vn_str_filter()`.
  - Định tuyến kết hợp giữa **Regex Matching** (xử lý route có tham số dạng `/sua-tin/(\d+)`, `/xoa-tin/(\d+)`, `/api/post-detail/(\d+)`) và biểu thức **`match (true)`** của PHP 8.x.
- **Tầng Dữ liệu (`app/Models`)**: Kết nối MySQL thông qua `PDO` đóng gói trong class `DB` (`DB::connect()`) và hàm tiện ích `query($sql, $params)`. (Lưu ý: còn sót kết nối `mysqli` trong `config/config.php` và một số hàm trong `PostController`).

---

## 2. SƠ ĐỒ ĐỊNH TUYẾN & ĐIỂM CUỐI (ROUTE MAP)

Dưới đây là bảng tổng hợp toàn bộ các Route được đăng ký trong hệ thống (`app/functions.php`):

| HTTP Method | URL Pattern / Route | Handler (Controller @ Action) | Yêu cầu xác thực / Quyền hạn | Mô tả chức năng |
|:---|:---|:---|:---|:---|
| **GET** | `/` (kèm tham số `gia`, `loai`, `dientich`, `dactiem`) | `HomeController@filter` | Công khai | Lọc danh sách bài đăng theo tiêu chí |
| **GET** | `/`, `/trang-chu`, `/index.php`, `''` | `HomeController@index` | Công khai | Hiển thị trang chủ, danh sách tin mới nhất |
| **GET / POST** | `/dang-nhap` | `AuthController@login` | Khách (Guest) | Hiển thị form / Xử lý đăng nhập tài khoản |
| **GET / POST** | `/dang-ky` | `AuthController@register` | Khách (Guest) | Hiển thị form / Xử lý đăng ký tài khoản |
| **GET** | `/dang-xuat` | `AuthController@logout` | Đã đăng nhập | Hủy session và chuyển hướng về trang chủ |
| **GET** | `/dang-tin` | `PostController@create` | Yêu cầu đăng nhập (view check) | Hiển thị form soạn bài đăng mới |
| **POST** | `/dang-tin/luu` | `PostController@store` | Yêu cầu đăng nhập (`$_SESSION['user']`) | Lưu thông tin bài đăng, upload ảnh và video |
| **GET** | `/tin-cua-toi` | `PostController@myPosts` | Yêu cầu đăng nhập (`$_SESSION['user']`) | Quản lý danh sách tin do user hiện tại đăng |
| **GET** | `/sua-tin/{id}` | `PostController@edit($id)` | Yêu cầu đăng nhập + Chủ sở hữu | Hiển thị form sửa bài đăng |
| **POST** | `/cap-nhat-tin` | `PostController@update` | Yêu cầu đăng nhập + Chủ sở hữu | Cập nhật thông tin bài đăng vào DB |
| **GET** | `/xoa-tin/{id}` | `PostController@delete($id)` | Yêu cầu đăng nhập + Chủ sở hữu *(Hiện dùng GET)* | Xóa bài đăng và ảnh liên quan |
| **POST** | `/bao-cao` | `PostController@report` | Yêu cầu đăng nhập (`$_SESSION['user_id']`) | Gửi báo cáo bài đăng vi phạm |
| **GET** | `/api/post-detail/{id}` | Anonymous Closure (`app/functions.php`) | Công khai (AJAX) | Trả về JSON chi tiết bài đăng và danh sách ảnh |
| **GET** | `/admin` | `AdminController@index` | Admin (`is_admin == 1`) | Trang Dashboard thống kê số liệu quản trị |
| **GET** | `/admin/reports` | `AdminController@manageReports` | Admin (`is_admin == 1`) | Quản lý, lọc, xử lý hoặc xóa báo cáo/user/post |
| **GET** | `/admin/users` | `AdminController@users` | Admin (`is_admin == 1`) | Quản lý danh sách thành viên thường, xóa user |
| **POST** | `/admin/delete-post` | `AdminController@deletePost` | Admin (`is_admin == 1`, AJAX) | Admin xóa bài đăng qua popup AJAX |
| **GET** | `/admin/post-detail` | `AdminController@getPostDetail` | Admin (`is_admin == 1`, AJAX) | Lấy chi tiết bài đăng phục vụ modal admin |
| **GET** | `/tai-khoan` | `UserController@profile` | Yêu cầu đăng nhập (`$_SESSION['user']`) | Giao diện quản lý thông tin cá nhân |
| **POST** | `/tai-khoan/cap-nhat` | `UserController@updateProfile` | Yêu cầu đăng nhập (`$_SESSION['user']`) | Đổi họ tên người dùng |
| **POST** | `/tai-khoan/doi-sdt` | `UserController@changePhone` | Yêu cầu đăng nhập (`$_SESSION['user']`) | Xác thực OTP và cập nhật số điện thoại mới |
| **POST** | `/tai-khoan/gui-otp-sdt`| `UserController@sendPhoneOTP` | Yêu cầu đăng nhập (AJAX) | Sinh mã OTP và gửi qua Gmail SMTP |
| **POST** | `/tai-khoan/doi-mat-khau`| `UserController@changePassword` | Yêu cầu đăng nhập (`$_SESSION['user']`) | Đổi mật khẩu đăng nhập |
| **GET** | `/tin-da-luu` | `FavoriteController@index` | Yêu cầu đăng nhập (`$_SESSION['user_id']`) | Danh sách các tin người dùng đã yêu thích |
| **POST** | `/api/favorite/toggle` | `FavoriteController@toggle` | Yêu cầu đăng nhập (AJAX) | Thêm vào hoặc xóa khỏi danh sách tin đã lưu |
| **ANY** | `*` (Không khớp route trên) | `view('errors/404')` | Công khai | Hiển thị trang báo lỗi 404 Not Found |

---

## 3. LUỒNG NGHIỆP VỤ CHÍNH (BUSINESS FLOWCHARTS)

### 3.1. Luồng Xác thực & Đăng ký / Đăng nhập
```mermaid
sequenceDiagram
    autonumber
    actor User as Người dùng
    participant AuthC as AuthController
    participant UserM as User Model
    participant DB as MySQL Database
    participant Sess as $_SESSION

    User->>AuthC: POST /dang-nhap (email, pass)
    AuthC->>UserM: findByEmail(email)
    UserM->>DB: SELECT * FROM user WHERE Email = ?
    DB-->>UserM: Trả về bản ghi user
    UserM-->>AuthC: $user
    alt Mật khẩu đúng (password_verify)
        AuthC->>Sess: Lưu user_id, user_name, email, is_admin, user
        alt is_admin == 1
            AuthC-->>User: Redirect /admin
        else User thường
            AuthC-->>User: Redirect / (Trang chủ)
        end
    else Mật khẩu sai hoặc không tồn tại
        AuthC->>Sess: error = "Email hoặc mật khẩu không đúng!"
        AuthC-->>User: Hiển thị lại form kèm lỗi
    end
```

### 3.2. Luồng Đăng tin mới & Tải lên tệp (Post Creation Flow)
```mermaid
flowchart TD
    A[Bắt đầu: User gửi Form POST /dang-tin/luu] --> B{Kiểm tra Session Đăng nhập?}
    B -- Chưa đăng nhập --> C[Redirect /dang-nhap]
    B -- Đã đăng nhập --> D[Lấy thông tin: Tiêu đề, Mô tả, Giá, Diện tích, Tọa độ]
    D --> E[Gọi Post::create - INSERT INTO post]
    E --> F[Lấy ID vừa tạo: lastInsertId]
    F --> G{Có upload ảnh $_FILES?}
    G -- Có ảnh --> H[Vòng lặp: Gọi hàm upload]
    H --> I[Lưu bản ghi vào bảng hinhanh: INSERT hinhanh]
    G -- Không ảnh --> J{Có upload Video?}
    I --> J
    J -- Có File Video --> K[Gọi hàm upload video -> UPDATE post SET Video]
    J -- Có Link Video --> L[UPDATE post SET Video = link]
    J -- Không Video --> M[Hoàn tất đăng tin]
    K --> M
    L --> M
    M --> N[Alert JS thành công & Redirect về Trang chủ]
```

### 3.3. Luồng Quản lý, Sửa và Xóa tin đăng của tôi
```mermaid
flowchart TD
    subgraph Sửa tin
    A1[GET /sua-tin/{id}] --> B1{Đã đăng nhập?}
    B1 -- Không --> C1[Redirect /dang-nhap]
    B1 -- Có --> D1[Lấy Post theo ID]
    D1 --> E1{UserID của tin == UserID hiện tại?}
    E1 -- Sai --> F1[Chặn: die 'Không có quyền']
    E1 -- Đúng --> G1[Hiển thị View Edit-Post]
    G1 --> H1[POST /cap-nhat-tin]
    H1 --> I1{Kiểm tra quyền sở hữu}
    I1 -- Hợp lệ --> J1[UPDATE post WHERE PostID=? AND UserID=?]
    J1 --> K1[Redirect /tin-cua-toi?updated=1]
    end

    subgraph Xóa tin
    A2[GET /xoa-tin/{id}] --> B2{Đã đăng nhập?}
    B2 -- Không --> C2[Redirect /dang-nhap]
    B2 -- Có --> D2[Lấy UserID từ session]
    D2 --> E2[DELETE FROM post WHERE PostID=? AND UserID=?]
    E2 --> F2[DELETE FROM hinhanh WHERE PostID=?]
    F2 --> G2[Redirect /tin-cua-toi?deleted=1]
    end
```

### 3.4. Luồng Tìm kiếm & Lọc bài đăng (Filter Flow)
```mermaid
flowchart TD
    A[User chọn bộ lọc tại Header Modal -> Bấm Áp dụng] --> B[Gửi GET request về / với tham số tinh, huyen, loai, gia, dientich, dactiem]
    B --> C[Router functions.php kiểm tra tham số GET]
    C --> D[Gọi HomeController::filter]
    D --> E[Lấy toàn bộ tin active: Post::getActivePostsWithDetails]
    E --> F[Duyệt từng tin qua vòng lặp PHP foreach]
    F --> G{Khớp Tỉnh/Thành?}
    G -- Không --> H[Bỏ qua tin]
    G -- Khớp --> I{Khớp Quận/Huyện?}
    I -- Không --> H
    I -- Khớp --> J{Khớp Loại phòng, Giá, Diện tích, Đặc điểm?}
    J -- Không --> H
    J -- Khớp --> K[Thêm vào mảng filteredPosts]
    K --> L[Kiểm tra trạng thái Favorite nếu user đã đăng nhập]
    L --> M[Render View home/index với danh sách đã lọc]
```

### 3.5. Luồng Yêu thích bài đăng (Favorite Toggle)
```mermaid
sequenceDiagram
    autonumber
    actor User as Người dùng
    participant Browser as Trình duyệt (JS Fetch)
    participant FavC as FavoriteController
    participant FavM as Favorite Model
    participant DB as MySQL (bảng favorites)

    User->>Browser: Bấm nút Trái tim (Heart) trên card tin
    Browser->>FavC: POST /api/favorite/toggle (post_id)
    alt Chưa đăng nhập
        FavC-->>Browser: JSON {success: false, message: "Vui lòng đăng nhập"}
        Browser->>User: Alert yêu cầu đăng nhập
    else Đã đăng nhập
        FavC->>FavM: toggle(userID, postID)
        FavM->>DB: SELECT * FROM favorites WHERE UserID=? AND PostID=?
        alt Đã lưu trước đó
            FavM->>DB: DELETE FROM favorites WHERE UserID=? AND PostID=?
            FavM-->>FavC: {action: 'removed', success: true}
            FavC-->>Browser: Trả JSON
            Browser->>User: Xóa class .saved (Trái tim rỗng)
        else Chưa lưu
            FavM->>DB: INSERT INTO favorites (UserID, PostID, NgayLuu) VALUES (?, ?, NOW())
            FavM-->>FavC: {action: 'added', success: true}
            FavC-->>Browser: Trả JSON
            Browser->>User: Thêm class .saved (Trái tim đỏ)
        end
    end
```

### 3.6. Luồng Báo cáo vi phạm (Report Flow)
```mermaid
sequenceDiagram
    autonumber
    actor User as Người dùng
    participant View as Modal Báo cáo (JS)
    participant PostC as PostController
    participant DB as MySQL (bảng baocao)
    actor Admin as Quản trị viên
    participant AdminC as AdminController

    User->>View: Bấm "Báo cáo" trên card bài đăng
    View->>User: Mở Popup nhập lý do (reason)
    User->>PostC: POST /bao-cao (postID, reason)
    PostC->>DB: INSERT INTO baocao (UserID, PostID, NoiDung, DaXuLy) VALUES (?, ?, ?, 0)
    PostC-->>User: Redirect trang chủ + flash thông báo thành công
    Note over Admin, AdminC: Khi Admin đăng nhập vào hệ thống
    Admin->>AdminC: GET /admin/reports
    AdminC->>DB: SELECT * FROM baocao JOIN post JOIN user
    DB-->>AdminC: Danh sách báo cáo
    AdminC-->>Admin: Hiển thị danh sách báo cáo vi phạm
```

### 3.7. Luồng Quản trị Admin (Dashboard & Quản lý)
```mermaid
flowchart TD
    A[Admin truy cập /admin hoặc /admin/reports hoặc /admin/users] --> B{Kiểm tra session user_id & is_admin == 1}
    B -- Sai --> C[Redirect về Trang chủ /]
    B -- Đúng --> D{Chọn tác vụ quản trị}
    
    D --> E[Trang chủ Dashboard: Thống kê Post, User, Report]
    D --> F[Quản lý Báo cáo: GET /admin/reports]
    D --> G[Quản lý Người dùng: GET /admin/users]
    D --> H[Xóa tin vi phạm: POST /admin/delete-post AJAX]
    
    F --> F1[Đánh dấu Đã xử lý: UPDATE baocao SET DaXuLy=1]
    F --> F2[Xóa báo cáo: DELETE FROM baocao]
    F --> F3[Xóa người dùng vi phạm: Xóa baocao, post, user]
    F --> F4[Xóa bài đăng bị báo cáo: Xóa baocao, post]
    
    G --> G1[Khóa/Xóa thành viên thường: DELETE FROM user WHERE IsAdmin=0]
    H --> H1[Xóa hình ảnh, xóa báo cáo, xóa post]
```

### 3.8. Luồng Đổi số điện thoại qua OTP Email
```mermaid
sequenceDiagram
    autonumber
    actor User as Người dùng
    participant UserC as UserController
    participant MailH as MailHelper (PHPMailer)
    participant SMTP as Gmail SMTP Server
    participant Sess as $_SESSION

    User->>UserC: POST /tai-khoan/gui-otp-sdt (new_phone) [AJAX]
    UserC->>UserC: Validate định dạng SĐT + Kiểm tra trùng lặp
    UserC->>UserC: Sinh mã OTP (6 số) & Thời hạn (hết hạn sau 3 phút)
    UserC->>Sess: Lưu otp_phone, otp_expire, phone_pending
    UserC->>MailH: sendOTP(email_user, otp)
    MailH->>SMTP: Gửi email HTML chứa mã OTP
    SMTP-->>MailH: Kết quả gửi thành công
    UserC-->>User: JSON {success: true, message: 'Đã gửi OTP'}
    Note over User, UserC: Người dùng kiểm tra hòm thư email
    User->>UserC: POST /tai-khoan/doi-sdt (new_phone, otp)
    alt OTP chính xác và còn trong 3 phút
        UserC->>UserC: Cập nhật SĐT mới vào CSDL (bảng user)
        UserC->>Sess: Xóa otp_phone, cập nhật session user
        UserC-->>User: Thông báo đổi SĐT thành công
    else OTP sai hoặc hết hạn
        UserC-->>User: Báo lỗi OTP không hợp lệ
    end
```

---

## 4. MÔ HÌNH THỰC THỂ & DỮ LIỆU (ERD & DATABASE SCHEMA)

### 4.1. Sơ đồ Quan hệ Thực thể (ERD)

```mermaid
erDiagram
    user ||--o{ post : "đăng (1 - n)"
    user ||--o{ favorites : "lưu (1 - n)"
    user ||--o{ baocao : "gửi báo cáo (1 - n)"
    user ||--o{ user_role : "gán vai trò (1 - n)"
    vaitro ||--o{ user_role : "định nghĩa (1 - n)"
    
    post ||--o{ hinhanh : "chứa (1 - n)"
    post ||--o{ favorites : "được lưu (1 - n)"
    post ||--o{ baocao : "bị báo cáo (1 - n)"

    user {
        int UserID PK "Khóa chính tự tăng"
        varchar HoTen "Họ và tên người dùng"
        varchar Email "Email duy nhất (Unique)"
        varchar MatKhau "Mật khẩu mã hóa (hash)"
        varchar SoDienThoai "Số điện thoại liên hệ"
        varchar DiaChi "Địa chỉ cư trú"
        tinyint TrangThai "1: Hoạt động, 0: Bị khóa"
        tinyint IsAdmin "0: Người dùng, 1: Quản trị viên"
        datetime created_at "Đề xuất thêm: Ngày tạo tài khoản"
    }

    vaitro {
        int RoleID PK "Khóa chính vai trò"
        varchar TenVaiTro "Tên vai trò (Admin, Chủ trọ, Người thuê)"
    }

    user_role {
        int UserID PK,FK "Khóa ngoại liên kết bảng user"
        int RoleID PK,FK "Khóa ngoại liên kết bảng vaitro"
    }

    post {
        int PostID PK "Khóa chính bài đăng"
        int UserID FK "Khóa ngoại người đăng"
        varchar TieuDe "Tiêu đề bài đăng"
        text MoTa "Mô tả chi tiết phòng trọ"
        varchar DiaChi "Địa chỉ chi tiết"
        decimal DienTich "Diện tích phòng (m²)"
        decimal GiaPhong "Giá cho thuê (VNĐ/tháng)"
        decimal Latitude "Tọa độ vĩ độ Leaflet"
        decimal Longitude "Tọa độ kinh độ Leaflet"
        tinyint TrangThai "1: Đang hiển thị, 0: Đã ẩn"
        datetime NgayDang "Thời gian đăng bài"
        varchar Video "Đường dẫn video hoặc link YouTube/TikTok"
    }

    hinhanh {
        int HinhID PK "Khóa chính hình ảnh"
        int PostID FK "Khóa ngoại bài đăng"
        varchar URLAnh "Đường dẫn ảnh upload"
    }

    favorites {
        int FavoriteID PK "Khóa chính bản ghi lưu (hoặc PK UserID+PostID)"
        int UserID FK "Khóa ngoại người dùng lưu tin"
        int PostID FK "Khóa ngoại bài đăng được lưu"
        datetime NgayLuu "Thời gian lưu tin"
    }

    baocao {
        int BaoCaoID PK "Khóa chính báo cáo"
        int UserID FK "Khóa ngoại người gửi báo cáo"
        int PostID FK "Khóa ngoại bài đăng bị báo cáo"
        text NoiDung "Lý do và nội dung vi phạm"
        datetime NgayBaoCao "Thời gian gửi báo cáo"
        tinyint DaXuLy "0: Chưa xử lý, 1: Đã xử lý"
    }
```

---

### 4.2. Đặc tả Chi tiết các Bảng (Suy diễn từ Codebase SQL)

Dựa trên toàn bộ câu lệnh SQL thực thi trong `app/Models/User.php`, `Post.php`, `Favorite.php`, `hinhanh.php`, `AdminController.php`, `PostController.php`:

#### 1. Bảng `user`
- `UserID`: `INT AUTO_INCREMENT PRIMARY KEY`
- `HoTen`: `VARCHAR(100) NOT NULL`
- `Email`: `VARCHAR(100) NOT NULL UNIQUE`
- `MatKhau`: `VARCHAR(255) NOT NULL` (Lưu `password_hash()`)
- `SoDienThoai`: `VARCHAR(20) NULL`
- `DiaChi`: `VARCHAR(255) NULL`
- `TrangThai`: `TINYINT DEFAULT 1` (1 = Active, 0 = Banned/Inactive)
- `IsAdmin`: `TINYINT DEFAULT 0` (0 = User thường, 1 = Quản trị viên)
- *(Khuyến nghị thêm)* `created_at`: `DATETIME DEFAULT CURRENT_TIMESTAMP` (Code `AdminController.php` và `users.php` có nhắc đến sự thiếu vắng của cột này).

#### 2. Bảng `vaitro` & Bảng trung gian `user_role`
- Bảng `vaitro`:
  - `RoleID`: `INT AUTO_INCREMENT PRIMARY KEY`
  - `TenVaiTro`: `VARCHAR(50) NOT NULL` (Dữ liệu ngầm định trong code: `3` = "Người thuê")
- Bảng `user_role`:
  - `UserID`: `INT NOT NULL`
  - `RoleID`: `INT NOT NULL`
  - Khóa chính kết hợp: `PRIMARY KEY (UserID, RoleID)`
  - Khóa ngoại: Ràng buộc tới `user(UserID)` và `vaitro(RoleID)`

#### 3. Bảng `post`
- `PostID`: `INT AUTO_INCREMENT PRIMARY KEY`
- `UserID`: `INT NOT NULL` (Khóa ngoại tham chiếu `user.UserID`)
- `TieuDe`: `VARCHAR(255) NOT NULL`
- `MoTa`: `TEXT NULL`
- `DiaChi`: `VARCHAR(255) NOT NULL`
- `DienTich`: `DECIMAL(8,2) NOT NULL DEFAULT 0.00`
- `GiaPhong`: `DECIMAL(15,2) NOT NULL DEFAULT 0.00`
- `Latitude`: `DECIMAL(10,8) NULL`
- `Longitude`: `DECIMAL(11,8) NULL`
- `TrangThai`: `TINYINT DEFAULT 1` (1 = Hiển thị, 0 = Ẩn)
- `NgayDang`: `DATETIME DEFAULT CURRENT_TIMESTAMP`
- `Video`: `VARCHAR(255) NULL` (Đường dẫn video cục bộ hoặc URL ngoài)

#### 4. Bảng `hinhanh`
- `HinhID`: `INT AUTO_INCREMENT PRIMARY KEY`
- `PostID`: `INT NOT NULL` (Khóa ngoại tham chiếu `post.PostID` ON DELETE CASCADE)
- `URLAnh`: `VARCHAR(255) NOT NULL`

#### 5. Bảng `favorites`
- `FavoriteID`: `INT AUTO_INCREMENT PRIMARY KEY` (hoặc khóa kết hợp `UserID`, `PostID`)
- `UserID`: `INT NOT NULL` (Khóa ngoại tham chiếu `user.UserID` ON DELETE CASCADE)
- `PostID`: `INT NOT NULL` (Khóa ngoại tham chiếu `post.PostID` ON DELETE CASCADE)
- `NgayLuu`: `DATETIME DEFAULT CURRENT_TIMESTAMP`
- Ràng buộc: `UNIQUE KEY (UserID, PostID)`

#### 6. Bảng `baocao`
- `BaoCaoID`: `INT AUTO_INCREMENT PRIMARY KEY`
- `UserID`: `INT NOT NULL` (Khóa ngoại tham chiếu `user.UserID` ON DELETE CASCADE)
- `PostID`: `INT NOT NULL` (Khóa ngoại tham chiếu `post.PostID` ON DELETE CASCADE)
- `NoiDung`: `TEXT NOT NULL`
- `NgayBaoCao`: `DATETIME DEFAULT CURRENT_TIMESTAMP`
- `DaXuLy`: `TINYINT DEFAULT 0` (0 = Chưa xử lý, 1 = Đã giải quyết)

---

### 4.3. Các câu hỏi / Điểm chưa rõ về CSDL cần xác nhận với User
1. **Bảng phân quyền (`vaitro` và `user_role`)**: Hiện tại code đăng ký có gán cứng `RoleID = 3` ("Người thuê") vào `user_role`, nhưng kiểm tra quyền Admin trong toàn bộ dự án lại dựa vào cột `user.IsAdmin` (0 hoặc 1) chứ không dùng `user_role`. Chúng ta có nên duy trì cả 2 cơ chế hay tinh gọn thành 1 cơ chế phân quyền nhất quán?
2. **Khóa ngoại và hành vi Cascade**: Khi Admin xóa một bài đăng hoặc xóa một User, hiện tại code PHP đang tự chạy các lệnh `DELETE FROM hinhanh...`, `DELETE FROM baocao...` thủ công. Trong file `schema.sql` sắp tới, chúng ta sẽ thiết lập chuẩn `ON DELETE CASCADE` ở mức CSDL MySQL để đảm bảo toàn vẹn dữ liệu?
3. **Cột `created_at` trong bảng `user`**: Trong `AdminController.php` (dòng 23) và `users.php` (dòng 84) có tham chiếu đến `created_at` của bảng `user` nhưng đang phải gán tạm là 0 hoặc `-`. Chúng ta sẽ chính thức thêm cột `created_at DATETIME DEFAULT CURRENT_TIMESTAMP` vào bảng `user` ở Giai đoạn 2?
4. **Các bảng rác trong file cũ**: Trong `public/dangtin.php` (file mồ côi) có tham chiếu bảng `baidang` thay vì `post`. Ta xác nhận loại bỏ hoàn toàn cấu trúc cũ này.

---

## 5. ĐÁNH GIÁ & AUDIT CODEBASE HIỆN TẠI

Toàn bộ các lỗ hổng bảo mật, lỗi logic, hiệu năng và sự thiếu nhất quán trong codebase đã được lập thành danh sách chi tiết kèm `file:dòng` cụ thể trong báo cáo thẩm định kỹ thuật.

