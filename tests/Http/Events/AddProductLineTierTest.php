<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

require_once __DIR__ . '/EventsHelper.php';

/**
 * T111 - Events::add_line with type 1 (product). The unit price is the price of the largest tier whose
 * volume is <= the requested volume; below the smallest tier the first price is used.
 */
final class AddProductLineTierTest extends HttpTestCase
{
    private function add(int $event, int $product, float $volume, int $btw = 6): array
    {
        return $this->json($this->ajaxPost('events/add_line/' . $event . '/1', [
            'line' => $product, 'title' => 'p', 'volume' => $volume, 'btw' => $btw,
            'booking' => 0, 'booking_default' => Fixtures::BOOKING_PRODUCTS,
        ]));
    }

    /** @return array<string, array{float, float, float, float}> volume, unit price, net, brut */
    public static function tiers(): array
    {
        return [
            // 0.5 is below the first tier: first price. net 5.00, brut 5.30
            'below the first tier' => [0.5, 10.0, 5.0, 5.3],
            // 3 -> tier 1 (10). net 30, brut 31.80
            'between tier 1 and 2' => [3.0, 10.0, 30.0, 31.8],
            // 5 -> exactly tier 2 (8). net 40, brut 42.40
            'exactly on tier 2' => [5.0, 8.0, 40.0, 42.4],
            // 7 -> tier 2 (8). net 56, brut 59.36
            'between tier 2 and 3' => [7.0, 8.0, 56.0, 59.36],
            // 12 -> tier 3 (6). net 72, brut 76.32
            'above the last tier' => [12.0, 6.0, 72.0, 76.32],
        ];
    }

    #[DataProvider('tiers')]
    public function testTheTierDecidesTheUnitPrice(float $volume, float $unit, float $net, float $brut): void
    {
        $e = EventsHelper::openEvent();
        $product = EventsHelper::tieredProduct();
        $this->loginAs('vet');

        $json = $this->add($e['event'], $product, $volume);

        $row = $this->find('events_products', (int) $json['return']);
        $this->assertEqualsWithDelta($unit, (float) $row['unit_price'], 0.001);
        $this->assertEqualsWithDelta($net, (float) $row['price_net'], 0.001);
        $this->assertEqualsWithDelta($brut, (float) $row['price_brut'], 0.001);
        $this->assertEqualsWithDelta($volume, (float) $row['volume'], 0.001);
        $this->assertNull($row['stock_id']);
        $this->assertSame(6, (int) $row['btw']);
        $this->assertEqualsWithDelta($net, (float) $json['net_price'], 0.001);
        $this->assertSame(number_format($brut, 2), $json['brut_price']);
    }

    public function testASingleTierIsUsedAtEveryVolume(): void
    {
        $e = EventsHelper::openEvent();
        $product = Factory::product(['price' => 7.5]);
        $this->loginAs('vet');

        foreach ([0.5, 1.0, 40.0] as $volume) {
            $json = $this->add($e['event'], $product, $volume);
            $row = $this->find('events_products', (int) $json['return']);
            $this->assertEqualsWithDelta(7.5, (float) $row['unit_price'], 0.001, 'volume ' . $volume);
            // net = 7.5 * volume
            $this->assertEqualsWithDelta(7.5 * $volume, (float) $row['price_net'], 0.001);
        }
    }

    #[Group('known-bug')]
    public function testAProductWithoutAnyPriceTierBreaksTheRequest(): void
    {
        // KNOWN BUG: count(false) on the empty tier list throws a TypeError (PHP 8), so the line is not
        // added and the browser gets a 500 instead of a free (price 0) line - decide in CI4
        $e = EventsHelper::openEvent();
        $product = Factory::product(['price' => null]);
        $this->loginAs('vet');

        $response = $this->ajaxPost('events/add_line/' . $e['event'] . '/1', [
            'line' => $product, 'title' => 'p', 'volume' => 1, 'btw' => 6,
            'booking' => 0, 'booking_default' => Fixtures::BOOKING_PRODUCTS,
        ]);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertRowCount(0, 'events_products', ['event_id' => $e['event']]);
    }
}
