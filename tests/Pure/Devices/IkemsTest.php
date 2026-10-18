<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class IkemsTest extends TestCase
{
    public static function resolveIdentificationProvider(): array
    {
        return [
            'mixed name and numbers' => [
                [
                    'pet_id' => 'ravier',
                    'pet_name' => '28',
                    'patient_number' => '12',
                ],
                [
                    'pet_id' => '28',
                    'owner_name' => 'ravier / 12',
                ],
            ],
            'pet name carries numeric id' => [
                [
                    'pet_id' => 'lolo',
                    'pet_name' => '4678',
                    'patient_number' => '345',
                ],
                [
                    'pet_id' => '4678',
                    'owner_name' => 'lolo / 345',
                ],
            ],
            'pet name only numeric' => [
                [
                    'pet_id' => null,
                    'pet_name' => '998414',
                    'patient_number' => null,
                ],
                [
                    'pet_id' => '998414',
                    'owner_name' => null,
                ],
            ],
            'patient number only numeric' => [
                [
                    'pet_id' => null,
                    'pet_name' => null,
                    'patient_number' => '998414',
                ],
                [
                    'pet_id' => '998414',
                    'owner_name' => null,
                ],
            ],
            'owner name kept when patient number is numeric' => [
                [
                    'pet_id' => null,
                    'pet_name' => 'BELLE',
                    'patient_number' => '192032',
                ],
                [
                    'pet_id' => '192032',
                    'owner_name' => 'BELLE',
                ],
            ],
            'owner name combines string identifiers' => [
                [
                    'pet_id' => '192032',
                    'pet_name' => 'BELLE',
                    'patient_number' => 'vanhoye',
                ],
                [
                    'pet_id' => '192032',
                    'owner_name' => 'vanhoye / BELLE',
                ],
            ],
            'string pet name preserved' => [
                [
                    'pet_id' => '176072',
                    'pet_name' => 'JOS/SANDWITCH',
                    'patient_number' => null,
                ],
                [
                    'pet_id' => '176072',
                    'owner_name' => 'JOS/SANDWITCH',
                ],
            ],
        ];
    }

    public function testParseNormalizesPayloadAndIdentification(): void
    {
        require_once APPPATH . 'third_party/api/devices/Ikems.php';

        $adapter = new Ikems();
        $parsed = $adapter->parse([
            'id' => 'sample-1',
            'pet_id' => '192032',
            'pet_name' => 'BELLE',
            'patient_number' => 'vanhoye',
            'chkdatetime' => '06/04/2026',
            'summary' => 'OK',
            'errorcode' => 'E1',
            'experiments' => [
                [
                    'N' => 'GLU',
                    'I' => ['1.5', '1.0', '2.0', 'mmol/L'],
                ],
            ],
        ]);

        $this->assertSame('ikems', $parsed['device']);
        $this->assertSame('sample-1', $parsed['source_id']);
        $this->assertSame('192032', $parsed['pet_id']);
        $this->assertSame('vanhoye / BELLE', $parsed['owner_name']);
        $this->assertSame('2026-04-06', $parsed['sample_date']);
        $this->assertSame('GLU', $parsed['results'][0]['code']);
        $this->assertSame('1.5', $parsed['results'][0]['value']);
        $this->assertSame(1.0, $parsed['results'][0]['ref_min']);
        $this->assertSame(2.0, $parsed['results'][0]['ref_max']);
        $this->assertSame('SUMMARY', $parsed['results'][1]['code']);
        $this->assertSame('ERRORCODE', $parsed['results'][2]['code']);
    }

    #[DataProvider('resolveIdentificationProvider')]
    public function testResolveIdentificationMatchesLegacyScriptCases(array $payload, array $expected): void
    {
        require_once APPPATH . 'third_party/api/devices/Ikems.php';

        $adapter = new Ikems();
        $method = new ReflectionMethod($adapter, 'resolveIdentification');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($adapter, $payload));
    }

    // ---- T018: output shape and edge cases ----------------------------------------------------

    private function parseIkems(array $payload): array
    {
        require_once APPPATH . 'third_party/api/devices/Ikems.php';

        return (new Ikems())->parse($payload);
    }

    public function testOutputShapeHasNoPhoneAndNoSource(): void
    {
        $parsed = $this->parseIkems(['id' => 'x']);

        $this->assertSame(
            ['device', 'source_id', 'pet_id', 'owner_name', 'sample_date', 'results', 'software_version', 'metadata'],
            array_keys($parsed)
        );
        $this->assertArrayNotHasKey('phone', $parsed);
        $this->assertArrayNotHasKey('source', $parsed);
    }

    public function testMetadataAndSoftwareVersionArePassedOn(): void
    {
        $parsed = $this->parseIkems([
            'id' => 'x',
            'software_version' => '2.1',
            'panel_id' => 'P1',
            'panel_index' => 3,
            'panel_lot' => 'L9',
            'machine_id' => 'M7',
        ]);

        $this->assertSame('2.1', $parsed['software_version']);
        $this->assertSame(['panel_id' => 'P1', 'panel_index' => 3, 'panel_lot' => 'L9', 'machine_id' => 'M7'], $parsed['metadata']);
    }

    public function testMissingOptionalFieldsAreNull(): void
    {
        $parsed = $this->parseIkems([]);

        $this->assertNull($parsed['source_id']);
        $this->assertNull($parsed['pet_id']);
        $this->assertNull($parsed['owner_name']);
        $this->assertNull($parsed['sample_date']);
        $this->assertNull($parsed['software_version']);
        $this->assertSame([], $parsed['results']);
        $this->assertSame(['panel_id' => null, 'panel_index' => null, 'panel_lot' => null, 'machine_id' => null], $parsed['metadata']);
    }

    public function testDatesAreReadAsDayMonthYear(): void
    {
        $this->assertSame('2026-04-06', $this->parseIkems(['chkdatetime' => '06/04/2026'])['sample_date']);
        $this->assertSame('2026-12-31', $this->parseIkems(['chkdatetime' => '31/12/2026'])['sample_date']);
        // another format, text and an empty value are rejected
        $this->assertNull($this->parseIkems(['chkdatetime' => '2026-04-06'])['sample_date']);
        $this->assertNull($this->parseIkems(['chkdatetime' => 'yesterday'])['sample_date']);
        $this->assertNull($this->parseIkems(['chkdatetime' => ''])['sample_date']);
    }

    public function testImpossibleDatesRollOverInsteadOfBeingRejected(): void
    {
        // quirk: DateTime::createFromFormat is lenient, month 13 is january of the next year
        // and the 31st of february is the 3rd of march
        $this->assertSame('2027-01-01', $this->parseIkems(['chkdatetime' => '01/13/2026'])['sample_date']);
        $this->assertSame('2026-03-03', $this->parseIkems(['chkdatetime' => '31/02/2026'])['sample_date']);
    }

    public function testEmptySummaryAndErrorcodeAreNotAddedAsResults(): void
    {
        $parsed = $this->parseIkems(['summary' => '', 'errorcode' => '', 'experiments' => [['N' => 'GLU', 'I' => ['1', '0', '2', 'u']]]]);

        $this->assertSame(['GLU'], array_column($parsed['results'], 'code'));
    }

    public function testSummaryAndErrorcodeComeAfterTheExperiments(): void
    {
        $parsed = $this->parseIkems([
            'summary' => 'OK',
            'errorcode' => 'E1',
            'experiments' => [['N' => 'GLU', 'I' => ['1', '0', '2', 'u']]],
        ]);

        $this->assertSame(['GLU', 'SUMMARY', 'ERRORCODE'], array_column($parsed['results'], 'code'));
        $this->assertSame(
            ['code' => 'SUMMARY', 'value' => 'OK', 'unit' => null, 'ref_min' => null, 'ref_max' => null],
            $parsed['results'][1]
        );
    }

    public function testExperimentValueIsKeptAsTextAndLimitsAsNumbers(): void
    {
        $parsed = $this->parseIkems(['experiments' => [
            ['N' => 'A', 'I' => ['1.50', '1', '2', 'mmol/L']],
            ['N' => 'B', 'I' => ['neg', '-', '', '']],
        ]]);

        $this->assertSame(
            ['code' => 'A', 'value' => '1.50', 'unit' => 'mmol/L', 'ref_min' => 1.0, 'ref_max' => 2.0],
            $parsed['results'][0]
        );
        $this->assertSame(
            ['code' => 'B', 'value' => 'neg', 'unit' => null, 'ref_min' => null, 'ref_max' => null],
            $parsed['results'][1]
        );
    }
}
