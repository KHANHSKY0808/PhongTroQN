<?php
// app/Views/partials/user-actions.php
// Gắn vào trang chi tiết tin: require_once __DIR__ . '/../partials/user-actions.php';
$postId = $data['post']['PostID']; // Giả sử $data['post'] từ controller detail
$favoriteModel = new Favorite();
$isFavorited = $favoriteModel->isFavorited($_SESSION['user']['UserID'], $postId);
$shareLink = BASE_URL . '/post/' . $postId; // Giả sử URL chi tiết tin là /post/{id}
?>

<!-- Nút hành động dưới tin đăng -->
<div class="action-buttons">
    <button class="action-btn <?= $isFavorited ? 'active' : '' ?>" id="saveBtn" data-post-id="<?= $postId ?>">
        <i class="fas fa-heart"></i> <span><?= $isFavorited ? 'Đã lưu' : 'Lưu tin' ?></span>
    </button>
    <button class="action-btn" onclick="openShare()">
        <i class="fas fa-share-alt"></i> <span>Chia sẻ</span>
    </button>
    <button class="action-btn" onclick="openReport()">
        <i class="fas fa-exclamation-triangle"></i> <span>Báo xấu</span>
    </button>
</div>

<!-- Popup Chia sẻ -->
<div class="overlay" id="shareOverlay">
    <div class="popup">
        <div class="popup-header">Chia sẻ tin <button class="close-btn" onclick="closeModal('shareOverlay')">&times;</button></div>
        <div class="popup-body">
            <div class="share-link">
                <input type="text" id="shareLink" value="<?= $shareLink ?>" readonly>
                <button class="copy-btn" onclick="copyLink()">Sao chép</button>
            </div>
        </div>
    </div>
</div>

<!-- Popup Báo xấu -->
<div class="overlay" id="reportOverlay">
    <div class="popup">
        <div class="popup-header">Báo xấu tin đăng <button class="close-btn" onclick="closeModal('reportOverlay')">&times;</button></div>
        <div class="popup-body">
            <form action="<?= BASE_URL ?>/user-action/report" method="POST">
                <input type="hidden" name="post_id" value="<?= $postId ?>">
                <label class="report-option">
                    <input type="radio" name="reason" value="Lừa đảo" required> Tin có dấu hiệu lừa đảo
                </label>
                <label class="report-option">
                    <input type="radio" name="reason" value="Trùng lặp"> Tin trùng lặp nội dung
                </label>
                <label class="report-option">
                    <input type="radio" name="reason" value="Không liên hệ được"> Không liên hệ được chủ tin
                </label>
                <label class="report-option">
                    <input type="radio" name="reason" value="Thông tin sai"> Thông tin không đúng thực tế
                </label>
                <label class="report-option">
                    <input type="radio" name="reason" value="Khác"> Lý do khác
                </label>
                <textarea name="detail" class="report-textarea" placeholder="Mô tả thêm (tùy chọn)"></textarea>
                <button type="submit" class="submit-report">Gửi báo cáo</button>
            </form>
        </div>
    </div>
</div>

<script>
function openShare() { document.getElementById('shareOverlay').style.display = 'flex'; }
function openReport() { document.getElementById('reportOverlay').style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function copyLink() {
    const input = document.getElementById('shareLink');
    input.select();
    document.execCommand('copy');
    alert('Đã sao chép link!');
}

// Toggle lưu tin (AJAX)
document.getElementById('saveBtn').addEventListener('click', function() {
    const postId = this.dataset.postId;
    fetch('<?= BASE_URL ?>/user-action/favorite', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + postId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            this.classList.toggle('active');
            const text = data.action === 'added' ? 'Đã lưu' : 'Lưu tin';
            this.querySelector('span').textContent = text;
        } else {
            alert('Lưu tin thất bại!');
        }
    });
});
</script>