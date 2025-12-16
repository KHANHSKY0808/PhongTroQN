<?php
class PostController
{
    // Hiển thị form đăng tin
    public function create()
    {
        view("post/dangtin");
    }

    // Xử lý lưu bài đăng
    public function store()
    {
        if (!isset($_POST['dangtin'])) {
            redirect('/dang-tin');
        }

        // Lấy userID hiện tại (ví dụ từ session)
        session_start();
        if (!isset($_SESSION['user'])) {
            redirect('/dang-nhap');
        }

        $userID = $_SESSION['user']['UserID'];

        $post = new Post();

        // Tạo bài đăng
        $postID = $post->create([
            'UserID'    => $userID,
            'TieuDe'    => $_POST['title'],
            'MoTa'      => $_POST['mota'] ?? "",
            'DiaChi'    => $_POST['diachi'],
            'DienTich'  => $_POST['dientich'],
            'GiaPhong'  => $_POST['gia'],
            'Latitude'  => !empty($_POST['latitude']) ? $_POST['latitude'] : null,
            'Longitude' => !empty($_POST['longitude']) ? $_POST['longitude'] : null
        ]);

        // ===== Upload ảnh =====
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $i => $name) {
                if ($_FILES['images']['error'][$i] === 0) {
                    $file = [
                        'name'     => $_FILES['images']['name'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error'    => 0
                    ];
                    $url = upload($file, 'posts');

                    // Lưu ảnh vào DB
                    $post->addImage($postID, $url);
                }
            }
        }

        // ===== Upload video =====
        if (!empty($_FILES['video']['tmp_name'])) {
            if ($_FILES['video']['error'] === 0) {
                $videoUrl = upload($_FILES['video'], 'videos');

                $post->updateVideo($postID, $videoUrl);
            }
        }

        // ===== Video link (Youtube/TikTok) =====
        if (!empty($_POST['video_link'])) {
            $post->updateVideo($postID, $_POST['video_link']);
        }

        // Hiển thị thông báo thành công và quay về trang chủ
        echo "<script>alert('Đăng tin thành công!'); window.location.href='" . BASE_URL . "/';</script>";
        exit;
    }
	public function myPosts()
{
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['UserID'])) {
        redirect('/dang-nhap');
    }

    $userId = $_SESSION['user']['UserID'];

    $postModel = new Post();
    $posts = $postModel->getPostsByUser($userId);  // hàm này phải trả về mảng các tin

    // GỌI VIEW VỚI TÊN FILE BẠN ĐANG DÙNG: My-Post.php (viết hoa)
    view('post/My-Post', [
        'title' => 'Tin đăng của tôi',
        'posts' => $posts
    ]);
}

public function edit($id)
{
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user'])) {
        redirect('/dang-nhap');
    }

    $postModel = new Post();
    $post = $postModel->getPostById($id);  // hoặc hàm lấy chi tiết tin

    if (!$post || $post['UserID'] != $_SESSION['user']['UserID']) {
        die("Tin đăng không tồn tại hoặc bạn không có quyền sửa.");
    }

    // TRUYỀN BIẾN $post CHO VIEW
    view('post/Edit-Post', ['post' => $post]);
}

// ==================== CẬP NHẬT TIN  ====================
public function update()
{
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['UserID'])) {
        redirect('/dang-nhap');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['PostID'])) {
        die("Dữ liệu không hợp lệ.");
    }

    $postID  = (int)$_POST['PostID'];
    $userID  = $_SESSION['user']['UserID'];

    // Kiểm tra quyền sở hữu trước khi cập nhật
    global $conn;
    $check = $conn->prepare("SELECT PostID FROM post WHERE PostID = ? AND UserID = ?");
    $check->bind_param("ii", $postID, $userID);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $check->close();
        die("Bạn không có quyền sửa tin này!");
    }
    $check->close();

    // Cập nhật dữ liệu thật
    $sql = "UPDATE post SET 
            TieuDe = ?, 
            MoTa = ?, 
            DiaChi = ?, 
            DienTich = ?, 
            GiaPhong = ? 
            WHERE PostID = ? AND UserID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssdii",
        $_POST['TieuDe'],
        $_POST['MoTa'],
        $_POST['DiaChi'],
        $_POST['DienTich'],
        $_POST['GiaPhong'],
        $postID,
        $userID
    );

    if ($stmt->execute()) {
        redirect('/tin-cua-toi?updated=1');
    } else {
        die("Cập nhật thất bại: " . $conn->error);
    }
    $stmt->close();
}

// ==================== XÓA TIN ====================
public function delete($id)
{
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['UserID'])) {
        redirect('/dang-nhap');
        exit;
    }

    $postID = (int)$id;
    $userID = $_SESSION['user']['UserID'];   // ← SỬA DÒNG NÀY

    global $conn;

    // Kiểm tra quyền + Xóa luôn bằng 1 câu lệnh (an toàn + nhanh)
    $sql = "DELETE FROM post WHERE PostID = ? AND UserID = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $postID, $userID);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        // Xóa luôn ảnh trong bảng hinhanh (nếu có)
        $conn->query("DELETE FROM hinhanh WHERE PostID = $postID");
        redirect('/tin-cua-toi?deleted=1');
    } else {
        die("Không thể xóa tin này (có thể không phải của bạn).");
    }
    $stmt->close();
    }

    // ==================== BÁO CÁO TIN ĐĂNG ====================
    public function report()
    {
        // Khởi động session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Kiểm tra POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo 'error: POST required';
            exit;
        }

        // Kiểm tra đã đăng nhập
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo 'error: Must login';
            exit;
        }

        $postID = intval($_POST['postID'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        // Validate
        if ($postID <= 0 || empty($reason)) {
            http_response_code(400);
            echo 'error: Invalid data';
            exit;
        }

        // Kiểm tra tin có tồn tại không
        $checkStmt = query("SELECT PostID FROM post WHERE PostID = ?", [$postID]);
        if ($checkStmt->rowCount() === 0) {
            http_response_code(404);
            echo 'error: Post not found';
            exit;
        }

        // Lưu báo cáo vào database
        try {
            query(
                "INSERT INTO baocao (UserID, PostID, NoiDung, DaXuLy) VALUES (?, ?, ?, 0)",
                [$_SESSION['user_id'], $postID, $reason]
            );
            // Lưu message vào session
            $_SESSION['success_message'] = 'Báo cáo đã được gửi tới admin. Cảm ơn bạn!';
            // Redirect về trang chủ
            redirect('/');
        } catch (Exception $e) {
            http_response_code(500);
            echo 'error: ' . $e->getMessage();
        }
        exit;
    }
}
