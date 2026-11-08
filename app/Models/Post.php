<?php
class Post 
{
    // Lấy danh sách bài đăng đang hiển thị
    public function getActivePosts()
    {
        $sql = "
            SELECT
                p.PostID,
                p.TieuDe,
                p.MoTa,
                p.DiaChi,
                p.DienTich,
                p.GiaPhong,
                p.NgayDang,
                u.HoTen AS TenNguoiDang,
                u.SoDienThoai,
                (SELECT URLAnh
                 FROM hinhanh
                 WHERE PostID = p.PostID
                 ORDER BY HinhID ASC
                 LIMIT 1) AS AnhDaiDien
            FROM post p
            JOIN user u ON p.UserID = u.UserID
            WHERE p.TrangThai = 1
            ORDER BY p.NgayDang DESC
        ";

        $stmt = query($sql);
        return $stmt->fetchAll();
    }

    // Đếm tổng bài đăng đang hiển thị
    public function countActivePosts()
    {
        $stmt = query("SELECT COUNT(*) AS total FROM post WHERE TrangThai = 1");
        return $stmt->fetch()['total'];
    }

    // Tạo bài đăng mới
    public function create($data)
    {
        $sql = "INSERT INTO post 
                (UserID, TieuDe, MoTa, DiaChi, DienTich, GiaPhong, Latitude, Longitude, TrangThai, NgayDang)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";

        query($sql, [
            $data['UserID'],
            $data['TieuDe'],
            $data['MoTa'],
            $data['DiaChi'],
            $data['DienTich'],
            $data['GiaPhong'],
            $data['Latitude'],
            $data['Longitude']
        ]);

        // trả về ID bài đăng vừa tạo
        return DB::connect()->lastInsertId();
    }

    // Thêm ảnh vào bài đăng
    public function addImage($postID, $url)
    {
        $sql = "INSERT INTO hinhanh (PostID, URLAnh) VALUES (?, ?)";
        query($sql, [$postID, $url]);
    }

    // Cập nhật video (link hoặc upload)
    public function updateVideo($postID, $video)
    {
        $sql = "UPDATE post SET Video = ? WHERE PostID = ?";
        query($sql, [$video, $postID]);
    }
	// Lấy tất cả tin theo UserID (tin của tôi)
public function getPostsByUser($userID)
{
    $sql = "
        SELECT 
            p.*,
            (SELECT URLAnh 
             FROM hinhanh 
             WHERE PostID = p.PostID 
             ORDER BY HinhID ASC LIMIT 1) AS AnhDaiDien
        FROM post p
        WHERE p.UserID = ?
        ORDER BY p.PostID DESC
    ";

    $stmt = query($sql, [$userID]);
    return $stmt->fetchAll();
}

// Lấy chi tiết 1 tin
public function getPostById($postID)
{
    $sql = "SELECT * FROM post WHERE PostID = ?";
    $stmt = query($sql, [$postID]);
    return $stmt->fetch();
}

// Cập nhật tin đăng
public function updatePost($data)
{
    $sql = "
        UPDATE post SET
            TieuDe = ?,
            MoTa = ?,
            DiaChi = ?,
            DienTich = ?,
            GiaPhong = ?
        WHERE PostID = ?
    ";

    query($sql, [
        $data['TieuDe'],
        $data['MoTa'],
        $data['DiaChi'],
        $data['DienTich'],
        $data['GiaPhong'],
        $data['PostID']
    ]);
}

// Xóa tin đăng
public function deletePost($postID)
{
    // Xóa dữ liệu liên quan trước
    query("DELETE FROM hinhanh WHERE PostID = ?", [$postID]);
    query("DELETE FROM favorites WHERE PostID = ?", [$postID]);
    query("DELETE FROM baocao WHERE PostID = ?", [$postID]);

    // Xóa bài đăng chính
    query("DELETE FROM post WHERE PostID = ?", [$postID]);
}

    /**
     * Xây dựng mệnh đề WHERE và tham số truy vấn lọc tin
     */
    private function buildFilterConditions(array $filters): array
    {
        $conditions = ["p.TrangThai = 1"];
        $params = [];

        // 1. Lọc theo Loại phòng
        if (!empty($filters['loai']) && $filters['loai'] !== 'Tất cả') {
            $loai = trim($filters['loai']);
            $conditions[] = "(p.TieuDe LIKE ? OR p.MoTa LIKE ?)";
            $params[] = "%{$loai}%";
            $params[] = "%{$loai}%";
        }

        // 2. Lọc theo Giá phòng (min-max)
        if (!empty($filters['gia'])) {
            $parts = explode('-', $filters['gia']);
            $minGia = floatval($parts[0] ?? 0);
            $maxGia = floatval($parts[1] ?? 999999999);
            $conditions[] = "p.GiaPhong >= ? AND p.GiaPhong <= ?";
            $params[] = $minGia;
            $params[] = $maxGia;
        }

        // 3. Lọc theo Diện tích (min-max)
        if (!empty($filters['dientich'])) {
            $parts = explode('-', $filters['dientich']);
            $minDt = floatval($parts[0] ?? 0);
            $maxDt = floatval($parts[1] ?? 9999);
            if ($maxDt >= 999) {
                $maxDt = 99999;
            }
            $conditions[] = "p.DienTich >= ? AND p.DienTich <= ?";
            $params[] = $minDt;
            $params[] = $maxDt;
        }

        // 4. Lọc theo Đặc điểm nổi bật
        if (!empty($filters['dactiem'])) {
            $tags = array_filter(array_map('trim', explode(',', $filters['dactiem'])));
            if (!empty($tags)) {
                $tagClauses = [];
                foreach ($tags as $tag) {
                    $tagClauses[] = "p.MoTa LIKE ?";
                    $params[] = "%{$tag}%";
                }
                $conditions[] = "(" . implode(" OR ", $tagClauses) . ")";
            }
        }

        // 5. Lọc theo Tỉnh/Thành phố & Quận/Huyện
        $tinh = trim($filters['tinh'] ?? '');
        $huyen = trim($filters['huyen'] ?? '');

        if (!empty($huyen)) {
            $huyenClean = trim(str_ireplace(['tp. ', 'thành phố ', 'huyện ', 'thị xã ', 'quận '], '', $huyen));
            $conditions[] = "p.DiaChi LIKE ?";
            $params[] = "%{$huyenClean}%";
        } elseif (!empty($tinh) && $tinh !== 'binhdinh') {
            $tinhMap = [
                'hcm' => 'Hồ Chí Minh', 'hn' => 'Hà Nội', 'danang' => 'Đà Nẵng',
                'binhduong' => 'Bình Dương', 'camaau' => 'Cà Mau', 'cantho' => 'Cần Thơ'
            ];
            $tinhSearch = $tinhMap[$tinh] ?? $tinh;
            $conditions[] = "p.DiaChi LIKE ?";
            $params[] = "%{$tinhSearch}%";
        }

        return [$conditions, $params];
    }

    /**
     * Lấy danh sách tin bài theo bộ lọc SQL kèm phân trang
     */
    public function getFilteredPosts(array $filters = [], int $limit = 12, int $offset = 0): array
    {
        [$conditions, $params] = $this->buildFilterConditions($filters);

        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);

        $whereClause = implode(" AND ", $conditions);

        $sql = "
            SELECT 
                p.PostID,
                p.TieuDe,
                p.MoTa,
                p.DiaChi,
                p.DienTich,
                p.GiaPhong,
                p.Latitude,
                p.Longitude,
                p.NgayDang,
                u.HoTen AS TenNguoiDang,
                u.SoDienThoai,
                (SELECT URLAnh FROM hinhanh WHERE PostID = p.PostID ORDER BY HinhID ASC LIMIT 1) AS AnhDaiDien,
                GROUP_CONCAT(h.URLAnh ORDER BY h.HinhID) AS danh_sach_anh
            FROM post p
            JOIN user u ON p.UserID = u.UserID
            LEFT JOIN hinhanh h ON p.PostID = h.PostID
            WHERE {$whereClause}
            GROUP BY p.PostID
            ORDER BY p.NgayDang DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        $stmt = query($sql, $params);
        $results = $stmt->fetchAll();

        foreach ($results as &$post) {
            if (!empty($post['danh_sach_anh'])) {
                $urls = explode(',', $post['danh_sach_anh']);
                $post['images'] = array_map(fn($url) => ['URLAnh' => trim($url)], $urls);
            } else {
                $post['images'] = [];
            }
            unset($post['danh_sach_anh']);
        }

        return $results;
    }

    /**
     * Đếm tổng số bài đăng khớp với bộ lọc SQL
     */
    public function countFilteredPosts(array $filters = []): int
    {
        [$conditions, $params] = $this->buildFilterConditions($filters);
        $whereClause = implode(" AND ", $conditions);

        $sql = "SELECT COUNT(DISTINCT p.PostID) as total FROM post p JOIN user u ON p.UserID = u.UserID WHERE {$whereClause}";
        $stmt = query($sql, $params);
        $row = $stmt->fetch();

        return (int)($row['total'] ?? 0);
    }

    public function getActivePostsWithDetails()
    {
        $sql = "
            SELECT 
                p.PostID,
                p.TieuDe,
                p.MoTa,
                p.DiaChi,
                p.DienTich,
                p.GiaPhong,
                p.Latitude,
                p.Longitude,
                p.NgayDang,
                u.HoTen AS TenNguoiDang,
                u.SoDienThoai,
                (SELECT URLAnh FROM hinhanh WHERE PostID = p.PostID ORDER BY HinhID ASC LIMIT 1) AS AnhDaiDien,
                GROUP_CONCAT(h.URLAnh ORDER BY h.HinhID) AS danh_sach_anh
            FROM post p
            JOIN user u ON p.UserID = u.UserID
            LEFT JOIN hinhanh h ON p.PostID = h.PostID
            WHERE p.TrangThai = 1
            GROUP BY p.PostID
            ORDER BY p.NgayDang DESC
        ";

        $stmt = query($sql);
        $results = $stmt->fetchAll();

        // Chuyển danh_sach_anh thành mảng images cho JS dùng
        foreach ($results as &$post) {
            if ($post['danh_sach_anh']) {
                $urls = explode(',', $post['danh_sach_anh']);
                $post['images'] = array_map(fn($url) => ['URLAnh' => trim($url)], $urls);
            } else {
                $post['images'] = [];
            }
            unset($post['danh_sach_anh']);
        }

        return $results;
    }  
}
