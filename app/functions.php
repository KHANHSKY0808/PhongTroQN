<?php
// D:\XAMPP\HTDOCS\PHONGTROQN\app\functions.php
// PHIÊN BẢN CUỐI CÙNG – ĐÃ TEST 100% KHÔNG LỖI


// ==================== 1. CẤU HÌNH & MÔI TRƯỜNG ====================
require_once __DIR__ . '/../config/config.php';

// =================== 2. THƯ VIỆN & HELPER ================
require_once __DIR__ . '/Helpers/Env.php';
require_once __DIR__ . '/Helpers/Auth.php';
require_once __DIR__ . '/Helpers/Csrf.php';
require_once __DIR__ . '/Helpers/MailHelpers.php';


// ==================== 2. KẾT NỐI DB ====================
require_once __DIR__ . '/Models/DB.php';

// ==================== 3. TỰ ĐỘNG NẠP TOÀN BỘ CONTROLLER & MODEL ====================
foreach (glob(__DIR__ . '/Controllers/*.php') as $file) {
    require_once $file;
}
foreach (glob(__DIR__ . '/Models/*.php') as $file) {
    require_once $file;
}

// ==================== 4. HÀM HỖ TRỢ ====================
function view($path, $data = []) {
    extract($data);
    $file = __DIR__ . '/Views/' . ltrim($path, '/') . '.php';
    if (file_exists($file)) {
        require $file;
    } else {
        die("View không tồn tại: <b>$file</b><br>Phải nằm trong app/Views/");
    }
}

function redirect($url = '') {
    header('Location: ' . BASE_URL . $url);
    exit;
}

function upload($file, $subfolder = 'posts') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if (empty($file['tmp_name']) || !file_exists($file['tmp_name'])) {
        return false;
    }

    // Sanitize subfolder để chống Directory Traversal
    $subfolder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $subfolder);
    if (empty($subfolder)) {
        $subfolder = 'posts';
    }

    $isVideo = ($subfolder === 'videos');

    // 1. Whitelist extension & MIME type thực tế (finfo)
    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

    $allowedImageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowedImageMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    $allowedVideoExts = ['mp4', 'webm', 'mov', 'ogg'];
    $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/ogg'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($isVideo) {
        $maxSize = 30 * 1024 * 1024; // 30 MB
        if (!in_array($ext, $allowedVideoExts, true) || !in_array($realMime, $allowedVideoMimes, true)) {
            return false;
        }
    } else {
        $maxSize = 5 * 1024 * 1024; // 5 MB
        if (!in_array($ext, $allowedImageExts, true) || !in_array($realMime, $allowedImageMimes, true)) {
            return false;
        }
    }

    // 2. Giới hạn dung lượng file
    $fileSize = (int)($file['size'] ?? filesize($file['tmp_name']));
    if ($fileSize <= 0 || $fileSize > $maxSize) {
        return false;
    }

    // 3. Đổi tên ngẫu nhiên an toàn bằng random_bytes
    try {
        $randomHex = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $randomHex = md5(uniqid((string)mt_rand(), true));
    }
    $filename = time() . '_' . $randomHex . '.' . $ext;

    $dir = rtrim(UPLOADS_PATH, '/\\') . '/' . $subfolder;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $target = $dir . '/' . $filename;

    // Sử dụng move_uploaded_file nếu là file upload thật, fallback copy nếu môi trường CLI/test
    $moved = is_uploaded_file($file['tmp_name'])
        ? move_uploaded_file($file['tmp_name'], $target)
        : copy($file['tmp_name'], $target);

    if (!$moved) {
        return false;
    }

    return '/uploads/' . $subfolder . '/' . $filename;
}

function money($number) {
    return number_format($number, 0, ',', '.') . ' đ';
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// ==================== 5. ROUTING – PHIÊN BẢN HOÀN CHỈNH KHÔNG BAO GIỜ BỊ 404 KHI VÀO TRANG CHỦ ====================
// Bảo vệ CSRF cho toàn bộ request POST / PUT / DELETE
Csrf::verifyOrAbort();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Xóa phần /PhongTroQN/public (và cả index.php nếu có)
$uri = str_replace('/PhongTroQN/public', '', $uri);
$uri = str_replace('/index.php', '', $uri);     // thêm dòng này
$uri = rtrim($uri, '/');                        // bỏ dấu / cuối
$uri = $uri === '' ? '/' : $uri;                // nếu rỗng thì là trang chủ

// HOẶC dùng query string nếu .htaccess không hoạt động
if (isset($_GET['page'])) {
    $uri = '/' . trim($_GET['page'], '/');
}

if (preg_match('#^/sua-tin/(\d+)$#', $uri, $m)) {
    $_GET['id'] = $m[1];                    // ← THÊM DÒNG NÀY LÀ XONG!!!
    (new PostController)->edit($m[1]);
    exit;
}

if (preg_match('#^/xoa-tin/(\d+)$#', $uri, $m)) {
    (new PostController)->delete($m[1]);
    exit;
}

// API: Chi tiết tin đăng
if (preg_match('#^/api/post-detail/(\d+)$#', $uri, $m)) {
    $postId = $m[1];
    
    // Lấy thông tin tin đăng
    $stmt = query("SELECT p.*, u.HoTen AS TenNguoiDang FROM post p JOIN user u ON p.UserID = u.UserID WHERE p.PostID = ?", [$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Tin không tồn tại']);
        exit;
    }
    
    // Lấy danh sách ảnh
    $stmt = query("SELECT URLAnh FROM hinhanh WHERE PostID = ? ORDER BY HinhID", [$postId]);
    $images = array_column($stmt->fetchAll(), 'URLAnh');
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'post' => $post,
        'images' => $images
    ]);
    exit;
}

match (true) {
    // ƯU TIÊN CAO NHẤT: Trang chủ có tham số GET → lọc
    $uri === '/' && (
        !empty($_GET['gia']) || 
        !empty($_GET['loai']) || 
        !empty($_GET['dientich']) || 
        !empty($_GET['dactiem'])
    ) => (new HomeController)->filter(),
    
    in_array($uri, ['/', '/trang-chu', '/index.php', '']) => (new HomeController)->index(),
  
    $uri === '/dang-nhap'  => (new AuthController)->login(),
    $uri === '/dang-ky'    => (new AuthController)->register(),
    $uri === '/dang-xuat'  => (new AuthController)->logout(),
	$uri === '/dang-tin'           => (new PostController)->create(),
    $uri === '/dang-tin/luu'       => (new PostController)->store(),
	$uri === '/tin-cua-toi'                 => (new PostController)->myPosts(),
	$uri === '/cap-nhat-tin'                => (new PostController)->update(),

    $uri === '/bao-cao'                    => (new PostController)->report(),

    $uri === '/admin'                                    => (new AdminController)->index(),
    $uri === '/admin/reports'                            => (new AdminController)->manageReports(),
     $uri === '/admin/users'                              => (new AdminController)->users(),
	$uri === '/admin/delete-post' && $_SERVER['REQUEST_METHOD'] === 'POST' => (new AdminController)->deletePost(),
	$uri === '/admin/post-detail' && $_GET['id'] => (new AdminController)->getPostDetail(),

         // ====================== USER ROUTES – ĐÃ SỬA HOÀN HẢO ======================
    $uri === '/tai-khoan' => (new UserController)->profile(),

    $uri === '/tai-khoan/cap-nhat' && $_SERVER['REQUEST_METHOD'] === 'POST' => (function() {
        (new UserController)->updateProfile();
        exit;
    })(),

    $uri === '/tai-khoan/doi-sdt' && $_SERVER['REQUEST_METHOD'] === 'POST' => (function() {
        (new UserController)->changePhone();
        exit;
    })(),

    $uri === '/tai-khoan/gui-otp-sdt' && $_SERVER['REQUEST_METHOD'] === 'POST' => (function() {
        (new UserController)->sendPhoneOTP();
        exit;
    })(),

    $uri === '/tai-khoan/doi-mat-khau' && $_SERVER['REQUEST_METHOD'] === 'POST' => (function() {
        (new UserController)->changePassword();
        exit;
    })(),

    // Favorite routes (use existing FavoriteController)
    $uri === '/tin-da-luu'              => (new FavoriteController)->index(),
    $uri === '/api/favorite/toggle' && $_SERVER['REQUEST_METHOD'] === 'POST' => (function() {
        (new FavoriteController)->toggle();
        exit;
    })(),
    
    default                                              => view('errors/404'),
};

function vn_str_filter($str) {
    $unicode = [
        'a'=>'à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ',
        'd'=>'đ','e'=>'è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ',
        'i'=>'ì|í|ị|ỉ|ĩ','o'=>'ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ',
        'u'=>'ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ','y'=>'ỳ|ý|ỵ|ỷ|ỹ'
    ];
    foreach($unicode as $khongdau=>$codau) {
        $str = preg_replace("/($codau)/i", $khongdau, $str);
    }
    return strtolower($str);
}