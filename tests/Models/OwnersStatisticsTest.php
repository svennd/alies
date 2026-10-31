<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T360 Owners statistics and duplicates.   Source: application/models/Owners_model.php
 *
 * Rules in plain words:
 *  - get_per_province counts owners per province (no filter: disabled and soft deleted owners are counted too).
 *  - duplicate_phone: active owners whose first non-null of telephone/mobile/phone2/phone3 starts with 04, shared by
 *    more than one owner.
 *  - disable(id, newOwner) sets disabled=1; with a new owner it also writes a merge message into msg.
 *  - duplicate_owner_street is broken SQL (refers to table alias "owner").
 * The seed has owners too, so everything uses unique values or counts a before/after difference.
 */
final class OwnersStatisticsTest extends CodeIgniterDatabaseTestCase
{
    private $owners;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->owners = $this->model('Owners_model', 'owners_stat');
    }

    private function byKey(array $rows, string $key, string $value): ?array
    {
        foreach ($rows as $row) {
            if ((string) $row[$key] === $value) {
                return $row;
            }
        }

        return null;
    }

    public function testGetPerProvinceCountsOwnersPerProvince(): void
    {
        $province = Factory::unique('Prov');
        Factory::owner(['province' => $province]);
        Factory::owner(['province' => $province]);
        $other = Factory::unique('Prov');
        Factory::owner(['province' => $other]);

        $rows = $this->owners->get_per_province();

        $this->assertSame(2, (int) $this->byKey($rows, 'province', $province)['amount']);
        $this->assertSame(1, (int) $this->byKey($rows, 'province', $other)['amount']);
    }

    public function testDuplicatePhoneFindsActiveOwnersSharingAMobileNumber(): void
    {
        $phone = '0499' . random_int(100000, 999999);
        $a = Factory::owner(['mobile' => $phone]);
        $b = Factory::owner(['telephone' => $phone, 'mobile' => null]);
        Factory::owner(['mobile' => $phone, 'disabled' => 1]); // disabled: not counted

        $rows = $this->owners->duplicate_phone();

        $hit = $this->byKey($rows, 'phone', $phone);
        $this->assertNotNull($hit);
        $this->assertSame(2, (int) $hit['cnt']);
        $this->assertSame('phone', $hit['match_type']);
        $ids = array_map('intval', explode(',', $hit['owner_ids']));
        sort($ids);
        $this->assertSame([$a, $b], $ids);
    }

    public function testDuplicatePhoneIgnoresLandlinesUniqueNumbersAndEmptyOnes(): void
    {
        $landline = '03' . random_int(1000000, 9999999);
        Factory::owner(['telephone' => $landline]);
        Factory::owner(['telephone' => $landline]);
        $single = '0488' . random_int(100000, 999999);
        Factory::owner(['mobile' => $single]);
        // empty telephone wins the COALESCE and is then filtered out, even with a shared mobile behind it
        $mobile = '0477' . random_int(100000, 999999);
        Factory::owner(['telephone' => '', 'mobile' => $mobile]);
        Factory::owner(['telephone' => '', 'mobile' => $mobile]);

        $rows = $this->owners->duplicate_phone();

        $this->assertNull($this->byKey($rows, 'phone', $landline), 'not starting with 04');
        $this->assertNull($this->byKey($rows, 'phone', $single), 'only one owner');
        $this->assertNull($this->byKey($rows, 'phone', $mobile), 'empty telephone hides the mobile');
    }

    public function testDisableWithoutANewOwnerOnlySetsTheFlag(): void
    {
        $id = Factory::owner(['msg' => 'oud bericht']);

        $this->owners->disable($id);

        $row = $this->ci->db->where('id', $id)->get('owners')->row_array();
        $this->assertSame(1, (int) $row['disabled']);
        $this->assertSame('oud bericht', $row['msg']);
        $this->assertNotNull($row['updated_at']);
    }

    public function testDisableWithANewOwnerWritesAMergeMessage(): void
    {
        $id = Factory::owner();
        $other = Factory::owner();

        $this->owners->disable($id, $other);

        $row = $this->ci->db->where('id', $id)->get('owners')->row_array();
        $this->assertSame(1, (int) $row['disabled']);
        $this->assertSame(1, preg_match('/^merge_id_disable:' . $other . ':\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $row['msg']));
        // positive control: the other owner is untouched
        $this->assertSame(0, (int) $this->ci->db->where('id', $other)->get('owners')->row_array()['disabled']);
    }

    #[Group('known-bug')]
    public function testDuplicateOwnerStreetIsBrokenSql(): void
    {
        // KNOWN BUG: the query uses owner.id but the table is called owners: unknown column - decide in CI4
        $db = $this->ci->db;
        $debug = $db->db_debug;
        $db->db_debug = false;
        $failed = false;
        try {
            $this->owners->duplicate_owner_street();
        } catch (\Throwable $e) {
            $failed = true;
        } finally {
            $db->db_debug = $debug;
            // a failed query marks the transaction as failed; roll back here so tearDown cannot skip it
            $db->trans_rollback();
            $db->trans_begin();
        }

        $this->assertTrue($failed, 'duplicate_owner_street throws');
    }
}
