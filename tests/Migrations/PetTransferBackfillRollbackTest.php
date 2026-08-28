<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PetTransferBackfillRollbackTest extends TestCase
{
    public function testForcedMidBackfillFailureRollsBackEveryTable(): void
    {
        $ci = get_instance();
        $ci->load->model('Pets_model', 'transfer_rollback_base_pets');
        $before = $this->clinicalReferences($ci->db);
        $pets = new PetTransferBackfillFailureModel();

        $this->assertFalse($pets->backfill_transferred_pets());
        $this->assertStringContainsString('Injected backfill failure', $pets->get_transfer_error());
        $this->assertSame($before, $this->clinicalReferences($ci->db));
    }

    private function clinicalReferences($db): array
    {
        $references = [];
        foreach ([
            'vaccine_pet' => 'pet',
            'pets_weight' => 'pets',
            'tooth' => 'pet',
            'tooth_msg' => 'pet',
            'rx' => 'pet_id',
            'lab_report' => 'pet_id',
        ] as $table => $column) {
            $references[$table] = $db
                ->select('id, ' . $column)
                ->order_by('id', 'ASC')
                ->get($table)
                ->result_array();
        }
        $references['events'] = $db->select('id, pet, payment')->order_by('id', 'ASC')->get('events')->result_array();
        $references['events_labs'] = $db->order_by('event_id', 'ASC')->order_by('lab_id', 'ASC')->get('events_labs')->result_array();
        return $references;
    }
}

final class PetTransferBackfillFailureModel extends Pets_model
{
    protected function after_transfer_step(string $step): void
    {
        if ($step === 'backfill_pair_complete') {
            throw new RuntimeException('Injected backfill failure.');
        }
    }
}
