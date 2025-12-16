<h2>Kết quả lọc</h2>

<?php if (empty($posts)): ?>
    <p>Không tìm thấy bài đăng phù hợp.</p>
<?php else: ?>
<div class="posts-grid">
    <?php foreach ($posts as $row): ?>
        <div class="post-card">
            <div class="post-title"><?= htmlspecialchars($row['TieuDe']) ?></div>
            <div class="post-price"><?= number_format($row['GiaPhong']) ?> đ/tháng</div>
            <div class="post-info"><?= number_format($row['DienTich']) ?> m² · <?= htmlspecialchars($row['DiaChi']) ?></div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

