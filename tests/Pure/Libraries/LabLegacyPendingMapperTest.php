<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LabLegacyPendingMapperTest extends TestCase
{
    public function testMapsLegacyReportNumericTextMetadataDatesAndComments(): void
    {
        require_once APPPATH . 'libraries/Lab_legacy_pending_mapper.php';
        $mapper = new Lab_legacy_pending_mapper();
        $mapped = $mapper->map([
            'legacy_table' => 'lab',
            'lab' => [
                'id' => '45',
                'lab_id' => '9988',
                'source' => 'medilab',
                'lab_date' => '2025-03-14',
                'lab_patient_id' => '123',
                'lab_comment' => 'External note',
                'comment' => 'Internal note',
                'lab_updated_at' => '2025-03-15 10:00:00',
                'deleted_at' => '2025-04-01 00:00:00',
            ],
            'details' => [
                [
                    'lab_code' => '1', 'lab_code_text' => 'Witte bloedcellen',
                    'value' => '4.5', 'string_value' => '', 'unit' => 'G/L',
                    'lower_limit' => '2.0', 'upper_limit' => '8.0', 'comment' => 'Numeric note',
                ],
                [
                    'lab_code' => '2', 'lab_code_text' => '---',
                    'value' => '0', 'string_value' => 'positive', 'unit' => '',
                    'lower_limit' => '', 'upper_limit' => '', 'comment' => 'Text note',
                ],
                [
                    'lab_code' => '3', 'lab_code_text' => 'Ignored',
                    'value' => '0', 'string_value' => 'niet medegedeeld', 'unit' => '',
                    'lower_limit' => '', 'upper_limit' => '', 'comment' => '',
                ],
            ],
        ]);

        $this->assertSame('medilab', $mapped['device']);
        $this->assertSame('medilab', $mapped['source']);
        $this->assertSame('9988', $mapped['source_id']);
        $this->assertSame('2025-03-14 00:00:00', $mapped['sample_date']);
        $this->assertSame('2025-03-15 10:00:00', $mapped['created_at']);
        $this->assertCount(2, $mapped['results']);
        $this->assertSame(4.5, $mapped['results'][0]['value']);
        $this->assertSame('positive', $mapped['results'][1]['value']);
        $this->assertSame('2', $mapped['results'][1]['code']);
        $this->assertSame('Internal note', $mapped['metadata']['legacy_comment']);
        $this->assertSame('2025-04-01 00:00:00', $mapped['metadata']['legacy_deleted_at']);
        $this->assertCount(2, $mapped['metadata']['legacy_detail_comments']);
    }

    public function testRejectsUnknownLegacyShape(): void
    {
        require_once APPPATH . 'libraries/Lab_legacy_pending_mapper.php';
        $this->expectException(InvalidArgumentException::class);
        (new Lab_legacy_pending_mapper())->map(['legacy_table' => 'other']);
    }
}
