<?php

declare(strict_types=1);

require_once __DIR__ . '/EventsHelper.php';

/** T112 - a product line with a posted stock lot stores stock_id and returns the lot info; stock is untouched. */
final class AddProductLineStockTest extends HttpTestCase
{
    public function testTheLotIsStoredAndReturnedButTheStockIsNotReduced(): void
    {
        $e = EventsHelper::openEvent();
        $product = Factory::product(['price' => 4.0]);
        $eol = date('Y-m-d', strtotime('+200 days'));
        $stock = Factory::stock($product, ['lotnr' => 'LOT-STOCK-12', 'eol' => $eol, 'volume' => 10.0]);
        $this->loginAs('vet');

        $json = $this->json($this->ajaxPost('events/add_line/' . $e['event'] . '/1', [
            'line' => $product, 'title' => 'p', 'volume' => 3, 'btw' => 6, 'stock' => $stock,
            'booking' => 0, 'booking_default' => Fixtures::BOOKING_PRODUCTS,
        ]));

        $this->assertSame('LOT-STOCK-12', $json['stock_lotnr']);
        $this->assertSame($eol, $json['stock_eol']);
        $this->assertEqualsWithDelta(10.0, (float) $json['stock_volume'], 0.001);
        $this->assertSame($stock, (int) $this->find('events_products', (int) $json['return'])['stock_id']);
        $this->assertEqualsWithDelta(10.0, (float) $this->find('stock', $stock)['volume'], 0.001, 'only a paid bill reduces stock');
    }

    public function testWithoutStockNoLotKeysAreReturned(): void
    {
        $e = EventsHelper::openEvent();
        $product = Factory::product();
        $this->loginAs('vet');

        $json = $this->json($this->ajaxPost('events/add_line/' . $e['event'] . '/1', [
            'line' => $product, 'title' => 'p', 'volume' => 1, 'btw' => 6,
            'booking' => 0, 'booking_default' => Fixtures::BOOKING_PRODUCTS,
        ]));

        $this->assertArrayNotHasKey('stock_lotnr', $json);
        $this->assertNull($this->find('events_products', (int) $json['return'])['stock_id']);
    }
}
