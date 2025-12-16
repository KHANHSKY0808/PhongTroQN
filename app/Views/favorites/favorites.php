<?php $title = "Tin đã lưu"; require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container" style="margin-top:30px; min-height:70vh;">
    <h2>Tin đã lưu (<?= $total ?> tin)</h2>

    <?php if (empty($posts)): ?>
        <p style="text-align:center; padding:100px 0; color:#888;">
            Bạn chưa lưu tin nào. <a href="<?= BASE_URL ?>">Quay lại trang chủ</a>
        </p>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($posts as $row): 
                $anh = $row['AnhDaiDien'] ? UPLOADS_URL . $row['AnhDaiDien'] : ASSETS_URL . '/img/noimage.png';
                $postData = base64_encode(json_encode($row));
            ?>
                <div class="post-card post-card-modal" data-post="<?= $postData ?>">
                    <div class="post-image">
                        <img src="<?= $anh ?>" alt="<?= e($row['TieuDe']) ?>" loading="lazy">
                        <button class="unsave-btn" data-id="<?= $row['PostID'] ?>">Bỏ lưu</button>
                    </div>
                    <div class="post-title"><?= e($row['TieuDe']) ?></div>
                    <div class="post-price"><?= money($row['GiaPhong']) ?>/tháng</div>
                    <div class="post-info"><?= $row['DienTich'] ?> m² · <?= e($row['DiaChi']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.unsave-btn').forEach(btn => {
    btn.onclick = function(e) {
        e.stopPropagation();
        const postId = this.dataset.id;
        if (!confirm('Bỏ lưu tin này?')) return;

        fetch('<?= BASE_URL ?>/api/favorite/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'post_id=' + postId
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.action === 'removed') {
                this.closest('.post-card').remove();
                const count = document.querySelector('h2').textContent.match(/\d+/)?.[0];
                if (count) {
                    document.querySelector('h2').textContent = `Tin đã lưu (${count - 1} tin)`;
                }
            }
        });
    };
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>