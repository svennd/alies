<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LabResultPresenterTest extends TestCase
{
    private Lab_result_presenter $presenter;

    protected function setUp(): void
    {
        require_once APPPATH . 'libraries/Lab_result_presenter.php';
        $this->presenter = new Lab_result_presenter();
    }

    public function testNormalLowAndHighNumericResultsAreNormalized(): void
    {
        $normal = $this->presenter->normalize($this->fixtureResult('5.00', '', '2.00', '8.00'));
        $low = $this->presenter->normalize($this->fixtureResult('1.00', '', '2.00', '8.00'));
        $high = $this->presenter->normalize($this->fixtureResult('9.00', '', '2.00', '8.00'));

        $this->assertFalse($normal['is_out']);
        $this->assertTrue($low['is_low']);
        $this->assertTrue($low['is_out']);
        $this->assertTrue($high['is_high']);
        $this->assertTrue($high['is_out']);
        $this->assertSame('2.00 - 8.00', $normal['limit']);
    }

    public function testTextAndZeroRangeResultsDoNotProducePlots(): void
    {
        $text = $this->presenter->normalize($this->fixtureResult(null, 'negative', null, null));
        $zero = $this->presenter->normalize($this->fixtureResult('4.00', '', '0.00', '0.00'));

        $this->assertTrue($text['is_text']);
        $this->assertSame('negative', $text['value']);
        $this->assertFalse($text['draw_plot']);
        $this->assertFalse($zero['draw_plot']);
        $this->assertNull($zero['pct']);
    }

    // ---- T021: position on the bar, clamping, value selection, edge cases ------------------------

    /**
     * The position (pct, 0-100) follows the same bar as calc_pct(): below the range 0-33.3,
     * inside it 33.3-66.7, above it 66.7-100, outer parts as wide as the range itself.
     * Range 2 - 8 (width 6), hand calculated.
     *
     * @return array<string, array{string, float}>
     */
    public static function positions(): array
    {
        return [
            // far below: 2 - 6 = -4 and lower is clamped
            'one width below' => ['-4.00', 0.0],
            'far below' => ['-100.00', 0.0],
            // (1 - -4) / 6 / 3 = 5/18
            'just below' => ['1.00', 100 * 5 / 18],
            'start of the range' => ['2.00', 100 / 3],
            'middle of the range' => ['5.00', 50.0],
            'end of the range' => ['8.00', 200 / 3],
            // 2/3 + (9 - 8) / 6 / 3 = 2/3 + 1/18
            'just above' => ['9.00', 100 * (2 / 3 + 1 / 18)],
            // 8 + 6 = 14 and higher is clamped
            'one width above' => ['14.00', 100.0],
            'far above' => ['500.00', 100.0],
        ];
    }

    #[DataProvider('positions')]
    public function testPositionOnTheBar(string $value, float $expectedPct): void
    {
        $result = $this->presenter->normalize($this->fixtureResult($value, '', '2.00', '8.00'));

        $this->assertTrue($result['draw_plot']);
        $this->assertEqualsWithDelta($expectedPct, $result['pct'], 0.0001);
    }

    public function testValuesExactlyOnTheLimitsAreNotOutsideTheRange(): void
    {
        $atLow = $this->presenter->normalize($this->fixtureResult('2.00', '', '2.00', '8.00'));
        $atHigh = $this->presenter->normalize($this->fixtureResult('8.00', '', '2.00', '8.00'));

        foreach ([$atLow, $atHigh] as $result) {
            $this->assertFalse($result['is_low']);
            $this->assertFalse($result['is_high']);
            $this->assertFalse($result['is_out']);
        }
    }

    public function testWhenLowEqualsHighThePositionIsHalfwayBelowOrAbove(): void
    {
        $equal = $this->presenter->normalize($this->fixtureResult('5.00', '', '5.00', '5.00'));
        $below = $this->presenter->normalize($this->fixtureResult('4.00', '', '5.00', '5.00'));
        $above = $this->presenter->normalize($this->fixtureResult('6.00', '', '5.00', '5.00'));

        $this->assertTrue($equal['draw_plot']);
        $this->assertSame(50.0, $equal['pct']);
        $this->assertSame(0.0, $below['pct']);
        $this->assertSame(100.0, $above['pct']);
        $this->assertTrue($below['is_low']);
        $this->assertTrue($above['is_high']);
    }

    /** @return array<string, array{mixed, mixed}> */
    public static function rangesWithoutPlot(): array
    {
        return [
            'no minimum' => [null, '5.00'],
            'no maximum' => ['1.00', null],
            'no limits' => [null, null],
            'both zero' => ['0.00', '0.00'],
            'both zero as numbers' => [0, 0],
        ];
    }

    #[DataProvider('rangesWithoutPlot')]
    public function testIncompleteRangesAreNotDrawn(mixed $min, mixed $max): void
    {
        $result = $this->presenter->normalize($this->fixtureResult('3.00', '', $min, $max));

        $this->assertFalse($result['draw_plot']);
        $this->assertFalse($result['is_out']);
        $this->assertSame('', $result['limit']);
        $this->assertNull($result['pct']);
    }

    public function testARangeStartingAtZeroIsStillDrawn(): void
    {
        $result = $this->presenter->normalize($this->fixtureResult('3.00', '', '0.00', '5.00'));

        $this->assertTrue($result['draw_plot']);
        $this->assertSame('0.00 - 5.00', $result['limit']);
    }

    public function testNumericValueIsShownAsTheNumberUnlessTheTextIsLongerThanOneCharacter(): void
    {
        $plain = $this->presenter->normalize($this->fixtureResult('5.00', '', '2.00', '8.00'));
        $oneChar = $this->presenter->normalize($this->fixtureResult('5.00', '+', '2.00', '8.00'));
        $longer = $this->presenter->normalize($this->fixtureResult('5.00', '>5', '2.00', '8.00'));

        $this->assertSame('5.00', $plain['value']);
        $this->assertSame('5.00', $oneChar['value']);
        $this->assertSame('>5', $longer['value']);
        $this->assertFalse($longer['is_text']);
    }

    public function testATextResultNeverHasPlotFlagsEvenWithALimit(): void
    {
        $result = $this->presenter->normalize($this->fixtureResult(null, 'positive', '1.00', '2.00'));

        $this->assertTrue($result['is_text']);
        $this->assertSame('positive', $result['value']);
        $this->assertFalse($result['draw_plot']);
        $this->assertFalse($result['is_low']);
        $this->assertFalse($result['is_high']);
        $this->assertSame('', $result['limit']);
        $this->assertNull($result['pct']);
    }

    public function testNormalizeKeepsTheOriginalFields(): void
    {
        $input = $this->fixtureResult('5.00', '', '2.00', '8.00');

        $result = $this->presenter->normalize($input);

        foreach ($input as $key => $value) {
            $this->assertSame($value, $result[$key], $key);
        }
        $this->assertSame(
            ['is_text', 'value', 'draw_plot', 'is_low', 'is_high', 'is_out', 'limit', 'pct'],
            array_slice(array_keys($result), count($input))
        );
    }

    public function testNormalizeManyKeepsOrderAndKeys(): void
    {
        $results = $this->presenter->normalize_many([
            'a' => $this->fixtureResult('1.00', '', '2.00', '8.00'),
            'b' => $this->fixtureResult(null, 'negative', null, null),
            'c' => $this->fixtureResult('9.00', '', '2.00', '8.00'),
        ]);

        $this->assertSame(['a', 'b', 'c'], array_keys($results));
        $this->assertTrue($results['a']['is_low']);
        $this->assertTrue($results['b']['is_text']);
        $this->assertTrue($results['c']['is_high']);
    }

    public function testNormalizeManyOfNothingIsNothing(): void
    {
        $this->assertSame([], $this->presenter->normalize_many([]));
    }

    private function fixtureResult($valueNum, string $valueText, $minimum, $maximum): array
    {
        return [
            'id' => 1,
            'report_id' => 1,
            'code' => 'TEST',
            'value_num' => $valueNum,
            'value_text' => $valueText,
            'unit' => 'u',
            'ref_min' => $minimum,
            'ref_max' => $maximum,
        ];
    }
}
