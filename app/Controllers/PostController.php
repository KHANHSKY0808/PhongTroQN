<?php
class PostController
{
    // Hiển thị form đăng tin
    public function create()
    {
        Auth::requireLogin();
        view("post/dangtin");
    }

    // Xử lý lưu bài đăng
    public function store()
    {
        Auth::requireLogin();

        if (!isset($_POST['dangtin'])) {
            redirect('/dang-tin');
        }

        $userID = Auth::id();

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
                if (isset($_FILES['images']['error'][$i]) && $_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name'     => $_FILES['images']['name'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error'    => $_FILES['images']['error'][$i],
                        'size'     => $_FILES['images']['size'][$i] ?? 0
                    ];
                    $url = upload($file, 'posts');

                    // Chỉ lưu ảnh vào DB nếu upload thành công và hợp lệ
                    if ($url) {
                        $post->addImage($postID, $url);
                    }
                }
            }
        }

        // ===== Upload video =====
        if (!empty($_FILES['video']['tmp_name'])) {
            if (isset($_FILES['video']['error']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
                $videoUrl = upload($_FILES['video'], 'videos');
                if ($videoUrl) {
                    $post->updateVideo($postID, $videoUrl);
                }
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
    Auth::requireLogin();
    $userId = Auth::id();

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
    Auth::requireLogin();
    $userID = Auth::id();

    $postModel = new Post();
    $post = $postModel->getPostById($id);

    // Cho phép chủ tin hoặc Admin được sửa
    if (!$post || ($post['UserID'] != $userID && !Auth::isAdmin())) {
        $_SESSION['error'] = "Tin đăng không tồn tại hoặc bạn không có quyền sửa.";
        redirect('/tin-cua-toi');
        exit;
    }

    // TRUYỀN BIẾN $post CHO VIEW
    view('post/Edit-Post', ['post' => $post]);
}

// ==================== CẬP NHẬT TIN  ====================
public function update()
{
    Auth::requireLogin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['PostID'])) {
        $_SESSION['error'] = "Dữ liệu không hợp lệ.";
        redirect('/tin-cua-toi');
        exit;
    }

    $postID = (int)$_POST['PostID'];
    $userID = Auth::id();

    $postModel = new Post();
    $post = $postModel->getPostById($postID);

    if (!$post) {
        $_SESSION['error'] = "Tin đăng không tồn tại!";
        redirect('/tin-cua-toi');
        exit;
    }

    // Chống IDOR: Chỉ chủ tin hoặc Admin mới được sửa
    if ($post['UserID'] != $userID && !Auth::isAdmin()) {
        $_SESSION['error'] = "Bạn không có quyền sửa tin này!";
        redirect('/tin-cua-toi');
        exit;
    }

    $postModel->updatePost([
        'PostID'   => $postID,
        'TieuDe'   => trim($_POST['TieuDe'] ?? ''),
        'MoTa'     => trim($_POST['MoTa'] ?? ''),
        'DiaChi'   => trim($_POST['DiaChi'] ?? ''),
        'DienTich' => floatval($_POST['DienTich'] ?? 0),
        'GiaPhong' => floatval($_POST['GiaPhong'] ?? 0)
    ]);

    $_SESSION['success'] = "Cập nhật tin thành công!";
    redirect('/tin-cua-toi?updated=1');
}

// ==================== XÓA TIN ====================
public function delete($id)
{
    Auth::requireLogin();

    $postID = (int)$id;
    $userID = Auth::id();

    $postModel = new Post();
    $post = $postModel->getPostById($postID);

    if (!$post) {
        $_SESSION['error'] = "Tin đăng không tồn tại!";
        redirect('/tin-cua-toi');
        exit;
    }

    // Chống IDOR: Chỉ chủ tin hoặc Admin mới được xóa
    if ($post['UserID'] != $userID && !Auth::isAdmin()) {
        $_SESSION['error'] = "Bạn không có quyền xóa tin này!";
        redirect('/tin-cua-toi');
        exit;
    }

    // Xóa an toàn thông qua Post Model
    $postModel->deletePost($postID);

    $_SESSION['success'] = "Xóa tin thành công!";
    redirect('/tin-cua-toi?deleted=1');
}

    // ==================== BÁO CÁO TIN ĐĂNG ====================
    public function report()
    {
        Auth::requireLogin();

        // Kiểm tra POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo 'error: POST required';
            exit;
        }

        $userID = Auth::id();

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
                [$userID, $postID, $reason]
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
