<?php
require 'config.php';

if (!isset($_GET['id'])) {
    die("Không tìm thấy tin");
}

$post_id = intval($_GET['id']);
$user_id = $_SESSION['UserID'];

// Kiểm tra có quyền xóa không
$sql = "SELECT * FROM posts WHERE PostID = $post_id AND UserID = $user_id";
$post = $conn->query($sql)->fetch_assoc();

if (!$post) {
    die("Bạn không có quyền xóa tin này");
}

// Xóa tin
$delete_sql = "DELETE FROM posts WHERE PostID = $post_id AND UserID = $user_id";

if ($conn->query($delete_sql)) {
    header("Location: my-posts.php?deleted=1");
    exit();
} else {
    echo "Lỗi khi xóa: " . $conn->error;
}
