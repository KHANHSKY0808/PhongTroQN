<?php
// app/Views/post/Edit-Post.php
// $post được truyền từ PostController::edit
if (empty($post)) {
    redirect('/tin-cua-toi');
    exit;
}
?>
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/edit-post.css?v=<?= time() ?>">
<h2>Cập nhật tin đăng</h2>

<?php if ($errorMsg = getFlash('error')): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 12px 18px; border-radius: 6px; margin: 15px 0; border: 1px solid #f5c6cb; font-weight: 500;">
        ⚠️ <?= htmlspecialchars($errorMsg) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/cap-nhat-tin" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="PostID" value="<?= $post['PostID'] ?>">

    <div class="form-group">
        <label>Tiêu đề:</label>
        <input type="text" name="TieuDe" value="<?= htmlspecialchars($post['TieuDe']) ?>" required>
    </div>

    <div class="form-group">
        <label>Mô tả:</label>
        <textarea name="MoTa" rows="5"><?= htmlspecialchars($post['MoTa']) ?></textarea>
    </div>

    <div class="form-group">
        <label>Địa chỉ:</label>
        <input type="text" name="DiaChi" value="<?= htmlspecialchars($post['DiaChi']) ?>" required>
    </div>

    <div class="form-group">
        <label>Diện tích (m²):</label>
        <input type="number" step="0.01" name="DienTich" value="<?= $post['DienTich'] ?>">
    </div>

    <div class="form-group">
        <label>Giá phòng (VNĐ):</label>
        <input type="number" name="GiaPhong" value="<?= $post['GiaPhong'] ?>" required>
    </div>

    <button type="submit" class="btn">Cập nhật tin</button>
    <a href="<?= BASE_URL ?>/tin-cua-toi" class="btn">Quay lại</a>
</form>