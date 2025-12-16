<?php
// app/Views/post/My-Post.php
// Biến $posts được truyền từ Controller
if (!isset($posts)) $posts = [];
?>
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/my-post.css?v=<?= time() ?>">
<!-- Ở trên cùng (góc phải) -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h2 style="margin:0;">Tin đăng của tôi</h2>
    <a href="<?= BASE_URL ?>/" class="btn" 
       style="background:#3498db; color:white; padding:12px 25px; border-radius:8px; text-decoration:none; font-weight:600;">
       Quay lại trang chủ
    </a>
</div>

<?php if (empty($posts)): ?>
    <div style="text-align:center; padding:50px 20px; background:#f8f9fa; border-radius:8px; margin:20px 0;">
        <p style="font-size:1.2em; color:#666;">Bạn chưa đăng tin nào.</p>
        <a href="<?= BASE_URL ?>/dang-tin" class="btn" style="padding:10px 25px; font-size:1.1em;">Đăng tin mới ngay!</a>
    </div>
<?php else: ?>
    <table class="my-posts" style="width:100%; border-collapse:collapse; margin-top:20px;">
        <thead>
            <tr style="background:#007bff; color:white;">
                <th style="padding:12px;">Tiêu đề</th>
                <th style="padding:12px;">Giá</th>
                <th style="padding:12px;">Địa chỉ</th>
                <th style="padding:12px;">Ngày đăng</th>
                <th style="padding:12px; width:160px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $p): ?>
            <tr style="border-bottom:1px solid #ddd;">
                <td style="padding:12px;"><?= e($p['TieuDe']) ?></td>
                <td style="padding:12px; color:#e74c3c; font-weight:bold;">
                    <?= money($p['GiaPhong']) ?>
                </td>
                <td style="padding:12px;"><?= e($p['DiaChi']) ?></td>
                <td style="padding:12px;">
                    <?= date('d/m/Y', strtotime($p['NgayDang'])) ?>
                </td>
                <td style="padding:12px; text-align:center;">
                    <a href="<?= BASE_URL ?>/sua-tin/<?= $p['PostID'] ?>" 
                       class="btn" style="padding:6px 12px; font-size:0.9em;">Cập nhật</a>
                    <a href="<?= BASE_URL ?>/xoa-tin/<?= $p['PostID'] ?>" 
                       class="btn btn-delete" 
                       style="padding:6px 12px; font-size:0.9em;"
                       onclick="return confirm('Xóa vĩnh viễn tin này?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>