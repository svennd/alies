<?php
declare(strict_types=1);

require_once APPPATH . 'models/Document_data_model.php';

final class DocumentDataTest extends CodeIgniterDatabaseTestCase
{
    private function copyRow(string $table, array $changes): int
    {
        $row = $this->ci->db->limit(1)->get($table)->row_array();
        self::assertNotNull($row, 'Fixture required: ' . $table);
        unset($row['id']);
        $this->ci->db->insert($table, array_replace($row, $changes));
        return (int) $this->ci->db->insert_id();
    }

    public function testReminderEligibilityIdentityOrderingAndReadOnlyBehavior(): void
    {
        $model = $this->model('Document_data_model', 'document_data');
        $owner = $this->copyRow('owners', ['disabled' => 0]);
        $companion = $this->copyRow('owners', ['disabled' => 0]);
        $pet = $this->copyRow('pets', ['owner' => $owner, 'companion' => $companion, 'death' => 0, 'lost' => 0, 'transfered' => 0, 'deleted_at' => null]);
        $product = $this->existingId('products');
        $vet = $this->existingId('users');
        $ids = [];
        for ($i = 0; $i < 11; $i++) {
            $ids[] = $this->copyRow('vaccine_pet', ['pet' => $pet, 'product_id' => $product, 'vet' => $vet, 'redo' => date('Y-m-15'), 'no_rappel' => 0]);
        }
        $start = count($this->ci->db->queries);
        $data = $model->reminders(0, [], array_reverse(array_slice($ids, 0, 10)));
        self::assertSame(array_slice($ids, 0, 10), array_map('intval', array_column($data['letters'], 'id')));
        self::assertSame($companion, (int) $data['letters'][0]['owner_id']);
        foreach (array_slice($this->ci->db->queries, $start) as $sql) { self::assertMatchesRegularExpression('/^(SELECT|SHOW)\b/i', ltrim($sql)); }
        foreach ([[], [$ids[0], $ids[0]], $ids] as $bad) {
            try { $model->reminders(0, [], $bad); self::fail('Invalid selection accepted'); }
            catch (InvalidArgumentException $e) { self::assertSame('selection', $e->getMessage()); }
        }
        self::assertSame([], array_values(array_filter($model->reminder_rows(0, [$product]), fn($r) => (int) $r['pet_id'] === $pet)));
        foreach ([[$product], []] as $excluded) {
            try { $model->reminders(0, $excluded, $excluded ? [$ids[0]] : [9999999999]); self::fail('Filtered or missing row accepted'); }
            catch (RuntimeException $e) { self::assertSame('stale', $e->getMessage()); }
        }
        foreach (['death', 'lost', 'transfered'] as $field) {
            $this->ci->db->where('id', $pet)->update('pets', [$field => 1]);
            self::assertSame([], array_values(array_filter($model->reminder_rows(0), fn($r) => (int) $r['pet_id'] === $pet)));
            $this->ci->db->where('id', $pet)->update('pets', [$field => 0]);
        }
        $this->ci->db->where('id', $companion)->update('owners', ['disabled' => 1]);
        self::assertSame([], array_values(array_filter($model->reminder_rows(0), fn($r) => (int) $r['pet_id'] === $pet)));
        $this->ci->db->where('id', $companion)->update('owners', ['disabled' => 0]);
        $this->ci->db->where('id', $ids[0])->update('vaccine_pet', ['no_rappel' => 1]);
        try { $model->reminders(0, [], [$ids[0]]); self::fail('Stale row accepted'); }
        catch (RuntimeException $e) { self::assertSame('stale', $e->getMessage()); }
        self::assertSame([], array_values(array_filter($model->reminder_rows(1), fn($r) => (int) $r['pet_id'] === $pet)));
    }

    public function testOverviewExcludesExternalRecordsAndHandlesEmpty(): void
    {
        $this->ci->lang->load('vet', 'dutch');
        $model = $this->model('Document_data_model', 'document_data');
        $owner = $this->copyRow('owners', ['disabled' => 0]);
        $pet = $this->copyRow('pets', ['owner' => $owner, 'deleted_at' => null]);
        self::assertSame([], $model->overview($pet)['vaccines']);
        $this->copyRow('vaccine_pet', ['pet' => $pet, 'event_id' => 0]);
        $this->copyRow('vaccine_pet', ['pet' => $pet, 'event_id' => $this->existingId('events'), 'no_rappel' => 1]);
        $start = count($this->ci->db->queries);
        $data = $model->overview($pet);
        self::assertCount(1, $data['vaccines']);
        self::assertSame('-', $data['vaccines'][0]['due']);
        foreach (array_slice($this->ci->db->queries, $start) as $sql) { self::assertMatchesRegularExpression('/^(SELECT|SHOW)\b/i', ltrim($sql)); }
    }

    public function testInvoiceUsesStoredValuesWithoutChangingBillingState(): void
    {
        $this->ci->lang->load('documents', 'dutch');
        $model = $this->model('Document_data_model', 'document_data');
        $owner = $this->copyRow('owners', ['invoice_addr' => "Alternative address\n9000 Gent", 'invoice_contact' => 'Accounts']);
        $pet = $this->copyRow('pets', ['owner' => $owner, 'deleted_at' => null]);
        $bill = $this->copyRow('bills', ['owner_id' => $owner, 'invoice_id' => null, 'status' => BILL_PENDING, 'total_net' => 100, 'total_brut' => 121, 'BTW_0' => 0, 'BTW_6' => 0, 'BTW_21' => 100, 'cash' => 0, 'card' => 0, 'transfer' => 0, 'deleted_at' => null]);
        $event = $this->copyRow('events', ['pet' => $pet, 'payment' => $bill]);
        $this->copyRow('events_products', ['event_id' => $event, 'volume' => 1, 'unit_price' => 100, 'price_net' => 100, 'price_brut' => 121, 'btw' => 21]);
        foreach ([BILL_PENDING, BILL_PAID] as $status) {
            $this->ci->db->where('id', $bill)->update('bills', ['status' => $status, 'invoice_id' => $status === BILL_PAID ? 999999 : null, 'invoice_date' => date('Y-m-d H:i:s')]);
            $before = $this->ci->db->where('id', $bill)->get('bills')->row_array();
            $start = count($this->ci->db->queries);
            $data = $model->invoice($bill);
            self::assertSame('121,00', $data['total']);
            self::assertStringContainsString('Alternative address', $data['address']);
            self::assertCount(1, $data['lines']);
            foreach (array_slice($this->ci->db->queries, $start) as $sql) { self::assertMatchesRegularExpression('/^(SELECT|SHOW)\b/i', ltrim($sql)); }
            self::assertSame($before, $this->ci->db->where('id', $bill)->get('bills')->row_array());
        }
        $this->ci->db->where('id', $bill)->update('bills', ['status' => BILL_DRAFT]);
        try { $model->invoice($bill); self::fail('Draft was rendered'); }
        catch (RuntimeException $e) { self::assertSame('unprepared', $e->getMessage()); }
        $this->ci->db->where('id', $bill)->update('bills', ['status' => BILL_PAID, 'total_net' => 0, 'total_brut' => 0]);
        $this->copyRow('liquidate', ['bill_id' => $bill, 'volume' => 2, 'product_name' => 'Write-off fixture', 'lotnr' => 'QA123', 'eol' => '2026-09-20']);
        $data = $model->invoice($bill);
        self::assertSame('Write-off fixture (lot QA123) EOL 20-09-2026', $data['lines'][0]['description']);
        self::assertSame('0,00', $data['lines'][0]['total']);
    }
}
