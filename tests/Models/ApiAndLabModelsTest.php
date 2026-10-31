<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T366 ApiKey_model + ApiRate_model, T367 LabReport_model + LabResult_model.
 * Sources: application/models/{ApiKey,ApiRate,LabReport,LabResult}_model.php
 *
 * Rules in plain words:
 *  - findActive(hash) gives ['id' => ..] for an active key, false for a revoked or unknown one.
 *  - ApiRate hit(key, minute, limit): true while the count for that key and minute is <= limit, false from limit+1;
 *    another minute or another key starts at 1. cleanup(days) deletes rows whose minute string is smaller than
 *    the minute string of "now - days".
 *  - LabReport findBySource tries the device identity first, then the source identity, and does not look at
 *    deleted_at. claimSource gives the existing id for a known (device|source, source_id) and inserts otherwise.
 */
final class ApiAndLabModelsTest extends CodeIgniterDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
    }

    // ------------------------------------------------------------------ T366 ApiKey

    private function keyId(string $hash): int
    {
        return (int) $this->ci->db->where('key_hash', $hash)->get('api_keys')->row()->id;
    }

    public function testFindActiveReturnsTheIdOfAnActiveKey(): void
    {
        $keys = $this->model('ApiKey_model', 'apikey_t366');
        $hash = Factory::apiKey(null, true);

        $row = $keys->findActive($hash);

        $this->assertSame(array('id'), array_keys($row));
        $this->assertSame($this->keyId($hash), (int) $row['id']);
    }

    public function testFindActiveIsFalseForARevokedOrUnknownKey(): void
    {
        $keys = $this->model('ApiKey_model', 'apikey_t366');
        $revoked = Factory::apiKey(null, false);
        $active = Factory::apiKey(null, true);

        $this->assertFalse($keys->findActive($revoked));
        $this->assertFalse($keys->findActive(Factory::unique('unknown')));
        $this->assertNotFalse($keys->findActive($active), 'positive control');
    }

    // ------------------------------------------------------------------ T366 ApiRate

    private function rateCount(int $key, string $minute): int
    {
        $row = $this->ci->db->where(array('api_key_id' => $key, 'minute' => $minute))->get('api_rate_limit')->row();

        return $row ? (int) $row->count : 0;
    }

    public function testHitIsTrueUpToTheLimitAndFalseAfterwards(): void
    {
        $rate = $this->model('ApiRate_model', 'apirate_t366');
        $key = $this->keyId(Factory::apiKey());
        $minute = '202601011200';

        for ($i = 1; $i <= 60; $i++) {
            $this->assertTrue($rate->hit($key, $minute, 60), 'hit ' . $i);
        }
        $this->assertFalse($rate->hit($key, $minute, 60), 'hit 61');
        $this->assertFalse($rate->hit($key, $minute, 60), 'hit 62');
        $this->assertSame(62, $this->rateCount($key, $minute));
    }

    public function testADifferentMinuteOrKeyStartsANewCount(): void
    {
        $rate = $this->model('ApiRate_model', 'apirate_t366');
        $key = $this->keyId(Factory::apiKey());
        $other = $this->keyId(Factory::apiKey());

        $this->assertTrue($rate->hit($key, '202601011200', 2));
        $this->assertTrue($rate->hit($key, '202601011200', 2));
        $this->assertFalse($rate->hit($key, '202601011200', 2));

        $this->assertTrue($rate->hit($key, '202601011201', 2), 'next minute');
        $this->assertSame(1, $this->rateCount($key, '202601011201'));
        $this->assertTrue($rate->hit($other, '202601011200', 2), 'other key');
        $this->assertSame(1, $this->rateCount($other, '202601011200'));
    }

    private function minuteAgo(string $interval): string
    {
        return $this->ci->db->query("SELECT DATE_FORMAT(NOW() - INTERVAL {$interval}, '%Y%m%d%H%i') AS m")->row()->m;
    }

    public function testCleanupDeletesRowsOlderThanTheGivenDays(): void
    {
        $rate = $this->model('ApiRate_model', 'apirate_t366');
        $key = $this->keyId(Factory::apiKey());
        $old = $this->minuteAgo('3 DAY');
        $recent = $this->minuteAgo('1 DAY');
        $now = $this->minuteAgo('0 MINUTE');
        foreach (array($old, $recent, $now) as $m) {
            $rate->hit($key, $m, 60);
        }

        $rate->cleanup(2);

        $this->assertSame(0, $this->rateCount($key, $old));
        $this->assertSame(1, $this->rateCount($key, $recent));
        $this->assertSame(1, $this->rateCount($key, $now));
    }

    public function testCleanupDefaultIsTwoDaysAndOtherDaysAreHonoured(): void
    {
        $rate = $this->model('ApiRate_model', 'apirate_t366');
        $key = $this->keyId(Factory::apiKey());
        $threeDays = $this->minuteAgo('3 DAY');
        $oneDay = $this->minuteAgo('1 DAY');
        $rate->hit($key, $threeDays, 60);
        $rate->hit($key, $oneDay, 60);

        $rate->cleanup(5);
        $this->assertSame(1, $this->rateCount($key, $threeDays), '5 days keeps a 3 day old row');

        $rate->cleanup();
        $this->assertSame(0, $this->rateCount($key, $threeDays));
        $this->assertSame(1, $this->rateCount($key, $oneDay));

        $rate->cleanup(0);
        $this->assertSame(0, $this->rateCount($key, $oneDay));
    }

    public function testCleanupComparesMinuteStrings(): void
    {
        // minute is a CHAR(12) compared as text: non numeric text sorts after the digits and is never cleaned
        $rate = $this->model('ApiRate_model', 'apirate_t366');
        $key = $this->keyId(Factory::apiKey());
        $rate->hit($key, '100000000000', 60);
        $rate->hit($key, 'abcdefabcdef', 60);

        $rate->cleanup(2);

        $this->assertSame(0, $this->rateCount($key, '100000000000'));
        $this->assertSame(1, $this->rateCount($key, 'abcdefabcdef'));
    }

    // ------------------------------------------------------------------ T367 LabReport

    private function pet(): int
    {
        return Factory::pet(Factory::owner());
    }

    public function testFindBySourceUsesTheDeviceIdentityFirst(): void
    {
        $lab = $this->model('LabReport_model', 'labreport_t367');
        $sid = Factory::unique('S');
        $dev = Factory::unique('dev');
        $id = Factory::labReport($this->pet(), array(), array('device' => $dev, 'source' => 'srcA', 'source_id' => $sid));

        $found = $lab->findBySource($dev, 'andere', $sid);

        $this->assertIsObject($found);
        $this->assertSame($id, (int) $found->id);
        $this->assertNull($lab->findBySource('onbekend', 'andere', $sid));
        $this->assertNull($lab->findBySource($dev, null, Factory::unique('other')));
    }

    public function testFindBySourceFallsBackToTheSourceIdentity(): void
    {
        $lab = $this->model('LabReport_model', 'labreport_t367');
        $sid = Factory::unique('S');
        $src = Factory::unique('src');
        $id = Factory::labReport($this->pet(), array(), array('device' => null, 'source' => $src, 'source_id' => $sid));

        $this->assertSame($id, (int) $lab->findBySource('nietbekend', $src, $sid)->id);
        $this->assertSame($id, (int) $lab->findBySource(null, $src, $sid)->id);
        $this->assertSame($id, (int) $lab->findBySource('', ' ' . $src . ' ', $sid)->id, 'values are trimmed');
    }

    public function testFindBySourceWithoutSourceIdOrAuthorityIsNull(): void
    {
        $lab = $this->model('LabReport_model', 'labreport_t367');

        $this->assertNull($lab->findBySource('dev', 'src', null));
        $this->assertNull($lab->findBySource('dev', 'src', '  '));
        $this->assertNull($lab->findBySource(null, null, 'abc'));
    }

    public function testFindBySourceForUpdateReturnsTheSameRow(): void
    {
        $lab = $this->model('LabReport_model', 'labreport_t367');
        $sid = Factory::unique('S');
        $dev = Factory::unique('dev');
        $id = Factory::labReport($this->pet(), array(), array('device' => $dev, 'source_id' => $sid));

        $this->assertSame($id, (int) $lab->findBySource($dev, null, $sid, true)->id);
    }

    #[Group('known-bug')]
    public function testFindBySourceIgnoresDeletedAt(): void
    {
        // KNOWN BUG: a soft deleted report is still found by its source - decide in CI4 whether to keep
        $lab = $this->model('LabReport_model', 'labreport_t367');
        $sid = Factory::unique('S');
        $dev = Factory::unique('dev');
        $id = Factory::labReport($this->pet(), array(), array('device' => $dev, 'source_id' => $sid, 'deleted_at' => Factory::raw('NOW()')));

        $found = $lab->findBySource($dev, null, $sid);

        $this->assertSame($id, (int) $found->id);
        $this->assertNotNull($found->deleted_at);
    }

    private function claimData(int $pet, ?string $device, ?string $source, ?string $sid): array
    {
        return array(
            'pet_id' => $pet, 'device' => $device, 'source' => $source, 'source_id' => $sid,
            'sample_date' => '2025-01-02 03:04:05', 'software_version' => '1.2', 'metadata' => '{}',
            'created_at' => date('Y-m-d H:i:s'),
        );
    }

    private function reportCount(string $column, string $value): int
    {
        return (int) $this->ci->db->where($column, $value)->count_all_results('lab_report');
    }

    public function testClaimSourceInsertsANewReportAndReturnsItsId(): void
    {
        $lab = $this->model('LabReport_model', 'labreport_t367');
        $pet = $this->pet();
        $sid = Factory::unique('S');
        $dev = Factory::unique('dev');

        $id = $lab->claimSource($this->claimData($pet, $dev, 'lab', $sid));

        $this->assertGreaterThan(0, $id);
        $row = $this->ci->db->where('id', $id)->get('lab_report')->row_array();
        $this->assertSame($sid, $row['source_id']);
        $this->assertSame($dev, $row['device']);
        $this->assertSame($pet, (int) $row['pet_id']);
        $this->assertSame('2025-01-02 03:04:05', $row['sample_date']);
    }

    public function testClaimSourceReturnsTheExistingIdForADuplicateSource(): void
    {
        $lab = $this->model('LabReport_model', 'labreport_t367');
        $pet = $this->pet();
        $sid = Factory::unique('S');
        $dev = Factory::unique('dev');

        $first = $lab->claimSource($this->claimData($pet, $dev, 'lab', $sid));
        $second = $lab->claimSource($this->claimData($pet, $dev, 'lab', $sid));
        $third = $lab->claimSource($this->claimData($pet, '  ' . $dev . '  ', 'lab', $sid));
        $other = $lab->claimSource($this->claimData($pet, $dev, 'lab', Factory::unique('S')));

        $this->assertSame($first, $second);
        $this->assertSame($first, $third, 'normalised before the lookup');
        $this->assertNotSame($first, $other);
        $this->assertSame(1, $this->reportCount('source_id', $sid));
    }

    public function testClaimSourceAlsoMatchesOnSourceWhenTheDeviceIsDifferent(): void
    {
        $lab = $this->model('LabReport_model', 'labreport_t367');
        $pet = $this->pet();
        $sid = Factory::unique('S');
        $src = Factory::unique('src');

        $first = $lab->claimSource($this->claimData($pet, Factory::unique('devA'), $src, $sid));
        $second = $lab->claimSource($this->claimData($pet, Factory::unique('devB'), $src, $sid));

        $this->assertSame($first, $second);
    }

    public function testClaimSourceWithoutAUsableIdentityAlwaysInserts(): void
    {
        $lab = $this->model('LabReport_model', 'labreport_t367');
        $pet = $this->pet();
        $dev = Factory::unique('dev');

        $a = $lab->claimSource($this->claimData($pet, $dev, null, null));
        $b = $lab->claimSource($this->claimData($pet, $dev, null, ''));

        $this->assertGreaterThan(0, $a);
        $this->assertNotSame($a, $b);
        $this->assertSame(2, $this->reportCount('device', $dev));
    }

    public function testGetForPetOrdersBySampleDateThenIdDescending(): void
    {
        $lab = $this->model('LabReport_model', 'labreport_t367');
        $pet = $this->pet();
        $old = Factory::labReport($pet, array(), array('sample_date' => '2024-01-01 10:00:00'));
        $new1 = Factory::labReport($pet, array(), array('sample_date' => '2025-06-01 10:00:00'));
        $new2 = Factory::labReport($pet, array(), array('sample_date' => '2025-06-01 10:00:00'));
        Factory::labReport($this->pet());

        $rows = $lab->get_for_pet($pet);

        $this->assertSame(array($new2, $new1, $old), array_map('intval', array_column($rows, 'id')));
        $this->assertSame(array(), $lab->get_for_pet($this->pet()));
    }

    public function testCountAndHasForPet(): void
    {
        $lab = $this->model('LabReport_model', 'labreport_t367');
        $pet = $this->pet();
        $empty = $this->pet();
        Factory::labReport($pet);
        Factory::labReport($pet);

        $this->assertSame(2, $lab->count_for_pet($pet));
        $this->assertTrue($lab->has_for_pet($pet));
        $this->assertSame(0, $lab->count_for_pet($empty));
        $this->assertFalse($lab->has_for_pet($empty));
    }

    #[Group('known-bug')]
    public function testPetQueriesAlsoCountSoftDeletedReports(): void
    {
        // KNOWN BUG: the $soft_delete typo means deleted_at is never filtered (see SoftDeleteInventoryTest)
        $lab = $this->model('LabReport_model', 'labreport_t367');
        $pet = $this->pet();
        Factory::labReport($pet, array(), array('deleted_at' => Factory::raw('NOW()')));

        $this->assertSame(1, $lab->count_for_pet($pet));
        $this->assertCount(1, $lab->get_for_pet($pet));
    }

    // ------------------------------------------------------------------ T367 LabResult

    public function testSaveInsertsAResultRow(): void
    {
        $results = $this->model('LabResult_model', 'labresult_t367');
        $report = Factory::labReport($this->pet());

        $ok = $results->save($report, array(
            'code' => 'GLU', 'value_num' => 5.25, 'value_text' => null, 'unit' => 'mmol/l', 'ref_min' => 3.5, 'ref_max' => 6.5,
        ));

        $this->assertTrue($ok);
        $row = $this->ci->db->where('report_id', $report)->get('lab_results')->row_array();
        $this->assertSame('GLU', $row['code']);
        $this->assertEqualsWithDelta(5.25, (float) $row['value_num'], 0.001);
        $this->assertNull($row['value_text']);
        $this->assertSame('mmol/l', $row['unit']);
        $this->assertEqualsWithDelta(3.5, (float) $row['ref_min'], 0.001);
        $this->assertEqualsWithDelta(6.5, (float) $row['ref_max'], 0.001);
    }

    public function testDeleteByReportRemovesOnlyThatReportsResults(): void
    {
        $results = $this->model('LabResult_model', 'labresult_t367');
        $a = Factory::labReport($this->pet(), array(array('code' => 'A'), array('code' => 'B')));
        $b = Factory::labReport($this->pet(), array(array('code' => 'C')));

        $this->assertTrue($results->deleteByReport($a));

        $this->assertSame(0, (int) $this->ci->db->where('report_id', $a)->count_all_results('lab_results'));
        $this->assertSame(1, (int) $this->ci->db->where('report_id', $b)->count_all_results('lab_results'));
    }

    public function testGetGroupedByReportsOfNoIdsIsEmpty(): void
    {
        $results = $this->model('LabResult_model', 'labresult_t367');

        $this->assertSame(array(), $results->get_grouped_by_reports(array()));
    }

    public function testGetGroupedByReportsGroupsRowsPerReportInInsertOrder(): void
    {
        $results = $this->model('LabResult_model', 'labresult_t367');
        $a = Factory::labReport($this->pet(), array(array('code' => 'A1'), array('code' => 'A2')));
        $b = Factory::labReport($this->pet(), array(array('code' => 'B1')));
        $c = Factory::labReport($this->pet(), array(array('code' => 'C1')));

        $grouped = $results->get_grouped_by_reports(array((string) $a, $b));

        $this->assertSame(array($a, $b), array_keys($grouped));
        $this->assertSame(array('A1', 'A2'), array_column($grouped[$a], 'code'));
        $this->assertSame(array('B1'), array_column($grouped[$b], 'code'));
        $this->assertArrayNotHasKey($c, $grouped);
        $this->assertSame(array(), $results->get_grouped_by_reports(array(Factory::labReport($this->pet()))));
    }
}
