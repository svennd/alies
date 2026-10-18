<?php

declare(strict_types=1);

require_once __DIR__ . '/../Http/Vamreg/VamregHelper.php';

use PHPUnit\Framework\Attributes\Group;

/**
 * T290 - Vamreg_index_model, Vamreg_in_buffer_model and Vamreg_out_buffer_model.
 *
 * Rules: set_ab_status_on_product flags the products (is_antibiotic = 0) whose CNK is in the index:
 * ab_unit_volume = packSize / buy_volume (1 when buy_volume is 1, or unknown), ab_unit = ML for unit_sell
 * 'ml', PACKS when the volume is 1, else PIECE, default_indication NULL -> 'NONE'.
 * The buffers select DRAFT rows with inclusive date bounds; summary groups the OUT rows per CNK.
 */
final class VamregModelsTest extends CodeIgniterDatabaseTestCase
{
    private Vamreg_index_model $index;
    private Vamreg_in_buffer_model $in;
    private Vamreg_out_buffer_model $out;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->index = $this->model('Vamreg_index_model', 'vamreg_index');
        $this->in = $this->model('Vamreg_in_buffer_model', 'vamreg_in');
        $this->out = $this->model('Vamreg_out_buffer_model', 'vamreg_out');
        $this->ci->db->query('DELETE FROM vamreg_in_buffer');
        $this->ci->db->query('DELETE FROM vamreg_out_buffer');
    }

    private function indexRow(string $cnk, ?string $packSize): void
    {
        Factory::insert('vamreg_index', ['cti' => Factory::unique('CTI'), 'cnk' => $cnk, 'ppnNl' => 'P ' . $cnk, 'packSize' => $packSize,
            'susage' => 'VETERINARY', 'created_at' => Factory::raw('NOW()')]);
    }

    private function product(string $cnk, array $over = []): int
    {
        return Factory::product($over + ['cnk' => $cnk, 'is_antibiotic' => 0, 'buy_volume' => 1, 'unit_sell' => 'stuk']);
    }

    private function productRow(int $id): array
    {
        return $this->ci->db->get_where('products', ['id' => $id])->row_array();
    }

    // ---- set_ab_status_on_product -------------------------------------------------------------

    public function testABuyVolumeAboveOneDividesThePackSize(): void
    {
        $this->indexRow('9100001', '20');
        $p = $this->product('9100001', ['buy_volume' => 10]);

        $this->index->set_ab_status_on_product();

        $row = $this->productRow($p);
        $this->assertSame(1, (int) $row['is_antibiotic']);
        $this->assertEqualsWithDelta(2.0, (float) $row['ab_unit_volume'], 0.001);   // 20 / 10
        $this->assertSame('PIECE', $row['ab_unit']);
    }

    public function testABuyVolumeOfOneIsOnePack(): void
    {
        $this->indexRow('9100002', '20');
        $p = $this->product('9100002', ['buy_volume' => 1]);

        $this->index->set_ab_status_on_product();

        $row = $this->productRow($p);
        $this->assertEqualsWithDelta(1.0, (float) $row['ab_unit_volume'], 0.001);
        $this->assertSame('PACKS', $row['ab_unit']);
    }

    public function testUnitSellMlGivesMlAndTheSameVolume(): void
    {
        $this->indexRow('9100003', '20');
        $p = $this->product('9100003', ['buy_volume' => 10, 'unit_sell' => 'ml']);

        $this->index->set_ab_status_on_product();

        $row = $this->productRow($p);
        $this->assertSame('ML', $row['ab_unit']);
        $this->assertEqualsWithDelta(2.0, (float) $row['ab_unit_volume'], 0.001);
    }

    public function testAnUnknownPackSizeFallsBackToOnePack(): void
    {
        $this->indexRow('9100004', null);
        $p = $this->product('9100004', ['buy_volume' => 5]);

        $this->index->set_ab_status_on_product();

        $row = $this->productRow($p);
        $this->assertEqualsWithDelta(1.0, (float) $row['ab_unit_volume'], 0.001);
        $this->assertSame('PACKS', $row['ab_unit']);
    }

    public function testAFractionalVolumeIsKeptWithTwoDecimals(): void
    {
        $this->indexRow('9100005', '20');
        $p = $this->product('9100005', ['buy_volume' => 8]);

        $this->index->set_ab_status_on_product();

        $this->assertEqualsWithDelta(2.5, (float) $this->productRow($p)['ab_unit_volume'], 0.001);   // 20 / 8
    }

    public function testANullDefaultIndicationBecomesNoneAndAnExistingOneStays(): void
    {
        $this->indexRow('9100006', '20');
        $this->indexRow('9100007', '20');
        $none = $this->product('9100006', ['default_indication' => null]);
        $kept = $this->product('9100007', ['default_indication' => 'EYE']);

        $this->index->set_ab_status_on_product();

        $this->assertSame('NONE', $this->productRow($none)['default_indication']);
        $this->assertSame('EYE', $this->productRow($kept)['default_indication']);
    }

    public function testProductsThatAreAlreadyAntibioticsAreNotTouched(): void
    {
        $this->indexRow('9100008', '20');
        $done = $this->product('9100008', ['is_antibiotic' => 1, 'buy_volume' => 10, 'ab_unit' => 'TUBE', 'ab_unit_volume' => 99, 'default_indication' => 'EYE']);

        $this->index->set_ab_status_on_product();

        $row = $this->productRow($done);
        $this->assertSame('TUBE', $row['ab_unit']);
        $this->assertEqualsWithDelta(99.0, (float) $row['ab_unit_volume'], 0.001);
        $this->assertSame('EYE', $row['default_indication']);
    }

    public function testProductsWithoutAnIndexEntryAreNotFlaggedAndTheReturnValueCountsTheChangedRows(): void
    {
        $this->indexRow('9100009', '20');
        $this->indexRow('9100010', '20');
        $unlinked = $this->product('9199999');
        $a = $this->product('9100009');
        $b = $this->product('9100010');
        // flag whatever else is pending first, so only the two rows above count
        $this->index->set_ab_status_on_product();
        $c = $this->product('9100009', ['name' => 'second product with the same cnk']);

        $changed = $this->index->set_ab_status_on_product();

        $this->assertSame(1, $changed);
        $this->assertSame(0, (int) $this->productRow($unlinked)['is_antibiotic']);
        $this->assertSame(1, (int) $this->productRow($c)['is_antibiotic']);
        $this->assertSame(1, (int) $this->productRow($a)['is_antibiotic']);
        $this->assertSame(1, (int) $this->productRow($b)['is_antibiotic']);
    }

    // ---- get_linked_stats / get_linked --------------------------------------------------------

    public function testLinkedStatsCountTheIndexRowsAndTheOnesWithAProduct(): void
    {
        $before = $this->index->get_linked_stats();
        $this->indexRow('9200001', '1');
        $this->indexRow('9200002', '1');
        $this->indexRow('9200003', '1');
        $this->product('9200001');

        $after = $this->index->get_linked_stats();

        $this->assertSame(3, (int) $after['total_products'] - (int) $before['total_products']);
        $this->assertSame(1, (int) $after['linked_products'] - (int) $before['linked_products']);
    }

    #[Group('known-bug')]
    public function testLinkedStatsCountAProductSharedByTwoProductsTwice(): void
    {
        // KNOWN BUG: the left join repeats the index row for every product with that CNK
        $before = $this->index->get_linked_stats();
        $this->indexRow('9200004', '1');
        $this->product('9200004');
        $this->product('9200004', ['name' => 'twin']);

        $after = $this->index->get_linked_stats();

        $this->assertSame(2, (int) $after['total_products'] - (int) $before['total_products']);
        $this->assertSame(2, (int) $after['linked_products'] - (int) $before['linked_products']);
    }

    public function testGetLinkedListsIndexRowsWithTheirProduct(): void
    {
        $this->indexRow('9200005', '12');
        $p = $this->product('9200005', ['name' => 'Linked name']);
        $rows = array_values(array_filter($this->index->get_linked(), fn ($r) => $r['cnk'] === '9200005'));

        $this->assertCount(1, $rows);
        $this->assertSame('Linked name', $rows[0]['product_name']);
        $this->assertSame((string) $p, (string) $rows[0]['product_id']);
    }

    // ---- IN buffer ----------------------------------------------------------------------------

    public function testInDraftsHaveInclusiveBoundsAndOnlyDraftStatus(): void
    {
        $first = VamregHelper::in('2031-01-01');
        $last = VamregHelper::in('2031-03-31');
        VamregHelper::in('2030-12-31');
        VamregHelper::in('2031-04-01');
        VamregHelper::in('2031-02-01', ['status' => 'SENT']);
        VamregHelper::in('2031-02-02', ['status' => 'INVALID']);
        VamregHelper::in('2031-02-03', ['status' => 'ERROR']);

        $drafts = $this->in->get_all_drafts_by_date('2031-01-01', '2031-03-31');

        $this->assertEqualsCanonicalizing([(string) $first, (string) $last], array_column($drafts, 'id'));
    }

    public function testInAggregateCountsDraftsOnly(): void
    {
        VamregHelper::in('2031-01-01');
        VamregHelper::in('2031-03-31');
        VamregHelper::in('2031-02-01', ['status' => 'SENT']);
        VamregHelper::in('2031-04-01');

        $this->assertSame(['draft_rows' => 2], $this->in->send_draft_aggregate('2031-01-01', '2031-03-31'));
        $this->assertSame(['draft_rows' => 0], $this->in->send_draft_aggregate('2040-01-01', '2040-03-31'));
    }

    // ---- OUT buffer ---------------------------------------------------------------------------

    public function testOutDraftsAreInclusiveAndCarryTheVetsOrderNumber(): void
    {
        $first = VamregHelper::out('2031-01-01', ['vet' => 2]);
        $last = VamregHelper::out('2031-03-31', ['vet' => 99]);
        VamregHelper::out('2031-04-01');
        VamregHelper::out('2031-02-02', ['status' => 'INVALID']);

        $drafts = $this->out->get_all_drafts_by_date('2031-01-01', '2031-03-31');

        $this->assertEqualsCanonicalizing([(string) $first, (string) $last], array_column($drafts, 'id'));
        $byId = array_column($drafts, null, 'id');
        $this->assertSame('123456', $byId[$first]['ordernr']);
        $this->assertNull($byId[$last]['ordernr'], 'an unknown vet gives NULL, not an error');
        $this->assertSame(['draft_rows' => 2], $this->out->send_draft_aggregate('2031-01-01', '2031-03-31'));
    }

    public function testSummaryStatusPriorityIsErrorThenDraftThenSent(): void
    {
        VamregHelper::out('2031-02-01', ['cnk' => '9300001', 'status' => 'SENT']);
        VamregHelper::out('2031-02-02', ['cnk' => '9300001', 'status' => 'DRAFT']);
        VamregHelper::out('2031-02-03', ['cnk' => '9300001', 'status' => 'ERROR']);
        VamregHelper::out('2031-02-01', ['cnk' => '9300002', 'status' => 'SENT']);
        VamregHelper::out('2031-02-02', ['cnk' => '9300002', 'status' => 'DRAFT']);
        VamregHelper::out('2031-02-01', ['cnk' => '9300003', 'status' => 'SENT']);

        $rows = array_column($this->out->summary('2031-01-01', '2031-03-31'), null, 'cnk');

        $this->assertSame('ERROR', $rows['9300001']['status']);
        $this->assertSame('DRAFT', $rows['9300002']['status']);
        $this->assertSame('SENT', $rows['9300003']['status']);
    }

    #[Group('known-bug')]
    public function testSummaryCallsAnAllInvalidCnkSentAndStillCountsInvalidQuantities(): void
    {
        // KNOWN BUG: INVALID rows fall in the ELSE branch ("SENT") and are summed like the others
        VamregHelper::out('2031-02-01', ['cnk' => '9300004', 'status' => 'INVALID', 'out_quantity_pack_count' => 4]);

        $row = array_column($this->out->summary('2031-01-01', '2031-03-31'), null, 'cnk')['9300004'];

        $this->assertSame('SENT', $row['status']);
        $this->assertEqualsWithDelta(4.0, (float) $row['total_quantity'], 0.001);
    }

    public function testSummarySumsTheQuantityAndFlagsNegatives(): void
    {
        VamregHelper::out('2031-02-01', ['cnk' => '9300005', 'out_quantity_pack_count' => 1.5]);
        VamregHelper::out('2031-02-02', ['cnk' => '9300005', 'out_quantity_pack_count' => 2]);
        VamregHelper::out('2031-02-03', ['cnk' => '9300006', 'out_quantity_pack_count' => 3]);
        VamregHelper::out('2031-02-04', ['cnk' => '9300006', 'out_quantity_pack_count' => -1]);
        VamregHelper::out('2031-02-05', ['cnk' => '9300007', 'out_quantity_type' => 'UNITS', 'out_quantity_pack_count' => null,
            'out_quantity_unit_count' => 2.5, 'out_quantity_unit' => 'ML']);
        VamregHelper::out('2031-05-05', ['cnk' => '9300005', 'out_quantity_pack_count' => 100]);

        $rows = array_column($this->out->summary('2031-01-01', '2031-03-31'), null, 'cnk');

        $this->assertEqualsWithDelta(3.5, (float) $rows['9300005']['total_quantity'], 0.001);   // 1.5 + 2
        $this->assertSame(0, (int) $rows['9300005']['has_negative']);
        $this->assertEqualsWithDelta(2.0, (float) $rows['9300006']['total_quantity'], 0.001);   // 3 - 1
        $this->assertSame(1, (int) $rows['9300006']['has_negative']);
        $this->assertEqualsWithDelta(2.5, (float) $rows['9300007']['total_quantity'], 0.001);   // unit count when no pack count
    }

    #[Group('known-bug')]
    public function testSummaryDoublesTheQuantityWhenTwoWholesaleRowsShareTheCnk(): void
    {
        // KNOWN BUG: the join on wholesale.cnk repeats every OUT row once per wholesale row with that CNK
        VamregHelper::out('2031-02-01', ['cnk' => '9300008', 'out_quantity_pack_count' => 2]);
        VamregHelper::out('2031-02-02', ['cnk' => '9300009', 'out_quantity_pack_count' => 2]);
        Factory::wholesale(['CNK' => '9300008', 'description' => 'twin A']);
        Factory::wholesale(['CNK' => '9300008', 'description' => 'twin B']);
        Factory::wholesale(['CNK' => '9300009', 'description' => 'single']);

        $rows = array_column($this->out->summary('2031-01-01', '2031-03-31'), null, 'cnk');

        $this->assertEqualsWithDelta(4.0, (float) $rows['9300008']['total_quantity'], 0.001, 'should be 2');
        $this->assertEqualsWithDelta(2.0, (float) $rows['9300009']['total_quantity'], 0.001);
        $this->assertSame('single', $rows['9300009']['wholesale_description']);
    }

    public function testGetByCnkReturnsTheLinesOfOneCnkInTheRange(): void
    {
        $product = Factory::product(['name' => 'By cnk product', 'cnk' => '9400001']);
        $owner = Factory::owner(['last_name' => 'Bycnkowner']);
        $pet = Factory::pet($owner, ['name' => 'Bycnkpet']);
        $event = Factory::event($pet, 0);
        $a = VamregHelper::out('2031-01-31', ['cnk' => '9400001', 'event' => $event, 'out_quantity_pack_count' => 2, 'vet' => 2]);
        VamregHelper::out('2031-02-01', ['cnk' => '9400002']);
        VamregHelper::out('2031-04-01', ['cnk' => '9400001']);
        $this->assertGreaterThan(0, $product);

        $rows = $this->out->get_by_cnk('9400001', '2031-01-01', '2031-03-31');

        $this->assertCount(1, $rows);
        $this->assertSame((string) $a, (string) $rows[0]['id']);
        $this->assertSame('By cnk product', $rows[0]['product_name']);
        $this->assertSame('Bycnkpet', $rows[0]['pet_name']);
        $this->assertSame('Bycnkowner', $rows[0]['owner']);
        $this->assertSame('Victor', $rows[0]['vet_name']);
        $this->assertEqualsWithDelta(2.0, (float) $rows[0]['total_quantity'], 0.001);
        $this->assertSame('PACKS', $rows[0]['unit']);
        $this->assertSame([], $this->out->get_by_cnk('0000000', '2031-01-01', '2031-03-31'));
    }

    public function testGetSimplifiedGivesTheEditableFieldsOfOneRow(): void
    {
        $units = VamregHelper::out('2031-02-01', ['cnk' => '9400003', 'out_quantity_type' => 'UNITS', 'out_quantity_pack_count' => null,
            'out_quantity_unit_count' => 2.5, 'out_quantity_unit' => 'ML']);
        $packs = VamregHelper::out('2031-02-02', ['cnk' => '9400003', 'out_quantity_pack_count' => 3]);

        $u = $this->out->get_simplified($units);
        $p = $this->out->get_simplified($packs);

        $this->assertSame('9400003', $u['cnk']);
        $this->assertEqualsWithDelta(2.5, (float) $u['total_quantity'], 0.001);
        $this->assertSame('ML', $u['unit']);
        $this->assertSame('2031-02-01', $u['out_date']);
        $this->assertEqualsWithDelta(3.0, (float) $p['total_quantity'], 0.001);
        $this->assertSame('PACKS', $p['unit']);
        $this->assertNull($this->out->get_simplified(999999999) ?: null);
    }
}
