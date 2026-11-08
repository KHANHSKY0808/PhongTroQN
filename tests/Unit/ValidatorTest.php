<?php

use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testRequiredRuleFailsOnEmptyOrWhitespace()
    {
        $validator = Validator::make(['title' => '   '], ['title' => 'required']);
        $this->assertTrue($validator->fails());
        $this->assertFalse($validator->passes());

        $validatorValid = Validator::make(['title' => 'Phòng trọ đẹp'], ['title' => 'required']);
        $this->assertTrue($validatorValid->passes());
    }

    public function testNumericAndMinMaxRules()
    {
        // Giá quá thấp
        $v1 = Validator::make(['gia' => '50000'], ['gia' => 'required|numeric|min:100000']);
        $this->assertTrue($v1->fails());

        // Giá hợp lệ
        $v2 = Validator::make(['gia' => '1500000'], ['gia' => 'required|numeric|min:100000|max:100000000']);
        $this->assertTrue($v2->passes());

        // Không phải số
        $v3 = Validator::make(['gia' => 'abc'], ['gia' => 'numeric']);
        $this->assertTrue($v3->fails());
    }

    public function testStringLengthRules()
    {
        $v1 = Validator::make(['title' => 'Ngắn'], ['title' => 'min_len:10']);
        $this->assertTrue($v1->fails());

        $v2 = Validator::make(['title' => 'Cho thuê phòng trọ Quy Nhơn'], ['title' => 'min_len:10|max_len:50']);
        $this->assertTrue($v2->passes());
    }

    public function testCoordinatesValidation()
    {
        $vValid = Validator::make([
            'latitude' => '13.7825',
            'longitude' => '109.2215'
        ], [
            'latitude' => 'latitude',
            'longitude' => 'longitude'
        ]);
        $this->assertTrue($vValid->passes());

        // Vĩ độ ngoài khoảng [-90, 90]
        $vInvalid = Validator::make(['latitude' => '95.5'], ['latitude' => 'latitude']);
        $this->assertTrue($vInvalid->fails());
    }

    public function testVideoUrlValidation()
    {
        // Hợp lệ: YouTube & TikTok
        $validUrls = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ',
            'https://www.tiktok.com/@user/video/1234567890',
            'http://vm.tiktok.com/ABC123xyz'
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue(Validator::isValidVideoUrl($url), "Failed asserting that $url is a valid video URL");
        }

        // Bất hợp pháp: Phishing / XSS / domain khác
        $invalidUrls = [
            'https://evil-phishing.com/video',
            'javascript:alert(1)',
            'http://attacker.com/youtube.com',
            'ftp://youtube.com',
            'not-a-url'
        ];

        foreach ($invalidUrls as $url) {
            $this->assertFalse(Validator::isValidVideoUrl($url), "Failed asserting that $url is rejected");
        }
    }

    public function testPhoneAndEmailValidation()
    {
        $vPhoneValid = Validator::make(['phone' => '0912345678'], ['phone' => 'phone']);
        $this->assertTrue($vPhoneValid->passes());

        $vPhoneInvalid = Validator::make(['phone' => '12345'], ['phone' => 'phone']);
        $this->assertTrue($vPhoneInvalid->fails());

        $vEmailValid = Validator::make(['email' => 'admin@phongtroqn.com'], ['email' => 'email']);
        $this->assertTrue($vEmailValid->passes());

        $vEmailInvalid = Validator::make(['email' => 'not-an-email'], ['email' => 'email']);
        $this->assertTrue($vEmailInvalid->fails());
    }
}
