<?php
// app/Models/Favorite.php
class Favorite {
    public function toggle($userID, $postID) {
        // Kiểm tra đã lưu chưa
        $stmt = query("SELECT * FROM favorites WHERE UserID = ? AND PostID = ?", [$userID, $postID]);
        $check = $stmt->fetch();
        if ($check) {
            // Đã lưu → xóa
            $this->remove($userID, $postID);
            return ['action' => 'removed', 'success' => true];
        } else {
            // Chưa lưu → thêm
            $this->add($userID, $postID);
            return ['action' => 'added', 'success' => true];
        }
    }

    public function add($userID, $postID) {
        query("INSERT INTO favorites (UserID, PostID, NgayLuu) VALUES (?, ?, NOW())", [$userID, $postID]);
    }

    public function remove($userID, $postID) {
        query("DELETE FROM favorites WHERE UserID = ? AND PostID = ?", [$userID, $postID]);
    }

    public function isFavorited($userID, $postID) {
        $stmt = query("SELECT 1 FROM favorites WHERE UserID = ? AND PostID = ?", [$userID, $postID]);
        return $stmt->fetch() !== false;
    }

    public function getUserFavorites($userID, $limit = 20, $offset = 0) {
        // Cast để đảm bảo LIMIT/OFFSET là int
        $limit = (int)$limit;
        $offset = (int)$offset;
        // Hardcode LIMIT/OFFSET trong SQL vì PDO không cho phép placeholder cho chúng
        $sql = "
            SELECT p.*, 
                   u.HoTen AS TenNguoiDang, u.SoDienThoai,
                   (SELECT URLAnh FROM hinhanh WHERE PostID = p.PostID LIMIT 1) AS AnhDaiDien
            FROM post p
            JOIN user u ON p.UserID = u.UserID
            JOIN favorites f ON p.PostID = f.PostID
            WHERE f.UserID = ? AND p.TrangThai = 1
            ORDER BY f.NgayLuu DESC
            LIMIT $limit OFFSET $offset
        ";
        $stmt = query($sql, [$userID]);
        $rows = $stmt->fetchAll();
        // Đảm bảo mỗi post có AnhDaiDien và cấu trúc giống Post model
        foreach ($rows as &$r) {
            $r['AnhDaiDien'] = $r['AnhDaiDien'] ?? null;
        }
        unset($r);
        return $rows;
    }

    public function countUserFavorites($userID) {
        $stmt = query("SELECT COUNT(*) AS cnt FROM favorites f JOIN post p ON f.PostID = p.PostID WHERE f.UserID = ? AND p.TrangThai = 1", [$userID]);
        $row = $stmt->fetch();
        return isset($row['cnt']) ? (int)$row['cnt'] : 0;
    }
}