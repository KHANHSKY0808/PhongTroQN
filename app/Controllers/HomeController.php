<?php
// app/Controllers/HomeController.php - Trang chủ và Bộ lọc bài đăng tối ưu hóa bằng SQL

class HomeController
{
    private Post $postModel;
    private Favorite $favoriteModel;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->favoriteModel = new Favorite();
    }

    public function index()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $posts = $this->postModel->getFilteredPosts([], $limit, $offset);
        $totalPosts = $this->postModel->countFilteredPosts([]);
        $totalPages = (int)ceil($totalPosts / $limit);

        // Gắn trạng thái yêu thích
        $this->attachFavorites($posts);

        return view('home/index', [
            'posts'       => $posts,
            'totalPosts'  => $totalPosts,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'limit'       => $limit,
            'filters'     => []
        ]);
    }

    public function filter()
    {
        $filters = [
            'loai'     => trim($_GET['loai'] ?? ''),
            'gia'      => trim($_GET['gia'] ?? ''),
            'dientich' => trim($_GET['dientich'] ?? ''),
            'dactiem'  => trim($_GET['dactiem'] ?? ''),
            'tinh'     => trim($_GET['tinh'] ?? ''),
            'huyen'    => trim($_GET['huyen'] ?? ''),
        ];

        // Nếu không có bất kỳ tiêu chí nào được chọn → về trang chủ
        if (empty($filters['gia']) && empty($filters['loai']) && empty($filters['dientich']) &&
            empty($filters['dactiem']) && empty($filters['tinh']) && empty($filters['huyen'])) {
            return $this->index();
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        // Truy vấn lọc trực tiếp bằng SQL (kèm Index và Prepared Statements)
        $filteredPosts = $this->postModel->getFilteredPosts($filters, $limit, $offset);
        $totalPosts = $this->postModel->countFilteredPosts($filters);
        $totalPages = (int)ceil($totalPosts / $limit);

        // Gắn trạng thái yêu thích
        $this->attachFavorites($filteredPosts);

        $title = $totalPosts > 0
            ? "Tìm thấy " . number_format($totalPosts) . " tin phòng trọ phù hợp"
            : "Không tìm thấy tin nào phù hợp";

        return view('home/index', [
            'title'       => $title,
            'posts'       => $filteredPosts,
            'totalPosts'  => $totalPosts,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'limit'       => $limit,
            'filters'     => $filters
        ]);
    }

    /**
     * Gắn cờ is_favorited cho danh sách bài đăng
     */
    private function attachFavorites(array &$posts): void
    {
        $isLoggedIn = Auth::check();
        $userID = $isLoggedIn ? Auth::id() : null;

        foreach ($posts as &$p) {
            $p['is_favorited'] = ($isLoggedIn && $userID)
                ? $this->favoriteModel->isFavorited($userID, (int)$p['PostID'])
                : false;
        }
        unset($p);
    }
}
