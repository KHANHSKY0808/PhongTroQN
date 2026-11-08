<?php
// app/Views/home/index.php
$title = $title ?? "PhongTroQN - Phòng trọ Quy Nhơn số 1";
require_once __DIR__ . '/../partials/header.php';
?>

<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/index.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/post-detail-modal.css?v=<?= time() ?>">

<style>
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin: 35px 0 20px;
}
.pagination {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
    justify-content: center;
}
.pagination .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 14px;
    border-radius: 8px;
    background: #fff;
    color: #333;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    border: 1px solid #dce2e6;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}
.pagination .page-link:hover {
    background: #0073e6;
    color: #fff;
    border-color: #0073e6;
    transform: translateY(-1px);
}
.pagination .page-link.active {
    background: #0073e6;
    color: #fff;
    border-color: #0073e6;
    cursor: default;
    box-shadow: 0 4px 10px rgba(0, 115, 230, 0.3);
}
.pagination .page-dots {
    color: #888;
    padding: 0 4px;
    font-weight: bold;
}
</style>

<!-- Hiển thị thông báo flash -->
<?php if ($successMsg = getFlash('success')): ?>
  <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin: 20px auto; max-width: 1200px; border: 1px solid #c3e6cb; font-weight: 500;">
    ✓ <?= htmlspecialchars($successMsg) ?>
  </div>
<?php endif; ?>
<?php if ($errorMsg = getFlash('error')): ?>
  <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin: 20px auto; max-width: 1200px; border: 1px solid #f5c6cb; font-weight: 500;">
    ⚠️ <?= htmlspecialchars($errorMsg) ?>
  </div>
<?php endif; ?>

<!-- Header info -->
<div class="header-info">
    <h1>Kênh thông tin Phòng Trọ số 1 Quy Nhơn</h1>
    <p>Hiện có <b><?= number_format($totalPosts ?? 0) ?></b> tin đăng đang hoạt động</p>
</div>

<div class="container">
    <h2 style="margin:20px 0; color:#0073e6;">Đề xuất dành cho bạn</h2>

    <?php if (!empty($posts)): ?>
        <div class="posts-grid">
            <?php foreach ($posts as $row): ?>
                <?php
                $anh = !empty($row['AnhDaiDien'])
                    ? UPLOADS_URL . $row['AnhDaiDien']
                    : ASSETS_URL . '/img/noimage.png';

                $images = $row['images'] ?? [];

                $postData = base64_encode(json_encode([
                    'PostID'       => $row['PostID'],
                    'TieuDe'       => $row['TieuDe'] ?? '',
                    'GiaPhong'     => $row['GiaPhong'] ?? 0,
                    'DienTich'     => $row['DienTich'] ?? 0,
                    'DiaChi'       => $row['DiaChi'] ?? '',
                    'MoTa'         => $row['MoTa'] ?? '',
                    'NgayDang'     => date("d/m/Y", strtotime($row['NgayDang'])),
                    'TenNguoiDang' => $row['TenNguoiDang'] ?? 'Chủ trọ',
                    'SoDienThoai'  => $row['SoDienThoai'] ?? '',
                    'AnhDaiDien'   => $row['AnhDaiDien'] ?? null,
                    'images'       => $images,
                    'Latitude'     => $row['Latitude'] ?? null,
                    'Longitude'    => $row['Longitude'] ?? null,
                ]));
                ?>

                <div class="post-card post-card-modal" data-post="<?= $postData ?>">
                    <div class="post-image">
                        <img src="<?= $anh ?>" alt="<?= htmlspecialchars($row['TieuDe']) ?>"
                             onerror="this.src='<?= ASSETS_URL ?>/img/noimage.png'" loading="lazy">
                    </div>
                    <div class="post-title"><?= htmlspecialchars($row['TieuDe']) ?></div>
                    <div class="post-price"><?= number_format($row['GiaPhong']) ?> đ/tháng</div>
                    <div class="post-info">
                        <?= number_format($row['DienTich'], 1) ?> m² · <?= htmlspecialchars($row['DiaChi']) ?>
                    </div>
                    <div class="post-info" style="color:#666;font-size:0.9rem;">
                        <?= mb_substr(strip_tags($row['MoTa'] ?? ''), 0, 100, 'UTF-8') . '...' ?>
                    </div>
                    <div class="post-user">
                        <img src="<?= ASSETS_URL ?>/img/default-user.png" alt="user">
                        <div>
                            <b><?= htmlspecialchars($row['TenNguoiDang']) ?></b><br>
                            <small><?= date("d/m/Y", strtotime($row['NgayDang'])) ?></small>
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <?php if (!empty($row['SoDienThoai'])): ?>
                                <a href="tel:<?= $row['SoDienThoai'] ?>" class="btn-call" onclick="event.stopPropagation();">Gọi ngay</a>
                            <?php else: ?>
                                <span class="btn-call" style="background:#ccc;cursor:not-allowed;">Liên hệ</span>
                            <?php endif; ?>
                            <button class="btn-call btn-report-action" onclick="handleReport(event, <?= $row['PostID'] ?>, '<?= addslashes(htmlspecialchars($row['TieuDe'])) ?>')">Báo cáo</button>
                        </div>
                        <span class="heart-btn <?= !empty($row['is_favorited']) ? 'saved' : '' ?>" data-id="<?= $row['PostID'] ?>" onclick="event.stopPropagation();">❤</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <?php
                $currentPage = (int)($currentPage ?? $page ?? 1);
                $totalPages = (int)$totalPages;
                $queryParams = $queryParams ?? $filters ?? [];
                $baseUrl = !empty($queryParams) ? BASE_URL . '/loc-tin' : BASE_URL . '/';
                $buildUrl = function($pageNum) use ($baseUrl, $queryParams) {
                    $params = array_merge($queryParams, ['page' => $pageNum]);
                    return $baseUrl . '?' . http_build_query($params);
                };
            ?>
            <div class="pagination-wrapper">
                <nav class="pagination" aria-label="Phân trang bài viết">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?= htmlspecialchars($buildUrl($currentPage - 1)) ?>" class="page-link prev-page" title="Trang trước">&laquo; Trước</a>
                    <?php endif; ?>

                    <?php
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);

                        if ($start > 1) {
                            echo '<a href="' . htmlspecialchars($buildUrl(1)) . '" class="page-link">1</a>';
                            if ($start > 2) {
                                echo '<span class="page-dots">&hellip;</span>';
                            }
                        }

                        for ($i = $start; $i <= $end; $i++) {
                            if ($i === $currentPage) {
                                echo '<span class="page-link active">' . $i . '</span>';
                            } else {
                                echo '<a href="' . htmlspecialchars($buildUrl($i)) . '" class="page-link">' . $i . '</a>';
                            }
                        }

                        if ($end < $totalPages) {
                            if ($end < $totalPages - 1) {
                                echo '<span class="page-dots">&hellip;</span>';
                            }
                            echo '<a href="' . htmlspecialchars($buildUrl($totalPages)) . '" class="page-link">' . $totalPages . '</a>';
                        }
                    ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= htmlspecialchars($buildUrl($currentPage + 1)) ?>" class="page-link next-page" title="Trang sau">Sau &raquo;</a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p style="text-align:center;padding:80px;color:#888;">Hiện chưa có tin đăng nào.</p>
    <?php endif; ?>
</div>

<!-- Phần giới thiệu giữ nguyên -->
<div class="footer-info container">
    <div class="footer-card">
        <h2>CHO THUÊ PHÒNG TRỌ, NHÀ TRỌ</h2>
        <p>Khi có nhu cầu thuê phòng trọ, chắc hẳn bạn sẽ băn khoăn với hàng loạt câu hỏi như: “Không biết bắt đầu từ đâu? Sợ bị mất cọc oan vì những phòng trọ “ảo”? Tìm mãi nhưng không ra phòng ưng ý?...”</p>
        <p>Đừng quá lo lắng, vì <strong>PhongtroQN.com</strong> chính là giải pháp tối ưu dành cho những vấn đề đó. Nơi bạn có thể tìm phòng trọ mà không cần lặn lội khắp nơi, chỉ cần vài cú nhấp chuột là tìm thấy ngay một nơi ở tiềm năng.</p>
        
        <h3>Giới thiệu về PhongtroQN.com</h3>
        <p>PhongtroQN.com là kênh thông tin Phòng trọ số 1 Quy Nhơn, một nền tảng chuyên biệt về cho thuê phòng trọ, nhà trọ lớn nhất hiện nay.</p>

        <h3>Ưu điểm của website PhongtroQN.com</h3>
        <ul>
            <li><b>Chuyên môn hóa về phòng trọ:</b> Giúp khách tìm kiếm tiết kiệm thời gian và tránh lọc qua tin không liên quan.</li>
            <li><b>Nguồn tin dồi dào, cập nhật liên tục:</b> Với rất nhiều người dùng, đảm bảo tin đăng mới mỗi ngày.</li>
            <li><b>Thông tin minh bạch - hạn chế rủi ro:</b> Kiểm duyệt kỹ lưỡng, tránh phòng trọ “ảo”.</li>
            <li><b>Bộ lọc thông minh, dễ dùng:</b> Lọc phòng trọ theo khu vực, giá, loại phòng nhanh chóng.</li>
            <li><b>Hỗ trợ người cho thuê phòng:</b> Chủ trọ nhanh chóng kết nối với người có nhu cầu, đăng tin dễ dàng.</li>
        </ul>

        <section class="stats">
            <h3>Thống kê nổi bật</h3>
            <div class="stat-items">
                <div class="stat-item"><span class="number">15.000+</span><span class="label">Chủ nhà & Môi giới</span></div>
                <div class="stat-item"><span class="number">50.000+</span><span class="label">Tin đăng</span></div>
                <div class="stat-item"><span class="number">200+</span><span class="label">Tin đăng/ngày</span></div>
                <div class="stat-item"><span class="number">300.000+</span><span class="label">Lượt xem/tháng</span></div>
            </div>
        </section>
    </div>
</div>

<?php
// QUAN TRỌNG: GỌI FOOTER Ở ĐÂY!!!
require __DIR__ . '/../partials/footer.php';
?>

<!-- ======================== MODAL CHI TIẾT PHÒNG TRỌ ======================== -->
<div id="detailModal" class="detail-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="detail-title" class="modal-title"></h2>
            <span class="close">×</span>
        </div>

        <div class="modal-body">
            <div class="modal-grid">
                <!-- Cột trái: Ảnh -->
                <div class="image-section">
                    <div class="main-slider-wrapper">
                        <button class="slider-btn prev">‹</button>
                        <img id="main-image" src="" alt="Phòng trọ">
                        <button class="slider-btn next">›</button>
                    </div>
                    <div class="thumbnail-container">
                        <ul id="detail-thumbnails"></ul>
                    </div>
                </div>

                <!-- Cột phải: Thông tin -->
                <div class="info-section">
                    <div class="price-tag" id="detail-price"></div>

                    <div class="meta-info">
                        <div class="meta-row"><strong>Diện tích:</strong> <span id="detail-area"></span> m²</div>
                        <div class="meta-row"><strong>Địa chỉ:</strong> <span id="detail-address"></span></div>
                        <div class="meta-row"><strong>Ngày đăng:</strong> <span id="detail-date"></span></div>
                    </div>

                    <h3 class="section-title">Mô tả chi tiết</h3>
                    <div class="description" id="detail-desc"></div>

                    <h3 class="section-title">Thông tin liên hệ</h3>
                    <div class="contact-box">
                        <p><strong>Chủ trọ:</strong> <span id="detail-hoten"></span></p>
                        <p><strong>Điện thoại:</strong> <a href="tel:" id="detail-phone" class="phone-link"></a></p>
                        <button type="button" class="btn-copy">Copy số điện thoại</button>
                    </div>

                    <div id="detail-map" class="map-container"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================== THƯ VIỆN CDN ======================== -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>



<!-- ======================== JAVASCRIPT CHO MODAL ======================== -->
<script>
let currentMap = null;
let currentSlide = 0;
let totalSlides = 0;
let imagesArray = [];

function openDetailModal(data) {
    // === Điền thông tin ===
    $('#detail-title').text(data.TieuDe || 'Phòng trọ');
    $('#detail-price').text(new Intl.NumberFormat('vi-VN').format(data.GiaPhong) + ' đ/tháng');
    $('#detail-area').text(data.DienTich || 'N/A');
    $('#detail-address').text(data.DiaChi || 'Không có');
    $('#detail-date').text(data.NgayDang || '');
    $('#detail-desc').html((data.MoTa || 'Không có mô tả').replace(/\n/g, '<br>'));
    $('#detail-hoten').text(data.TenNguoiDang || 'Chủ trọ');

    const $phone = $('#detail-phone');
    if (data.SoDienThoai) {
        $phone.text(data.SoDienThoai).attr('href', 'tel:' + data.SoDienThoai);
    } else {
        $phone.text('Không công khai').removeAttr('href');
    }

    // === Xử lý ảnh ===
    const $thumbs = $('#detail-thumbnails').empty();
    imagesArray = data.images && data.images.length > 0 ? data.images : [];
    if (imagesArray.length === 0 && data.AnhDaiDien) imagesArray = [{URLAnh: data.AnhDaiDien}];
    if (imagesArray.length === 0) imagesArray = [{URLAnh: '/public/assets/img/noimage.png'}];

    totalSlides = imagesArray.length;

    imagesArray.forEach((img, i) => {
        const src = '<?= UPLOADS_URL ?>' + img.URLAnh;
        $thumbs.append(`<li><img src="${src}" alt="Thumbnail ${i+1}" onclick="selectSlide(${i})"></li>`);
    });

    currentSlide = 0;
    updateMainImage();

    // === Bản đồ ===
    const $map = $('#detail-map').html('<div style="height:100%;display:flex;align-items:center;justify-content:center;color:#666;font-size:1.2rem;">Đang tải bản đồ...</div>');

    if (currentMap) { currentMap.remove(); currentMap = null; }

    setTimeout(() => {
        let lat = parseFloat(data.Latitude) || 10.762622;
        let lng = parseFloat(data.Longitude) || 106.660172;

        currentMap = L.map('detail-map').setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(currentMap);

        L.marker([lat, lng]).addTo(currentMap)
            .bindPopup(`<b>${data.TieuDe}</b><br>${data.DiaChi}`)
            .openPopup();

        setTimeout(() => currentMap && currentMap.invalidateSize(), 200);
    }, 300);

    // Hiển thị modal
    $('#detailModal').fadeIn(300);
}

function closeModal() {
    $('#detailModal').fadeOut(300);
    if (currentMap) { currentMap.remove(); currentMap = null; }
}

function moveSlide(n) {
    currentSlide = (currentSlide + n + totalSlides) % totalSlides;
    updateMainImage();
}

function selectSlide(i) {
    currentSlide = i;
    updateMainImage();
}

function updateMainImage() {
    const src = '<?= UPLOADS_URL ?>' + imagesArray[currentSlide].URLAnh;
    $('#main-image').attr('src', src);
    $('#detail-thumbnails img').removeClass('active').eq(currentSlide).addClass('active');
}

// === Sự kiện đóng modal ===
$('.detail-modal .close, .detail-modal').on('click', function(e) {
    if (e.target === this || $(e.target).hasClass('close')) closeModal();
});

// === Copy số điện thoại ===
$('.btn-copy').on('click', function() {
    const phone = $('#detail-phone').text();
    if (phone && phone !== 'Không công khai') {
        navigator.clipboard.writeText(phone).then(() => {
            alert('Đã copy: ' + phone);
        }).catch(() => alert('Không thể copy'));
    }
});

// === Hàm hiển thị thông báo ===
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Hiện thông báo
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Tự động ẩn sau 4 giây
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// === Mở modal từ card ===
$(document).on('click', '.post-card-modal', function(e) {
    if ($(e.target).closest('.btn-call, .heart, .btn-report-action').length) return;
    const data = JSON.parse(atob(this.dataset.post));
    openDetailModal(data);
});

// === Xử lý báo cáo ===
function handleReport(event, postID, postTitle) {
    event.stopPropagation();
    
    // Kiểm tra đã đăng nhập
    <?php if (!isset($_SESSION['user_id'])): ?>
        if (!confirm('Bạn cần đăng nhập để báo cáo tin. Bạn có muốn đăng nhập không?')) {
            return;
        }
        window.location.href = '<?= BASE_URL ?>/index.php?page=dang-nhap';
        return;
    <?php endif; ?>
    
    openReportModal(event, postID, postTitle);
}

function openReportModal(event, postID, postTitle) {
    event.stopPropagation();
    document.getElementById('reportPostID').value = postID;
    document.getElementById('reportPostTitle').textContent = postTitle;
    document.getElementById('reportModal').style.display = 'block';
}

function closeReportModal() {
    document.getElementById('reportModal').style.display = 'none';
}

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    let modal = document.getElementById('reportModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

//xủ lý lưu/ bỏ lưu
document.querySelectorAll('.heart-btn').forEach(btn => {
    btn.addEventListener('click', function() {

        const postID = this.dataset.id;
        const heart = this;

        fetch("<?= BASE_URL ?>/api/favorite/toggle", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-CSRF-Token": "<?= csrf_token() ?>"
            },
            body: "post_id=" + postID
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || "Vui lòng đăng nhập");
                return;
            }

            if (data.action === "added") {
                heart.classList.add("saved");
            } else {
                heart.classList.remove("saved");
            }
        })
        .catch(err => console.log(err));
    });
});

</script>

<!-- CSS cho nút báo cáo -->
<style>
.btn-report-action {
    background: #dc3545 !important;
    color: white !important;
}

.btn-report-action:hover {
    background: #c82333 !important;
}

/* Thông báo (Notification Toast) */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.3s ease;
    z-index: 9999;
    max-width: 400px;
}

.notification.show {
    opacity: 1;
    transform: translateX(0);
}

.notification-success {
    background: #28a745;
}

.notification-error {
    background: #dc3545;
}

.notification-info {
    background: #17a2b8;
}

/* Modal báo cáo */
#reportModal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.report-modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 30px;
    border: 1px solid #888;
    width: 90%;
    max-width: 500px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.report-modal-content h2 {
    color: #dc3545;
    margin-top: 0;
}

.report-modal-content label {
    display: block;
    margin: 15px 0 5px 0;
    font-weight: 600;
    color: #333;
}

.report-modal-content textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-family: Arial, sans-serif;
    resize: vertical;
    min-height: 120px;
    box-sizing: border-box;
}

.report-modal-content textarea:focus {
    outline: none;
    border-color: #dc3545;
    box-shadow: 0 0 5px rgba(220, 53, 69, 0.3);
}

.report-button-group {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    justify-content: flex-end;
}

.report-button-group button {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
}

.report-button-group .btn-submit {
    background: #dc3545;
    color: white;
}

.report-button-group .btn-submit:hover {
    background: #c82333;
}

.report-button-group .btn-cancel {
    background: #6c757d;
    color: white;
}

.report-button-group .btn-cancel:hover {
    background: #5a6268;
}

.close-report {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-report:hover {
    color: #000;
}
</style>

<!-- Modal báo cáo HTML -->
<div id="reportModal">
    <div class="report-modal-content">
        <span class="close-report" onclick="closeReportModal()">&times;</span>
        <h2>Báo cáo tin đăng</h2>
        
        <p>Tin đăng: <strong id="reportPostTitle"></strong></p>
        
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=bao-cao" id="reportForm">
            <?= csrf_field() ?>
            <input type="hidden" id="reportPostID" name="postID">
            
            <label for="reportReason">Lý do báo cáo *</label>
            <textarea id="reportReason" name="reason" placeholder="Vui lòng mô tả lý do báo cáo (lừa đảo, hình ảnh không đúng, liên hệ không được, v.v...)" required></textarea>
            
            <div class="report-button-group">
                <button type="button" class="btn-cancel" onclick="closeReportModal()">Hủy</button>
                <button type="submit" class="btn-submit">Gửi báo cáo</button>
            </div>
        </form>
    </div>
</div>