<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PhongTroQN</title>
    <style>
        :root{--p:#2d5aa0;--l:#E7F1FF;--bg:#FEF9EE;--shadow:0 2px 10px rgba(0,0,0,.06);}
        *{box-sizing:border-box}
        body{margin:0;font-family:Arial;background:var(--bg);color:#333}
        .sidebar{width:260px;height:100vh;background:var(--l);position:fixed;padding:30px 0;box-shadow:var(--shadow);overflow-y:auto}
        .sidebar h2{text-align:center;color:var(--p);margin-bottom:40px;font-size:24px}
        .sidebar a{display:block;padding:14px 30px;color:var(--p);text-decoration:none;font-weight:500;transition:.3s}
        .sidebar a:hover{background:rgba(45,90,160,.1);color:#1e40af}
        .sidebar a.active{background:rgba(45,90,160,.15);border-left:4px solid var(--p)}
        .content{margin-left:260px;padding:30px}
        .topbar{background:#fff;padding:20px 30px;border-radius:12px;margin-bottom:25px;display:flex;justify-content:space-between;align-items:center;box-shadow:var(--shadow)}
        .topbar h2{margin:0;color:var(--p)}
        .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:40px}
        .card{background:#fff;padding:25px;border-radius:12px;text-align:center;box-shadow:var(--shadow)}
        .card h3{margin:0 0 10px;font-size:28px;color:var(--p)}
        .card p{margin:0;color:#666;font-size:16px}
        .section-title{color:var(--p);margin:40px 0 20px;font-size:22px}
        table{width:100%;background:#fff;border-collapse:collapse;border-radius:12px;overflow:hidden;box-shadow:var(--shadow)}
        th{background:var(--l);color:var(--p);padding:15px;text-align:left;font-weight:600}
        td{padding:12px 15px;border-bottom:1px solid #eee}
        tr:hover{background:#f8fbff}
        .btn{padding:6px 12px;background:var(--p);color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:14px}
        .btn:hover{background:#1e40af}
        .btn-danger{background:#dc3545}
        .btn-danger:hover{background:#c82333}

        /* POPUP XÁC NHẬN XÓA - ĐẸP NHƯ ẢNH BẠN GỬI */
        .delete-modal {
          display: none;
          position: fixed; top: 0; left: 0; width: 100%; height: 100%;
          background: rgba(0,0,0,0.5);
          justify-content: center; align-items: center;
          z-index: 9999;
        }
        .delete-modal.active { display: flex; }

        /* THÊM CSS CHO MODAL CHI TIẾT */
        .detail-modal {
        display: none;
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        justify-content: center; align-items: center;
        z-index: 9999;
        }  
              
        .modal-content {
          background: white; padding: 35px; border-radius: 16px;
          text-align: center; max-width: 420px; width: 90%;
          box-shadow: 0 15px 35px rgba(0,0,0,0.2);
          animation: fadeIn 0.3s;
        }
        @keyframes fadeIn { from {opacity: 0; transform: scale(0.9);} to {opacity: 1; transform: scale(1);} }
        .modal-icon {
          width: 80px; height: 80px; margin: 0 auto 20px;
          background: #fff0e6; border: 5px solid #ff9800; border-radius: 50%;
          display: flex; align-items: center; justify-content: center;
          font-size: 40px; color: #ff9800;
          font-weight: bold;
        }
        .modal-title {
          font-size: 24px; font-weight: bold; color: #333; margin: 10px 0 15px;
        }
        .modal-desc {
          color: #666; margin-bottom: 30px; font-size: 16px;
        }
        .modal-btns button {
          padding: 12px 28px; margin: 0 10px; border: none; border-radius: 8px;
          font-size: 15px; cursor: pointer; font-weight: 500;
        }
        .btn-confirm { background: #2196F3; color: white; }
        .btn-confirm:hover { background: #1976D2; }
        .btn-cancel { background: #f44336; color: white; }
        .btn-cancel:hover { background: #d32f2f; }

        /* TOAST THÔNG BÁO */
        .toast {
          position: fixed; top: 20px; right: 20px; z-index: 10000;
          padding: 16px 24px; border-radius: 8px; color: white;
          font-size: 15px; min-width: 300px; box-shadow: 0 6px 20px rgba(0,0,0,0.2);
          transform: translateX(400px); transition: transform 0.4s ease;
        }
        .toast.show { transform: translateX(0);
        }
        .toast.success { background: #4caf50; }
        .toast.error { background: #f44336; }
    </style>
</head>
<body>

<?php
// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    redirect('/'); exit;
}

// Require models nếu cần (từ code mới)
require_once __DIR__ . '/../../Models/DB.php';

// Lấy dữ liệu thống kê (kết hợp đầy đủ từ code cũ)
$totalPosts     = query("SELECT COUNT(*) FROM post")->fetchColumn();
$todayPosts     = query("SELECT COUNT(*) FROM post WHERE DATE(NgayDang) = CURDATE()")->fetchColumn();
$totalUsers     = query("SELECT COUNT(*) FROM user WHERE IsAdmin = 0")->fetchColumn();
$todayUsers     = 0; // `user` table has no `created_at` column; fallback to 0
$totalReports   = query("SELECT COUNT(*) FROM baocao")->fetchColumn();
$pendingReports = query("SELECT COUNT(*) FROM baocao WHERE DaXuLy = 0")->fetchColumn();
$resolvedReports= query("SELECT COUNT(*) FROM baocao WHERE DaXuLy = 1")->fetchColumn();

$recentPosts = query("SELECT p.PostID, p.TieuDe, u.HoTen, p.NgayDang 
                      FROM post p JOIN user u ON p.UserID = u.UserID 
                      ORDER BY p.NgayDang DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="sidebar">
    <h2>Quản Trị Viên</h2>
    <a href="<?= BASE_URL ?>/index.php?page=admin" class="active">Tổng quan</a>
    <a href="<?= BASE_URL ?>/index.php?page=admin/users">Quản lý người dùng</a>
    <a href="<?= BASE_URL ?>/index.php?page=admin/posts">Quản lý tin đăng</a>
    <a href="<?= BASE_URL ?>/index.php?page=admin/reports">Quản lý báo cáo</a>
    <a href="<?= BASE_URL ?>/index.php">Về trang chủ</a>
</div>

<div class="content">
    <div class="topbar">
        <h2>Bảng điều khiển</h2>
        <span>Xin chào, Admin! Hôm nay: <?= date('d/m/Y') ?></span>
    </div>

    <div class="cards">
        <div class="card"><h3><?= $todayPosts ?> / <?= $totalPosts ?></h3><p>Số tin đăng hôm nay / Tổng tin</p></div>
        <div class="card"><h3><?= $todayUsers ?> / <?= $totalUsers ?></h3><p>Người dùng mới hôm nay / Tổng người dùng</p></div>
        <div class="card"><h3><?= $pendingReports ?> / <?= $totalReports ?></h3><p>Báo cáo chưa xử lý / Tổng báo cáo</p></div>
        <div class="card"><h3><?= $resolvedReports ?></h3><p>Báo cáo đã xử lý</p></div>
    </div>

    <h2 class="section-title">Tin đăng gần đây</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Tiêu đề</th><th>Người đăng</th><th>Ngày đăng</th><th>Hành động</th>
            </tr>
        </thead>
      <tbody>
        <?php foreach ($recentPosts as $post): ?>
        <tr>
          <td><?= $post['PostID'] ?></td>
          <td><?= htmlspecialchars($post['TieuDe']) ?></td>
          <td><?= htmlspecialchars($post['HoTen']) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($post['NgayDang'])) ?></td>
          <td>
            <a href="<?= BASE_URL ?>/chi-tiet/<?= $post['PostID'] ?>">
              <button class="btn">Xem</button>
            </a>
            <!-- NÚT XÓA ĐÃ ĐƯỢC THAY ĐỔI -->
            <button class="btn btn-danger btn-delete" data-id="<?= $post['PostID'] ?>">Xóa</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
</div>

<!-- POPUP XÁC NHẬN XÓA -->
<div class="delete-modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-icon">!</div>
        <h3 class="modal-title">Bạn có chắc không?</h3>
        <div class="modal-btns">
            <button class="btn-confirm" id="confirmDelete">Đúng rồi, xóa nó đi!</button>
            <button class="btn-cancel" id="cancelDelete">Hủy bỏ</button>
        </div>
    </div>
</div>

<!-- TOAST THÔNG BÁO -->
<div id="toast"></div>

<!-- ====================== MODAL CHI TIẾT TIN ĐĂNG ====================== -->
<div class="detail-modal" id="detailModal" style="display:none;"> <!-- Đổi class để tránh xung đột -->
    <div class="modal-content" style="max-width:900px; width:95%; padding:0; overflow:hidden;">
        <div style="background:#2d5aa0; color:white; padding:15px 25px; font-size:20px; font-weight:bold; position:relative;">
            Chi tiết tin đăng
            <span id="closeDetail" style="position:absolute; right:20px; top:50%; transform:translateY(-50%); font-size:28px; cursor:pointer; font-weight:bold;">×</span>
        </div>
        
        <div style="padding:25px; max-height:80vh; overflow-y:auto;">
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

<script>
    // Định nghĩa biến global từ PHP
    const BASE_URL = '<?= BASE_URL ?>';
    const ASSETS_URL = '<?= ASSETS_URL ?>';
    const CSRF_TOKEN = '<?= csrf_token() ?>';
</script>
<script src="<?= ASSETS_URL ?>/js/admin-dashboard.js"></script>
</body>
</html>