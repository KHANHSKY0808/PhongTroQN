-- =====================================================================
-- DATABASE SCHEMA: PhongTroQN
-- He thong tim kiem va dang tin phong tro Quy Nhon
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `baocao`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `hinhanh`;
DROP TABLE IF EXISTS `post`;
DROP TABLE IF EXISTS `user_role`;
DROP TABLE IF EXISTS `vaitro`;
DROP TABLE IF EXISTS `user`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. BANG NGUOI DUNG (user)
CREATE TABLE `user` (
    `UserID` INT AUTO_INCREMENT PRIMARY KEY,
    `HoTen` VARCHAR(100) NOT NULL,
    `Email` VARCHAR(100) NOT NULL,
    `MatKhau` VARCHAR(255) NOT NULL,
    `SoDienThoai` VARCHAR(20) DEFAULT NULL,
    `DiaChi` VARCHAR(255) DEFAULT NULL,
    `TrangThai` TINYINT NOT NULL DEFAULT 1 COMMENT '1: Hoat dong, 0: Khoa',
    `IsAdmin` TINYINT NOT NULL DEFAULT 0 COMMENT '0: Nguoi dung, 1: Quan tri vien',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_user_email` UNIQUE (`Email`),
    INDEX `idx_user_phone` (`SoDienThoai`),
    INDEX `idx_user_status` (`TrangThai`, `IsAdmin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BANG VAI TRO (vaitro)
CREATE TABLE `vaitro` (
    `RoleID` INT AUTO_INCREMENT PRIMARY KEY,
    `TenVaiTro` VARCHAR(50) NOT NULL,
    CONSTRAINT `uq_vaitro_ten` UNIQUE (`TenVaiTro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BANG PHAN QUYEN NGUOI DUNG (user_role)
CREATE TABLE `user_role` (
    `UserID` INT NOT NULL,
    `RoleID` INT NOT NULL,
    PRIMARY KEY (`UserID`, `RoleID`),
    CONSTRAINT `fk_userrole_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_userrole_role` FOREIGN KEY (`RoleID`) REFERENCES `vaitro` (`RoleID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BANG BAI DANG PHONG TRO (post)
CREATE TABLE `post` (
    `PostID` INT AUTO_INCREMENT PRIMARY KEY,
    `UserID` INT NOT NULL,
    `TieuDe` VARCHAR(255) NOT NULL,
    `MoTa` TEXT DEFAULT NULL,
    `DiaChi` VARCHAR(255) NOT NULL,
    `DienTich` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `GiaPhong` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `Latitude` DECIMAL(10,8) DEFAULT NULL,
    `Longitude` DECIMAL(11,8) DEFAULT NULL,
    `TrangThai` TINYINT NOT NULL DEFAULT 1 COMMENT '1: Hien thi, 0: An',
    `NgayDang` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `Video` VARCHAR(255) DEFAULT NULL,
    CONSTRAINT `fk_post_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_post_filter` (`TrangThai`, `GiaPhong`, `DienTich`, `NgayDang`),
    INDEX `idx_post_user` (`UserID`),
    INDEX `idx_post_created` (`NgayDang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. BANG HINH ANH BAI DANG (hinhanh)
CREATE TABLE `hinhanh` (
    `HinhID` INT AUTO_INCREMENT PRIMARY KEY,
    `PostID` INT NOT NULL,
    `URLAnh` VARCHAR(255) NOT NULL,
    CONSTRAINT `fk_hinhanh_post` FOREIGN KEY (`PostID`) REFERENCES `post` (`PostID`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_hinhanh_post` (`PostID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. BANG TIN YEU THICH (favorites)
CREATE TABLE `favorites` (
    `FavoriteID` INT AUTO_INCREMENT PRIMARY KEY,
    `UserID` INT NOT NULL,
    `PostID` INT NOT NULL,
    `NgayLuu` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_favorite_user_post` UNIQUE (`UserID`, `PostID`),
    CONSTRAINT `fk_favorites_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_favorites_post` FOREIGN KEY (`PostID`) REFERENCES `post` (`PostID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. BANG BAO CAO VI PHAM (baocao)
CREATE TABLE `baocao` (
    `BaoCaoID` INT AUTO_INCREMENT PRIMARY KEY,
    `UserID` INT NOT NULL,
    `PostID` INT NOT NULL,
    `NoiDung` TEXT NOT NULL,
    `NgayBaoCao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `DaXuLy` TINYINT NOT NULL DEFAULT 0 COMMENT '0: Chua xu ly, 1: Da xu ly',
    CONSTRAINT `fk_baocao_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_baocao_post` FOREIGN KEY (`PostID`) REFERENCES `post` (`PostID`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_baocao_status` (`DaXuLy`, `NgayBaoCao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
