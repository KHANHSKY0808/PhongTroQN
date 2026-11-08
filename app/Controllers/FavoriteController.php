<?php
// app/Controllers/FavoriteController.php
class FavoriteController {
    private $favoriteModel;

    public function __construct() {
        $this->favoriteModel = new Favorite();
    }

    // AJAX: Lưu / bỏ lưu tin
    public function toggle() {
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            exit;
        }

        $postID = (int)($_POST['post_id'] ?? 0);
        if (!$postID) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID tin']);
            exit;
        }

        $result = $this->favoriteModel->toggle(Auth::id(), $postID);
        echo json_encode($result);
        exit;
    }

    // Trang danh sách tin đã lưu
    public function index() {
        Auth::requireLogin('/dang-nhap');

        $userID = Auth::id();

        // Phân trang
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $favorites = $this->favoriteModel->getUserFavorites($userID, $limit, $offset);
        $total = $this->favoriteModel->countUserFavorites($userID);
        $totalPages = ceil($total / $limit);

        view('user/saved-posts', [  
            'data' => [
                'favorites' => $favorites,
                'total' => $total,
                'page' => $page,
                'totalPages' => $totalPages,
                'limit' => $limit
            ]
        ]);
    }
}