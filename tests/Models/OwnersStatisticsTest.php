<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T360 Owners statistics and duplicates.   Source: application/models/Owners_model.php
 *
 * Rules in plain words:
 *  - get_per_city / get_per_province count owners per main_city / province (no filter: disabled and soft deleted
 *    owners are counted too).
 *  - last_bill_by_year_month_init_vet(5): per year of last_bill and initial vet, only years after (this year - 5)
 *    up to this year.
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

    private function yearTotal(int $year, string $vet): int
    {
        $sum = 0;
        foreach ($this->owners->last_bill_by_year_month_init_vet(5) as $row) {
            if ((int) $row['y'] === $year && $row['vet'] === $vet) {
                $sum += (int) $row['total'];
            }
        }

        return $sum;
    }

    public function testGetPerCityCountsOwnersPerMainCity(): void
    {
        $city = Factory::unique('City');
        Factory::owner(['main_city' => $city]);
        Factory::owner(['main_city' => $city]);
        Factory::owner(['main_city' => $city, 'disabled' => 1]);
        Factory::owner(['main_city' => $city, 'deleted_at' => Factory::raw('NOW()')]);
        $other = Factory::unique('City');
        Factory::owner(['main_city' => $other]);

        $rows = $this->owners->get_per_city();

        // pinned: disabled and soft deleted owners are counted as well
        $this->assertSame(4, (int) $this->byKey($rows, 'main_city', $city)['amount']);
        $this->assertSame(1, (int) $this->byKey($rows, 'main_city', $other)['amount']);
        $this->assertSame(['main_city', 'amount'], array_keys($rows[0]));
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

    public function testLastBillByYearCountsPerYearAndVetWithinTheWindow(): void
    {
        $now = (int) date('Y');
        // the first name of user 6 as stored now (other test classes may rename users)
        $vet = (string) $this->ci->db->where('id', Fixtures::USER_VET2)->get('users')->row()->first_name;
        $before = [
            $now => $this->yearTotal($now, $vet),
            $now - 4 => $this->yearTotal($now - 4, $vet),
            $now - 5 => $this->yearTotal($now - 5, $vet),
            $now + 1 => $this->yearTotal($now + 1, $vet),
        ];

        Factory::owner(['initial_vet' => Fixtures::USER_VET2, 'last_bill' => $now . '-01-15']);
        Factory::owner(['initial_vet' => Fixtures::USER_VET2, 'last_bill' => $now . '-02-15']);
        Factory::owner(['initial_vet' => Fixtures::USER_VET2, 'last_bill' => ($now - 4) . '-06-01']);
        Factory::owner(['initial_vet' => Fixtures::USER_VET2, 'last_bill' => ($now - 5) . '-06-01']);
        Factory::owner(['initial_vet' => Fixtures::USER_VET2, 'last_bill' => ($now + 1) . '-06-01']);

        $this->assertSame($before[$now] + 2, $this->yearTotal($now, $vet));
        $this->assertSame($before[$now - 4] + 1, $this->yearTotal($now - 4, $vet));
        // y > YEAR(today - 5 years): the year five back is out, next year is out as well
        $this->assertSame(0, $this->yearTotal($now - 5, $vet));
        $this->assertSame(0, $this->yearTotal($now + 1, $vet));
        $this->assertSame(0, $before[$now - 5] + $before[$now + 1]);
    }

    public function testLastBillByYearOrdersByYearAscending(): void
    {
        Factory::owner(['last_bill' => (date('Y') - 3) . '-01-01']);
        Factory::owner(['last_bill' => date('Y') . '-01-01']);

        $years = array_map('intval', array_column($this->owners->last_bill_by_year_month_init_vet(5), 'y'));

        $sorted = $years;
        sort($sorted);
        $this->assertSame($sorted, $years);
        $this->assertContains((int) date('Y') - 3, $years);
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
