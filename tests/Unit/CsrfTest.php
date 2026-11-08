<?php

use PHPUnit\Framework\TestCase;

class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        unset($_SESSION['csrf_token']);
    }

    public function testTokenGeneratesRandomString()
    {
        $token1 = Csrf::token();
        $this->assertNotEmpty($token1);
        $this->assertEquals(64, strlen($token1)); // 32 bytes hex = 64 characters

        // Gọi lại phải trả về cùng token trong session
        $token2 = Csrf::token();
        $this->assertEquals($token1, $token2);
    }

    public function testFieldGeneratesHiddenInput()
    {
        $field = Csrf::field();
        $this->assertStringContainsString('type="hidden"', $field);
        $this->assertStringContainsString('name="csrf_token"', $field);
        $this->assertStringContainsString('value="' . Csrf::token() . '"', $field);
    }

    public function testValidateAcceptsValidTokenAndRejectsInvalid()
    {
        $validToken = Csrf::token();
        $this->assertTrue(Csrf::validate($validToken));

        $this->assertFalse(Csrf::validate('invalid_token_123456'));
        $this->assertFalse(Csrf::validate(null));
        $this->assertFalse(Csrf::validate(''));
    }
}
