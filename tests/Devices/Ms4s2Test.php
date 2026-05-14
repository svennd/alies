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
            (string) file_get_contents(__DIR__ . '/../Fixtures/ms4s2_payload.json'),
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
        $this->assertSame('keppens/maurice', strtolower($parsed['owner_name']));
        $this->assertSame('cat', $parsed['species']);
        $this->assertSame('2025-10-21', $parsed['sample_date']);
        $this->assertCount(2, $parsed['results']);
        $this->assertSame('WBC', $parsed['results'][0]['code']);
        $this->assertSame(5.66, $parsed['results'][0]['value']);
        $this->assertSame('#Lym.', $parsed['results'][1]['code']);
        $this->assertSame(['THR' => [0, 1, 2], 'RBC' => [3, 4, 5]], $parsed['plots']);
        $this->assertSame(['markers' => [80, 85]], $parsed['metadata']);
    }
}
