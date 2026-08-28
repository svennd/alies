<?php

declare(strict_types=1);

final class LabPendingWorkflowTest extends PHPUnit\Framework\TestCase
{
    private CI_Controller $ci;
    private string $prefix;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ci = get_instance();
        $this->prefix = 'ut_lab_workflow_' . gmdate('His') . '_' . random_int(1000, 9999);
        $this->ci->load->library('LabResultService');
        $this->ci->load->model('LabReportPending_model', 'workflow_pending');
        $this->ci->load->model('LabReport_model', 'workflow_reports');
        $this->ci->load->model('Events_lab_model', 'workflow_event_labs');
		$this->ci->load->library('lab_pending_preview');
        $this->ci->lang->load('vet', 'dutch');
    }

    protected function tearDown(): void
    {
        $reports = $this->ci->db->select('id')->like('source_id', 'ut_lab_workflow_', 'after')->get('lab_report')->result_array();
        $reportIds = array_map('intval', array_column($reports, 'id'));
        if ($reportIds) {
            $this->ci->db->where_in('lab_id', $reportIds)->delete('events_labs');
            $this->ci->db->where_in('report_id', $reportIds)->delete('lab_results');
            $this->ci->db->where_in('report_id', $reportIds)->delete('lab_plots');
            $this->ci->db->where_in('id', $reportIds)->delete('lab_report');
        }
        $this->ci->db->like('source_id', 'ut_lab_workflow_', 'after')->delete('lab_report_pending');
        parent::tearDown();
    }

    public function testQueueToRecoveredReportAndEventEligibilityWorkflow(): void
    {
		$event = $this->ci->db->select('events.id, events.pet')->from('events')
			->join('pets', 'pets.id = events.pet')->join('owners', 'owners.id = pets.owner')
			->where('pets.death', 0)->where('pets.transfered', 0)
			->where('pets.deleted_at IS NULL', null, false)->where('owners.deleted_at IS NULL', null, false)
			->order_by('events.id', 'asc')->get()->row_array();
        $this->assertNotNull($event);
        $pet = $this->ci->db->select('pets.id, pets.name, owners.last_name')
            ->from('pets')->join('owners', 'owners.id = pets.owner')->where('pets.id', (int) $event['pet'])->get()->row_array();
        $this->assertNotNull($pet);
        $userId = (int) $this->ci->db->select('id')->order_by('id', 'asc')->get('users')->row_array()['id'];
        $pendingId = $this->insertMs4s2Pending($pet);

        $active = array_values(array_filter($this->ci->workflow_pending->get_active(), fn(array $row): bool => (int) $row['id'] === $pendingId));
        $this->assertCount(1, $active);
        $identifiers = json_decode($active[0]['identifiers'], true);
        $queueHtml = $this->ci->load->view('lab/pending', [
            'pending_results' => [[
                'id' => $pendingId, 'device' => $active[0]['device'], 'source' => $active[0]['source'],
                'source_id' => $active[0]['source_id'], 'reason' => $active[0]['reason'],
                'created_at' => $active[0]['created_at'], 'identifiers' => $identifiers,
            ]],
            'can_manage_pending' => true, 'pending_message' => null, 'pending_message_type' => null,
        ], true);
        $this->assertStringContainsString($pet['last_name'], $queueHtml);
        $this->assertStringContainsString($pet['name'], $queueHtml);
        $this->assertStringContainsString('lab/pending_detail/' . $pendingId, $queueHtml);
        $this->assertStringNotContainsString('name="pet_id"', $queueHtml);

		$built = $this->ci->lab_pending_preview->build($active[0]);
		$detailHtml = $this->ci->load->view('lab/pending_detail', [
			'pending' => $active[0], 'identifiers' => $identifiers,
			'preview' => $built['preview'], 'preview_warning' => $built['warning'], 'raw_json' => $built['raw_json'],
			'pending_message' => null, 'pending_message_type' => null,
		], true);
		$this->assertStringContainsString('WBC', $detailHtml);
		$this->assertStringContainsString('name="owner_id"', $detailHtml);
		$this->assertStringContainsString('name="pet_id"', $detailHtml);
		$this->assertStringContainsString('<details class="mt-4">', $detailHtml);
		$this->assertStringContainsString($this->prefix, $detailHtml);

        $ownerId = (int) $this->ci->db->select('owner')->where('id', (int) $pet['id'])->get('pets')->row_array()['owner'];
        $recovered = $this->ci->labresultservice->recover_pending($pendingId, $ownerId, (int) $pet['id'], $userId);
        $this->assertSame('ok', $recovered['status']);
        $reportId = (int) $recovered['report_id'];
        $this->assertSame(2, $this->ci->db->where('report_id', $reportId)->count_all_results('lab_results'));
        $this->assertSame(2, $this->ci->db->where('report_id', $reportId)->count_all_results('lab_plots'));

        $pending = $this->ci->db->where('id', $pendingId)->get('lab_report_pending')->row_array();
        $this->assertSame($reportId, (int) $pending['report_id']);
        $this->assertSame((int) $pet['id'], (int) $pending['resolved_pet_id']);
        $this->assertSame($userId, (int) $pending['resolved_by']);
        $this->assertNotNull($pending['resolved_at']);

        $petReportIds = array_map('intval', array_column($this->ci->workflow_reports->get_for_pet((int) $pet['id']), 'id'));
        $labIndexIds = array_map('intval', array_column($this->ci->workflow_reports->get_labs('2000-01-01', '2100-01-01'), 'id'));
        $linkableIds = array_map('intval', array_column($this->ci->workflow_event_labs->get_linkable_for_event((int) $event['id'], (int) $pet['id']), 'id'));
        $this->assertContains($reportId, $petReportIds);
        $this->assertContains($reportId, $labIndexIds);
        $this->assertContains($reportId, $linkableIds);
        $this->assertTrue($this->ci->workflow_event_labs->link((int) $event['id'], $reportId, (int) $pet['id']));
    }

    public function testConfirmedDismissalRetainsAuditAndRemovesActiveCount(): void
    {
        $userId = (int) $this->ci->db->select('id')->order_by('id', 'asc')->get('users')->row_array()['id'];
        $before = $this->ci->workflow_pending->count_active();
        $pendingId = $this->insertPending([
            'device' => 'unknown', 'source_id' => $this->prefix . '_dismiss',
            'raw_payload' => '{"unresolvable":true}',
            'identifiers' => '{"owner_name":"Unknown"}',
            'created_at' => '2020-01-01 00:00:00',
        ]);
        $this->assertSame($before + 1, $this->ci->workflow_pending->count_active());

        $html = $this->ci->load->view('lab/pending', [
            'pending_results' => [[
                'id' => $pendingId, 'device' => 'unknown', 'source' => null,
                'source_id' => $this->prefix . '_dismiss', 'reason' => 'pet_not_found',
                'created_at' => '2020-01-01 00:00:00', 'identifiers' => ['owner_name' => 'Unknown'],
            ]],
            'can_manage_pending' => true, 'pending_message' => null, 'pending_message_type' => null,
        ], true);
		$this->assertStringContainsString('lab/pending_detail/' . $pendingId, $html);
		$this->assertStringNotContainsString('lab/delete_pending/' . $pendingId, $html);

        $deleted = $this->ci->labresultservice->soft_delete_pending($pendingId, $userId);
        $this->assertSame('ok', $deleted['status']);
        $this->assertSame($before, $this->ci->workflow_pending->count_active());
        $row = $this->ci->db->where('id', $pendingId)->get('lab_report_pending')->row_array();
        $this->assertSame('{"unresolvable":true}', $row['raw_payload']);
        $this->assertSame($userId, (int) $row['deleted_by']);
        $this->assertNotNull($row['deleted_at']);
        $this->assertNull($row['resolved_at']);
        $this->assertSame('error', $this->ci->labresultservice->soft_delete_pending($pendingId, $userId)['status']);
    }

    private function insertMs4s2Pending(array $pet): int
    {
        return $this->insertPending([
            'device' => 'ms4s2', 'source_id' => $this->prefix,
            'identifiers' => json_encode(['owner_name' => $pet['last_name'], 'pet_name' => $pet['name']]),
            'raw_payload' => json_encode([
                'id' => $this->prefix, 'pet_id' => 'unmatched',
                'owner_name' => $pet['last_name'] . '/' . $pet['name'],
                'species' => 'dog', 'phone' => '', 'day' => '27', 'month' => '08', 'year' => '2026',
                'experiments' => [
                    'WBC' => ['value' => '5.5', 'unit' => 'G/L', 'min' => '2', 'max' => '8'],
                ],
                'wbc_calc' => [
                    '#Lym.' => ['value' => '1.5', 'unit' => 'G/L', 'min' => '1', 'max' => '4'],
                ],
                'plots' => ['RBC' => ['1', '2'], 'WBC' => ['3', '4']],
                'markers' => ['10', '20'],
            ]),
        ]);
    }

    private function insertPending(array $values): int
    {
        $this->ci->db->insert('lab_report_pending', array_merge([
            'device' => 'unknown', 'source' => null, 'source_id' => $this->prefix,
            'raw_payload' => '{}', 'identifiers' => '{}', 'reason' => 'pet_not_found',
            'created_at' => date('Y-m-d H:i:s'),
        ], $values));
        return (int) $this->ci->db->insert_id();
    }
}
