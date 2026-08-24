<?php

declare(strict_types=1);

final class EventsLabModelTest extends CodeIgniterDatabaseTestCase
{
    public function testLinkingIsPetScopedDuplicateSafeAndSupportsMultipleLabs(): void
    {
        [$eventId, $petId, $otherPetId] = $this->fixtureIds();
        $model = $this->model('Events_lab_model', 'events_lab_test');
        $first = $this->insertLab($petId);
        $second = $this->insertLab($petId);
        $other = $this->insertLab($otherPetId);
        $deleted = $this->insertLab($petId, date('Y-m-d H:i:s'));

        $this->assertTrue($model->link($eventId, $first, $petId));
        $this->assertFalse($model->link($eventId, $first, $petId));
        $this->assertTrue($model->link($eventId, $second, $petId));
        $this->assertFalse($model->link($eventId, $other, $petId));
        $this->assertFalse($model->link($eventId, $deleted, $petId));

        $linked = $model->get_linked_for_event($eventId, $petId);
        $linkedIds = array_map('intval', array_column($linked, 'id'));
        sort($linkedIds);
        $expectedIds = [$first, $second];
        sort($expectedIds);
        $this->assertSame($expectedIds, $linkedIds);
        $linkableIds = array_map('intval', array_column($model->get_linkable_for_event($eventId, $petId), 'id'));
        $this->assertNotContains($first, $linkableIds);
        $this->assertNotContains($second, $linkableIds);
        $this->assertNotContains($other, $linkableIds);
        $this->assertNotContains($deleted, $linkableIds);
    }

    public function testStaleLinksAreHiddenAndUnlinkOnlyRemovesRelationship(): void
    {
        [$eventId, $petId, $otherPetId] = $this->fixtureIds();
        $model = $this->model('Events_lab_model', 'events_lab_stale_test');
        $labId = $this->insertLab($petId);

        $this->assertTrue($model->link($eventId, $labId, $petId));
        $this->ci->db->where('id', $labId)->update('lab_report', ['pet_id' => $otherPetId]);
        $this->assertSame([], $model->get_linked_for_event($eventId, $petId));

        $this->assertTrue($model->unlink($eventId, $labId));
        $this->assertFalse($model->unlink($eventId, $labId));
        $lab = $this->ci->db->where('id', $labId)->get('lab_report')->row_array();
        $this->assertNotNull($lab);
        $this->assertSame($otherPetId, (int) $lab['pet_id']);
    }

    private function fixtureIds(): array
    {
        $event = $this->ci->db
            ->select('id, pet')
            ->where('pet >', 0)
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get('events')
            ->row_array();
        $this->assertNotNull($event, 'An event fixture is required.');

        $other = $this->ci->db
            ->select('id')
            ->where('id !=', (int) $event['pet'])
            ->where('deleted_at IS NULL', null, false)
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get('pets')
            ->row_array();
        $this->assertNotNull($other, 'A second pet fixture is required.');

        return [(int) $event['id'], (int) $event['pet'], (int) $other['id']];
    }

    private function insertLab(int $petId, ?string $deletedAt = null): int
    {
        $this->ci->db->insert('lab_report', [
            'pet_id' => $petId,
            'source' => 'phpunit',
            'source_id' => $this->uniqueString('event_lab'),
            'sample_date' => date('Y-m-d H:i:s'),
            'deleted_at' => $deletedAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->ci->db->insert_id();
    }
}
