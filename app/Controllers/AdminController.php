<?php
class AdminController
{
    /* ==================================================================
     * 1. TRANG CHỦ ADMIN (Tổng quan) – ĐÃ CẬP NHẬT THEỐNG KÊ VÀ DỮ LIỆU MỚI
     * ================================================================== */
    public function index()
    {
        // Kiểm tra đăng nhập + quyền admin
        if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
            redirect('/');
            exit;
        }

        // Lấy dữ liệu thống kê và tin mới nhất
        require_once __DIR__ . '/../Models/DB.php';
        require_once __DIR__ . '/../Models/User.php';
        require_once __DIR__ . '/../Models/Post.php';

        $totalPosts     = query("SELECT COUNT(*) FROM post")->fetchColumn();
        $todayPosts     = query("SELECT COUNT(*) FROM post WHERE DATE(NgayDang) = CURDATE()")->fetchColumn();
        $totalUsers     = query("SELECT COUNT(*) FROM user WHERE IsAdmin = 0")->fetchColumn();
        // The `user` table in this project doesn't have a `created_at` column.
        // Use a safe fallback (0) here to avoid SQL errors. If you prefer,
        // add a `created_at` DATETIME column to the `user` table instead.
        $todayUsers     = 0;
        $pendingReports = query("SELECT COUNT(*) FROM baocao WHERE DaXuLy = 0")->fetchColumn();

        $stmt = query("SELECT p.PostID, p.TieuDe, u.HoTen, p.NgayDang
                       FROM post p
                       JOIN user u ON p.UserID = u.UserID
                       ORDER BY p.NgayDang DESC LIMIT 10");
        $recentPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Trả về view dashboard admin với dữ liệu
        return view('admin/index', compact(
            'totalPosts',
            'todayPosts',
            'totalUsers',
            'todayUsers',
            'pendingReports',
            'recentPosts'
        ));
    }


    /* ==================================================================
     * 2. QUẢN LÝ BÁO CÁO – CHỨC NĂNG CHÍNH (giữ nguyên như cũ)
     * ================================================================== */
    public function manageReports()
    {
        // ────── KIỂM TRA QUYỀN ADMIN ──────
        if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
            redirect('/');
            exit;
        }

        // ────── LẤY THAM SỐ HÀNH ĐỘNG ──────
        $action   = $_GET['action'] ?? null;   // resolve | delete | delete_user | delete_post
        $reportID = $_GET['id'] ?? null;        // ID báo cáo (dùng cho resolve/delete)

        // 1. Đánh dấu báo cáo là "Đã xử lý"
        if ($action === 'resolve' && $reportID) {
            query("UPDATE baocao SET DaXuLy = 1 WHERE BaoCaoID = ?", [$reportID]);
            redirect('/index.php?page=admin/reports&success=1');
        }

        // 2. Xóa một báo cáo riêng lẻ
        if ($action === 'delete' && $reportID) {
            query("DELETE FROM baocao WHERE BaoCaoID = ?", [$reportID]);
            redirect('/index.php?page=admin/reports&success=2');
        }

        // 3. Xóa toàn bộ người dùng (kèm bài đăng + báo cáo của họ)
        if ($action === 'delete_user') {
            $userID = $_GET['user_id'] ?? null;
            if ($userID) {
                // Bảo vệ: không cho xóa admin
                $user = query("SELECT IsAdmin FROM user WHERE UserID = ?", [$userID])->fetch(PDO::FETCH_ASSOC);
                if ($user && $user['IsAdmin'] == 1) {
                    redirect('/index.php?page=admin/reports&error=admin');
                    exit;
                }

                // Xóa liên quan trước → xóa user cuối cùng
                query("DELETE FROM baocao WHERE UserID = ?", [$userID]);   // báo cáo do user tạo
                query("DELETE FROM post   WHERE UserID = ?", [$userID]);   // bài đăng của user
                query("DELETE FROM user   WHERE UserID = ?", [$userID]);   // xóa user

                redirect('/index.php?page=admin/reports&success=3');
            }
        }

        // 4. Xóa bài đăng + toàn bộ báo cáo liên quan đến bài đăng đó
        if ($action === 'delete_post') {
            $postID = $_GET['post_id'] ?? null;
            if ($postID) {
                query("DELETE FROM baocao WHERE PostID = ?", [$postID]); // xóa báo cáo trước
                query("DELETE FROM post   WHERE PostID = ?", [$postID]); // rồi xóa bài đăng
                redirect('/index.php?page=admin/reports&success=4');
            }
        }

        // ────── LỌC & TÌM KIẾM ──────
        $filterStatus  = $_GET['status'] ?? '';   // '' | '0' | '1'  → lọc trạng thái xử lý
        $searchKeyword = $_GET['search'] ?? '';   // từ khóa tìm kiếm

        // ────── TẠO CÂU QUERY CHÍNH ──────
        $sql = "SELECT b.BaoCaoID, b.UserID, b.PostID, b.NoiDung, b.NgayBaoCao, b.DaXuLy,
                       u.HoTen as TenNguoiBaoCao, p.TieuDe, p.GiaPhong, p.DiaChi, p.NgayDang
                FROM baocao b
                JOIN user u ON b.UserID = u.UserID
                JOIN post p ON b.PostID = p.PostID
                WHERE 1=1";

        // Lọc theo trạng thái (chưa xử lý / đã xử lý)
        if ($filterStatus !== '') {
            $sql .= " AND b.DaXuLy = " . ($filterStatus == '1' ? '1' : '0');
        }

        // Tìm kiếm trong tiêu đề bài đăng, tên người báo, nội dung báo cáo
        if ($searchKeyword !== '') {
            $sql .= " AND (p.TieuDe LIKE ? OR u.HoTen LIKE ? OR b.NoiDung LIKE ?)";
            $keyword = "%$searchKeyword%";
            $reports = query($sql . " ORDER BY b.NgayBaoCao DESC", [$keyword, $keyword, $keyword])
                          ->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $reports = query($sql . " ORDER BY b.NgayBaoCao DESC")
                          ->fetchAll(PDO::FETCH_ASSOC);
        }

        // ────── THỐNG KÊ TỔNG QUAN ──────
        $totalReports    = query("SELECT COUNT(*) FROM baocao")->fetchColumn();
        $pendingReports  = query("SELECT COUNT(*) FROM baocao WHERE DaXuLy = 0")->fetchColumn();
        $resolvedReports = query("SELECT COUNT(*) FROM baocao WHERE DaXuLy = 1")->fetchColumn();

        // ────── TRUYỀN DỮ LIỆU CHO VIEW (dùng $GLOBALS vì view cũ dùng cách này) ──────
        $GLOBALS['reports']          = $reports;
        $GLOBALS['totalReports']     = $totalReports;
        $GLOBALS['pendingReports']   = $pendingReports;
        $GLOBALS['resolvedReports']  = $resolvedReports;
        $GLOBALS['filterStatus']     = $filterStatus;
        $GLOBALS['searchKeyword']    = $searchKeyword;

        // Load view quản lý báo cáo
        require __DIR__ . '/../Views/admin/manage-reports.php';
    }


    /* ==================================================================
     * 3. QUẢN LÝ NGƯỜI DÙNG (chỉ user thường)
     * ================================================================== */
    public function users()
    {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
            redirect('/');
            exit;
        }

        // ────── XỬ LÝ XÓA NGƯỜI DÙNG ──────
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $userId = (int)$_GET['id'];

            // Bảo vệ: không cho xóa chính mình hoặc admin gốc (ID = 1)
            if ($userId === $_SESSION['user_id'] || $userId === 1) {
                redirect('/index.php?page=admin/users&error=protected');
                exit;
            }

            // Xóa dữ liệu liên quan trước
            query("DELETE FROM post   WHERE UserID = ?", [$userId]);
            query("DELETE FROM baocao WHERE UserID = ?", [$userId]);
            // Chỉ xóa nếu không phải admin
            query("DELETE FROM user WHERE UserID = ? AND IsAdmin = 0", [$userId]);

            redirect('/index.php?page=admin/users&success=1');
        }

        // Lấy danh sách người dùng thường (không phải admin)
        // Order users by newest `UserID` as the table does not have `created_at`.
        $users = query("SELECT * FROM user WHERE IsAdmin = 0 ORDER BY UserID DESC")
                 ->fetchAll(PDO::FETCH_ASSOC);

        // Truyền dữ liệu cho view bằng hàm view()
        return view('admin/users', [
            'users' => $users,
            'total' => count($users)
        ]);
    }


    /* ==================================================================
     * 4. XỬ LÝ XÓA BÀI ĐĂNG QUA AJAX (chức năng mới)
     * ================================================================== */
    public function deletePost()
    {
        // Chỉ chấp nhận POST + phải là admin
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'
            || !isset($_SESSION['user_id'])
            || $_SESSION['is_admin'] != 1) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
            exit;
        }

        $id = $_POST['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit;
        }

        try {
            // 1. Xóa ảnh trong bảng hinhanh
            query("DELETE FROM hinhanh WHERE PostID = ?", [$id]);

            // 2. Xóa các báo cáo liên quan (nếu có)
            query("DELETE FROM baocao WHERE PostID = ?", [$id]);

            // 3. Xóa bài đăng chính
            $stmt = query("DELETE FROM post WHERE PostID = ?", [$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Xóa tin thành công!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Tin không tồn tại hoặc đã bị xóa'
                ]);
            }
        } catch (Exception $e) {
            // Log lỗi nếu cần: error_log($e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi hệ thống, vui lòng thử lại'
            ]);
        }
        exit;
    }


    /* ==================================================================
     * 5. LẤY CHI TIẾT BÀI ĐĂNG QUA AJAX (chức năng mới – dùng để xem modal chi tiết)
     * ================================================================== */
    public function getPostDetail()
    {
        $id = $_GET['id'] ?? 0;

        $post = query("SELECT p.*, u.HoTen FROM post p JOIN user u ON p.UserID = u.UserID WHERE p.PostID = ?", [$id])->fetch(PDO::FETCH_ASSOC);

        $images = query("SELECT URLAnh FROM hinhanh WHERE PostID = ? ORDER BY HinhID", [$id])->fetchAll(PDO::FETCH_COLUMN);

        // Thêm full URL vào mỗi hình ảnh để tránh lỗi load khi ở subfolder
        $images = array_map(function($url) {
            return UPLOADS_URL . $url;
        }, $images);

        header('Content-Type: application/json');
        echo json_encode(['success' => !!$post, 'post' => $post, 'images' => $images]);
        exit;
    }
}