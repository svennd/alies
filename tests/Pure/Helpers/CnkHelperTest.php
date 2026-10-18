<?php

declare(strict_types=1);

require_once APPPATH . 'helpers/cnk_helper.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T005 - application/helpers/cnk_helper.php: isValidCNK().
 *
 * A CNK is either V + 6 digits (veterinary), or 7 digits whose last digit is a check digit:
 * from the right the check digit counts as is, every second digit after it is doubled (minus 9
 * when above 9), and the sum must be a multiple of 10. The valid codes below were verified
 * independently of the helper.
 */
final class CnkHelperTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function validCodes(): array
    {
        return [
            'veterinary' => ['V123456'],
            'veterinary other digits' => ['V000001'],
            'seven digits 1' => ['4578753'],
            'seven digits 2' => ['3614765'],
            // digits doubled from the right: 6 -> 3, 4 -> 8, 2 -> 4; 3+5+8+3+4+1 = 24, check digit 6
            'seven digits 3' => ['1234566'],
            // the checksum of all zeros is 0
            'all zeros' => ['0000000'],
        ];
    }

    #[DataProvider('validCodes')]
    public function testAcceptsValidCodes(string $cnk): void
    {
        $this->assertTrue(isValidCNK($cnk));
    }

    /** @return array<string, array{string}> */
    public static function invalidCodes(): array
    {
        return [
            'wrong check digit' => ['1234567'],
            'check digit off by one' => ['4578754'],
            'six digits' => ['123456'],
            'eight digits' => ['12345678'],
            'letters' => ['ABC1234'],
            'empty' => [''],
            'veterinary lowercase' => ['v123456'],
            'veterinary too short' => ['V12345'],
            'veterinary too long' => ['V1234567'],
            'veterinary with letter' => ['V12345A'],
            'leading space' => [' 4578753'],
            'dash' => ['457-8753'],
        ];
    }

    #[DataProvider('invalidCodes')]
    public function testRejectsInvalidCodes(string $cnk): void
    {
        $this->assertFalse(isValidCNK($cnk));
    }

    public function testATrailingNewlineIsStillAccepted(): void
    {
        // quirk: in the patterns `$` also matches before a final newline, so "4578753\n" passes
        $this->assertTrue(isValidCNK("4578753\n"));
        $this->assertTrue(isValidCNK("V123456\n"));
    }
}
