<?php

declare(strict_types=1);

require_once APPPATH . 'helpers/gs1_helper.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T004 - application/helpers/gs1_helper.php: gs1(), gtincheck(), gs1_get_due_date(), parse_gs1().
 *
 * GS1 application identifiers used: 01 GTIN (14 digits, check digit), 10 lot, 17 expiry (YYMMDD),
 * 21 serial. "[GS]" is what the scanner sends between elements of variable length.
 * GTIN 05414736002828 is a valid code (checked independently, mod 10 with weights 3 and 1).
 */
final class Gs1HelperTest extends TestCase
{
    private const GTIN = '05414736002828';

    // ---- gs1() ------------------------------------------------------------------------------

    public function testGs1ParsesAiSegmentsAndExtractsDueDate(): void
    {
        $parsed = gs1('01054147360028281722080010B449703');

        $this->assertSame(
            [
                'GTIN' => '05414736002828',
                'EXP_DATE' => '2022-08-31',
                'LOTNR' => 'B449703',
            ],
            $parsed
        );
        $this->assertSame('2022-08-31', gs1_get_due_date($parsed));
    }

    public function testBracketsAndSpacesAreIgnored(): void
    {
        $plain = gs1('01054147360028281722080010B449703');

        $this->assertSame($plain, gs1('(01)05414736002828(17)220800(10)B449703'));
        $this->assertSame($plain, gs1('(01) 05414736002828 (17) 220800 (10) B449703'));
    }

    public function testGroupSeparatorsSplitElementsOfVariableLength(): void
    {
        $parsed = gs1('010084016450365421139167056223[GS]1725022810W009516');

        $this->assertSame(
            [
                'GTIN' => '00840164503654',
                'SERIAL' => '139167056223',
                'EXP_DATE' => '2025-02-28',
                'LOTNR' => 'W009516',
            ],
            $parsed
        );
    }

    public function testWithoutTextTheRawApplicationIdentifiersAreTheKeys(): void
    {
        $this->assertSame(
            [
                '01' => '05414736002828',
                '17' => '2022-08-31',
                '10' => 'B449703',
            ],
            gs1('01054147360028281722080010B449703', false)
        );
    }

    public function testAllDateIdentifiersAreRecognised(): void
    {
        $parsed = gs1('(01)' . self::GTIN . '(11)240102(12)240203(13)240304(15)240405(16)240506(17)240607');

        $this->assertSame('2024-01-02', $parsed['PROD_DATE']);
        $this->assertSame('2024-02-03', $parsed['DUE_DATE']);
        $this->assertSame('2024-03-04', $parsed['PACK_DATE']);
        $this->assertSame('2024-04-05', $parsed['BEST_BEFORE_DATE']);
        $this->assertSame('2024-05-06', $parsed['SELL_BY_DATE']);
        $this->assertSame('2024-06-07', $parsed['EXP_DATE']);
    }

    public function testProductVariantTakesTwoDigits(): void
    {
        $this->assertSame(['GTIN' => self::GTIN, 'PROD_VAR' => '42'], gs1('01' . self::GTIN . '2042'));
    }

    /** @return array<string, array{string, string}> */
    public static function dayZeroDates(): array
    {
        return [
            'february 2021' => ['210200', '2021-02-28'],
            'leap year february' => ['240200', '2024-02-29'],
            'thirty day month' => ['250400', '2025-04-30'],
            'thirty one day month' => ['211000', '2021-10-31'],
            'a real day is kept' => ['250415', '2025-04-15'],
        ];
    }

    #[DataProvider('dayZeroDates')]
    public function testDayZeroMeansTheLastDayOfTheMonth(string $yymmdd, string $expected): void
    {
        $this->assertSame($expected, gs1('01' . self::GTIN . '17' . $yymmdd . '10L1')['EXP_DATE']);
    }

    public function testExistingLotWithDayZeroInTheDemoData(): void
    {
        $parsed = gs1('010341111207988917210200103145600');

        $this->assertSame('2021-02-28', $parsed['EXP_DATE']);
        $this->assertSame('3145600', $parsed['LOTNR']);
    }

    public function testWithoutGroupSeparatorALotNumberSwallowsTheElementsAfterIt(): void
    {
        // a lot (AI 10) takes up to 20 characters, so without [GS] the expiry date that follows
        // is read as part of the lot number and no EXP_DATE is found
        $parsed = gs1('01' . self::GTIN . '10ABC17220800');

        $this->assertSame(['GTIN' => self::GTIN, 'LOTNR' => 'ABC17220800'], $parsed);
        $this->assertArrayNotHasKey('EXP_DATE', $parsed);
    }

    public function testInvalidGtinGivesAnEmptyResult(): void
    {
        $this->assertSame([], gs1('010123456789012310ABCD17YYMMDD21ABCD'));
        // same barcode as the valid one, last digit of the GTIN changed from 8 to 9
        $this->assertSame([], gs1('01054147360028291722080010B449703'));
    }

    public function testUnknownApplicationIdentifierGivesAnEmptyResult(): void
    {
        $this->assertSame([], gs1('9912345'));
        // one unknown element spoils the whole code, the valid GTIN in front is not returned
        $this->assertSame([], gs1('01' . self::GTIN . '99123'));
    }

    public function testEmptyInputGivesAnEmptyResult(): void
    {
        $this->assertSame([], gs1(''));
    }

    // ---- gtincheck() ------------------------------------------------------------------------

    /** @return array<string, array{string, bool}> */
    public static function gtins(): array
    {
        return [
            'valid 1' => ['05414736002828', true],
            'valid 2' => ['00840164503654', true],
            'valid 3' => ['05414567000017', true],
            'check digit off by one' => ['05414736002829', false],
            'sequence with wrong check digit' => ['01234567890123', false],
        ];
    }

    #[DataProvider('gtins')]
    public function testGtinCheckDigit(string $gtin, bool $valid): void
    {
        $this->assertSame($valid, gtincheck($gtin));
    }

    // ---- gs1_get_due_date() -----------------------------------------------------------------

    public function testDueDatePrefersExpiryOverTheOtherDates(): void
    {
        $all = [
            'SELL_BY_DATE' => '2024-04-04',
            'BEST_BEFORE_DATE' => '2024-03-03',
            'DUE_DATE' => '2024-02-02',
            'EXP_DATE' => '2024-01-01',
        ];

        $this->assertSame('2024-01-01', gs1_get_due_date($all));
        unset($all['EXP_DATE']);
        $this->assertSame('2024-02-02', gs1_get_due_date($all));
        unset($all['DUE_DATE']);
        $this->assertSame('2024-03-03', gs1_get_due_date($all));
        unset($all['BEST_BEFORE_DATE']);
        $this->assertSame('2024-04-04', gs1_get_due_date($all));
    }

    public function testDueDateWithoutAnyKnownDateIsAnEmptyString(): void
    {
        // the function is declared to return string but ends with `return false`;
        // without strict types that becomes ""
        $this->assertSame('', gs1_get_due_date([]));
        $this->assertSame('', gs1_get_due_date(['PROD_DATE' => '2024-01-01', 'LOTNR' => 'A']));
    }

    // ---- parse_gs1() (older parser, different rules) ----------------------------------------

    public function testParseGs1SupportsLegacyFormatWithDateBeforeLot(): void
    {
        $this->assertSame(
            [
                'date' => '2021-10-01',
                'lotnr' => '19KQ173',
                'pid' => '05420036936138',
            ],
            parse_gs1('0105420036936138172110001019KQ173')
        );
    }

    public function testParseGs1SupportsTheFormatWithLotBeforeDateAndSerial(): void
    {
        $this->assertSame(
            ['date' => '2021-10-15', 'lotnr' => 'LOT123', 'pid' => '05420036936138'],
            parse_gs1('0105420036936138' . '10LOT123' . '17211015' . '21SER9')
        );
    }

    public function testParseGs1UsesTheFirstDayWhereGs1UsesTheLastDay(): void
    {
        $barcode = '0105420036936138172110001019KQ173';

        // same barcode, day "00": parse_gs1 says the 1st, gs1() says the last day of the month
        $this->assertSame('2021-10-01', parse_gs1($barcode)['date']);
        $this->assertSame('2021-10-31', gs1($barcode)['EXP_DATE']);
    }

    public function testParseGs1ReturnsFalseWhenNothingMatches(): void
    {
        $this->assertFalse(parse_gs1('garbage'));
        $this->assertFalse(parse_gs1(''));
    }
}
