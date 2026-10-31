<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Ms4s2Test extends TestCase
{
    public function testParsePlotsMatchesLegacyScriptPayload(): void
    {
        require_once APPPATH . 'third_party/api/devices/Ms4s2.php';

        $adapter = new Ms4s2();
        $payload = json_decode(
            (string) file_get_contents(__DIR__ . '/../../Fixtures/ms4s2_payload.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $method = new ReflectionMethod($adapter, 'parsePlots');
        $method->setAccessible(true);

        [$plots, $markers] = $method->invoke($adapter, $payload);

        $this->assertSame([80, 85], $markers);
        $this->assertSame([0, 0, 0, 0, 0], array_slice($plots['THR'], 0, 5));
        $this->assertSame([1, 1, 1, 1, 1], array_slice($plots['THR'], 32, 5));
        $this->assertSame([0, 1, 1, 1, 1], array_slice($plots['RBC'], 16, 5));
        $this->assertSame([0, 0, 0, 0, 0], array_slice($plots['WBC'], -5));
        $this->assertCount(256, $plots['THR']);
        $this->assertCount(256, $plots['RBC']);
        $this->assertCount(256, $plots['WBC']);
    }

    public function testParseBuildsNormalizedResultsAndPlots(): void
    {
        require_once APPPATH . 'third_party/api/devices/Ms4s2.php';

        $adapter = new Ms4s2();
        $parsed = $adapter->parse([
            'id' => '0006',
            'pet_id' => '998193',
            'owner_name' => 'JOS/MAURICE',
            'species' => 'Cat',
            'phone' => '0411.11.11.11',
            'day' => '21',
            'month' => '10',
            'year' => '2025',
            'experiments' => [
                'WBC' => [
                    'value' => '5.66',
                    'unit' => 'G/L',
                    'min' => '5',
                    'max' => '15',
                ],
            ],
            'wbc_calc' => [
                '#Lym.' => [
                    'value' => '1.38',
                    'unit' => 'G/L',
                    'min' => '1.5',
                    'max' => '7',
                ],
            ],
            'plots' => [
                'THR' => ['0', '1', '2'],
                'RBC' => ['3', '4', '5'],
            ],
            'markers' => ['80', '85'],
        ]);

        $this->assertSame('ms4s2', $parsed['device']);
        $this->assertSame('0006', $parsed['source_id']);
        $this->assertSame('998193', $parsed['pet_id']);
        $this->assertSame('jos/maurice', strtolower($parsed['owner_name']));
        $this->assertSame('cat', $parsed['species']);
        $this->assertSame('2025-10-21', $parsed['sample_date']);
        $this->assertCount(2, $parsed['results']);
        $this->assertSame('WBC', $parsed['results'][0]['code']);
        $this->assertSame(5.66, $parsed['results'][0]['value']);
        $this->assertSame('#Lym.', $parsed['results'][1]['code']);
        $this->assertSame(['THR' => [0, 1, 2], 'RBC' => [3, 4, 5]], $parsed['plots']);
        $this->assertSame(['markers' => [80, 85]], $parsed['metadata']);
    }

    // ---- T018: the complete fixture and edge cases ---------------------------------------------

    /** @return array<string, mixed> */
    private function fixture(): array
    {
        return json_decode(
            (string) file_get_contents(__DIR__ . '/../../Fixtures/ms4s2_payload.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    private function parseMs4s2(array $payload): array
    {
        require_once APPPATH . 'third_party/api/devices/Ms4s2.php';

        return (new Ms4s2())->parse($payload);
    }

    public function testCompleteFixtureHasTheDocumentedShape(): void
    {
        $parsed = $this->parseMs4s2($this->fixture());

        $this->assertSame(
            ['device', 'source_id', 'pet_id', 'owner_name', 'species', 'phone', 'sample_date', 'results', 'plots', 'metadata'],
            array_keys($parsed)
        );
        $this->assertSame('ms4s2', $parsed['device']);
        $this->assertSame('0006', $parsed['source_id']);
        $this->assertSame('998193', $parsed['pet_id']);
        // owner and pet in one field: OWNER/PET
        $this->assertSame('KEPPENS/MAURICE', $parsed['owner_name']);
        $this->assertSame('cat', $parsed['species']);
        $this->assertSame('0477.88.14.64', $parsed['phone']);
        $this->assertSame('2025-10-21', $parsed['sample_date']);
        $this->assertSame(['markers' => [80, 85]], $parsed['metadata']);
        $this->assertSame(['THR', 'RBC', 'WBC'], array_keys($parsed['plots']));
    }

    public function testExperimentsComeBeforeTheCalculatedWhiteBloodCellCounts(): void
    {
        $parsed = $this->parseMs4s2($this->fixture());
        $codes = array_column($parsed['results'], 'code');

        // 17 experiments followed by the 5 calculated values
        $this->assertCount(22, $codes);
        $this->assertSame('WBC', $codes[0]);
        $this->assertSame(['#Lym.', '#Mon.', '#Neu.', '#Eo.', '#Ba.'], array_slice($codes, 17));
    }

    public function testResultValuesAreFloatsAndMissingLimitsAreNull(): void
    {
        $results = array_column($this->parseMs4s2($this->fixture())['results'], null, 'code');

        $this->assertSame(
            ['code' => 'WBC', 'value' => 5.66, 'unit' => 'G/L', 'ref_min' => 5.0, 'ref_max' => 15.0],
            $results['WBC']
        );
        // the device sends "-" when there is no reference range
        $this->assertSame(
            ['code' => 'Ba.', 'value' => 0.9, 'unit' => '%', 'ref_min' => null, 'ref_max' => null],
            $results['Ba.']
        );
    }

    public function testDateIsNotPaddedWhenTheDeviceSendsSingleDigits(): void
    {
        // quirk: the parts are glued together as they come, "2025-3-5" is not a padded date
        $payload = array_merge($this->fixture(), ['day' => '5', 'month' => '3']);

        $this->assertSame('2025-3-5', $this->parseMs4s2($payload)['sample_date']);
    }

    public function testWithoutPlotsAndMarkersThereAreNoPlotsAndNoMetadata(): void
    {
        $payload = $this->fixture();
        unset($payload['plots'], $payload['markers']);

        $parsed = $this->parseMs4s2($payload);

        $this->assertNull($parsed['plots']);
        $this->assertNull($parsed['metadata']);
    }

    public function testPlotsWithoutMarkersHaveNoMetadata(): void
    {
        $payload = $this->fixture();
        unset($payload['markers']);

        $parsed = $this->parseMs4s2($payload);

        $this->assertNotNull($parsed['plots']);
        $this->assertNull($parsed['metadata']);
    }

    public function testSpeciesIsLowerCasedAndAnEmptyUnitBecomesNull(): void
    {
        $payload = $this->fixture();
        $payload['species'] = 'DOG';
        $payload['experiments']['WBC']['unit'] = '';

        $parsed = $this->parseMs4s2($payload);
        $results = array_column($parsed['results'], null, 'code');

        $this->assertSame('dog', $parsed['species']);
        $this->assertNull($results['WBC']['unit']);
    }
}
