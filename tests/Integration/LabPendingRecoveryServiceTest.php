<?php

declare(strict_types=1);

require_once APPPATH . 'third_party/api/devices/DeviceAdapterInterface.php';

final class LabPendingRecoveryServiceTest extends PHPUnit\Framework\TestCase
{
    private CI_Controller $ci;
    private string $prefix;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ci = get_instance();
        $this->prefix = 'ut_lab_' . gmdate('His') . '_' . random_int(1000, 9999);
        $this->ci->load->library('LabResultService');
    }

    protected function tearDown(): void
    {
        $reportRows = $this->ci->db->select('id')->like('source_id', 'ut_lab_', 'after')->get('lab_report')->result_array();
        $reportIds = array_map('intval', array_column($reportRows, 'id'));
        if ($reportIds) {
            $this->ci->db->where_in('lab_id', $reportIds)->delete('events_labs');
            $this->ci->db->where_in('report_id', $reportIds)->delete('lab_results');
            $this->ci->db->where_in('report_id', $reportIds)->delete('lab_plots');
            $this->ci->db->where_in('id', $reportIds)->delete('lab_report');
        }
        $this->ci->db->like('source_id', 'ut_lab_', 'after')->delete('lab_report_pending');
		$this->ci->db->like('raw_payload', 'ut_lab_', 'both')->delete('lab_report_pending');
        $this->ci->db->db_debug = true;
        parent::tearDown();
    }

    public function testUnmatchedIngestionPersistsSourceIdentityAndKeepsApiResponse(): void
    {
        $adapter = new LabPendingSourceAdapter($this->prefix, null);
        $response = $this->ci->labresultservice->ingest($adapter, ['original' => 'payload']);

        $this->assertSame(['status' => 'pending'], $response);
        $pending = $this->ci->db->where('source_id', $this->prefix)->get('lab_report_pending')->row_array();
        $this->assertNotNull($pending);
        $this->assertSame('remote', $pending['source']);
        $this->assertSame($this->prefix, $pending['source_id']);
        $this->assertSame('{"original":"payload"}', $pending['raw_payload']);
    }

	public function testRepeatedUnmatchedIngestionRefreshesOnePendingRow(): void
	{
		$adapter = new LabPendingSourceAdapter($this->prefix, null);
		$this->assertSame(['status' => 'pending'], $this->ci->labresultservice->ingest($adapter, ['version' => 1]));
		$first = $this->ci->db->where('source_id', $this->prefix)->get('lab_report_pending')->row_array();
		$this->assertSame(['status' => 'pending'], $this->ci->labresultservice->ingest($adapter, ['version' => 2]));
		$second = $this->ci->db->where('source_id', $this->prefix)->get('lab_report_pending')->row_array();

		$this->assertSame((int) $first['id'], (int) $second['id']);
		$this->assertSame(1, $this->ci->db->where('source_id', $this->prefix)->count_all_results('lab_report_pending'));
		$this->assertSame('{"version":2}', $second['raw_payload']);
		$this->assertSame($first['created_at'], $second['created_at']);
		$this->assertNotNull($second['last_received_at']);
	}

	public function testExistingReportRefreshUsesItsAssignedPetBeforePayloadMatching(): void
	{
		$petId = $this->existingPetId();
		$first = $this->ci->labresultservice->ingest(new LabPendingSourceAdapter($this->prefix, $petId, true, '5.5'), []);
		$report = $this->ci->db->where('source_id', $this->prefix)->get('lab_report')->row_array();
		$second = $this->ci->labresultservice->ingest(new LabPendingSourceAdapter($this->prefix, null, true, '6.5'), []);
		$refreshed = $this->ci->db->where('source_id', $this->prefix)->get('lab_report')->row_array();
		$result = $this->ci->db->where('report_id', $report['id'])->get('lab_results')->row_array();

		$this->assertSame(['status' => 'ok'], $first);
		$this->assertSame(['status' => 'ok'], $second);
		$this->assertSame((int) $report['id'], (int) $refreshed['id']);
		$this->assertSame($petId, (int) $refreshed['pet_id']);
		$this->assertSame(6.5, (float) $result['value_num']);
		$this->assertSame(0, $this->ci->db->where('source_id', $this->prefix)->count_all_results('lab_report_pending'));
	}

	public function testNormalReportLookupPreservesSourceFallbackWhenDeviceCandidateMisses(): void
	{
		$petId = $this->existingPetId();
		$this->ci->db->insert('lab_report', [
			'pet_id' => $petId, 'device' => 'original-device', 'source' => 'remote',
			'source_id' => $this->prefix, 'sample_date' => '2026-08-30 10:00:00',
			'created_at' => '2026-08-30 10:00:00',
		]);
		$reportId = (int) $this->ci->db->insert_id();

		$response = $this->ci->labresultservice->ingest(new LabSourceFallbackAdapter($this->prefix), []);
		$report = $this->ci->db->where('id', $reportId)->get('lab_report')->row_array();
		$result = $this->ci->db->where('report_id', $reportId)->get('lab_results')->row_array();

		$this->assertSame(['status' => 'ok'], $response);
		$this->assertSame($petId, (int) $report['pet_id']);
		$this->assertSame(6.5, (float) $result['value_num']);
		$this->assertSame(1, $this->ci->db->where('source_id', $this->prefix)->count_all_results('lab_report'));
	}

	public function testLaterAutomaticMatchResolvesPendingWithoutHumanActor(): void
	{
		$petId = $this->existingPetId();
		$this->ci->labresultservice->ingest(new LabPendingSourceAdapter($this->prefix, null), ['phase' => 'pending']);
		$pending = $this->ci->db->where('source_id', $this->prefix)->get('lab_report_pending')->row_array();

		$response = $this->ci->labresultservice->ingest(new LabPendingSourceAdapter($this->prefix, $petId), ['phase' => 'matched']);
		$resolved = $this->ci->db->where('id', $pending['id'])->get('lab_report_pending')->row_array();

		$this->assertSame(['status' => 'ok'], $response);
		$this->assertNotNull($resolved['resolved_at']);
		$this->assertNull($resolved['resolved_by']);
		$this->assertSame($petId, (int) $resolved['resolved_pet_id']);
		$this->assertGreaterThan(0, (int) $resolved['report_id']);
	}

	public function testFailedAutomaticMatchRollsBackReportAndPendingResolution(): void
	{
		$petId = $this->existingPetId();
		$this->ci->labresultservice->ingest(new LabPendingSourceAdapter($this->prefix, null), ['phase' => 'pending']);
		$pending = $this->ci->db->where('source_id', $this->prefix)->get('lab_report_pending')->row_array();

		try {
			$this->ci->labresultservice->ingest(new LabPendingSourceAdapter($this->prefix, $petId, false), ['phase' => 'invalid']);
			$this->fail('Expected invalid result persistence to fail.');
		} catch (UnexpectedValueException $error) {
			$this->assertStringContainsString('code', $error->getMessage());
		}

		$active = $this->ci->db->where('id', $pending['id'])->get('lab_report_pending')->row_array();
		$this->assertNull($active['resolved_at']);
		$this->assertNull($active['deleted_at']);
		$this->assertSame(0, $this->ci->db->where('source_id', $this->prefix)->count_all_results('lab_report'));
	}

	public function testDismissedRepeatStaysSuppressedButLaterMatchCanImport(): void
	{
		$petId = $this->existingPetId();
		$userId = $this->existingUserId();
		$adapter = new LabPendingSourceAdapter($this->prefix, null);
		$this->ci->labresultservice->ingest($adapter, ['version' => 1]);
		$pending = $this->ci->db->where('source_id', $this->prefix)->get('lab_report_pending')->row_array();
		$this->assertSame('ok', $this->ci->labresultservice->soft_delete_pending((int) $pending['id'], $userId)['status']);

		$this->assertSame(['status' => 'pending'], $this->ci->labresultservice->ingest($adapter, ['version' => 2]));
		$dismissed = $this->ci->db->where('id', $pending['id'])->get('lab_report_pending')->row_array();
		$this->assertSame('{"version":1}', $dismissed['raw_payload']);
		$this->assertSame(0, $this->ci->db->where('source_id', $this->prefix)->where('resolved_at IS NULL', null, false)->where('deleted_at IS NULL', null, false)->count_all_results('lab_report_pending'));

		$this->assertSame(['status' => 'ok'], $this->ci->labresultservice->ingest(new LabPendingSourceAdapter($this->prefix, $petId), ['version' => 3]));
		$this->assertSame(1, $this->ci->db->where('source_id', $this->prefix)->count_all_results('lab_report'));
		$this->assertNotNull($this->ci->db->where('id', $pending['id'])->get('lab_report_pending')->row_array()['deleted_at']);
	}

	public function testIdentitylessUnmatchedDeliveriesRemainSeparate(): void
	{
		$adapter = new LabIdentitylessSourceAdapter($this->prefix);
		$this->ci->labresultservice->ingest($adapter, ['marker' => $this->prefix, 'version' => 1]);
		$this->ci->labresultservice->ingest($adapter, ['marker' => $this->prefix, 'version' => 2]);

		$this->assertSame(2, $this->ci->db->like('raw_payload', $this->prefix, 'both')->count_all_results('lab_report_pending'));
	}

	public function testConcurrencyGuardsArePresentAroundCrossTableIdentityClaims(): void
	{
		$service = file_get_contents(APPPATH . 'libraries/LabResultService.php');
		$report = file_get_contents(APPPATH . 'models/LabReport_model.php');
		$pending = file_get_contents(APPPATH . 'models/LabReportPending_model.php');

		$this->assertStringContainsString('GET_LOCK', $service);
		$this->assertStringContainsString('RELEASE_LOCK', $service);
		$this->assertStringContainsString('FOR UPDATE', $report);
		$this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $report);
		$this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $pending);
	}

	public function testConcurrentMatchedDeliveriesLeaveOneCompleteReport(): void
	{
		$petId = $this->existingPetId();
		$results = $this->runConcurrentIngestion([
			[$this->prefix, (string) $petId, '5.5'],
			[$this->prefix, (string) $petId, '6.5'],
		]);

		$this->assertSame([['status' => 'ok'], ['status' => 'ok']], $results);
		$report = $this->ci->db->where('source_id', $this->prefix)->get('lab_report')->row_array();
		$this->assertNotNull($report);
		$this->assertSame(1, $this->ci->db->where('source_id', $this->prefix)->count_all_results('lab_report'));
		$this->assertSame(1, $this->ci->db->where('report_id', $report['id'])->count_all_results('lab_results'));
		$this->assertSame(1, $this->ci->db->where('report_id', $report['id'])->count_all_results('lab_plots'));
		$this->assertSame(0, $this->ci->db->where('source_id', $this->prefix)->where('resolved_at IS NULL', null, false)->where('deleted_at IS NULL', null, false)->count_all_results('lab_report_pending'));
	}

	public function testConcurrentMatchedAndUnmatchedDeliveriesDoNotLeaveContradictoryPendingState(): void
	{
		$petId = $this->existingPetId();
		$results = $this->runConcurrentIngestion([
			[$this->prefix, '-', '5.5'],
			[$this->prefix, (string) $petId, '6.5'],
		]);

		$statuses = array_column($results, 'status');
		$this->assertContains('ok', $statuses);
		$this->assertContains($statuses[0] === 'ok' ? $statuses[1] : $statuses[0], ['ok', 'pending']);
		$report = $this->ci->db->where('source_id', $this->prefix)->get('lab_report')->row_array();
		$this->assertNotNull($report);
		$this->assertSame(1, $this->ci->db->where('source_id', $this->prefix)->count_all_results('lab_report'));
		$this->assertSame(1, $this->ci->db->where('report_id', $report['id'])->count_all_results('lab_results'));
		$this->assertSame(1, $this->ci->db->where('report_id', $report['id'])->count_all_results('lab_plots'));
		$this->assertSame(0, $this->ci->db->where('source_id', $this->prefix)->where('resolved_at IS NULL', null, false)->where('deleted_at IS NULL', null, false)->count_all_results('lab_report_pending'));
	}

    public function testMatchedIngestionPersistsResultsAndPlotsAtomically(): void
    {
        $petId = $this->existingPetId();
        $response = $this->ci->labresultservice->ingest(new LabPendingSourceAdapter($this->prefix, $petId), []);

        $this->assertSame(['status' => 'ok'], $response);
        $report = $this->ci->db->where('source_id', $this->prefix)->get('lab_report')->row_array();
        $this->assertSame($petId, (int) $report['pet_id']);
        $this->assertSame(1, $this->ci->db->where('report_id', $report['id'])->count_all_results('lab_results'));
        $this->assertSame(1, $this->ci->db->where('report_id', $report['id'])->count_all_results('lab_plots'));
    }

    public function testPersistenceFailureRollsBackNewReport(): void
    {
        $petId = $this->existingPetId();
        $this->expectException(UnexpectedValueException::class);
        try {
            $this->ci->labresultservice->ingest(new LabPendingSourceAdapter($this->prefix, $petId, false), []);
        } finally {
            $this->assertSame(0, $this->ci->db->where('source_id', $this->prefix)->count_all_results('lab_report'));
        }
    }

    public function testRecoversCurrentPayloadAndRejectsRepeatedSubmission(): void
    {
        $petId = $this->existingPetId();
        $userId = $this->existingUserId();
        $sourceId = $this->prefix;
        $pendingId = $this->insertLmscanPending($sourceId);

        $result = $this->ci->labresultservice->recover_pending($pendingId, $this->ownerIdForPet($petId), $petId, $userId);
        $this->assertSame('ok', $result['status']);
        $this->assertSame($petId, $result['pet_id']);
        $pending = $this->ci->db->where('id', $pendingId)->get('lab_report_pending')->row_array();
        $this->assertSame($petId, (int) $pending['resolved_pet_id']);
        $this->assertSame($userId, (int) $pending['resolved_by']);
        $this->assertSame((int) $result['report_id'], (int) $pending['report_id']);
        $this->assertNotNull($pending['resolved_at']);

        $again = $this->ci->labresultservice->recover_pending($pendingId, $this->ownerIdForPet($petId), $petId, $userId);
        $this->assertSame('error', $again['status']);
        $this->assertSame(1, $this->ci->db->where('source_id', $sourceId)->count_all_results('lab_report'));
    }

    public function testRecoversLegacyPayloadAndCanonicalizesResults(): void
    {
        $petId = $this->existingPetId();
        $userId = $this->existingUserId();
        $pendingId = $this->insertLegacyPending($this->prefix);

        $result = $this->ci->labresultservice->recover_pending($pendingId, $this->ownerIdForPet($petId), $petId, $userId);
        $this->assertSame('ok', $result['status']);
        $report = $this->ci->db->where('id', $result['report_id'])->get('lab_report')->row_array();
        $labResult = $this->ci->db->where('report_id', $result['report_id'])->get('lab_results')->row_array();
        $this->assertSame('WBC', $labResult['code']);
        $this->assertSame('4.50', number_format((float) $labResult['value_num'], 2, '.', ''));
        $this->assertStringContainsString('legacy_detail_comments', $report['metadata']);
    }

    public function testMalformedPayloadRollsBackAndRemainsActive(): void
    {
        $pendingId = $this->insertPending([
            'device' => 'lmscan', 'source_id' => $this->prefix,
            'raw_payload' => '{malformed', 'reason' => 'pet_not_found',
        ]);

        $petId = $this->existingPetId();
        $result = $this->ci->labresultservice->recover_pending($pendingId, $this->ownerIdForPet($petId), $petId, $this->existingUserId());
        $this->assertSame('error', $result['status']);
        $pending = $this->ci->db->where('id', $pendingId)->get('lab_report_pending')->row_array();
        $this->assertNull($pending['resolved_at']);
        $this->assertNull($pending['deleted_at']);
        $this->assertSame(0, $this->ci->db->where('source_id', $this->prefix)->count_all_results('lab_report'));
    }

	public function testRecoveryRequiresAnExistingOwnerAndPetOwnedByThatOwner(): void
	{
		$petId = $this->existingPetId();
		$ownerId = $this->ownerIdForPet($petId);
		$otherOwner = $this->ci->db->select('id')->where('id !=', $ownerId)
			->where('deleted_at IS NULL', null, false)->order_by('id', 'ASC')->get('owners')->row_array();
		$this->assertNotNull($otherOwner);
		$pendingId = $this->insertLmscanPending($this->prefix);
		$userId = $this->existingUserId();

		$missingOwner = $this->ci->labresultservice->recover_pending($pendingId, 0, $petId, $userId);
		$this->assertSame('error', $missingOwner['status']);
		$this->assertStringContainsString('valid owner', $missingOwner['message']);
		$mismatch = $this->ci->labresultservice->recover_pending($pendingId, (int) $otherOwner['id'], $petId, $userId);
		$this->assertSame('error', $mismatch['status']);
		$this->assertStringContainsString('selected owner', $mismatch['message']);

		$row = $this->ci->db->where('id', $pendingId)->get('lab_report_pending')->row_array();
		$this->assertNull($row['resolved_at']);
		$this->assertSame(0, $this->ci->db->where('source_id', $this->prefix)->count_all_results('lab_report'));
	}

    public function testSamePetSourceIsRefreshedButOtherPetSourceIsRejected(): void
    {
        $pets = $this->ci->db->select('pets.id')->from('pets')->join('owners', 'owners.id = pets.owner')
			->where('pets.death', 0)->where('pets.transfered', 0)
			->where('pets.deleted_at IS NULL', null, false)->where('owners.deleted_at IS NULL', null, false)
			->order_by('pets.id', 'asc')->limit(2)->get()->result_array();
        $this->assertCount(2, $pets);
        $petId = (int) $pets[0]['id'];
        $otherPetId = (int) $pets[1]['id'];
        $userId = $this->existingUserId();
        $sourceId = $this->prefix;
        $reportId = $this->insertReport($petId, $sourceId);
        $samePending = $this->insertLmscanPending($sourceId);

        $same = $this->ci->labresultservice->recover_pending($samePending, $this->ownerIdForPet($petId), $petId, $userId);
        $this->assertSame('ok', $same['status']);
        $this->assertSame($reportId, (int) $same['report_id']);
        $this->assertSame(1, $this->ci->db->where('source_id', $sourceId)->count_all_results('lab_report'));

        $conflictSource = $this->prefix . '_conflict';
        $this->insertReport($petId, $conflictSource);
        $conflictPending = $this->insertLmscanPending($conflictSource);
        $conflict = $this->ci->labresultservice->recover_pending($conflictPending, $this->ownerIdForPet($otherPetId), $otherPetId, $userId);
        $this->assertSame('error', $conflict['status']);
        $this->assertStringContainsString('another pet', $conflict['message']);
        $this->assertNotNull($this->ci->db->where('id', $conflictPending)->where('resolved_at IS NULL', null, false)->get('lab_report_pending')->row_array());
        $this->assertSame($petId, (int) $this->ci->db->where('source_id', $conflictSource)->get('lab_report')->row_array()['pet_id']);
    }

    public function testSoftDeleteRetainsPayloadAndIsRepeatSafe(): void
    {
        $pendingId = $this->insertLmscanPending($this->prefix);
        $userId = $this->existingUserId();
        $first = $this->ci->labresultservice->soft_delete_pending($pendingId, $userId);
        $second = $this->ci->labresultservice->soft_delete_pending($pendingId, $userId);

        $this->assertSame('ok', $first['status']);
        $this->assertSame('error', $second['status']);
        $pending = $this->ci->db->where('id', $pendingId)->get('lab_report_pending')->row_array();
        $this->assertNotNull($pending['raw_payload']);
        $this->assertSame($userId, (int) $pending['deleted_by']);
        $this->assertNotNull($pending['deleted_at']);
        $this->assertNull($pending['resolved_at']);
    }

    private function insertLmscanPending(string $sourceId): int
    {
        return $this->insertPending([
            'device' => 'lmscan',
            'source_id' => $sourceId,
            'raw_payload' => json_encode([
                'serial_number' => $sourceId,
                'sample_id' => 'unmatched',
                'test_end_time' => '2026-08-27 12:00:00',
                'project_number' => 'P1', 'project_name' => 'GLU',
                'sample_type' => 'blood', 'result' => '5.5', 'unit' => 'mmol/L',
                'ref_low' => '4.0', 'ref_high' => '7.0',
            ]),
            'reason' => 'pet_not_found',
        ]);
    }

    private function insertLegacyPending(string $sourceId): int
    {
        return $this->insertPending([
            'device' => 'medilab', 'source' => 'medilab', 'source_id' => $sourceId,
            'reason' => 'legacy_missing_pet',
            'raw_payload' => json_encode([
                'legacy_table' => 'lab',
                'lab' => [
                    'id' => 77, 'lab_id' => $sourceId, 'source' => 'medilab',
                    'lab_date' => '2026-08-20', 'lab_comment' => 'Report comment',
                    'created_at' => '2026-08-20 10:00:00',
                ],
                'details' => [[
                    'lab_code' => '1', 'lab_code_text' => 'Witte bloedcellen',
                    'value' => '4.5', 'string_value' => '', 'unit' => 'G/L',
                    'lower_limit' => '2', 'upper_limit' => '8', 'comment' => 'Detail comment',
                ]],
            ]),
        ]);
    }

    private function insertPending(array $values): int
    {
        $this->ci->db->insert('lab_report_pending', array_merge([
            'device' => 'lmscan', 'source' => null, 'source_id' => $this->prefix,
            'raw_payload' => '{}', 'identifiers' => '{}', 'reason' => 'pet_not_found',
            'created_at' => date('Y-m-d H:i:s'),
        ], $values));
        return (int) $this->ci->db->insert_id();
    }

    private function insertReport(int $petId, string $sourceId): int
    {
        $this->ci->db->insert('lab_report', [
            'pet_id' => $petId, 'device' => 'lmscan', 'source' => null,
            'source_id' => $sourceId, 'sample_date' => '2026-01-01 00:00:00',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->ci->db->insert_id();
    }

    private function existingPetId(): int
    {
		return (int) $this->ci->db->select('pets.id')->from('pets')->join('owners', 'owners.id = pets.owner')
			->where('pets.death', 0)->where('pets.transfered', 0)
			->where('pets.deleted_at IS NULL', null, false)->where('owners.deleted_at IS NULL', null, false)
			->order_by('pets.id', 'asc')->get()->row_array()['id'];
    }

    private function existingUserId(): int
    {
        return (int) $this->ci->db->select('id')->order_by('id', 'asc')->get('users')->row_array()['id'];
    }

	private function ownerIdForPet(int $petId): int
	{
		return (int) $this->ci->db->select('owner')->where('id', $petId)->get('pets')->row_array()['owner'];
	}

	private function runConcurrentIngestion(array $jobs): array
	{
		$processes = array();
		foreach ($jobs as $job) {
			$command = array_merge([PHP_BINARY, __DIR__ . '/../Support/LabIngestionWorker.php'], $job);
			$pipes = array();
			$process = proc_open($command, [
				0 => ['pipe', 'r'],
				1 => ['pipe', 'w'],
				2 => ['pipe', 'w'],
			], $pipes, dirname(__DIR__, 2));
			$this->assertIsResource($process);
			fclose($pipes[0]);
			$processes[] = array($process, $pipes);
		}

		$results = array();
		foreach ($processes as [$process, $pipes]) {
			$stdout = stream_get_contents($pipes[1]);
			$stderr = stream_get_contents($pipes[2]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			$exitCode = proc_close($process);
			$this->assertSame(0, $exitCode, $stderr);
			$decoded = json_decode(trim($stdout), true);
			$this->assertIsArray($decoded, $stdout . $stderr);
			$results[] = $decoded;
		}

		return $results;
	}
}

final class LabPendingSourceAdapter implements DeviceAdapterInterface
{
    public function __construct(private string $sourceId, private ?int $petId, private bool $valid = true, private string $value = '5.5') {}

    public function parse(array $input)
    {
        return [
            'device' => 'phpunit', 'source' => 'remote', 'source_id' => $this->sourceId,
            'pet_id' => $this->petId, 'sample_date' => '2026-08-27 12:00:00',
            'results' => [[
				'code' => $this->valid ? 'GLU' : null, 'value' => $this->value, 'unit' => 'mmol/L',
                'ref_min' => 4.0, 'ref_max' => 7.0,
            ]],
            'plots' => ['curve' => [1, 2, 3]],
        ];
    }
}

final class LabIdentitylessSourceAdapter implements DeviceAdapterInterface
{
	public function __construct(private string $marker) {}

	public function parse(array $input)
	{
		return [
			'device' => null, 'source' => null, 'source_id' => null,
			'owner_name' => $this->marker, 'results' => [[
				'code' => 'GLU', 'value' => '5.5', 'unit' => 'mmol/L', 'ref_min' => 4.0, 'ref_max' => 7.0,
			]],
		];
	}
}

final class LabSourceFallbackAdapter implements DeviceAdapterInterface
{
	public function __construct(private string $sourceId) {}

	public function parse(array $input)
	{
		return [
			'device' => 'replacement-device', 'source' => 'remote', 'source_id' => $this->sourceId,
			'results' => [[
				'code' => 'GLU', 'value' => '6.5', 'unit' => 'mmol/L', 'ref_min' => 4.0, 'ref_max' => 7.0,
			]],
		];
	}
}
