<?php

declare(strict_types=1);

/**
 * T371  Source: application/models/Wholesale_model.php (+ Wholesale_price_model, Wholesale_type_model)
 *
 * Rules in plain words:
 *  - get_bruto_increase: products whose wholesale bruto is above last_bruto, percentage = ROUND((bruto-last)/last*100, 1),
 *    highest first; decreases, equal prices and a NULL last_bruto are left out.
 *  - update_price(id, new): false when bruto is unchanged; otherwise sets the new bruto, stores a wholesale_price row
 *    with the OLD bruto (art_nr = vendor_id) and returns true.
 *  - update_record: bruto '-' is skipped (true, nothing stored); new article = insert with last_bruto = bruto; group
 *    null becomes wholesale_type 'unknown' (created on demand); existing article = update, and a history row with the
 *    old bruto only when the price changed. Decimal commas are converted.
 *  - get_wh_id(vendor_id): id or false.
 */
final class WholesaleModelTest extends CodeIgniterDatabaseTestCase
{
    private $wh;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->wh = $this->model('Wholesale_model', 'wholesale_t371');
    }

    private function row($id): array
    {
        return $this->ci->db->where('id', $id)->get('wholesale')->row_array();
    }

    private function history(string $artNr): array
    {
        return $this->ci->db->where('art_nr', $artNr)->order_by('id')->get('wholesale_price')->result_array();
    }

    /** @return array<int,array> keyed by wholesale id */
    private function increases(): array
    {
        $out = [];
        foreach ($this->wh->get_bruto_increase(1000) as $r) {
            $out[(int) $r['wh_id']] = $r;
        }

        return $out;
    }

    // -------------------------------------------------------------- get_bruto_increase

    public function testBrutoIncreaseListsOnlyRisesWithPercentageRoundedToOneDecimal(): void
    {
        $small = Factory::wholesale(['bruto' => 10.34, 'last_bruto' => 10.00]);   // 3.4 %
        $big = Factory::wholesale(['bruto' => 9.00, 'last_bruto' => 8.00]);       // 12.5 %
        $round = Factory::wholesale(['bruto' => 10.06, 'last_bruto' => 10.00]);   // 0.6 %
        $down = Factory::wholesale(['bruto' => 7.00, 'last_bruto' => 8.00]);
        $same = Factory::wholesale(['bruto' => 8.00, 'last_bruto' => 8.00]);
        $none = Factory::wholesale(['bruto' => 8.00, 'last_bruto' => null]);
        foreach ([$small, $big, $round, $down, $same, $none] as $id) {
            Factory::product(['wholesale' => $id]);
        }
        $unlinked = Factory::wholesale(['bruto' => 20.00, 'last_bruto' => 10.00]); // no product: JOIN drops it

        $rows = $this->increases();

        $this->assertEqualsWithDelta(3.4, (float) $rows[$small]['percentage_change'], 0.0001);
        $this->assertEqualsWithDelta(12.5, (float) $rows[$big]['percentage_change'], 0.0001);
        $this->assertEqualsWithDelta(0.6, (float) $rows[$round]['percentage_change'], 0.0001);
        foreach ([$down, $same, $none, $unlinked] as $id) {
            $this->assertArrayNotHasKey($id, $rows);
        }
        // highest percentage first
        $order = array_keys(array_intersect_key($rows, array_flip([$small, $big, $round])));
        $this->assertSame([$big, $small, $round], $order);
    }

    public function testBrutoIncreaseRespectsTheLimit(): void
    {
        $this->assertLessThanOrEqual(1, count($this->wh->get_bruto_increase(1)));
        Factory::product(['wholesale' => Factory::wholesale(['bruto' => 11, 'last_bruto' => 10])]);
        $this->assertCount(1, $this->wh->get_bruto_increase(1));
    }

    // -------------------------------------------------------------- update_price

    public function testUpdatePriceReturnsFalseAndStoresNothingWhenUnchanged(): void
    {
        $id = Factory::wholesale(['vendor_id' => 'UP-SAME', 'bruto' => 10.00]);

        $this->assertFalse($this->wh->update_price($id, '10.00'));
        $this->assertSame([], $this->history('UP-SAME'));
        $this->assertEqualsWithDelta(10.00, (float) $this->row($id)['bruto'], 0.001);
    }

    public function testUpdatePriceStoresTheOldBrutoInTheHistory(): void
    {
        $id = Factory::wholesale(['vendor_id' => 'UP-NEW', 'bruto' => 10.00]);

        $this->assertTrue($this->wh->update_price($id, '12.50'));

        $this->assertEqualsWithDelta(12.50, (float) $this->row($id)['bruto'], 0.001);
        $history = $this->history('UP-NEW');
        $this->assertCount(1, $history);
        $this->assertEqualsWithDelta(10.00, (float) $history[0]['bruto'], 0.001);
    }

    // -------------------------------------------------------------- update_record

    private function record(string $art, string $bruto, $group = 'Geneesmiddelen', string $desc = 'Desc'): void
    {
        $this->wh->update_record($art, $desc, $bruto, 6, '19,95', 'Distri', '1234567', 'VHB1', 'D-1', $group);
    }

    public function testRecordWithDashBrutoIsSkipped(): void
    {
        $this->assertTrue($this->wh->update_record('REC-DASH', 'x', '-', 6, '1', 'd', '1', 'v', 'i', 'g'));
        $this->assertFalse($this->wh->get_wh_id('REC-DASH'));
    }

    public function testRecordInsertsANewArticleWithLastBrutoEqualToBruto(): void
    {
        $this->record('REC-NEW', '12,50', 'NieuweGroep');

        $id = $this->wh->get_wh_id('REC-NEW');
        $row = $this->row($id);
        $this->assertEqualsWithDelta(12.50, (float) $row['bruto'], 0.001);
        $this->assertEqualsWithDelta(12.50, (float) $row['last_bruto'], 0.001);
        $this->assertSame(date('Y-m-d'), $row['last_bruto_date']);
        $this->assertEqualsWithDelta(19.95, (float) $row['sell_price'], 0.001);
        $this->assertSame('Distri', $row['distributor']);
        $this->assertSame('D-1', $row['distributor_id']);
        $type = $this->ci->db->where('name', 'NieuweGroep')->get('wholesale_type')->row_array();
        $this->assertNotNull($type, 'group created on demand');
        $this->assertSame((int) $type['id'], (int) $row['type']);
        $this->assertSame([], $this->history('REC-NEW'), 'a new article has no price history');
    }

    public function testRecordWithoutGroupUsesTheUnknownTypeAndReusesIt(): void
    {
        $this->record('REC-G1', '1,00', null);
        $this->record('REC-G2', '2,00', null);

        $this->assertSame(1, $this->ci->db->where('name', 'unknown')->count_all_results('wholesale_type'));
        $unknown = (int) $this->ci->db->where('name', 'unknown')->get('wholesale_type')->row()->id;
        $this->assertSame($unknown, (int) $this->row($this->wh->get_wh_id('REC-G1'))['type']);
        $this->assertSame($unknown, (int) $this->row($this->wh->get_wh_id('REC-G2'))['type']);
    }

    public function testRecordUpdatesAnExistingArticleAndKeepsTheOldPriceInTheHistory(): void
    {
        $id = Factory::wholesale(['vendor_id' => 'REC-UPD', 'bruto' => 10.00, 'last_bruto' => 10.00, 'description' => 'Old']);

        $this->record('REC-UPD', '11,00', 'Geneesmiddelen', 'Changed');

        $row = $this->row($id);
        $this->assertSame('Changed', $row['description']);
        $this->assertEqualsWithDelta(11.00, (float) $row['bruto'], 0.001);
        $this->assertEqualsWithDelta(10.00, (float) $row['last_bruto'], 0.001, 'update path leaves last_bruto alone');
        $history = $this->history('REC-UPD');
        $this->assertCount(1, $history);
        $this->assertEqualsWithDelta(10.00, (float) $history[0]['bruto'], 0.001);
        $this->assertSame(1, $this->ci->db->where('vendor_id', 'REC-UPD')->count_all_results('wholesale'));
    }

    public function testRecordWithTheSamePriceStoresNoHistory(): void
    {
        Factory::wholesale(['vendor_id' => 'REC-SAME', 'bruto' => 10.00]);

        ob_start();
        $this->record('REC-SAME', '10,00', 'Geneesmiddelen', 'Changed');
        $echoed = ob_get_clean();

        $this->assertSame([], $this->history('REC-SAME'));
        // the optional echo of "ERROR : issue updating product" depends on MY_Model::update; only pin that nothing else is printed
        $this->assertContains($echoed, ['', "ERROR : issue updating product\n"]);
    }

    // -------------------------------------------------------------- others

    public function testGetWhIdFindsTheRowOrReturnsFalse(): void
    {
        $id = Factory::wholesale(['vendor_id' => 'WHID-1']);

        $this->assertSame($id, (int) $this->wh->get_wh_id('WHID-1'));
        $this->assertFalse($this->wh->get_wh_id('WHID-NOPE'));
    }

    public function testSearchIndexFindsByDescriptionVendorIdAndProductName(): void
    {
        $token = Factory::unique('Zq');
        $byDesc = Factory::wholesale(['description' => 'Pil ' . $token]);
        $byVendor = Factory::wholesale(['vendor_id' => substr($token . 'V', 0, 25)]);
        $byProduct = Factory::wholesale();
        Factory::product(['wholesale' => $byProduct, 'name' => 'Prod ' . $token]);
        $other = Factory::wholesale();

        $ids = array_map('intval', array_column($this->wh->search_index($token), 'id'));
        sort($ids);

        $this->assertSame([$byDesc, $byVendor, $byProduct], $ids);
        $this->assertNotContains($other, $ids);
        $this->assertCount(1, $this->wh->search_index($token, 1));
    }

    public function testIndexStatsAndUnlinkedItemsSeparateLinkedFromUnlinkedArticles(): void
    {
        $before = $this->wh->get_index_stats();
        $linked = Factory::wholesale();
        Factory::product(['wholesale' => $linked]);
        $unlinked = Factory::wholesale();

        $after = $this->wh->get_index_stats();

        $this->assertSame($before['wholesale_count'] + 2, $after['wholesale_count']);
        $this->assertSame($before['unlinked_count'] + 1, $after['unlinked_count']);
        $unlinkedIds = array_map('intval', array_column($this->wh->get_unlinked_items(5000), 'id'));
        $this->assertContains($unlinked, $unlinkedIds);
        $this->assertNotContains($linked, $unlinkedIds);
    }
}
