<?php

use PHPUnit\Framework\TestCase;

class DatabaseModelTest extends TestCase
{
    private ?PDO $sqlite = null;

    protected function setUp(): void
    {
        // Khởi tạo SQLite in-memory database
        $this->sqlite = new PDO('sqlite::memory:');
        $this->sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Tạo bảng user
        $this->sqlite->exec("
            CREATE TABLE user (
                UserID INTEGER PRIMARY KEY AUTOINCREMENT,
                HoTen TEXT NOT NULL,
                Email TEXT NOT NULL UNIQUE,
                MatKhau TEXT NOT NULL,
                SoDienThoai TEXT,
                DiaChi TEXT,
                TrangThai INTEGER DEFAULT 1,
                IsAdmin INTEGER DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Tạo bảng post
        $this->sqlite->exec("
            CREATE TABLE post (
                PostID INTEGER PRIMARY KEY AUTOINCREMENT,
                UserID INTEGER NOT NULL,
                TieuDe TEXT NOT NULL,
                MoTa TEXT,
                GiaPhong REAL NOT NULL,
                DienTich REAL NOT NULL,
                DiaChi TEXT NOT NULL,
                Latitude REAL,
                Longitude REAL,
                TrangThai TEXT DEFAULT 'ChoThue',
                VideoURL TEXT,
                NgayDang TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Tạo bảng phụ
        $this->sqlite->exec("
            CREATE TABLE hinhanh (
                HinhID INTEGER PRIMARY KEY AUTOINCREMENT,
                PostID INTEGER,
                URLAnh TEXT
            );
            CREATE TABLE favorites (
                FavoriteID INTEGER PRIMARY KEY AUTOINCREMENT,
                UserID INTEGER,
                PostID INTEGER
            );
            CREATE TABLE baocao (
                BaoCaoID INTEGER PRIMARY KEY AUTOINCREMENT,
                UserID INTEGER,
                PostID INTEGER,
                NoiDung TEXT,
                DaXuLy INTEGER DEFAULT 0
            );
        ");

        DB::setConnection($this->sqlite);
    }

    protected function tearDown(): void
    {
        DB::setConnection(null);
        $this->sqlite = null;
    }

    public function testUserExistsAndPhoneValidation()
    {
        $userModel = new User();

        // Ban đầu chưa có email
        $this->assertFalse($userModel->exists('Email', 'test@phongtroqn.com'));

        // Chèn 1 user mẫu
        DB::query("
            INSERT INTO user (HoTen, Email, MatKhau, SoDienThoai, DiaChi, TrangThai, IsAdmin)
            VALUES (?, ?, ?, ?, ?, 1, 0)
        ", ['Nguyễn Văn A', 'test@phongtroqn.com', password_hash('123456', PASSWORD_DEFAULT), '0912345678', 'Quy Nhơn']);

        // Sau khi chèn, exists() phải trả về true
        $this->assertTrue($userModel->exists('Email', 'test@phongtroqn.com'));
        $this->assertTrue($userModel->exists('SoDienThoai', '0912345678'));

        // Kiểm tra phoneExistsExcept
        $this->assertFalse($userModel->phoneExistsExcept('0912345678', 1)); // Chính user đó thì false
        $this->assertTrue($userModel->phoneExistsExcept('0912345678', 99)); // User khác thì true
    }

    public function testUserUpdateNameAndPhone()
    {
        $userModel = new User();

        DB::query("
            INSERT INTO user (HoTen, Email, MatKhau, SoDienThoai, DiaChi, TrangThai, IsAdmin)
            VALUES (?, ?, ?, ?, ?, 1, 0)
        ", ['Trần B', 'tranb@gmail.com', 'hash', '0900000001', 'Quy Nhơn']);

        $user = DB::query("SELECT * FROM user WHERE Email = 'tranb@gmail.com'")->fetch();
        $userId = (int)$user['UserID'];

        // Cập nhật tên
        $userModel->updateName($userId, 'Trần Văn B (Updated)');
        $updated = DB::query("SELECT HoTen FROM user WHERE UserID = ?", [$userId])->fetch();
        $this->assertEquals('Trần Văn B (Updated)', $updated['HoTen']);

        // Cập nhật SĐT
        $userModel->updatePhone($userId, '0988888888');
        $updatedPhone = DB::query("SELECT SoDienThoai FROM user WHERE UserID = ?", [$userId])->fetch();
        $this->assertEquals('0988888888', $updatedPhone['SoDienThoai']);
    }

    public function testPostCreateAndFetch()
    {
        $postModel = new Post();

        $postID = $postModel->create([
            'UserID'    => 1,
            'TieuDe'    => 'Phòng trọ cao cấp gần ĐH Quy Nhơn',
            'MoTa'      => 'Phòng mới xây thoáng mát, giờ giấc tự do.',
            'DiaChi'    => '123 An Dương Vương, Quy Nhơn',
            'DienTich'  => 25.5,
            'GiaPhong'  => 2000000,
            'Latitude'  => 13.762,
            'Longitude' => 109.223
        ]);

        $this->assertGreaterThan(0, $postID);

        $fetched = $postModel->getPostById($postID);
        $this->assertNotEmpty($fetched);
        $this->assertEquals('Phòng trọ cao cấp gần ĐH Quy Nhơn', $fetched['TieuDe']);
        $this->assertEquals(2000000, (float)$fetched['GiaPhong']);
        $this->assertEquals(1, (int)$fetched['TrangThai']);
    }

    public function testPostDeletionWithCascade()
    {
        $postModel = new Post();

        $postID = $postModel->create([
            'UserID'    => 1,
            'TieuDe'    => 'Tin chuẩn bị xóa',
            'MoTa'      => 'Mô tả',
            'DiaChi'    => 'Quy Nhơn',
            'DienTich'  => 20,
            'GiaPhong'  => 1500000
        ]);

        // Thêm ảnh và favorite liên quan
        $postModel->addImage($postID, '/uploads/posts/test.jpg');
        DB::query("INSERT INTO favorites (UserID, PostID) VALUES (1, ?)", [$postID]);
        DB::query("INSERT INTO baocao (UserID, PostID, NoiDung) VALUES (1, ?, 'Spam')", [$postID]);

        // Xóa tin bằng deletePost()
        $postModel->deletePost($postID);

        // Đảm bảo tin và các dữ liệu liên quan đã bị dọn dẹp
        $this->assertFalse($postModel->getPostById($postID));
        $this->assertEquals(0, DB::query("SELECT COUNT(*) FROM hinhanh WHERE PostID = ?", [$postID])->fetchColumn());
        $this->assertEquals(0, DB::query("SELECT COUNT(*) FROM favorites WHERE PostID = ?", [$postID])->fetchColumn());
        $this->assertEquals(0, DB::query("SELECT COUNT(*) FROM baocao WHERE PostID = ?", [$postID])->fetchColumn());
    }
}
