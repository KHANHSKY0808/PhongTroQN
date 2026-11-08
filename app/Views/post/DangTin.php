<?php
// app/Views/post/DangTin.php
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng tin cho thuê</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/dangtin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        function previewImages(event) {
            let files = event.target.files;
            let preview = document.getElementById("previewImages");
            preview.innerHTML = "";

            for (let i = 0; i < files.length; i++) {
                let img = document.createElement("img");
                img.src = URL.createObjectURL(files[i]);
                img.style.maxWidth = "100px";
                img.style.marginRight = "5px";
                preview.appendChild(img);
            }
        }
    </script>

    <style>
        #map { height: 400px; width: 100%; margin-top: 10px; }
        .success-popup {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #d4edda;
            color: #155724;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            z-index: 9999;
            font-weight: bold;
        }
        .error-alert {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php if ($successMsg = getFlash('success')): ?>
        <div class="success-popup">
            <?= htmlspecialchars($successMsg) ?>
        </div>
        <script>
            setTimeout(function(){
                window.location.href = "<?= BASE_URL ?>";
            }, 2000);
        </script>
    <?php endif; ?>

    <div class="main-container">

        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="user-info-card">
                <div class="user-profile">
                    <div class="user-avatar"></div>
                    <div class="user-details">
                        <p class="user-name"><?= Auth::check() ? htmlspecialchars(Auth::user()['HoTen'] ?? 'Thành viên') : 'Khách' ?></p>
                        <p class="user-phone"><?= Auth::check() ? htmlspecialchars(Auth::user()['SoDienThoai'] ?? '') : '' ?></p>
                        <p class="user-account-id">Mã tài khoản: <?= Auth::check() ? htmlspecialchars((string)Auth::id()) : '' ?></p>
                    </div>
                </div>

                <div class="balance-card">
                    <p>Số dư tài khoản</p>
                    <div class="balance-row">
                        <span class="balance-amount">0</span>
                        <button class="deposit-button">
                            <i class="deposit-icon"></i>
                            Nạp tiền
                        </button>
                    </div>
                </div>
            </div>

            <a href="<?= BASE_URL ?>/dang-tin" class="post-new-item">
                <i class="post-new-icon"></i>
                Đăng tin mới
            </a>
        </div>

        <!-- CONTENT -->
        <div class="content-area">
            <?php if ($errorMsg = getFlash('error')): ?>
                <div class="error-alert">
                    ⚠️ <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/dang-tin/luu" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- THÔNG TIN MÔ TẢ -->
                <div class="card">
                    <h3>Thông tin mô tả</h3>

                    <label>Tiêu đề (*)</label>
                    <input type="text" name="title" class="form-control" required>

                    <label>Địa chỉ (*)</label>
                    <input type="text" name="diachi" class="form-control" required placeholder="Ví dụ: 123 Nguyễn Tất Thành, Quy Nhơn">

                    <label>Nội dung mô tả</label>
                    <textarea name="mota" class="form-control" rows="5"></textarea>

                    <label>Giá cho thuê (VNĐ)</label>
                    <input type="number" name="gia" class="form-control" required>

                    <label>Diện tích (m²)</label>
                    <input type="number" name="dientich" class="form-control" required>
                </div>

                <!-- VỊ TRÍ -->
                <div class="card">
                    <h3>Vị trí (Tùy chọn)</h3>

                    <label>Latitude (Vĩ độ)</label>
                    <input type="text" name="latitude" id="latitude" class="form-control" placeholder="Ví dụ: 13.7825">

                    <label>Longitude (Kinh độ)</label>
                    <input type="text" name="longitude" id="longitude" class="form-control" placeholder="Ví dụ: 109.2215">

                    <div id="map"></div>
                    <p>Nhấn vào bản đồ để chọn vị trí.</p>

                    <script>
                        var map = L.map('map').setView([13.7825, 109.2215], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '© OpenStreetMap'
                        }).addTo(map);

                        var marker;
                        map.on('click', function(e){
                            var lat = e.latlng.lat.toFixed(6);
                            var lng = e.latlng.lng.toFixed(6);

                            if(marker) marker.setLatLng(e.latlng);
                            else marker = L.marker(e.latlng).addTo(map);

                            document.getElementById('latitude').value = lat;
                            document.getElementById('longitude').value = lng;
                        });
                    </script>
                </div>

                <!-- THÔNG TIN LIÊN HỆ -->
                <div class="card">
                    <h3>Thông tin liên hệ</h3>
                    <label>Họ Tên</label>
                    <input type="text" name="hoten" class="form-control" required>
                    <label>Số điện thoại</label>
                    <input type="text" name="sdt" class="form-control" required>
                </div>

                <!-- HÌNH ẢNH -->
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

                <!-- VIDEO -->
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

                <button type="submit" name="dangtin">Đăng tin</button>
            </form>
        </div>

    </div>
</body>
</html>
