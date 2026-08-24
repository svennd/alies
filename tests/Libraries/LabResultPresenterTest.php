<?php

declare(strict_types=1);

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
