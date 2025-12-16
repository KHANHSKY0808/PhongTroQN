<?php
// Kết nối database
$conn = new PDO("mysql:host=localhost;dbname=phongtroqn;charset=utf8", "root", "");

// Khi bấm nút đăng tin
if(isset($_POST['dangtin'])){
    // Lấy dữ liệu chính từ form
    $title      = $_POST['title'];
    $mota       = $_POST['mota'];
    $gia        = $_POST['gia'];
    $dientich   = $_POST['dientich'];
    $hoten      = $_POST['hoten'];
    $sdt        = $_POST['sdt'];
    $video_link = $_POST['video_link'];

    // Insert bài đăng trước
    $sql = "INSERT INTO baidang (title, mota, gia, dientich, hoten, sdt, video_link) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$title, $mota, $gia, $dientich, $hoten, $sdt, $video_link]);

    $post_id = $conn->lastInsertId(); // Lấy ID bài đăng vừa tạo

    // UPLOAD ẢNH
    $targetDir = "uploads/images/";
    foreach($_FILES['images']['name'] as $key => $name){
        if($_FILES['images']['error'][$key] == 0){
            $newName = time() . "_" . $name;
            move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetDir.$newName);

            $stm = $conn->prepare("INSERT INTO hinhanh (post_id, file_name) VALUES (?, ?)");
            $stm->execute([$post_id, $newName]);
        }
    }

    // UPLOAD VIDEO THIẾT BỊ
    if(!empty($_FILES['video']['name'])){
        $videoName = time() . "_" . $_FILES['video']['name'];
        move_uploaded_file($_FILES['video']['tmp_name'], "uploads/videos/".$videoName);

        $stm = $conn->prepare("UPDATE baidang SET video_upload=? WHERE id=?");
        $stm->execute([$videoName, $post_id]);
    }

    echo "<script>alert('Đăng tin thành công!');</script>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng tin cho thuê</title>
<link rel="stylesheet" href="public/assets/css/dangtin.css">
<style>
body { font-family: Arial; background:#f7f8fa; padding:20px; }

.card { background:#fff; padding:20px; margin-bottom:20px; border-radius:8px; border:1px solid #ddd; }

.upload-box{
    border:2px dashed #bcdfff;
    background:#eef6ff;
    padding:30px; text-align:center;
    cursor:pointer; border-radius:10px;
    position:relative;
}
.upload-box input{ opacity:0; position:absolute; width:100%; height:100%; cursor:pointer; }

.upload-icon i { font-size:40px; color:#007bff; }

.image-preview img{
    width:120px; height:120px;
    object-fit:cover; margin:6px;
    border-radius:8px; border:1px solid #ccc;
}

.form-control{
    width:100%; padding:8px; margin-top:6px;
    border:1px solid #ccc; border-radius:6px;
}

button{
    background:#007bff; color:#fff;
    padding:10px 18px; border:none;
    border-radius:6px; cursor:pointer;
}
</style>

<script>
function previewImages(event) {
    let files = event.target.files;
    let preview = document.getElementById("previewImages");
    preview.innerHTML = "";
    for (let i = 0; i < files.length; i++) {
        let img = document.createElement("img");
        img.src = URL.createObjectURL(files[i]);
        preview.appendChild(img);
    }
}
</script>

</head>
<body>

<form method="POST" enctype="multipart/form-data">

<!-- ========================== HÌNH ẢNH ========================== -->
<div class="card">
    <h3>Hình ảnh</h3>
    <div class="upload-box">
        <input type="file" name="images[]" multiple accept="image/*" onchange="previewImages(event)">
        <div class="upload-icon">
            <i>📷</i><br>
            <p>Tải ảnh từ thiết bị</p>
        </div>
    </div>
    <div id="previewImages" class="image-preview"></div>
</div>

<!-- ========================== VIDEO ========================== -->
<div class="card">
    <h3>Video</h3>

    <label>Video Link (Youtube/Tiktok)</label>
    <input type="text" name="video_link" class="form-control" placeholder="https://youtu.be/xxxxxx">

    <p><b>Hoặc tải video từ thiết bị:</b></p>
    <div class="upload-box">
        <input type="file" name="video" accept="video/*">
        <div class="upload-icon">
            <i>🎥</i><br>
            <p>Tải video từ thiết bị</p>
        </div>
    </div>
</div>

<!-- ========================== THÔNG TIN LIÊN HỆ ========================== -->
<div class="card">
    <h3>Thông tin liên hệ</h3>

    <label>Họ Tên</label>
    <input type="text" name="hoten" class="form-control" required>

    <label>Số điện thoại</label>
    <input type="text" name="sdt" class="form-control" required>
</div>

<!-- ========================== MÔ TẢ ========================== -->
<div class="card">
    <h3>Thông tin mô tả</h3>

    <label>Tiêu đề (*)</label>
    <input type="text" name="title" class="form-control" required>

    <label>Nội dung mô tả</label>
    <textarea name="mota" class="form-control" rows="5"></textarea>

    <label>Giá cho thuê</label>
    <input type="number" name="gia" class="form-control" required>

    <label>Diện tích</label>
    <input type="number" name="dientich" class="form-control" required>
</div>

<button type="submit" name="dangtin">Đăng tin</button>

</form>
</body>
</html>
