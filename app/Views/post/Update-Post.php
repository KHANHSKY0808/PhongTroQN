<?php
session_start();
require 'config.php';

$user_id = $_SESSION['UserID'];

$id = $_POST['PostID'];
$TieuDe = $_POST['TieuDe'];
$MoTa = $_POST['MoTa'];
$DiaChi = $_POST['DiaChi'];
$DienTich = $_POST['DienTich'];
$GiaPhong = $_POST['GiaPhong'];

$sql = "UPDATE posts SET 
            TieuDe = '$TieuDe',
            MoTa = '$MoTa',
            DiaChi = '$DiaChi',
            DienTich = '$DienTich',
            GiaPhong = '$GiaPhong'
        WHERE PostID = $id AND UserID = $user_id";

if ($conn->query($sql)) {
    header("Location: My-Post.php?success=1");
} else {
    echo "Lỗi: " . $conn->error;
}
