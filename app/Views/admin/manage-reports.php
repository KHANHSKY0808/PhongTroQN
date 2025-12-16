<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Quản lý Báo Cáo - PhongTroQN Admin</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      background: #FEF9EE;
      color: #333;
    }
    .sidebar {
      width: 260px;
      height: 100vh;
      background: #E7F1FF;
      position: fixed;
      top: 0;
      left: 0;
      padding: 30px 0;
      box-shadow: 2px 0 10px rgba(0,0,0,0.05);
      overflow-y: auto;
    }
    .sidebar h2 {
      text-align: center;
      color: #2d5aa0;
      margin-bottom: 40px;
      font-size: 24px;
    }
    .sidebar a {
      display: block;
      padding: 14px 30px;
      color: #2d5aa0;
      text-decoration: none;
      font-size: 16px;
      font-weight: 500;
      transition: all 0.3s;
    }
    .sidebar a:hover {
      background: rgba(45, 90, 160, 0.1);
      color: #1e40af;
    }
    .sidebar a.active {
      background: rgba(45, 90, 160, 0.15);
      border-left: 4px solid #2d5aa0;
    }
    .content {
      margin-left: 260px;
      padding: 30px;
    }
    .topbar {
      background: #fff;
      padding: 20px 30px;
      border-radius: 12px;
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    .topbar h2 {
      margin: 0;
      color: #2d5aa0;
    }
    .filter-bar {
      background: #fff;
      padding: 20px 30px;
      border-radius: 12px;
      margin-bottom: 25px;
      display: flex;
      gap: 15px;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    .filter-bar select, .filter-bar input {
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
    }
    .filter-bar button {
      padding: 8px 16px;
      background: #2d5aa0;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
    }
    .filter-bar button:hover {
      background: #1e40af;
    }
    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .stat-card {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    .stat-card h3 {
      margin: 0 0 10px 0;
      font-size: 24px;
      color: #2d5aa0;
    }
    .stat-card p {
      margin: 0;
      color: #666;
      font-size: 14px;
    }
    table {
      width: 100%;
      background: #fff;
      border-collapse: collapse;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    table th {
      background: #E7F1FF;
      color: #2d5aa0;
      padding: 15px;
      text-align: left;
      font-weight: 600;
    }
    table td {
      padding: 12px 15px;
      border-bottom: 1px solid #eee;
    }
    table tr:hover {
      background: #f8fbff;
    }
    .btn {
      padding: 6px 12px;
      background: #2d5aa0;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      margin-right: 5px;
      text-decoration: none;
      display: inline-block;
    }
    .btn:hover {
      background: #1e40af;
    }
    .btn-success {
      background: #28a745;
    }
    .btn-success:hover {
      background: #218838;
    }
    .btn-danger {
      background: #dc3545;
    }
    .btn-danger:hover {
      background: #c82333;
    }
    .btn-info {
      background: #17a2b8;
    }
    .btn-info:hover {
      background: #138496;
    }
    .status-pending {
      color: #dc3545;
      font-weight: bold;
    }
    .status-resolved {
      color: #28a745;
      font-weight: bold;
    }
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
      background-color: #fefefe;
      margin: 5% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
      max-width: 600px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    .close-modal {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    .close-modal:hover {
      color: #000;
    }
    .modal h2 {
      color: #2d5aa0;
      margin-top: 0;
    }
    .detail-row {
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }
    .detail-row strong {
      color: #2d5aa0;
    }
    .empty-message {
      text-align: center;
      padding: 40px;
      color: #999;
      font-size: 16px;
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <h2>Quản Trị Viên</h2>
    <a href="<?= BASE_URL ?>/index.php?page=admin">Tổng quan</a>
    <a href="<?= BASE_URL ?>/index.php?page=admin/users">Quản lý người dùng</a>
    <a href="#">Quản lý tin đăng</a>
    <a href="<?= BASE_URL ?>/index.php?page=admin/reports" class="active">Quản lý báo cáo</a>
    <a href="<?= BASE_URL ?>/index.php">Về trang chủ</a>
  </div>

  <div class="content">
    <div class="topbar">
      <h2>Quản Lý Báo Cáo</h2>
      <span>Hôm nay: <?= date('d/m/Y') ?></span>
    </div>

    <?php if (isset($_GET['success'])): ?>
      <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        <?php if ($_GET['success'] == '1'): ?>
          ✓ Cập nhật trạng thái báo cáo thành công!
        <?php elseif ($_GET['success'] == '2'): ?>
          ✓ Xóa báo cáo thành công!
        <?php elseif ($_GET['success'] == '3'): ?>
          ✓ Xóa người dùng và tất cả dữ liệu của họ thành công!
        <?php elseif ($_GET['success'] == '4'): ?>
          ✓ Xóa bài đăng thành công!
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
        <?php if ($_GET['error'] == 'admin'): ?>
          ✗ Không thể xóa admin! Vui lòng liên hệ người quản lý hệ thống.
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Thống kê -->
    <div class="stats-container">
      <div class="stat-card">
        <h3><?= $GLOBALS['totalReports'] ?? 0 ?></h3>
        <p>Tổng báo cáo</p>
      </div>
      <div class="stat-card">
        <h3 style="color: #dc3545;"><?= $GLOBALS['pendingReports'] ?? 0 ?></h3>
        <p>Chưa xử lý</p>
      </div>
      <div class="stat-card">
        <h3 style="color: #28a745;"><?= $GLOBALS['resolvedReports'] ?? 0 ?></h3>
        <p>Đã xử lý</p>
      </div>
    </div>

    <!-- Bộ lọc -->
    <div class="filter-bar">
      <form method="get" style="display: flex; gap: 15px; align-items: center; width: 100%;">
        <select name="status">
          <option value="">Tất cả trạng thái</option>
          <option value="0" <?= ($GLOBALS['filterStatus'] ?? '') === '0' ? 'selected' : '' ?>>Chưa xử lý</option>
          <option value="1" <?= ($GLOBALS['filterStatus'] ?? '') === '1' ? 'selected' : '' ?>>Đã xử lý</option>
        </select>
        <input type="text" name="search" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($GLOBALS['searchKeyword'] ?? '') ?>">
        <button type="submit">Tìm kiếm</button>
        <?php if (($GLOBALS['filterStatus'] ?? '') !== '' || ($GLOBALS['searchKeyword'] ?? '') !== ''): ?>
          <a href="<?= BASE_URL ?>/index.php?page=admin/reports" style="text-decoration: none;">
            <button type="button" style="background: #6c757d;">Xóa bộ lọc</button>
          </a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Bảng báo cáo -->
    <?php if (empty($GLOBALS['reports'])): ?>
      <div class="empty-message">
        Không có báo cáo nào.
      </div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Người báo cáo</th>
            <th>Tin bị báo cáo</th>
            <th>Lý do</th>
            <th>Ngày báo cáo</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($GLOBALS['reports'] as $report): ?>
          <tr>
            <td><?= $report['BaoCaoID'] ?></td>
            <td><?= htmlspecialchars($report['TenNguoiBaoCao']) ?></td>
            <td><?= htmlspecialchars(substr($report['TieuDe'], 0, 40)) ?>...</td>
            <td><?= htmlspecialchars(substr($report['NoiDung'], 0, 50)) ?>...</td>
            <td><?= date('d/m/Y H:i', strtotime($report['NgayBaoCao'])) ?></td>
            <td>
              <?php if ($report['DaXuLy'] == 0): ?>
                <span class="status-pending">Chưa xử lý</span>
              <?php else: ?>
                <span class="status-resolved">Đã xử lý</span>
              <?php endif; ?>
            </td>
            <td>
              <button class="btn btn-info" onclick="openDetail(<?= $report['BaoCaoID'] ?>, '<?= addslashes(htmlspecialchars($report['TenNguoiBaoCao'])) ?>', '<?= addslashes(htmlspecialchars($report['TieuDe'])) ?>', '<?= addslashes(htmlspecialchars($report['NoiDung'])) ?>', '<?= addslashes(htmlspecialchars($report['DiaChi'])) ?>', <?= $report['GiaPhong'] ?>, '<?= $report['NgayBaoCao'] ?>', <?= $report['UserID'] ?>, <?= $report['PostID'] ?>)">Chi tiết</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<!-- Modal chi tiết -->
<div id="detailModal" class="modal">
  <div class="modal-content">
    <span class="close-modal" onclick="closeDetail()">&times;</span>
    <h2>Chi Tiết Báo Cáo</h2>
    
    <div class="detail-row">
      <strong>ID Báo cáo:</strong>
      <div id="reportID"></div>
    </div>
    
    <div class="detail-row">
      <strong>Người báo cáo:</strong>
      <div id="reporterName"></div>
    </div>
    
    <div class="detail-row">
      <strong>Tiêu đề tin bị báo cáo:</strong>
      <div id="postTitle"></div>
    </div>
    
    <div class="detail-row">
      <strong>Địa chỉ:</strong>
      <div id="postAddress"></div>
    </div>
    
    <div class="detail-row">
      <strong>Giá phòng:</strong>
      <div id="postPrice"></div>
    </div>
    
    <div class="detail-row">
      <strong>Lý do báo cáo:</strong>
      <div id="reportReason"></div>
    </div>
    
    <div class="detail-row">
      <strong>Ngày báo cáo:</strong>
      <div id="reportDate"></div>
    </div>

    <div style="text-align: right; margin-top: 20px;">
      <button class="btn btn-success" onclick="resolveReport()">Xử lý</button>
      <button class="btn btn-danger" onclick="deleteReport()">Xóa báo cáo</button>
      <button class="btn btn-danger" onclick="deleteUser()" style="background: #856404 !important;">Xóa người dùng</button>
      <button class="btn btn-danger" onclick="deletePost()" style="background: #721c24 !important;">Xóa bài đăng</button>
      <button class="btn" onclick="closeDetail()">Đóng</button>
    </div>
  </div>
</div>

<script>
let currentReportID = null;
let currentUserID = null;
let currentPostID = null;

function openDetail(id, name, title, reason, address, price, date, userID, postID) {
  currentReportID = id;
  currentUserID = userID;
  currentPostID = postID;
  document.getElementById('reportID').textContent = id;
  document.getElementById('reporterName').textContent = name;
  document.getElementById('postTitle').textContent = title;
  document.getElementById('reportReason').textContent = reason;
  document.getElementById('postAddress').textContent = address;
  document.getElementById('postPrice').textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
  document.getElementById('reportDate').textContent = new Date(date).toLocaleDateString('vi-VN');
  
  document.getElementById('detailModal').style.display = 'block';
}

function resolveReport() {
  if (currentReportID) {
    window.location.href = '<?= BASE_URL ?>/index.php?page=admin/reports&action=resolve&id=' + currentReportID;
  }
}

function deleteReport() {
  if (currentReportID && confirm('Bạn chắc chắn muốn xóa báo cáo này?')) {
    window.location.href = '<?= BASE_URL ?>/index.php?page=admin/reports&action=delete&id=' + currentReportID;
  }
}

function deleteUser() {
  if (currentUserID && confirm('Bạn chắc chắn muốn xóa người dùng này? Hành động này không thể hoàn tác!')) {
    window.location.href = '<?= BASE_URL ?>/index.php?page=admin/reports&action=delete_user&user_id=' + currentUserID;
  }
}

function deletePost() {
  if (currentPostID && confirm('Bạn chắc chắn muốn xóa bài đăng này? Hành động này không thể hoàn tác!')) {
    window.location.href = '<?= BASE_URL ?>/index.php?page=admin/reports&action=delete_post&post_id=' + currentPostID;
  }
}

function closeDetail() {
  document.getElementById('detailModal').style.display = 'none';
  currentReportID = null;
}

window.onclick = function(event) {
  var modal = document.getElementById('detailModal');
  if (event.target == modal) {
    modal.style.display = 'none';
  }
}
</script>

</body>
</html>
