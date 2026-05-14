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
}
