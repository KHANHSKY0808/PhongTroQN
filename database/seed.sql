-- =====================================================================
-- DATABASE SEED DATA: PhongTroQN
-- Du lieu mau phuc vu test, demo CV va chay lai he thong
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `baocao`;
TRUNCATE TABLE `favorites`;
TRUNCATE TABLE `hinhanh`;
TRUNCATE TABLE `post`;
TRUNCATE TABLE `user_role`;
TRUNCATE TABLE `vaitro`;
TRUNCATE TABLE `user`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. SEED VAI TRO
INSERT INTO `vaitro` (`RoleID`, `TenVaiTro`) VALUES
(1, 'Admin'),
(2, 'Chủ trọ'),
(3, 'Người thuê');

-- 2. SEED NGUOI DUNG
-- Mat khau admin: admin123 ($2y$10$7Su5oZwQgD3T0ZMFVvLJWehBW3ZqjpzQBYVg300Sog5IaiPsMG.g2)
-- Mat khau user: 123456 ($2y$10$I3bqASQLDL6WQGV5rZDEhe/DFRE8iimxQz/4SzNaM3smkxe3DhR2G)
INSERT INTO `user` (`UserID`, `HoTen`, `Email`, `MatKhau`, `SoDienThoai`, `DiaChi`, `TrangThai`, `IsAdmin`, `created_at`) VALUES
(1, 'Ban Quản Trị PhongTroQN', 'admin@phongtroqn.com', '$2y$10$7Su5oZwQgD3T0ZMFVvLJWehBW3ZqjpzQBYVg300Sog5IaiPsMG.g2', '0905123456', '01 Trần Phú, TP. Quy Nhơn, Bình Định', 1, 1, NOW()),
(2, 'Nguyễn Văn An (Chủ trọ)', 'chutro@gmail.com', '$2y$10$I3bqASQLDL6WQGV5rZDEhe/DFRE8iimxQz/4SzNaM3smkxe3DhR2G', '0912345678', '45 Ngô Mây, TP. Quy Nhơn, Bình Định', 1, 0, NOW()),
(3, 'Trần Thị Bình (Sinh viên)', 'user@gmail.com', '$2y$10$I3bqASQLDL6WQGV5rZDEhe/DFRE8iimxQz/4SzNaM3smkxe3DhR2G', '0987654321', '120 Nguyễn Thái Học, TP. Quy Nhơn, Bình Định', 1, 0, NOW());

-- 3. SEED USER_ROLE
INSERT INTO `user_role` (`UserID`, `RoleID`) VALUES
(1, 1),
(2, 2),
(3, 3);

-- 4. SEED BAI DANG PHONG TRO (Quy Nhon, Binh Dinh)
INSERT INTO `post` (`PostID`, `UserID`, `TieuDe`, `MoTa`, `DiaChi`, `DienTich`, `GiaPhong`, `Latitude`, `Longitude`, `TrangThai`, `NgayDang`, `Video`) VALUES
(1, 2, 'Phòng trọ cao cấp gần Đại học Quy Nhơn, đầy đủ tiện nghi', 'Phòng trọ mới xây, có gác lửng đúc kiên cố, kệ bếp nấu ăn, máy lạnh Inverter, toilet khép kín sạch sẽ. Giờ giấc tự do, không chung chủ, có camera an ninh 24/7, wifi tốc độ cao miễn phí.', '68 Ngô Mây, Phường Ngô Mây, TP. Quy Nhơn, Bình Định', 25.00, 2200000.00, 13.759230, 109.217890, 1, NOW() - INTERVAL 1 DAY, NULL),
(2, 2, 'Căn hộ mini full nội thất view biển Quy Nhơn cực đẹp', 'Căn hộ mini 1 phòng ngủ, ban công thoáng mát, view biển lộng gió. Nội thất gồm giường nệm, tủ quần áo, tủ lạnh, máy giặt, máy lạnh, bếp từ. Phù hợp cho chuyên gia hoặc vợ chồng trẻ.', '12 An Dương Vương, Phường Nguyễn Văn Cừ, TP. Quy Nhơn, Bình Định', 40.00, 4500000.00, 13.753820, 109.218540, 1, NOW() - INTERVAL 2 DAY, NULL),
(3, 2, 'Phòng trọ sinh viên giá rẻ đường Nguyễn Thái Học', 'Cho thuê phòng trọ giá sinh viên, sạch sẽ, thoáng mát, khu dân cư an ninh yên tĩnh. Gần chợ, bến xe buýt, các trường cao đẳng và đại học. Điện nước tính theo công tơ giá nhà nước.', '180 Nguyễn Thái Học, Phường Ngô Mây, TP. Quy Nhơn, Bình Định', 18.00, 1300000.00, 13.765140, 109.222300, 1, NOW() - INTERVAL 3 DAY, NULL),
(4, 2, 'Nhà nguyên căn 2 tầng thích hợp ở nhóm hoặc gia đình', 'Nhà nguyên căn 1 trệt 1 lầu, 3 phòng ngủ, 2 toilet, phòng khách và bếp rộng rãi. Có sân để xe máy an toàn, khu vực không ngập nước. Ưu tiên hợp đồng thuê lâu dài từ 1 năm.', '25 Hoàng Văn Thụ, Phường Quang Trung, TP. Quy Nhơn, Bình Định', 65.00, 6000000.00, 13.771120, 109.213450, 1, NOW() - INTERVAL 4 DAY, NULL),
(5, 2, 'Phòng trọ có gác lửng, không chung chủ đường Tây Sơn', 'Phòng trọ có ban công riêng, gác cao không đụng đầu, có chỗ phơi đồ riêng biệt. Khóa vân tay ra vào, hầm để xe có bảo vệ trực đêm. Tiền rác và wifi miễn phí.', '310 Tây Sơn, Phường Quang Trung, TP. Quy Nhơn, Bình Định', 22.00, 1800000.00, 13.762890, 109.208760, 1, NOW() - INTERVAL 5 DAY, NULL),
(6, 2, 'Ở ghép phòng trọ rộng rãi, tiện nghi gần Chợ Lớn Quy Nhơn', 'Tìm 1 bạn nam/nữ ở ghép share tiền phòng. Phòng hiện đã có sẵn tủ lạnh, máy giặt, máy lạnh. Bạn chỉ cần xách vali vào ở. Tính tình hòa đồng, sạch sẽ, giữ gìn vệ sinh chung.', '92 Trần Hưng Đạo, Phường Lê Lợi, TP. Quy Nhơn, Bình Định', 30.00, 950000.00, 13.775430, 109.231200, 1, NOW() - INTERVAL 6 DAY, NULL);

-- 5. SEED HINH ANH BAI DANG
INSERT INTO `hinhanh` (`HinhID`, `PostID`, `URLAnh`) VALUES
(1, 1, '/public/assets/img/noimage.png'),
(2, 2, '/public/assets/img/noimage.png'),
(3, 3, '/public/assets/img/noimage.png'),
(4, 4, '/public/assets/img/noimage.png'),
(5, 5, '/public/assets/img/noimage.png'),
(6, 6, '/public/assets/img/noimage.png');

-- 6. SEED TIN YEU THICH
INSERT INTO `favorites` (`FavoriteID`, `UserID`, `PostID`, `NgayLuu`) VALUES
(1, 3, 1, NOW() - INTERVAL 12 HOUR),
(2, 3, 2, NOW() - INTERVAL 6 HOUR);

-- 7. SEED BAO CAO VI PHAM
INSERT INTO `baocao` (`BaoCaoID`, `UserID`, `PostID`, `NoiDung`, `NgayBaoCao`, `DaXuLy`) VALUES
(1, 3, 4, 'Tin đăng ghi còn phòng nhưng khi gọi điện chủ báo đã cho thuê tuần trước.', NOW() - INTERVAL 1 DAY, 0);
