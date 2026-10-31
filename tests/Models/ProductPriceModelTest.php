<?php

declare(strict_types=1);

/**
 * T372  Source: application/models/Product_price_model.php
 *
 * Rules in plain words:
 *  - add_price(pid, price, volume) inserts a products_price row, returns its id, logs INFO 'add_price'.
 *  - update_price(pid, ppid, price, volume) changes that row and logs INFO 'modify_price'.
 *  - rm_price(ppid) hard deletes the row, logs INFO 'rm_price' and returns the product id; for an unknown id it
 *    returns 0 and logs ERROR 'failed_to_rm_price'.
 */
final class ProductPriceModelTest extends CodeIgniterDatabaseTestCase
{
    private $prices;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
        $this->prices = $this->model('Product_price_model', 'price_t372');
    }

    private function logs(string $event): array
    {
        return $this->ci->db->where('event', $event)->order_by('id')->get('log')->result_array();
    }

    private function priceRow(int $id): ?array
    {
        return $this->ci->db->where('id', $id)->get('products_price')->row_array();
    }

    public function testAddPriceInsertsTheRowReturnsItsIdAndLogs(): void
    {
        $pid = Factory::product(['price' => null]);
        $before = count($this->logs('add_price'));

        $id = $this->prices->add_price($pid, 12.5, 3);

        $this->assertGreaterThan(0, $id);
        $row = $this->priceRow((int) $id);
        $this->assertSame($pid, (int) $row['product_id']);
        $this->assertEqualsWithDelta(12.5, (float) $row['price'], 0.001);
        $this->assertEqualsWithDelta(3.0, (float) $row['volume'], 0.001);
        $log = $this->logs('add_price');
        $this->assertCount($before + 1, $log);
        $last = end($log);
        $this->assertSame(INFO, (int) $last['level']);
        // price is cast to int in the message: (int) 12.5 = 12
        $this->assertSame('Add price:12 for volume: 3 for pid ' . $pid, $last['msg']);
    }

    public function testUpdatePriceChangesPriceAndVolumeOfThatRowOnly(): void
    {
        $pid = Factory::product(['price' => null]);
        $one = Factory::productPrice($pid, 1.0, 10.0);
        $two = Factory::productPrice($pid, 5.0, 8.0);

        $this->prices->update_price($pid, $one, 9.5, 2);

        $this->assertEqualsWithDelta(9.5, (float) $this->priceRow($one)['price'], 0.001);
        $this->assertEqualsWithDelta(2.0, (float) $this->priceRow($one)['volume'], 0.001);
        $this->assertEqualsWithDelta(8.0, (float) $this->priceRow($two)['price'], 0.001);
        $this->assertEqualsWithDelta(5.0, (float) $this->priceRow($two)['volume'], 0.001);
        $log = $this->logs('modify_price');
        $this->assertCount(1, $log);
        $this->assertSame(INFO, (int) $log[0]['level']);
        $this->assertSame('Change price pid:' . $pid . ' on price_id:' . $one . ' for volume: 2 to (price)9.5', $log[0]['msg']);
    }

    public function testRemovePriceDeletesTheRowAndReturnsTheProductId(): void
    {
        $pid = Factory::product(['price' => null]);
        $one = Factory::productPrice($pid, 1.0, 10.0);
        $two = Factory::productPrice($pid, 5.0, 8.0);

        $this->assertSame($pid, (int) $this->prices->rm_price($one));

        $this->assertNull($this->priceRow($one));
        $this->assertNotNull($this->priceRow($two));
        $log = $this->logs('rm_price');
        $this->assertCount(1, $log);
        $this->assertSame(INFO, (int) $log[0]['level']);
        $this->assertSame('rm price pid:' . $one . ' pid:' . $pid, $log[0]['msg']);
        $this->assertSame([], $this->logs('failed_to_rm_price'));
    }

    public function testRemovingAnUnknownPriceReturnsZeroAndLogsAnError(): void
    {
        $this->assertSame(0, $this->prices->rm_price(2147000000));

        $log = $this->logs('failed_to_rm_price');
        $this->assertCount(1, $log);
        $this->assertSame(ERROR, (int) $log[0]['level']);
        $this->assertSame('rm price pid:2147000000', $log[0]['msg']);
        $this->assertSame([], $this->logs('rm_price'));
    }
}
