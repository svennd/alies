<?php

declare(strict_types=1);

require_once APPPATH . 'helpers/generate_bill_id_helper.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * T002 + T003 - generate_struct_message(): the Belgian structured message (OGM, +++xxx/xxxx/xxxxx+++).
 *
 * The 12 digits are a 10 digit message followed by 2 check digits: message mod 97, where 0 becomes 97.
 * Expected values below were worked out by hand (message, then message mod 97), not with the helper.
 *
 * CLIENT_BILL = 0, CLIENT = 1, CLIENT_3DIGIT_BILL = 2 (application/config/constants.php).
 */
final class StructMessageTest extends TestCase
{
    /** @return array<string, array{int, int, int, string}> */
    public static function messages(): array
    {
        return [
            // 12300000 + 45 = 1230000045; 1230000045 mod 97 = 81
            'client and bill' => [123, 45, CLIENT_BILL, '+++123/0000/04581+++'],
            // 1230000000 mod 97 = 36
            'client only' => [123, 45, CLIENT, '+++123/0000/00036+++'],
            // 1230000 + 045 = 1230000045, same message as above: 81
            'client and 3 digit bill' => [123, 45, CLIENT_3DIGIT_BILL, '+++123/0000/04581+++'],
            // 123456 + 78901 is 11 digits, too long: falls back to the client alone, 1234560000 mod 97 = 66
            'too long falls back to client' => [123456, 78901, CLIENT_BILL, '+++123/4560/00066+++'],
            // 1000000005 mod 97 = 39
            'single digits' => [1, 5, CLIENT_BILL, '+++100/0000/00539+++'],
            // 1000000063 mod 97 = 0, which becomes 97
            'remainder 0 becomes 97' => [1, 63, CLIENT_BILL, '+++100/0000/06397+++'],
            // 1234000000 mod 97 = 47
            'larger client, client only' => [1234, 56, CLIENT, '+++123/4000/00047+++'],
            // 9999900999 mod 97 = 85
            'nine digit message' => [99999, 999, CLIENT_BILL, '+++999/9900/99985+++'],
            // 7000000 + 001 = 7000000001 mod 97 = 45
            '3 digit bill is left padded' => [7, 1, CLIENT_3DIGIT_BILL, '+++700/0000/00145+++'],
        ];
    }

    #[DataProvider('messages')]
    public function testStructuredMessage(int $client, int $bill, int $mode, string $expected): void
    {
        $this->assertSame($expected, generate_struct_message($client, $bill, $mode));
    }

    public function testDefaultModeIsClientAndBill(): void
    {
        $this->assertSame(generate_struct_message(123, 45, CLIENT_BILL), generate_struct_message(123, 45));
    }

    public function testFormatIsAlwaysThreeFourFiveDigitsBetweenPlusSigns(): void
    {
        foreach ([[1, 1], [123, 45], [123456, 78901], [999999, 99999]] as [$client, $bill]) {
            $this->assertMatchesRegularExpression('#^\+\+\+\d{3}/\d{4}/\d{5}\+\+\+$#', generate_struct_message($client, $bill));
        }
    }

    /**
     * For check values of 10 and above the output is a valid OGM: the last two digits are the
     * remainder of the first ten digits divided by 97. (Values 1-9 are wrong, see the known-bug test.)
     */
    public function testCheckDigitsAreTheRemainderModuloNinetySevenWhenTheyHaveTwoDigits(): void
    {
        mt_srand(20260418);
        $checked = 0;

        for ($i = 0; $i < 200; $i++) {
            $client = mt_rand(1, 999999);
            $bill = mt_rand(1, 99999);
            $digits = preg_replace('/\D/', '', generate_struct_message($client, $bill));
            $this->assertSame(12, strlen($digits));

            $remainder = (int) substr($digits, 0, 10) % 97;
            $expected = $remainder === 0 ? 97 : $remainder;
            if ($expected >= 10) {
                $this->assertSame($expected, (int) substr($digits, 10), "client $client bill $bill");
                $checked++;
            }
        }

        $this->assertGreaterThan(150, $checked, 'the property should have been checked for most pairs');
    }

    /**
     * T003. A remainder of 1 to 9 must be written with a leading zero ("01"), but the helper pads on
     * the right ("10"), so the check digits are wrong for roughly one message in ten.
     */
    #[Group('known-bug')]
    public function testCheckValueBelowTenIsPaddedOnTheWrongSide(): void
    {
        // KNOWN BUG: right padded check value - decide in CI4 whether to keep
        // 1000000064 mod 97 = 1: a correct OGM ends in 01, this one ends in 10
        $this->assertSame('+++100/0000/06410+++', generate_struct_message(1, 64, CLIENT_BILL));

        // 1234000056 mod 97 = 6: correct would be 06, the helper gives 60
        $this->assertSame('+++123/4000/05660+++', generate_struct_message(1234, 56, CLIENT_BILL));
        $this->assertSame('+++123/4000/05660+++', generate_struct_message(1234, 56, CLIENT_3DIGIT_BILL));
    }
}
