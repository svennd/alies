<?php

declare(strict_types=1);

/**
 * T373  Source: application/models/Pets_model.php (transfer_pet, get_transfer_error,
 *       transfer_existing_medical_records).
 *
 * Rules in plain words (the existing PetTransferWorkflowTest only checks the boolean false, never the
 * reason, and does not cover an already transferred or soft-deleted source or a soft-deleted owner):
 *  - source pet missing, already transferred or soft deleted -> false, "source pet is missing ..."
 *  - target owner missing, soft deleted or disabled          -> false, "target owner is missing or disabled."
 *  - target owner already owns the pet                       -> false, "already owns this pet."
 *  - dental conflict (same tooth number on source and successor) -> RuntimeException "Dental chart conflict".
 *    transfer_pet itself can never hit it (the clone has no teeth); it is only reachable through
 *    transfer_existing_medical_records (used by the historical backfill).
 *  - after every failure the source pet is unchanged and no successor exists; the error text is reset
 *    at the start of the next call.
 */
final class PetTransferFailureReasonsTest extends CodeIgniterDatabaseTestCase
{
    private $pets;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->pets = $this->model('Pets_model', 'failure_reason_pets');
    }

    private function assertSourceUntouched(int $petId, int $ownerId): void
    {
        $row = $this->ci->db->where('id', $petId)->get('pets')->row_array();
        $this->assertSame(0, (int) $row['transfered']);
        $this->assertSame($ownerId, (int) $row['owner']);
        $this->assertSame(1, $this->ci->db->where('name', $row['name'])->count_all_results('pets'));
    }

    public function testMissingSourceGivesTheSourceMissingReason(): void
    {
        $target = Factory::owner();

        $this->assertFalse($this->pets->transfer_pet(2147483000, $target));
        $this->assertSame('The source pet is missing or has already been transferred.', $this->pets->get_transfer_error());
    }

    public function testAlreadyTransferredSourceIsRefusedWithTheSourceMissingReason(): void
    {
        $owner = Factory::owner();
        $target = Factory::owner();
        $pet = Factory::pet($owner, ['transfered' => 1]);

        $this->assertFalse($this->pets->transfer_pet($pet, $target));
        $this->assertSame('The source pet is missing or has already been transferred.', $this->pets->get_transfer_error());
        $this->assertSame(0, $this->ci->db->where('owner', $target)->count_all_results('pets'));
    }

    public function testSoftDeletedSourceIsRefusedWithTheSourceMissingReason(): void
    {
        $owner = Factory::owner();
        $target = Factory::owner();
        $pet = Factory::pet($owner, ['deleted_at' => date('Y-m-d H:i:s')]);

        $this->assertFalse($this->pets->transfer_pet($pet, $target));
        $this->assertSame('The source pet is missing or has already been transferred.', $this->pets->get_transfer_error());
        $this->assertSame(0, $this->ci->db->where('owner', $target)->count_all_results('pets'));
    }

    public function testMissingTargetOwnerGivesTheOwnerReason(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);

        $this->assertFalse($this->pets->transfer_pet($pet, 2147483000));
        $this->assertSame('The target owner is missing or disabled.', $this->pets->get_transfer_error());
        $this->assertSourceUntouched($pet, $owner);
    }

    public function testSoftDeletedTargetOwnerGivesTheOwnerReason(): void
    {
        $owner = Factory::owner();
        $deleted = Factory::owner(['deleted_at' => date('Y-m-d H:i:s')]);
        $pet = Factory::pet($owner);

        $this->assertFalse($this->pets->transfer_pet($pet, $deleted));
        $this->assertSame('The target owner is missing or disabled.', $this->pets->get_transfer_error());
        $this->assertSourceUntouched($pet, $owner);
    }

    public function testDisabledTargetOwnerGivesTheOwnerReason(): void
    {
        $owner = Factory::owner();
        $disabled = Factory::owner(['disabled' => 1]);
        $pet = Factory::pet($owner);

        $this->assertFalse($this->pets->transfer_pet($pet, $disabled));
        $this->assertSame('The target owner is missing or disabled.', $this->pets->get_transfer_error());
        $this->assertSourceUntouched($pet, $owner);
    }

    public function testTargetOwnerThatAlreadyOwnsThePetGivesTheAlreadyOwnsReason(): void
    {
        $owner = Factory::owner();
        $pet = Factory::pet($owner);

        $this->assertFalse($this->pets->transfer_pet($pet, $owner));
        $this->assertSame('The target owner already owns this pet.', $this->pets->get_transfer_error());
        $this->assertSourceUntouched($pet, $owner);
    }

    public function testErrorTextIsClearedByTheNextSuccessfulTransfer(): void
    {
        $owner = Factory::owner();
        $target = Factory::owner();
        $pet = Factory::pet($owner);

        // positive control: the same pet moves to a valid owner, and the earlier error text is gone
        $this->assertFalse($this->pets->transfer_pet($pet, $owner));
        $this->assertNotSame('', $this->pets->get_transfer_error());

        $newId = $this->pets->transfer_pet($pet, $target);
        $this->assertGreaterThan(0, $newId);
        $this->assertSame('', $this->pets->get_transfer_error());
        $this->assertSame($target, (int) $this->ci->db->where('id', $newId)->get('pets')->row_array()['owner']);
        $this->assertSame(1, (int) $this->ci->db->where('id', $pet)->get('pets')->row_array()['transfered']);
    }

    public function testDentalConflictBetweenSourceAndSuccessorIsRefused(): void
    {
        $owner = Factory::owner();
        $source = Factory::pet($owner);
        $successor = Factory::pet($owner);
        Factory::insert('tooth', ['pet' => $source, 'vet' => 2, 'tooth' => 101, 'tooth_status' => '#fff']);
        Factory::insert('tooth', ['pet' => $successor, 'vet' => 2, 'tooth' => 101, 'tooth_status' => '#000']);

        try {
            $this->pets->transfer_existing_medical_records($source, $successor);
            $this->fail('A dental conflict must throw');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Dental chart conflict while transferring pet #' . $source . ' to pet #' . $successor . '.',
                $e->getMessage()
            );
        }
        // nothing moved
        $this->assertSame(1, $this->ci->db->where('pet', $source)->count_all_results('tooth'));
        $this->assertSame(1, $this->ci->db->where('pet', $successor)->count_all_results('tooth'));
    }

    public function testDifferentToothNumbersDoNotConflictAndAreMoved(): void
    {
        // positive control for the dental conflict test
        $owner = Factory::owner();
        $source = Factory::pet($owner);
        $successor = Factory::pet($owner);
        Factory::insert('tooth', ['pet' => $source, 'vet' => 2, 'tooth' => 101, 'tooth_status' => '#fff']);
        Factory::insert('tooth', ['pet' => $successor, 'vet' => 2, 'tooth' => 102, 'tooth_status' => '#000']);

        $result = $this->pets->transfer_existing_medical_records($source, $successor);

        $this->assertSame($source, $result['source_pet_id']);
        $this->assertSame($successor, $result['successor_pet_id']);
        $this->assertSame(0, $this->ci->db->where('pet', $source)->count_all_results('tooth'));
        $this->assertSame(2, $this->ci->db->where('pet', $successor)->count_all_results('tooth'));
    }
}
