<?php

declare(strict_types=1);

final class LabReportReassignmentServiceTest extends PHPUnit\Framework\TestCase
{
	private CI_Controller $ci;
	private array $reportIds = [];

	protected function setUp(): void
	{
		parent::setUp();
		$this->ci = get_instance();
		$this->ci->load->library('LabResultService');
	}

	protected function tearDown(): void
	{
		if ($this->reportIds) {
			$this->ci->db->where_in('lab_id', $this->reportIds)->delete('events_labs');
			$this->ci->db->where_in('report_id', $this->reportIds)->delete('lab_results');
			$this->ci->db->where_in('report_id', $this->reportIds)->delete('lab_plots');
			$this->ci->db->where_in('id', $this->reportIds)->delete('lab_report');
		}
		$this->ci->db->db_debug = true;
		parent::tearDown();
	}

	public function testReassignmentChangesPatientAndRemovesOnlyFormerPetEventLinks(): void
	{
		$pair = $this->petPair();
		$oldPet = $pair[0];
		$newPet = $pair[1];
		$oldEvent = $this->eventForPet((int) $oldPet['id']);
		$otherEvent = $this->ci->db->select('id, pet')->where('pet !=', (int) $oldPet['id'])->order_by('id', 'ASC')->get('events')->row_array();
		$this->assertNotNull($oldEvent, 'An event for the original pet is required by the fixture.');
		$this->assertNotNull($otherEvent, 'An event for another pet is required by the fixture.');
		$reportId = $this->insertReport((int) $oldPet['id']);
		$this->ci->db->insert_batch('events_labs', [
			['event_id' => (int) $oldEvent['id'], 'lab_id' => $reportId],
			['event_id' => (int) $otherEvent['id'], 'lab_id' => $reportId],
		]);

		$result = $this->ci->labresultservice->reassign_report($reportId, (int) $newPet['owner'], (int) $newPet['id']);

		$this->assertSame('ok', $result['status']);
		$this->assertSame((int) $oldPet['id'], $result['old_pet_id']);
		$this->assertSame((int) $newPet['id'], $result['pet_id']);
		$this->assertSame(1, $result['removed_event_links']);
		$this->assertSame((int) $newPet['id'], (int) $this->ci->db->where('id', $reportId)->get('lab_report')->row_array()['pet_id']);
		$this->assertSame(0, $this->ci->db->where(['event_id' => $oldEvent['id'], 'lab_id' => $reportId])->count_all_results('events_labs'));
		$this->assertSame(1, $this->ci->db->where(['event_id' => $otherEvent['id'], 'lab_id' => $reportId])->count_all_results('events_labs'));

		$again = $this->ci->labresultservice->reassign_report($reportId, (int) $newPet['owner'], (int) $newPet['id']);
		$this->assertSame('noop', $again['status']);
		$this->assertSame(1, $this->ci->db->where(['event_id' => $otherEvent['id'], 'lab_id' => $reportId])->count_all_results('events_labs'));
	}

	public function testSameAssignmentIsNoopAndInvalidOwnerPetPairChangesNothing(): void
	{
		$pair = $this->petPair();
		$pet = $pair[0];
		$other = $pair[1];
		$reportId = $this->insertReport((int) $pet['id']);

		$noop = $this->ci->labresultservice->reassign_report($reportId, (int) $pet['owner'], (int) $pet['id']);
		$this->assertSame('noop', $noop['status']);
		$this->assertSame((int) $pet['id'], (int) $this->ci->db->where('id', $reportId)->get('lab_report')->row_array()['pet_id']);

		$invalid = $this->ci->labresultservice->reassign_report($reportId, (int) $other['owner'], (int) $pet['id']);
		$this->assertSame('error', $invalid['status']);
		$this->assertStringContainsString('selected owner', $invalid['message']);
		$this->assertSame((int) $pet['id'], (int) $this->ci->db->where('id', $reportId)->get('lab_report')->row_array()['pet_id']);
	}

	public function testServiceDefinesTransactionalRollbackPathsForReassignment(): void
	{
		$source = file_get_contents(APPPATH . 'libraries/LabResultService.php');
		$start = strpos($source, 'public function reassign_report');
		$end = strpos($source, 'private function persist_report', $start);
		$method = substr($source, $start, $end - $start);
		$this->assertStringContainsString('trans_begin()', $method);
		$this->assertStringContainsString('trans_commit()', $method);
		$this->assertGreaterThanOrEqual(2, substr_count($method, 'trans_rollback()'));
		$this->assertStringContainsString('trans_status() === false', $method);
	}

	private function petPair(): array
	{
		$oldPets = $this->ci->db->select('pets.id, pets.owner')->distinct()->from('pets')
			->join('owners', 'owners.id = pets.owner')->join('events', 'events.pet = pets.id')
			->where('pets.death', 0)->where('pets.transfered', 0)
			->where('pets.deleted_at IS NULL', null, false)->where('owners.deleted_at IS NULL', null, false)
			->order_by('pets.id', 'ASC')->get()->result_array();
		$pets = $this->ci->db->select('pets.id, pets.owner')->from('pets')->join('owners', 'owners.id = pets.owner')
			->where('pets.death', 0)->where('pets.transfered', 0)
			->where('pets.deleted_at IS NULL', null, false)->where('owners.deleted_at IS NULL', null, false)
			->order_by('pets.id', 'ASC')->get()->result_array();
		foreach ($oldPets as $first) {
			foreach ($pets as $second) {
				if ((int) $first['id'] !== (int) $second['id'] && (int) $first['owner'] !== (int) $second['owner']) {
					return [$first, $second];
				}
			}
		}
		$this->fail('Two assignable pets with different owners are required by the fixture.');
	}

	private function eventForPet(int $petId): ?array
	{
		return $this->ci->db->select('id, pet')->where('pet', $petId)->order_by('id', 'ASC')->get('events')->row_array() ?: null;
	}

	private function insertReport(int $petId): int
	{
		$this->ci->db->insert('lab_report', [
			'pet_id' => $petId, 'device' => 'phpunit-reassign', 'source' => 'phpunit-reassign',
			'source_id' => uniqid('ut_reassign_', true), 'sample_date' => '2026-08-28 00:00:00',
			'created_at' => date('Y-m-d H:i:s'),
		]);
		$reportId = (int) $this->ci->db->insert_id();
		$this->reportIds[] = $reportId;
		return $reportId;
	}
}
