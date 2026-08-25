<?php

declare(strict_types=1);

final class PetTransferWorkflowTest extends CodeIgniterDatabaseTestCase
{
    public function testHistorySummaryFormatsAvailableGroupsAndEscapesCatalogText(): void
    {
        $pets = $this->model('Pets_model', 'transfer_formatter_pets');

        $productsOnly = $pets->format_transfer_history('', [
            ['name' => 'Dose <unsafe>', 'volume' => '-0.50', 'unit' => 'ml'],
        ], []);
        $proceduresOnly = $pets->format_transfer_history('Original', [], [
            ['name' => 'Consult', 'volume' => '1.00', 'unit' => null],
        ]);
        $both = $pets->format_transfer_history('<p>Original</p>', [
            ['name' => 'Tablet', 'volume' => '2.00', 'unit' => 'pcs'],
        ], [
            ['name' => 'Examination', 'volume' => '1.00', 'unit' => null],
        ]);

        $this->assertStringContainsString('Overgenomen producten', $productsOnly);
        $this->assertStringContainsString('Dose &lt;unsafe&gt;', $productsOnly);
        $this->assertStringContainsString('-0.5 ml', $productsOnly);
        $this->assertStringNotContainsString('Overgenomen procedures', $productsOnly);
        $this->assertStringContainsString('Original<hr>', $proceduresOnly);
        $this->assertStringContainsString('Consult</strong>: 1', $proceduresOnly);
        $this->assertStringContainsString('<p>Original</p><hr>', $both);
        $this->assertStringContainsString('Tablet</strong>: 2 pcs', $both);
        $this->assertStringContainsString('Examination</strong>: 1', $both);
        $this->assertSame('Unchanged', $pets->format_transfer_history('Unchanged', [], []));
    }

    public function testTransferRejectsInvalidTargetsAndCloneFailureWithoutChangingSource(): void
    {
        $pets = $this->model('Pets_model', 'transfer_validation_pets');
        $ownerId = $this->createOwner('VALIDATION');
        $disabledOwnerId = $this->createOwner('DISABLED', true);
        $petId = $this->createPet($ownerId, 'VALIDATION');

        $this->assertFalse($pets->transfer_pet(PHP_INT_MAX, $disabledOwnerId));
        $this->assertFalse($pets->transfer_pet($petId, PHP_INT_MAX));
        $this->assertFalse($pets->transfer_pet($petId, $ownerId));
        $this->assertFalse($pets->transfer_pet($petId, $disabledOwnerId));

        $failing = new PetTransferCloneFailureModel();
        $targetOwnerId = $this->createOwner('CLONEFAIL');
        $this->assertFalse($failing->transfer_pet($petId, $targetOwnerId));

        $source = $this->ci->db->where('id', $petId)->get('pets')->row_array();
        $this->assertSame(0, (int) $source['transfered']);
        $this->assertNotNull($source['chip']);
        $this->assertSame(0, $this->ci->db->where('owner', $targetOwnerId)->count_all_results('pets'));
    }

    public function testTransferMovesClinicalRecordsCopiesHistoryAndPreservesAccounting(): void
    {
        $pets = $this->model('Pets_model', 'transfer_workflow_pets');
        $events = $this->model('Events_model', 'transfer_workflow_events');
        $bills = $this->model('Bills_model', 'transfer_workflow_bills');
        $vaccines = $this->model('Vaccine_model', 'transfer_workflow_vaccines');
        $labs = $this->model('Events_lab_model', 'transfer_workflow_labs');
        $rx = $this->model('Rx_model', 'transfer_workflow_rx');
        $legacyLabs = $this->model('Lab_model', 'transfer_workflow_legacy_labs');
        $labReports = $this->model('LabReport_model', 'transfer_workflow_lab_reports');
        $oldOwnerId = $this->createOwner('OLD');
        $newOwnerId = $this->createOwner('NEW');
        $petName = $this->uniqueString('TRANSFERPET');
        $petId = $this->createPet($oldOwnerId, 'WORKFLOW', $petName);
        $userId = $this->existingId('users');
        $locationId = $this->existingId('stock_location');
        $productId = $this->existingId('products');
        $procedureId = $this->existingId('procedures');
        $bookingId = $this->existingId('booking_codes');
        $billId = $this->createBill($oldOwnerId);
        $eventDate = '2024-03-04 10:11:12';

        $visibleEventId = $this->insertEvent($petId, $billId, 0, 'Visible transfer history', $eventDate);
        $hiddenEventId = $this->insertEvent($petId, $billId, 1, 'Hidden transfer history', $eventDate);
        $this->ci->db->insert('events_products', [
            'product_id' => $productId,
            'event_id' => $visibleEventId,
            'volume' => '-0.50',
            'price_net' => '10.00',
            'price_brut' => '12.10',
            'unit_price' => '20.00',
            'price_ori_net' => '10.00',
            'reduction_reason' => null,
            'btw' => 21,
            'booking' => $bookingId,
            'stock_id' => null,
        ]);
        $eventLineId = (int) $this->ci->db->insert_id();
        $this->ci->db->insert('events_procedures', [
            'procedures_id' => $procedureId,
            'event_id' => $visibleEventId,
            'volume' => '1.00',
            'price_net' => '25.00',
            'price_brut' => '30.25',
            'unit_price' => '25.00',
            'price_ori_net' => '25.00',
            'reduction_reason' => null,
            'btw' => 21,
            'booking' => $bookingId,
        ]);

        $this->ci->db->insert('vaccine_pet', [
            'product_id' => $productId,
            'event_id' => $visibleEventId,
            'event_line' => $eventLineId,
            'product' => 'Transfer vaccine',
            'pet' => $petId,
            'redo' => date('Y-m-d'),
            'no_rappel' => 0,
            'location' => $locationId,
            'vet' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $vaccineId = (int) $this->ci->db->insert_id();
        $weightId = $this->insertRow('pets_weight', ['pets' => $petId, 'weight' => '8.75']);
        $toothId = $this->insertRow('tooth', [
            'pet' => $petId,
            'vet' => $userId,
            'tooth' => 101,
            'tooth_status' => '#ffffff',
        ]);
        $toothMessageId = $this->insertRow('tooth_msg', [
            'pet' => $petId,
            'vet' => $userId,
            'location' => $locationId,
            'msg' => 'Dental transfer note',
        ]);
        $rxId = $this->insertRow('rx', [
            'path' => $this->uniqueString('rx') . '.jpg',
            'pet_id' => $petId,
            'studydate' => '2024-03-04',
            'description' => 'Transfer image',
            'bodypart' => 'chest',
        ]);
        $legacyLabId = $this->insertRow('lab', [
            'lab_id' => random_int(100000000, 999999999),
            'lab_date' => '2024-03-04',
            'lab_patient_id' => $petId,
            'pet' => $petId,
            'lab_comment' => 'Legacy transfer lab',
            'source' => 'phpunit',
            'comment' => '',
        ]);
        $apiLabId = $this->insertRow('lab_report', [
            'pet_id' => $petId,
            'device' => 'phpunit-transfer',
            'source' => 'phpunit-transfer',
            'source_id' => $this->uniqueString('transfer_lab'),
            'metadata' => '{"source":"transfer"}',
            'sample_date' => $eventDate,
            'created_at' => $eventDate,
        ]);
        $unlinkedApiLabId = $this->insertRow('lab_report', [
            'pet_id' => $petId,
            'device' => 'phpunit-transfer',
            'source' => 'phpunit-transfer',
            'source_id' => $this->uniqueString('transfer_unlinked_lab'),
            'metadata' => '{"source":"transfer-unlinked"}',
            'sample_date' => $eventDate,
            'created_at' => $eventDate,
        ]);
        $this->ci->db->insert('events_labs', ['event_id' => $visibleEventId, 'lab_id' => $apiLabId]);

        $newPetId = $pets->transfer_pet($petId, $newOwnerId);
        $this->assertIsInt($newPetId);

        $source = $this->ci->db->where('id', $petId)->get('pets')->row_array();
        $successor = $this->ci->db->where('id', $newPetId)->get('pets')->row_array();
        $this->assertSame(1, (int) $source['transfered']);
        $this->assertNull($source['chip']);
        $this->assertSame($newOwnerId, (int) $successor['owner']);
        $this->assertSame('Transfer medication', $successor['medication']);
        $this->assertSame('Transfer nutrition', $successor['nutritional_advice']);
        $this->assertSame('VAC-BOOK', $successor['nr_vac_book']);
        $this->assertSame('pet_' . str_repeat('a', 32) . '.jpg', $successor['avatar']);

        $this->assertMoved('vaccine_pet', 'pet', $vaccineId, $petId, $newPetId);
        $this->assertMoved('pets_weight', 'pets', $weightId, $petId, $newPetId);
        $this->assertMoved('tooth', 'pet', $toothId, $petId, $newPetId);
        $this->assertMoved('tooth_msg', 'pet', $toothMessageId, $petId, $newPetId);
        $this->assertMoved('rx', 'pet_id', $rxId, $petId, $newPetId);
        $this->assertMoved('lab', 'pet', $legacyLabId, $petId, $newPetId);
        $this->assertMoved('lab_report', 'pet_id', $apiLabId, $petId, $newPetId);
        $this->assertMoved('lab_report', 'pet_id', $unlinkedApiLabId, $petId, $newPetId);

        $rxRows = $rx->get_images($newPetId);
        $this->assertNotEmpty($rxRows);
        $this->assertStringContainsString($this->ci->db->where('id', $rxId)->get('rx')->row()->path, $rxRows[0]['images']);
        $legacyLabRows = $legacyLabs->get_labs(date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('+1 day')));
        $this->assertContains($legacyLabId, array_map('intval', array_column($legacyLabRows, 'id')));
        $apiLabRows = $labReports->get_for_pet($newPetId);
        $this->assertContains($apiLabId, array_map('intval', array_column($apiLabRows, 'id')));
        $this->assertContains($unlinkedApiLabId, array_map('intval', array_column($apiLabRows, 'id')));

        $copies = $this->ci->db->where('pet', $newPetId)->where('title', 'Visible transfer history')->get('events')->result_array();
        $this->assertCount(1, $copies);
        $copy = $copies[0];
        $this->assertSame(STATUS_HISTORY, (int) $copy['status']);
        $this->assertSame(BILL_INVALID, (int) $copy['payment']);
        $this->assertSame(REPORT_DONE, (int) $copy['report']);
        $this->assertSame($eventDate, $copy['created_at']);
        $this->assertStringContainsString('<p>Original report</p><hr>', $copy['anamnese']);
        $this->assertStringContainsString('Overgenomen producten', $copy['anamnese']);
        $this->assertStringContainsString('Overgenomen procedures', $copy['anamnese']);
        $this->assertSame(0, $this->ci->db->where('event_id', (int) $copy['id'])->count_all_results('events_products'));
        $this->assertSame(0, $this->ci->db->where('event_id', (int) $copy['id'])->count_all_results('events_procedures'));
        $this->assertSame(0, $this->ci->db->where('event', (int) $copy['id'])->count_all_results('events_upload'));
        $this->assertSame(0, $this->ci->db->where('pet', $newPetId)->where('title', 'Hidden transfer history')->count_all_results('events'));

        $original = $events->get($visibleEventId);
        $this->assertSame($petId, (int) $original['pet']);
        $this->assertSame($billId, (int) $original['payment']);
        $this->assertNotNull($events->get($hiddenEventId));
        $this->assertSame(1, $this->ci->db->where('event_id', $visibleEventId)->count_all_results('events_products'));
        $this->assertSame(1, $this->ci->db->where('event_id', $visibleEventId)->count_all_results('events_procedures'));

        $linked = $labs->get_linked_for_event((int) $copy['id'], $newPetId);
        $this->assertSame([$apiLabId], array_map('intval', array_column($linked, 'id')));
        $linkable = $labs->get_linkable_for_event((int) $copy['id'], $newPetId);
        $this->assertContains($unlinkedApiLabId, array_map('intval', array_column($linkable, 'id')));

        $reminders = $vaccines->get_expiring_vaccines(date('Y-m-d'), []);
        $matchingReminders = array_values(array_filter($reminders, static function (array $row) use ($petName): bool {
            return $row['pet_name'] === $petName;
        }));
        $this->assertCount(1, $matchingReminders);
        $this->assertSame($newOwnerId, (int) $matchingReminders[0]['owner_id']);

        $bills->pets = $pets;
        $bills->events = $events;
        $details = $bills->get_details($billId, $oldOwnerId);
        $this->assertArrayHasKey($petId, $details);
        $this->assertContains($visibleEventId, $details[$petId]['events']);

        $newBillId = $this->createBill($newOwnerId);
        $events->set_open_events_to_bills($newOwnerId, $newBillId);
        $copyAfterBilling = $events->get((int) $copy['id']);
        $this->assertSame(BILL_INVALID, (int) $copyAfterBilling['payment']);

        $this->assertFalse($pets->transfer_pet($petId, $oldOwnerId));
        $this->assertSame(1, $this->ci->db->where('owner', $newOwnerId)->where('name', $petName)->count_all_results('pets'));
    }

    private function createOwner(string $label, bool $disabled = false): int
    {
        $this->ci->db->insert('owners', [
            'first_name' => $this->uniqueString($label . 'FN'),
            'last_name' => $this->uniqueString($label . 'LN'),
            'street' => 'Transfer Street',
            'nr' => '1',
            'city' => 'Ghent',
            'main_city' => 'Ghent',
            'province' => 'East Flanders',
            'zip' => '9000',
            'msg' => '',
            'debts' => 0,
            'disabled' => $disabled ? 1 : 0,
            'low_budget' => 0,
            'language' => 0,
            'contact' => 1,
            'last_bill' => date('Y-m-d'),
            'initial_vet' => $this->existingId('users'),
            'initial_loc' => $this->existingId('stock_location'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->ci->db->insert_id();
    }

    private function createPet(int $ownerId, string $label, ?string $name = null): int
    {
        $this->ci->db->insert('pets', [
            'type' => DOG,
            'name' => $name ?? $this->uniqueString($label . 'PET'),
            'birth' => '2020-01-02',
            'death' => 0,
            'gender' => MALE,
            'color' => 'brown',
            'last_weight' => '8.75',
            'lost' => 0,
            'chip' => $this->uniqueString('CHIP'),
            'nr_vac_book' => 'VAC-BOOK',
            'hairtype' => 'short',
            'note' => 'Transfer note',
            'nutritional_advice' => 'Transfer nutrition',
            'medication' => 'Transfer medication',
            'owner' => $ownerId,
            'location' => $this->existingId('stock_location'),
            'init_vet' => $this->existingId('users'),
            'transfered' => 0,
            'avatar' => 'pet_' . str_repeat('a', 32) . '.jpg',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->ci->db->insert_id();
    }

    private function createBill(int $ownerId): int
    {
        $this->ci->db->insert('bills', [
            'owner_id' => $ownerId,
            'vet' => $this->existingId('users'),
            'location' => $this->existingId('stock_location'),
            'total_brut' => '0.00',
            'total_net' => '0.00',
            'BTW_0' => '0.00',
            'BTW_6' => '0.00',
            'BTW_21' => '0.00',
            'cash' => '0.00',
            'card' => '0.00',
            'transfer' => '0.00',
            'transfer_verified' => 0,
            'modified' => 0,
            'status' => BILL_PENDING,
            'msg' => '',
            'msg_invoice' => '',
            'mail' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->ci->db->insert_id();
    }

    private function insertEvent(int $petId, int $billId, int $hidden, string $title, string $createdAt): int
    {
        $this->ci->db->insert('events', [
            'title' => $title,
            'anamnese' => '<p>Original report</p>',
            'pet' => $petId,
            'type' => DISEASE,
            'status' => STATUS_CLOSED,
            'payment' => $billId,
            'location' => $this->existingId('stock_location'),
            'vet' => $this->existingId('users'),
            'vet_support_1' => 0,
            'vet_support_2' => 0,
            'report' => REPORT_DONE,
            'no_history' => $hidden,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
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

    private function assertMoved(string $table, string $column, int $id, int $sourcePetId, int $successorPetId): void
    {
        $row = $this->ci->db->where('id', $id)->get($table)->row_array();
        $this->assertNotNull($row);
        $this->assertSame($successorPetId, (int) $row[$column]);
        $this->assertSame(0, $this->ci->db->where('id', $id)->where($column, $sourcePetId)->count_all_results($table));
    }
}

final class PetTransferCloneFailureModel extends Pets_model
{
    protected function clone_pet(array $pet, int $new_owner_id)
    {
        return false;
    }
}
