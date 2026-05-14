<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CnkHelperTest extends TestCase
{
    public function testAcceptsVeterinaryCnks(): void
    {
        $this->assertTrue(isValidCNK('V123456'));
    }

    public function testAcceptsKnownValidSevenDigitCnks(): void
    {
        $this->assertTrue(isValidCNK('4578753'));
        $this->assertTrue(isValidCNK('3614765'));
    }

    public function testRejectsMalformedOrInvalidCnks(): void
    {
        $this->assertFalse(isValidCNK('1234567'));
        $this->assertFalse(isValidCNK('123456'));
        $this->assertFalse(isValidCNK('ABC1234'));
    }
}
