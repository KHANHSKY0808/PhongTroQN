<?php
// app/Views/admin/users.php
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    redirect('/');
    exit;
}
$users = $data['users'] ?? [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Admin</title>
    <style>
        /* Dùng lại hoàn toàn style từ file admin/index.php của bạn */
        body { margin:0; font-family:Arial; background:#FEF9EE; }
        .sidebar { width:260px; height:100vh; background:#E7F1FF; position:fixed; padding:30px 0; box-shadow:2px 0 10px rgba(0,0,0,0.05); }
        .sidebar h2 { text-align:center; color:#2d5aa0; margin-bottom:40px; }
        .sidebar a { display:block; padding:14px 30px; color:#2d5aa0; text-decoration:none; }
        .sidebar a:hover, .sidebar a.active { background:rgba(45,90,160,0.1); }
        .sidebar a.active { border-left:4px solid #2d5aa0; }
        .content { margin-left:260px; padding:30px; }
        .topbar { background:#fff; padding:20px 30px; border-radius:12px; margin-bottom:25px; box-shadow:0 2px 10px rgba(0,0,0,0.06); display:flex; justify-content:space-between; }
        table { width:100%; background:#fff; border-collapse:collapse; border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-top:20px; }
        th { background:#E7F1FF; color:#2d5aa0; padding:15px; text-align:left; }
        td { padding:12px 15px; border-bottom:1px solid #eee; }
        tr:hover { background:#f8fbff; }
        .btn { padding:6px 12px; background:#2d5aa0; color:#fff; border:none; border-radius:6px; text-decoration:none; font-size:14px; }
        .btn:hover { background:#1e40af; }
        .btn-danger { background:#dc3545; }
        .btn-danger:hover { background:#c82333; }
        .alert { padding:15px; border-radius:8px; margin-bottom:20px; }
        .alert-success { background:#d4edda; color:#155724; }
        .alert-danger { background:#f8d7da; color:#721c24; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Quản Trị Viên</h2>
    <a href="<?= BASE_URL ?>/index.php?page=admin">Tổng quan</a>
    <a href="<?= BASE_URL ?>/index.php?page=admin/users" class="active">Quản lý người dùng</a>
    <a href="#">Quản lý tin đăng</a>
    <a href="<?= BASE_URL ?>/index.php?page=admin/reports">Quản lý báo cáo</a>
    <a href="<?= BASE_URL ?>/">Về trang chủ</a>
</div>

<div class="content">
    <div class="topbar">
        <h2>Quản lý người dùng</h2>
        <div>Tổng: <?= count($users) ?> người dùng</div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Địa chỉ</th>
                <th>Ngày đăng ký</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['UserID'] ?></td>
                <td><?= htmlspecialchars($u['HoTen'] ?? 'Chưa đặt') ?></td>
                <td><?= htmlspecialchars($u['Email']) ?></td>
                <td><?= htmlspecialchars($u['SoDienThoai'] ?? '-') ?></td>
                <td><?= htmlspecialchars($u['DiaChi'] ?? '-') ?></td>
                <td><?= isset($u['created_at']) ? date('d/m/Y H:i', strtotime($u['created_at'])) : '-' ?></td>
                <td><?= $u['TrangThai'] == 1 ? '<span style="color:green">Hoạt động</span>' : '<span style="color:red">Khóa</span>' ?></td>
                <td>
                    <a href="?page=admin/users&action=delete&id=<?= $u['UserID'] ?>"
                       onclick="return confirm('Xóa người dùng này?\nTất cả tin đăng sẽ bị xóa!')">
                        <button class="btn btn-danger">Xóa</button>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
            <tr><td colspan="8" style="text-align:center; padding:40px; color:#999;">Chưa có người dùng nào</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>