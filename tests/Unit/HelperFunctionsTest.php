<?php

use PHPUnit\Framework\TestCase;

class HelperFunctionsTest extends TestCase
{
    public function testVnStrFilterRemovesVietnameseAccents()
    {
        $input = "Phòng Trọ Quy Nhơn Giá Rẻ Nhất 2026! Đầy Đủ Tiện Nghi";
        $expected = "phong tro quy nhon gia re nhat 2026! day du tien nghi";
        $this->assertEquals($expected, vn_str_filter($input));
    }

    public function testMoneyFormat()
    {
        $this->assertEquals("1.500.000 đ", money(1500000));
        $this->assertEquals("0 đ", money(0));
        $this->assertEquals("25.000.000 đ", money(25000000));
    }

    public function testEFunctionEscapesHtmlSpecialCharacters()
    {
        $malicious = '<script>alert("XSS")</script>';
        $escaped = e($malicious);
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }

    public function testFlashMessages()
    {
        setFlash('test_key', 'Xin chào test');
        $this->assertTrue(hasFlash('test_key'));
        
        $value = getFlash('test_key');
        $this->assertEquals('Xin chào test', $value);

        // Flash message chỉ hiển thị một lần (bị xóa sau khi get)
        $this->assertNull(getFlash('test_key'));
        $this->assertFalse(hasFlash('test_key'));
    }
}

