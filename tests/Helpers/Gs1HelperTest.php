<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Gs1HelperTest extends TestCase
{
    public function testParseGs1SupportsLegacyFormat(): void
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

    public function testGs1HandlesDayZeroByUsingLastDayOfMonth(): void
    {
        $parsed = gs1('010341111207988917210200103145600');

        $this->assertSame('2021-02-28', $parsed['EXP_DATE']);
        $this->assertSame('3145600', $parsed['LOTNR']);
    }

    public function testGs1RejectsInvalidGtins(): void
    {
        $this->assertSame([], gs1('010123456789012310ABCD17YYMMDD21ABCD'));
        $this->assertFalse(gtincheck('01234567890123'));
    }
}
