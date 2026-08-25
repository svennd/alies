<?php

declare(strict_types=1);

require_once BASEPATH . 'libraries/Migration.php';
require_once APPPATH . 'migrations/053_transfer_pet_medical_records.php';

final class PetTransferMedicalRecordsMigrationTest extends CodeIgniterDatabaseTestCase
{
    public function testCurrentHistoricalTransfersResolveOnceWithoutSchemaChanges(): void
    {
        $pets = $this->model('Pets_model', 'transfer_migration_resolution_pets');
        $pairs = $pets->resolve_historical_transfer_pairs();
        $expected = $this->ci->db
            ->where('transfered', 1)
            ->where('deleted_at IS NULL', null, false)
            ->where("note REGEXP '^\\\\[transfer:send:[0-9]+\\\\]$'", null, false)
            ->count_all_results('pets');

        $this->assertIsArray($pairs, $pets->get_transfer_error());
        $this->assertCount($expected, $pairs);
        $this->assertSame(count($pairs), count(array_unique(array_column($pairs, 'source_pet_id'))));
        $this->assertSame(count($pairs), count(array_unique(array_column($pairs, 'successor_pet_id'))));
        $this->assertFalse($this->ci->db->field_exists('transfer_source_event_id', 'events'));

        $source = file_get_contents(APPPATH . 'migrations/053_transfer_pet_medical_records.php');
        $this->assertStringNotContainsString('ALTER TABLE', strtoupper($source));
        $this->assertStringNotContainsString('CREATE TABLE', strtoupper($source));
    }

    public function testMigrationMovesHistoricalDataAndRetainsExistingSuccessorRows(): void
    {
        [$sourcePetId, $successorPetId] = $this->createHistoricalPair('BACKFILL');
        $productId = $this->existingId('products');
        $userId = $this->existingId('users');
        $locationId = $this->existingId('stock_location');

        $sourceVaccineId = $this->insertRow('vaccine_pet', [
            'product_id' => $productId,
            'event_id' => 0,
            'event_line' => null,
            'product' => 'Historical vaccine',
            'pet' => $sourcePetId,
            'redo' => date('Y-m-d'),
            'no_rappel' => 0,
            'location' => $locationId,
            'vet' => $userId,
        ]);
        $successorVaccineId = $this->insertRow('vaccine_pet', [
            'product_id' => $productId,
            'event_id' => 0,
            'event_line' => null,
            'product' => 'Successor vaccine',
            'pet' => $successorPetId,
            'redo' => date('Y-m-d'),
            'no_rappel' => 0,
            'location' => $locationId,
            'vet' => $userId,
        ]);
        $sourceWeightId = $this->insertRow('pets_weight', ['pets' => $sourcePetId, 'weight' => '5.25']);
        $successorWeightId = $this->insertRow('pets_weight', ['pets' => $successorPetId, 'weight' => '5.50']);
        $eventId = $this->insertRow('events', [
            'title' => 'Historical migration event',
            'anamnese' => 'Historical migration report',
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
            'updated_at' => '2024-01-02 03:04:05',
            'created_at' => '2024-01-02 03:04:05',
        ]);

        $migration = new Migration_transfer_pet_medical_records();
        $this->assertSame('053', $migration->up());

        $this->assertSame($successorPetId, (int) $this->ci->db->where('id', $sourceVaccineId)->get('vaccine_pet')->row()->pet);
        $this->assertSame($successorPetId, (int) $this->ci->db->where('id', $sourceWeightId)->get('pets_weight')->row()->pets);
        $this->assertSame($successorPetId, (int) $this->ci->db->where('id', $successorVaccineId)->get('vaccine_pet')->row()->pet);
        $this->assertSame($successorPetId, (int) $this->ci->db->where('id', $successorWeightId)->get('pets_weight')->row()->pets);
        $this->assertSame($sourcePetId, (int) $this->ci->db->where('id', $eventId)->get('events')->row()->pet);

        $copy = $this->ci->db
            ->where('pet', $successorPetId)
            ->where('title', 'Historical migration event')
            ->get('events')
            ->row_array();
        $this->assertNotNull($copy);
        $this->assertSame(STATUS_HISTORY, (int) $copy['status']);
        $this->assertSame(BILL_INVALID, (int) $copy['payment']);
        $this->assertSame('2024-01-02 03:04:05', $copy['created_at']);
        $this->assertSame('052', $migration->down());
    }

    public function testAmbiguousSuccessorAndDentalConflictFailBeforeMutation(): void
    {
        [$sourcePetId, $successorPetId, $newOwnerId, $time, $identity] = $this->createHistoricalPair('AMBIGUOUS', true);
        $this->createSuccessorPet($newOwnerId, $time, $identity);
        $weightId = $this->insertRow('pets_weight', ['pets' => $sourcePetId, 'weight' => '4.25']);
        $pets = $this->model('Pets_model', 'transfer_migration_preflight_pets');

        $this->assertFalse($pets->resolve_historical_transfer_pairs());
        $this->assertStringContainsString('successor candidates', $pets->get_transfer_error());
        $this->assertSame($sourcePetId, (int) $this->ci->db->where('id', $weightId)->get('pets_weight')->row()->pets);

        $this->ci->db->where('id !=', $successorPetId)->where('owner', $newOwnerId)->delete('pets');
        $userId = $this->existingId('users');
        $this->insertRow('tooth', ['pet' => $sourcePetId, 'vet' => $userId, 'tooth' => 202, 'tooth_status' => '#fff']);
        $this->insertRow('tooth', ['pet' => $successorPetId, 'vet' => $userId, 'tooth' => 202, 'tooth_status' => '#000']);

        $this->assertFalse($pets->resolve_historical_transfer_pairs());
        $this->assertStringContainsString('Dental chart conflict', $pets->get_transfer_error());
        $this->assertSame($sourcePetId, (int) $this->ci->db->where('id', $weightId)->get('pets_weight')->row()->pets);
    }

    private function createHistoricalPair(string $label, bool $returnDetails = false): array
    {
        $oldOwnerId = $this->createOwner($label . 'OLD');
        $newOwnerId = $this->createOwner($label . 'NEW');
        $time = date('Y-m-d H:i:s');
        $identity = [
            'type' => CAT,
            'name' => $this->uniqueString($label . 'PET'),
            'birth' => '2021-02-03',
        ];

        $sourcePetId = $this->createPet($oldOwnerId, $time, $identity, 1, '[transfer:send:' . $newOwnerId . ']');
        $successorPetId = $this->createSuccessorPet($newOwnerId, $time, $identity);

        return $returnDetails
            ? [$sourcePetId, $successorPetId, $newOwnerId, $time, $identity]
            : [$sourcePetId, $successorPetId];
    }

    private function createSuccessorPet(int $ownerId, string $time, array $identity): int
    {
        return $this->createPet(
            $ownerId,
            $time,
            $identity,
            0,
            'Original note [transfer:owner:' . $ownerId . ']'
        );
    }

    private function createOwner(string $label): int
    {
        $this->ci->db->insert('owners', [
            'first_name' => $this->uniqueString($label . 'FN'),
            'last_name' => $this->uniqueString($label . 'LN'),
            'street' => 'Migration Street',
            'nr' => '2',
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
            'initial_vet' => $this->existingId('users'),
            'initial_loc' => $this->existingId('stock_location'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->ci->db->insert_id();
    }

    private function createPet(
        int $ownerId,
        string $time,
        array $identity,
        int $transferred,
        string $note
    ): int {
        $this->ci->db->insert('pets', [
            'type' => $identity['type'],
            'name' => $identity['name'],
            'birth' => $identity['birth'],
            'death' => 0,
            'gender' => FEMALE,
            'last_weight' => '4.25',
            'lost' => 0,
            'hairtype' => 'short',
            'note' => $note,
            'owner' => $ownerId,
            'location' => $this->existingId('stock_location'),
            'init_vet' => $this->existingId('users'),
            'transfered' => $transferred,
            'created_at' => $time,
            'updated_at' => $time,
        ]);
        return (int) $this->ci->db->insert_id();
    }

    private function insertRow(string $table, array $data): int
    {
        if (!array_key_exists('created_at', $data)) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $this->ci->db->insert($table, $data);
        return (int) $this->ci->db->insert_id();
    }
}
