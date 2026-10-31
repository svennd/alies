<?php

declare(strict_types=1);

require_once APPPATH . 'third_party/api/devices/Lmscan.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * T016 - application/third_party/api/devices/Lmscan.php: payload of the LMSCAN device (API.md).
 * Each request is one measurement, so a report has exactly one result.
 */
final class LmscanTest extends TestCase
{
    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        $payload = json_decode(
            (string) file_get_contents(__DIR__ . '/../../Fixtures/lmscan_payload.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return array_merge($payload, $overrides);
    }

    public function testTheDocumentedExampleIsParsedIntoOneReportWithOneResult(): void
    {
        $this->assertSame(
            [
                'device' => 'lmscan',
                'source_id' => 'LMSCAN-2026-00042',
                'pet_id' => '998193',
                'sample_date' => '2026-08-24',
                'results' => [
                    [
                        'code' => 'cPL',
                        'value' => 187.4,
                        'unit' => 'ug/L',
                        'ref_min' => 0.0,
                        'ref_max' => 200.0,
                    ],
                ],
                'metadata' => [
                    'project_number' => 'P-17',
                    'test_end_time' => '2026-08-24T14:31:00+02:00',
                    'sample_type' => 'serum',
                ],
            ],
            (new Lmscan())->parse($this->payload())
        );
    }

    public function testTheSampleDateIsTheFirstTenCharactersOfTheEndTime(): void
    {
        $parsed = (new Lmscan())->parse($this->payload(['test_end_time' => '2027-01-02 03:04:05']));

        $this->assertSame('2027-01-02', $parsed['sample_date']);
    }

    public function testTheSampleIdIsTheAlliesPetIdAndIsPassedAsGiven(): void
    {
        $this->assertSame(998193, (new Lmscan())->parse($this->payload(['sample_id' => 998193]))['pet_id']);
        $this->assertSame('12', (new Lmscan())->parse($this->payload(['sample_id' => '12']))['pet_id']);
    }

    public function testWithoutSampleIdThePetIdIsNullBecauseThereIsNoOtherWayToIdentifyThePet(): void
    {
        $payload = $this->payload();
        unset($payload['sample_id']);

        $parsed = (new Lmscan())->parse($payload);

        $this->assertNull($parsed['pet_id']);
        $this->assertArrayNotHasKey('owner_name', $parsed);
        $this->assertArrayNotHasKey('chip', $parsed);
        $this->assertArrayNotHasKey('phone', $parsed);
    }

    public function testTheSerialNumberIsTheSourceIdAndNoSourceIsGiven(): void
    {
        $parsed = (new Lmscan())->parse($this->payload(['serial_number' => 'X-1']));

        $this->assertSame('X-1', $parsed['source_id']);
        $this->assertArrayNotHasKey('source', $parsed);
    }

    /** @return array<string, array{mixed, float}> */
    public static function results(): array
    {
        return [
            'decimal string' => ['187.4', 187.4],
            'integer string' => ['12', 12.0],
            'number' => [3.5, 3.5],
            'negative' => ['-1.5', -1.5],
            // quirk: a value that is not a number silently becomes 0.0
            'text becomes zero' => ['N/A', 0.0],
            'empty becomes zero' => ['', 0.0],
        ];
    }

    #[DataProvider('results')]
    public function testTheResultIsConvertedToAFloat(mixed $result, float $expected): void
    {
        $this->assertSame($expected, (new Lmscan())->parse($this->payload(['result' => $result]))['results'][0]['value']);
    }

    /** @return array<string, array{mixed, ?float}> */
    public static function referenceValues(): array
    {
        return [
            'numeric string' => ['12.5', 12.5],
            'zero string' => ['0', 0.0],
            'integer' => [7, 7.0],
            'dash' => ['-', null],
            'empty' => ['', null],
            'text' => ['n.a.', null],
            'null' => [null, null],
        ];
    }

    #[DataProvider('referenceValues')]
    public function testReferenceLimitsAreOnlyKeptWhenTheyAreNumbers(mixed $given, ?float $expected): void
    {
        $result = (new Lmscan())->parse($this->payload(['ref_low' => $given, 'ref_high' => $given]))['results'][0];

        $this->assertSame($expected, $result['ref_min']);
        $this->assertSame($expected, $result['ref_max']);
    }

    public function testAnEmptyUnitBecomesNull(): void
    {
        $this->assertNull((new Lmscan())->parse($this->payload(['unit' => '']))['results'][0]['unit']);
        $this->assertSame('mg/dL', (new Lmscan())->parse($this->payload(['unit' => 'mg/dL']))['results'][0]['unit']);
    }

    public function testTheMeasurementNameIsTheResultCode(): void
    {
        $this->assertSame('CRP', (new Lmscan())->parse($this->payload(['project_name' => 'CRP']))['results'][0]['code']);
    }

    public function testItImplementsTheDeviceAdapterContract(): void
    {
        $this->assertInstanceOf(DeviceAdapterInterface::class, new Lmscan());
    }
}
