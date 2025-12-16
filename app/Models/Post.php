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
    // Xóa ảnh trước
    query("DELETE FROM hinhanh WHERE PostID = ?", [$postID]);

    // Xóa video
    query("UPDATE post SET Video = NULL WHERE PostID = ?", [$postID]);

    // Xóa bài đăng
    query("DELETE FROM post WHERE PostID = ?", [$postID]);
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
