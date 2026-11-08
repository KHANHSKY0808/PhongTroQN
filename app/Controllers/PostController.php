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

        // 1. Validate dữ liệu đầu vào bằng Validator tập trung
        $validator = Validator::make($_POST, [
            'title'      => 'required|min_len:10|max_len:255',
            'diachi'     => 'required|min_len:5|max_len:255',
            'gia'        => 'required|numeric|min:100000|max:100000000',
            'dientich'   => 'required|numeric|min:5|max:500',
            'mota'       => 'max_len:5000',
            'latitude'   => 'latitude',
            'longitude'  => 'longitude',
            'video_link' => 'video_url',
        ]);

        if ($validator->fails()) {
            setFlash('error', $validator->firstError());
            redirect('/dang-tin');
            return;
        }

        $post = new Post();

        // Tạo bài đăng
        $postID = $post->create([
            'UserID'    => $userID,
            'TieuDe'    => trim($_POST['title']),
            'MoTa'      => trim($_POST['mota'] ?? ""),
            'DiaChi'    => trim($_POST['diachi']),
            'DienTich'  => (float)$_POST['dientich'],
            'GiaPhong'  => (float)$_POST['gia'],
            'Latitude'  => !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null,
            'Longitude' => !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null
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
            $post->updateVideo($postID, trim($_POST['video_link']));
        }

        // Thông báo thành công và chuyển về trang chủ
        setFlash('success', 'Đăng tin thành công! Tin của bạn đã hiển thị.');
        redirect('/');
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
        setFlash('error', "Dữ liệu không hợp lệ.");
        redirect('/tin-cua-toi');
        exit;
    }

    $postID = (int)$_POST['PostID'];
    $userID = Auth::id();

    $postModel = new Post();
    $post = $postModel->getPostById($postID);

    if (!$post) {
        setFlash('error', "Tin đăng không tồn tại!");
        redirect('/tin-cua-toi');
        exit;
    }

    // Chống IDOR: Chỉ chủ tin hoặc Admin mới được sửa
    if ($post['UserID'] != $userID && !Auth::isAdmin()) {
        setFlash('error', "Bạn không có quyền sửa tin này!");
        redirect('/tin-cua-toi');
        exit;
    }

    // Validate dữ liệu cập nhật
    $validator = Validator::make($_POST, [
        'TieuDe'   => 'required|min_len:10|max_len:255',
        'DiaChi'   => 'required|min_len:5|max_len:255',
        'GiaPhong' => 'required|numeric|min:100000|max:100000000',
        'DienTich' => 'required|numeric|min:5|max:500',
        'MoTa'     => 'max_len:5000',
    ]);

    if ($validator->fails()) {
        setFlash('error', $validator->firstError());
        redirect('/sua-tin/' . $postID);
        exit;
    }

    $postModel->updatePost([
        'PostID'   => $postID,
        'TieuDe'   => trim($_POST['TieuDe']),
        'MoTa'     => trim($_POST['MoTa'] ?? ''),
        'DiaChi'   => trim($_POST['DiaChi']),
        'DienTich' => (float)$_POST['DienTich'],
        'GiaPhong' => (float)$_POST['GiaPhong']
    ]);

    setFlash('success', "Cập nhật tin thành công!");
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
            Logger::error("Lỗi gửi báo cáo: " . $e->getMessage(), ['post_id' => $postID, 'user_id' => $userID]);
            http_response_code(500);
            echo 'Đã xảy ra lỗi khi gửi báo cáo. Vui lòng thử lại sau!';
        }
        exit;
    }
}
