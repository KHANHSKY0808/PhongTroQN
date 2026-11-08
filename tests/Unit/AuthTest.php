<?php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        // Xóa sạch session trước mỗi test
        $_SESSION = [];
    }

    public function testGuestState()
    {
        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::id());
        $this->assertNull(Auth::user());
        $this->assertFalse(Auth::isAdmin());
    }

    public function testLoginNormalUser()
    {
        $userData = [
            'UserID'      => 10,
            'HoTen'       => 'Nguyễn Văn A',
            'Email'       => 'user@example.com',
            'SoDienThoai' => '0901234567',
            'IsAdmin'     => 0
        ];

        Auth::login($userData);

        $this->assertTrue(Auth::check());
        $this->assertEquals(10, Auth::id());
        $this->assertEquals('Nguyễn Văn A', Auth::user()['HoTen']);
        $this->assertFalse(Auth::isAdmin());
    }

    public function testLoginAdminUser()
    {
        $adminData = [
            'UserID'      => 1,
            'HoTen'       => 'Quản Trị Viên',
            'Email'       => 'admin@phongtroqn.com',
            'SoDienThoai' => '0987654321',
            'IsAdmin'     => 1
        ];

        Auth::login($adminData);

        $this->assertTrue(Auth::check());
        $this->assertEquals(1, Auth::id());
        $this->assertTrue(Auth::isAdmin());
    }

    public function testLogoutClearsSession()
    {
        Auth::login([
            'UserID'  => 5,
            'HoTen'   => 'User B',
            'IsAdmin' => 0
        ]);

        $this->assertTrue(Auth::check());

        Auth::logout();

        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::id());
        $this->assertNull(Auth::user());
    }
}

