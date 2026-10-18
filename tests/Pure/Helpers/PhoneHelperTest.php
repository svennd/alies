<?php

declare(strict_types=1);

require_once APPPATH . 'helpers/phone_helper.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T006 - application/helpers/phone_helper.php: format_phone() and print_phone().
 *
 * format_phone() normalises user input before searching: it removes spaces, "+", "." and "/",
 * then cuts off anything that is not a digit at the END. Dashes and letters inside stay.
 * print_phone() inserts spaces for display, based on the number of digits.
 */
final class PhoneHelperTest extends TestCase
{
    /** @return array<string, array{mixed, string|false}> */
    public static function formatCases(): array
    {
        return [
            'spaces' => ['0470 12 34 56', '0470123456'],
            'international with plus, spaces, slash and dots' => ['+32 3/123.45.67', '3231234567'],
            'a dash inside is kept' => ['03-123 45 67', '03-1234567'],
            // the space goes first, so "(gsm)" becomes one trailing run of non-digits and is cut off
            'trailing text in brackets' => ['0470123456 (gsm)', '0470123456'],
            'trailing dash' => ['0470-', '0470'],
            'trailing space' => ['0470123456 ', '0470123456'],
            'brackets around the prefix stay' => ['(0470)123456', '(0470)123456'],
            'only letters become an empty string' => ['abc', ''],
            'letters inside, letter at the end' => ['12ab34x', '12ab34'],
            'already clean' => ['0470123456', '0470123456'],
            'empty string' => ['', false],
            'null' => [null, false],
            'the string zero counts as empty' => ['0', false],
            'the number zero counts as empty' => [0, false],
        ];
    }

    #[DataProvider('formatCases')]
    public function testFormatPhone(mixed $input, string|false $expected): void
    {
        $this->assertSame($expected, format_phone($input));
    }

    /** @return array<string, array{string, string}> */
    public static function printCases(): array
    {
        return [
            'ten digits (mobile)' => ['0470123456', '0470 12 34 56'],
            'nine digits antwerp prefix 03' => ['031234567', '03 123 45 67'],
            'nine digits brussels prefix 02' => ['021234567', '02 123 45 67'],
            'nine digits prefix 04' => ['041234567', '04 123 45 67'],
            'nine digits other prefix' => ['091234567', '091 23 45 67'],
            'eleven digits (international)' => ['32470123456', '32 470 12 34 56'],
            'five digits unchanged' => ['12345', '12345'],
            'twelve digits unchanged' => ['324701234567', '324701234567'],
            'ten characters that are not all digits stay unchanged' => ['04701234ab', '04701234ab'],
            'empty' => ['', ''],
            'the string zero counts as empty' => ['0', ''],
        ];
    }

    #[DataProvider('printCases')]
    public function testPrintPhone(string $input, string $expected): void
    {
        $this->assertSame($expected, print_phone($input));
    }

    public function testPrintPhoneDoesNotAcceptNull(): void
    {
        $this->expectException(TypeError::class);

        print_phone(null);
    }

    public function testFormatThenPrintRoundTrip(): void
    {
        $this->assertSame('0470 12 34 56', print_phone(format_phone('+0470/12.34.56')));
    }
}
