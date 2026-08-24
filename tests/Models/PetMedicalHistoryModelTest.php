<?php

declare(strict_types=1);

final class PetMedicalHistoryModelTest extends CodeIgniterDatabaseTestCase
{
    private const TABLES = [
        'events',
        'users',
        'stock_location',
        'events_upload',
        'events_labs',
        'lab_report',
        'events_products',
        'products',
        'events_procedures',
        'procedures',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTemporaryTables();
        $this->seedTemporaryTables();
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            $this->ci->db->query('DROP TEMPORARY TABLE IF EXISTS `' . $table . '`');
        }

        parent::tearDown();
    }

    public function testHistoryCountsOnlyValidLinkedLabsWithoutDuplicatingEvents(): void
    {
        $model = $this->model('Events_model', 'pet_history_events_test');

        $history = $model->get_pet_history(201);
        $this->assertSame(2, (int) $this->historyEvent($history, 101)['lab_count']);
        $this->assertSame(0, (int) $this->historyEvent($history, 102)['lab_count']);
        $this->assertSame(1, $this->eventOccurrenceCount($history, 101));
        $this->assertSame(1, $this->eventOccurrenceCount($history, 102));

        $this->ci->db->where('id', 301)->update('lab_report', [
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);
        $history = $model->get_pet_history(201);
        $this->assertSame(1, (int) $this->historyEvent($history, 101)['lab_count']);

        $this->ci->db->where('id', 302)->update('lab_report', [
            'pet_id' => 999,
        ]);
        $history = $model->get_pet_history(201);
        $this->assertSame(0, (int) $this->historyEvent($history, 101)['lab_count']);
    }

    private function createTemporaryTables(): void
    {
        $statements = [
            'CREATE TEMPORARY TABLE events (id INT PRIMARY KEY, pet INT NOT NULL, no_history TINYINT NOT NULL, created_at DATETIME NOT NULL, vet INT NULL, vet_support_1 INT NULL, vet_support_2 INT NULL, location INT NULL)',
            'CREATE TEMPORARY TABLE users (id INT PRIMARY KEY, first_name VARCHAR(255) NULL)',
            'CREATE TEMPORARY TABLE stock_location (id INT PRIMARY KEY, name VARCHAR(255) NULL)',
            'CREATE TEMPORARY TABLE events_upload (event INT NOT NULL)',
            'CREATE TEMPORARY TABLE events_labs (event_id INT NOT NULL, lab_id INT NOT NULL, PRIMARY KEY (event_id, lab_id))',
            'CREATE TEMPORARY TABLE lab_report (id INT PRIMARY KEY, pet_id INT NOT NULL, deleted_at DATETIME NULL)',
            'CREATE TEMPORARY TABLE events_products (event_id INT NOT NULL, product_id INT NULL)',
            'CREATE TEMPORARY TABLE products (id INT PRIMARY KEY, name VARCHAR(255) NULL, unit_sell VARCHAR(255) NULL)',
            'CREATE TEMPORARY TABLE events_procedures (event_id INT NOT NULL, procedures_id INT NULL)',
            'CREATE TEMPORARY TABLE procedures (id INT PRIMARY KEY, name VARCHAR(255) NULL)',
        ];

        foreach ($statements as $statement) {
            $this->ci->db->query($statement);
        }
    }

    private function seedTemporaryTables(): void
    {
        $this->ci->db->insert_batch('events', [
            ['id' => 101, 'pet' => 201, 'no_history' => 0, 'created_at' => '2026-08-24 10:00:00'],
            ['id' => 102, 'pet' => 201, 'no_history' => 0, 'created_at' => '2026-08-23 10:00:00'],
        ]);
        $this->ci->db->insert_batch('lab_report', [
            ['id' => 301, 'pet_id' => 201, 'deleted_at' => null],
            ['id' => 302, 'pet_id' => 201, 'deleted_at' => null],
            ['id' => 303, 'pet_id' => 201, 'deleted_at' => '2026-08-24 11:00:00'],
            ['id' => 304, 'pet_id' => 999, 'deleted_at' => null],
        ]);
        $this->ci->db->insert_batch('events_labs', [
            ['event_id' => 101, 'lab_id' => 301],
            ['event_id' => 101, 'lab_id' => 302],
            ['event_id' => 101, 'lab_id' => 303],
            ['event_id' => 101, 'lab_id' => 304],
        ]);
    }

    private function historyEvent(array $history, int $eventId): array
    {
        foreach ($history as $event) {
            if ((int) $event['id'] === $eventId) {
                return $event;
            }
        }

        $this->fail('The selected event is missing from pet history.');
    }

    private function eventOccurrenceCount(array $history, int $eventId): int
    {
        return count(array_filter($history, static function (array $event) use ($eventId): bool {
            return (int) $event['id'] === $eventId;
        }));
    }
}
