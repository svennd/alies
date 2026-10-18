<?php

declare(strict_types=1);

require_once APPPATH . 'helpers/lab_helper.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T012 - application/helpers/lab_helper.php: calc_pct($value, $low, $high).
 *
 * Position of a lab value on a bar that is split in three equal parts: below the reference range
 * (0 - 1/3), inside it (1/3 - 2/3) and above it (2/3 - 1). The outer parts are as wide as the range
 * itself, so a value further away than one range width is clamped to 0 or 1.
 *
 * Hand calculated for the range 0-10 (width 10): -10 and below = 0, -5 = (-5 - -10)/10 * 1/3 = 1/6,
 * 0 = 1/3, 5 = 1/3 + 5/10 * 1/3 = 1/2, 10 = 2/3, 15 = 2/3 + 5/10 * 1/3 = 5/6, 20 and above = 1.
 * The helper is not used by any view any more (Lab_result_presenter replaced it), the tests keep
 * the behaviour documented.
 */
final class LabHelperTest extends TestCase
{
    /** @return array<string, array{mixed, mixed, mixed, ?float}> */
    public static function cases(): array
    {
        return [
            'value above range with unknown limit' => [5, '-', 10, null],
            'unknown upper limit' => [5, 0, '-', null],
            'both limits zero' => [5, 0, 0, null],
            'low equals high, value equal' => [5, 5, 5, 0.5],
            'low equals high, value below' => [4, 5, 5, 0.0],
            'low equals high, value above' => [6, 5, 5, 1.0],
            'middle of the range' => [5, 0, 10, 0.5],
            'start of the range' => [0, 0, 10, 1 / 3],
            'end of the range' => [10, 0, 10, 2 / 3],
            'half a width below' => [-5, 0, 10, 1 / 6],
            'one width below is clamped' => [-10, 0, 10, 0.0],
            'far below is clamped' => [-100, 0, 10, 0.0],
            'half a width above' => [15, 0, 10, 5 / 6],
            'one width above is clamped' => [20, 0, 10, 1.0],
            'far above is clamped' => [500, 0, 10, 1.0],
            'low above high' => [5, 10, 0, 0.0],
            'numeric strings' => ['5', '0', '10', 0.5],
            'decimals' => [2.5, 1.5, 3.5, 0.5],
        ];
    }

    #[DataProvider('cases')]
    public function testPercentagePositionOnTheBar(mixed $value, mixed $low, mixed $high, ?float $expected): void
    {
        $result = calc_pct($value, $low, $high);

        if ($expected === null) {
            $this->assertNull($result);
        } else {
            $this->assertEqualsWithDelta($expected, $result, 0.0001);
        }
    }

    public function testTheResultAlwaysStaysBetweenZeroAndOne(): void
    {
        for ($value = -50; $value <= 60; $value += 2.5) {
            $result = calc_pct($value, 10, 30);

            $this->assertGreaterThanOrEqual(0.0, $result);
            $this->assertLessThanOrEqual(1.0, $result);
        }
    }

    public function testTheResultNeverDecreasesWhenTheValueIncreases(): void
    {
        $previous = -1.0;
        for ($value = -50; $value <= 60; $value += 2.5) {
            $result = calc_pct($value, 10, 30);

            $this->assertGreaterThanOrEqual($previous, $result);
            $previous = $result;
        }
    }
}
