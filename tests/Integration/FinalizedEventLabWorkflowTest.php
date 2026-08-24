<?php

declare(strict_types=1);

final class FinalizedEventLabWorkflowTest extends CodeIgniterDatabaseTestCase
{
    public function testLinkDisplayNavigationReassignmentProtectionAndUnlinkFlow(): void
    {
        $event = $this->ci->db->select('id, pet')->where('pet >', 0)->order_by('id', 'ASC')->get('events')->row_array();
        $this->assertNotNull($event);
        $otherPet = $this->ci->db->select('id')->where('id !=', (int) $event['pet'])->where('deleted_at IS NULL', null, false)->get('pets')->row_array();
        $this->assertNotNull($otherPet);

        $labId = $this->insertLab((int) $event['pet']);
        $this->ci->db->insert('lab_results', [
            'report_id' => $labId,
            'code' => 'ALT',
            'value_num' => '12.00',
            'value_text' => '',
            'unit' => 'U/L',
            'ref_min' => '1.00',
            'ref_max' => '5.00',
        ]);

        $relations = $this->model('Events_lab_model', 'workflow_events_lab');
        $results = $this->model('LabResult_model', 'workflow_lab_results');
        $this->ci->load->library('lab_result_presenter');

        $this->assertTrue($relations->link((int) $event['id'], $labId, (int) $event['pet']));
        $linked = $relations->get_linked_for_event((int) $event['id'], (int) $event['pet']);
        $grouped = $results->get_grouped_by_reports([$labId]);
        $linked[0]['results'] = $this->ci->lab_result_presenter->normalize_many($grouped[$labId]);
        $this->ci->lang->load('vet', 'dutch');

        $html = $this->ci->load->view('event/report/block_lab_results', [
            'event_id' => (int) $event['id'],
            'linked_labs' => $linked,
            'linkable_labs' => [],
            'user' => (object) ['user_date' => 'd/m/Y'],
        ], true);
        $this->assertStringContainsString('lab/detail/' . $labId, $html);
        $this->assertStringContainsString('12.00', $html);
        $this->assertStringContainsString('event-lab-result-out', $html);

        $this->ci->db->where('id', $labId)->update('lab_report', ['pet_id' => (int) $otherPet['id']]);
        $this->assertSame([], $relations->get_linked_for_event((int) $event['id'], (int) $event['pet']));
        $this->assertTrue($relations->unlink((int) $event['id'], $labId));
        $this->assertNotNull($this->ci->db->where('id', $labId)->get('lab_report')->row_array());
    }

    public function testSoftDeletedLinkedLabIsNotDisclosed(): void
    {
        $event = $this->ci->db->select('id, pet')->where('pet >', 0)->order_by('id', 'ASC')->get('events')->row_array();
        $this->assertNotNull($event);
        $labId = $this->insertLab((int) $event['pet']);
        $relations = $this->model('Events_lab_model', 'deleted_workflow_events_lab');

        $this->assertTrue($relations->link((int) $event['id'], $labId, (int) $event['pet']));
        $this->ci->db->where('id', $labId)->update('lab_report', ['deleted_at' => date('Y-m-d H:i:s')]);
        $this->assertSame([], $relations->get_linked_for_event((int) $event['id'], (int) $event['pet']));
    }

    private function insertLab(int $petId): int
    {
        $this->ci->db->insert('lab_report', [
            'pet_id' => $petId,
            'device' => 'phpunit',
            'source' => 'phpunit',
            'source_id' => $this->uniqueString('workflow_lab'),
            'sample_date' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->ci->db->insert_id();
    }
}
