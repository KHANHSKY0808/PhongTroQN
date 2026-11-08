<?php
$favorites = $data['favorites'] ?? [];
$total = $data['total'] ?? 0;
$page = $data['page'] ?? 1;
$totalPages = $data['totalPages'] ?? 1;
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container" style="margin-top:30px; min-height:70vh;">
    <h1 class="page-title" style="font-size:26px; font-weight:700; color:#2d5aa0; margin-bottom:20px;">Tin đã lưu (<?= $total ?> tin)</h1>

    <?php if (empty($favorites)): ?>
        <div style="text-align:center; padding:80px 20px; color:#999; font-size:18px;">
            <p>Chưa có tin nào được lưu.</p>
            <a href="<?= BASE_URL ?>/" style="color:#1976d2; text-decoration:underline;">Quay lại trang chủ tìm phòng ngay →</a>
        </div>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
            <thead>
                <tr style="background:#2d5aa0; color:white;">
                    <th style="padding:14px 16px; text-align:left; font-weight:600; text-transform:uppercase; font-size:14px; border-bottom:1px solid #eee; width:80px;">ID</th>
                    <th style="padding:14px 16px; text-align:left; font-weight:600; text-transform:uppercase; font-size:14px; border-bottom:1px solid #eee;">Tiêu đề</th>
                    <th style="padding:14px 16px; text-align:left; font-weight:600; text-transform:uppercase; font-size:14px; border-bottom:1px solid #eee; width:140px;">Giá phòng</th>
                    <th style="padding:14px 16px; text-align:left; font-weight:600; text-transform:uppercase; font-size:14px; border-bottom:1px solid #eee; width:120px;">Diện tích</th>
                    <th style="padding:14px 16px; text-align:left; font-weight:600; text-transform:uppercase; font-size:14px; border-bottom:1px solid #eee; width:180px;">Ngày đăng</th>
                    <th style="padding:14px 16px; text-align:left; font-weight:600; text-transform:uppercase; font-size:14px; border-bottom:1px solid #eee; width:180px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($favorites as $post): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:14px 16px;"><?= $post['PostID'] ?? 'N/A' ?></td>
                    <td style="padding:14px 16px;"><?= htmlspecialchars($post['TieuDe'] ?? '') ?></td>
                    <td style="padding:14px 16px; color:#e67e22; font-weight:bold;">
                        <?= money($post['GiaPhong'] ?? 0) ?>/tháng
                    </td>
                    <td style="padding:14px 16px;"><?= number_format($post['DienTich'] ?? 0, 1) ?> m²</td>
                    <td style="padding:14px 16px;"><?= date('d/m/Y H:i', strtotime($post['NgayDang'] ?? date('Y-m-d H:i:s'))) ?></td>
                    <td style="padding:14px 16px;">
                        <button class="btn btn-detail" data-id="<?= $post['PostID'] ?>" 
                            style="padding:6px 12px; border:none; border-radius:6px; font-size:13px; cursor:pointer; background:#1976d2; color:white; transition:all 0.2s; margin-right:6px;">
                            Xem chi tiết
                        </button>
                        <button class="btn btn-unsave" data-id="<?= $post['PostID'] ?>" 
                            style="padding:6px 12px; border:none; border-radius:6px; font-size:13px; cursor:pointer; background:#d32f2f; color:white; transition:all 0.2s;">
                            Bỏ lưu
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
        <div style="margin-top:30px; text-align:center;">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" 
                   style="display:inline-block; padding:8px 12px; margin:0 4px; border:1px solid #ddd; border-radius:4px; text-decoration:none; color:<?= $i == $page ? 'white' : '#333' ?>; background:<?= $i == $page ? '#2d5aa0' : '#f5f5f5' ?>;">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- MODAL XÁC NHẬN BỎ LƯU -->
<div id="deleteModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
    <div style="background:white; border-radius:12px; width:420px; max-width:90%; text-align:center; padding:30px 20px; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
        <div style="font-size:60px; color:#ff5252; margin-bottom:15px;">!</div>
        <h3 style="font-size:22px; margin:10px 0; color:#333;">Bỏ lưu tin này?</h3>
        <p style="color:#666; margin-bottom:25px;">Tin sẽ bị xóa khỏi danh sách đã lưu của bạn.</p>
        <div style="display:flex; gap:10px; justify-content:center;">
            <button id="confirmDelete" style="padding:10px 20px; border:none; border-radius:8px; font-size:15px; cursor:pointer; background:#d32f2f; color:white;">
                Đúng, bỏ lưu
            </button>
            <button id="cancelDelete" style="padding:10px 20px; border:none; border-radius:8px; font-size:15px; cursor:pointer; background:#eee; color:#333;">
                Hủy
            </button>
        </div>
    </div>
</div>

<!-- ====================== MODAL CHI TIẾT TIN ĐĂNG ====================== -->
<div class="delete-modal" id="detailModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
    <div class="modal-content" style="max-width:900px; width:95%; padding:0; overflow:hidden; background:white; border-radius:12px; max-height:90vh; display:flex; flex-direction:column;">
        <div style="background:#2d5aa0; color:white; padding:15px 25px; font-size:20px; font-weight:bold; position:relative; flex-shrink:0;">
            Chi tiết tin đăng
            <span id="closeDetail" style="position:absolute; right:20px; top:50%; transform:translateY(-50%); font-size:28px; cursor:pointer; font-weight:bold;">×</span>
        </div>
        
        <div style="padding:25px; max-height:calc(90vh - 80px); overflow-y:auto; flex-grow:1;">
            <div id="detailLoading" style="text-align:center; padding:40px; font-size:18px; color:#666;">
                Đang tải thông tin...
            </div>
            
            <div id="detailContent" style="display:none;">
                <h3 id="detailTitle" style="margin-top:0; color:#2d5aa0; font-size:24px;"></h3>
                
                <!-- Carousel ảnh -->
                <div id="imageCarousel" style="margin:20px 0; text-align:center; position:relative;">
                    <div id="carouselImages" style="display:flex; justify-content:center; align-items:center; overflow:hidden; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                        <img id="mainImage" src="" style="max-width:100%; max-height:400px; object-fit:cover; border-radius:8px;">
                    </div>
                    <button onclick="prevImage()" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.5); color:white; border:none; width:40px; height:40px; border-radius:50%; font-size:20px; cursor:pointer;">◄</button>
                    <button onclick="nextImage()" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.5); color:white; border:none; width:40px; height:40px; border-radius:50%; font-size:20px; cursor:pointer;">►</button>
                    <div id="thumbnailContainer" style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap; justify-content:center;"></div>
                </div>

                <div style="background:#f8fbff; padding:20px; border-radius:10px; margin:20px 0; line-height:1.8;">
                    <p><strong>Giá phòng:</strong> <span id="detailPrice" style="font-size:22px; color:#e67e22; font-weight:bold;"></span></p>
                    <p><strong>Diện tích:</strong> <span id="detailArea"></span> m²</p>
                    <p><strong>Địa chỉ:</strong> <span id="detailAddress"></span></p>
                    <p><strong>Người đăng:</strong> <span id="detailPoster"></span></p>
                    <p><strong>Ngày đăng:</strong> <span id="detailDate"></span></p>
                </div>

                <div style="margin-top:20px;">
                    <h4>Mô tả chi tiết:</h4>
                    <div id="detailDesc" style="background:white; padding:15px; border-radius:8px; border:1px solid #eee; min-height:80px; white-space:pre-wrap;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TOAST THÔNG BÁO -->
<div id="toast" style="position:fixed; top:20px; right:20px; z-index:10000; min-width:300px; padding:16px 20px; border-radius:8px; color:white; font-weight:500; transform:translateX(400px); transition:transform 0.4s ease;"></div>

<style>
    @keyframes modalShow {
        from { transform: scale(0.7); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    #toast.show { transform: translateX(0); }
    #toast.success { background: #4caf50; }
    #toast.error { background: #f44336; }
    .btn:hover { opacity: 0.9; transform: translateY(-1px); }
</style>

<script>
let postIdToRemove = null;
let currentImages = [];
let currentIndex = 0;

// ===== XEM CHI TIẾT =====
document.querySelectorAll('.btn-detail').forEach(btn => {
    btn.addEventListener('click', function() {
        const postId = this.getAttribute('data-id');
        openDetailModal(postId);
    });
});

function openDetailModal(postId) {
    document.getElementById('detailModal').style.display = 'flex';
    document.getElementById('detailContent').style.display = 'none';
    document.getElementById('detailLoading').style.display = 'block';
    currentIndex = 0;

    fetch(`<?= BASE_URL ?>/api/post-detail/${postId}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            document.getElementById('detailLoading').textContent = 'Lỗi: ' + (data.message || 'Không tải được');
            return;
        }

        const p = data.post;
        currentImages = data.images || [];

        document.getElementById('detailTitle').textContent = p.TieuDe;
        document.getElementById('detailPrice').textContent = new Intl.NumberFormat('vi-VN').format(p.GiaPhong) + ' đ';
        document.getElementById('detailArea').textContent = p.DienTich;
        document.getElementById('detailAddress').textContent = p.DiaChi;
        document.getElementById('detailPoster').textContent = p.TenNguoiDang || p.HoTen;
        document.getElementById('detailDate').textContent = new Date(p.NgayDang).toLocaleString('vi-VN');
        document.getElementById('detailDesc').textContent = p.MoTa || '(Không có mô tả)';

        // Xử lý ảnh
        const mainImg = document.getElementById('mainImage');
        const thumbContainer = document.getElementById('thumbnailContainer');
        thumbContainer.innerHTML = '';

        if (currentImages.length > 0) {
            mainImg.src = '<?= UPLOADS_URL ?>' + currentImages[0];
            currentImages.forEach((img, i) => {
                const thumb = document.createElement('img');
                thumb.src = '<?= UPLOADS_URL ?>' + img;
                thumb.style.width = '60px';
                thumb.style.height = '60px';
                thumb.style.objectFit = 'cover';
                thumb.style.borderRadius = '6px';
                thumb.style.cursor = 'pointer';
                thumb.style.opacity = i === 0 ? '1' : '0.6';
                thumb.style.border = i === 0 ? '2px solid #2d5aa0' : '2px solid transparent';
                thumb.onclick = () => { currentIndex = i; showImage(i); };
                thumbContainer.appendChild(thumb);
            });
        } else {
            mainImg.src = '<?= ASSETS_URL ?>/img/noimage.png';
            thumbContainer.innerHTML = '<i style="color:#999;">Không có hình ảnh</i>';
        }

        document.getElementById('detailLoading').style.display = 'none';
        document.getElementById('detailContent').style.display = 'block';
    })
    .catch(err => {
        document.getElementById('detailLoading').textContent = 'Lỗi: ' + err.message;
    });
}

function showImage(index) {
    document.getElementById('mainImage').src = '<?= UPLOADS_URL ?>' + currentImages[index];
    document.querySelectorAll('#thumbnailContainer img').forEach((t, i) => {
        t.style.opacity = i === index ? '1' : '0.6';
        t.style.border = i === index ? '2px solid #2d5aa0' : '2px solid transparent';
    });
}

function prevImage() {
    if (currentImages.length === 0) return;
    currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
    showImage(currentIndex);
}

function nextImage() {
    if (currentImages.length === 0) return;
    currentIndex = (currentIndex + 1) % currentImages.length;
    showImage(currentIndex);
}

document.getElementById('closeDetail').addEventListener('click', () => {
    document.getElementById('detailModal').style.display = 'none';
});

// Đóng modal khi click ngoài
document.getElementById('detailModal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('detailModal')) {
        document.getElementById('detailModal').style.display = 'none';
    }
});

// ===== BỎ LƯU =====
document.querySelectorAll('.btn-unsave').forEach(btn => {
    btn.addEventListener('click', function() {
        postIdToRemove = this.getAttribute('data-id');
        document.getElementById('deleteModal').style.display = 'flex';
    });
});

document.getElementById('cancelDelete').addEventListener('click', () => {
    document.getElementById('deleteModal').style.display = 'none';
    postIdToRemove = null;
});

document.getElementById('confirmDelete').addEventListener('click', () => {
    if (!postIdToRemove) return;

    fetch('<?= BASE_URL ?>/api/favorite/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= csrf_token() ?>'
        },
        body: 'post_id=' + postIdToRemove
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('deleteModal').style.display = 'none';
        if (data.success && data.action === 'removed') {
            showToast('Đã bỏ lưu tin thành công!', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast('Có lỗi xảy ra, vui lòng thử lại.', 'error');
        }
    })
    .catch(() => {
        showToast('Lỗi kết nối!', 'error');
        document.getElementById('deleteModal').style.display = 'none';
    });
});

// Toast
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = type;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>