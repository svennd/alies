<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PetTransferRuntimeRollbackTest extends TestCase
{
    public function testFailureAfterClinicalMovementRollsBackSuccessorHistoryAndDirectRecords(): void
    {
        $ci = get_instance();
        $ci->load->model('Pets_model', 'transfer_runtime_rollback_base_pets');
        $userId = (int) $ci->db->select('id')->order_by('id', 'ASC')->get('users')->row()->id;
        $locationId = (int) $ci->db->select('id')->order_by('id', 'ASC')->get('stock_location')->row()->id;
        $suffix = gmdate('YmdHis') . random_int(1000, 9999);
        $sourceOwnerId = $this->createOwner($ci, 'ROLLBACK-OLD-' . $suffix, $userId, $locationId);
        $targetOwnerId = $this->createOwner($ci, 'ROLLBACK-NEW-' . $suffix, $userId, $locationId);
        $sourcePetId = 0;
        $weightId = 0;
        $eventId = 0;

        try {
            $ci->db->insert('pets', [
                'type' => DOG,
                'name' => 'ROLLBACK-PET-' . $suffix,
                'birth' => '2020-01-02',
                'death' => 0,
                'gender' => MALE,
                'last_weight' => '7.50',
                'lost' => 0,
                'chip' => 'ROLLBACK-CHIP-' . $suffix,
                'hairtype' => 'short',
                'note' => 'Runtime rollback fixture',
                'owner' => $sourceOwnerId,
                'location' => $locationId,
                'init_vet' => $userId,
                'transfered' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $sourcePetId = (int) $ci->db->insert_id();
            $originalChip = (string) $ci->db->select('chip')->where('id', $sourcePetId)->get('pets')->row()->chip;
            $ci->db->insert('pets_weight', [
                'pets' => $sourcePetId,
                'weight' => '7.50',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $weightId = (int) $ci->db->insert_id();
            $ci->db->insert('events', [
                'title' => 'Runtime rollback event',
                'anamnese' => 'Must be rolled back',
                'pet' => $sourcePetId,
                'type' => DISEASE,
                'status' => STATUS_CLOSED,
                'payment' => BILL_INVALID,
                'location' => $locationId,
                'vet' => $userId,
                'vet_support_1' => 0,
                'vet_support_2' => 0,
                'report' => REPORT_DONE,
                'no_history' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $eventId = (int) $ci->db->insert_id();

            $pets = new PetTransferRuntimeFailureModel();
            $this->assertFalse($pets->transfer_pet($sourcePetId, $targetOwnerId));
            $this->assertStringContainsString('Injected runtime transfer failure', $pets->get_transfer_error());

            $source = $ci->db->where('id', $sourcePetId)->get('pets')->row_array();
            $this->assertSame(0, (int) $source['transfered']);
            $this->assertSame($originalChip, $source['chip']);
            $this->assertSame($sourcePetId, (int) $ci->db->where('id', $weightId)->get('pets_weight')->row()->pets);
            $this->assertSame($sourcePetId, (int) $ci->db->where('id', $eventId)->get('events')->row()->pet);
            $this->assertSame(0, $ci->db->where('owner', $targetOwnerId)->count_all_results('pets'));
        } finally {
            if ($eventId) {
                $ci->db->where('id', $eventId)->delete('events');
            }
            if ($weightId) {
                $ci->db->where('id', $weightId)->delete('pets_weight');
            }
            if ($sourcePetId) {
                $ci->db->where('id', $sourcePetId)->delete('pets');
            }
            $ci->db->where_in('id', [$sourceOwnerId, $targetOwnerId])->delete('owners');
        }
    }

    private function createOwner($ci, string $name, int $userId, int $locationId): int
    {
        $ci->db->insert('owners', [
            'first_name' => $name,
            'last_name' => 'PHPUnit',
            'street' => 'Rollback Street',
            'nr' => '1',
            'city' => 'Ghent',
            'main_city' => 'Ghent',
            'province' => 'East Flanders',
            'zip' => '9000',
            'msg' => '',
            'debts' => 0,
            'disabled' => 0,
            'low_budget' => 0,
            'language' => 0,
            'contact' => 1,
            'initial_vet' => $userId,
            'initial_loc' => $locationId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $ci->db->insert_id();
    }
}

final class PetTransferRuntimeFailureModel extends Pets_model
{
    protected function after_transfer_step(string $step): void
    {
        if ($step === 'medical_records_transferred') {
            throw new RuntimeException('Injected runtime transfer failure.');
        }
    }
}
