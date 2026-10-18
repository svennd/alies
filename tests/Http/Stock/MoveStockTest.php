<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T170 - move stock between locations (Stock::move, Stock::move_verification, Stock_model::move).
 *
 * The user is logged in at location 1 (Praktijk) and fetches stock from location 2 (Wagen).
 * The source loses the volume; the destination gets a row with the same lot, price, eol and supplier
 * (a new row, or added to an identical IN_USE row). Dead volume is never involved in a move.
 */
final class MoveStockTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('vet');
    }

    /** Source row at the car (location 2). */
    private function source(int $pid, float $volume, string $lot, array $over = []): int
    {
        return Factory::stock($pid, $over + [
            'location' => '2', 'volume' => $volume, 'lotnr' => $lot, 'in_price' => 1.25,
            'eol' => '2031-03-04', 'supplier' => 'MoveSup',
        ]);
    }

    private function move(int $source, int $pid, float $volume, float $max): \Psr\Http\Message\ResponseInterface
    {
        return $this->post('stock/move_verification', [
            'submit' => 1, 'from' => 2,
            'move_volume' => [$source => $volume], 'max_move' => [$source => $max], 'move_prod' => [$source => $pid],
        ]);
    }

    private function atLocation(int $pid, string $location, string $lot): array
    {
        return $this->rows('SELECT * FROM stock WHERE product_id = ? AND location = ? AND lotnr = ? ORDER BY id', [$pid, $location, $lot]);
    }

    public function testTheMovePageAndTheOverviewStepRender(): void
    {
        $pid = Factory::product(['name' => 'Move Overview Product', 'dead_volume' => 0.5]);
        $source = $this->source($pid, 10, 'MOVE-OV');

        $this->assertStatus($this->get('stock/move'), 200);

        $overview = $this->post('stock/move', ['submit' => 1, 'move_volume' => [$source => 3], 'location_hidden' => 2]);
        $this->assertStatus($overview, 200);
        $this->assertSee($overview, 'Move Overview Product');
        $this->assertSee($overview, 'MOVE-OV');
        // nothing is moved by the overview step
        $this->assertEqualsWithDelta(10.0, (float) $this->find('stock', $source)['volume'], 0.001);
    }

    public function testMovingPartOfARowCreatesTheDestinationRow(): void
    {
        $pid = Factory::product(['dead_volume' => 0.5]);
        $source = $this->source($pid, 10, 'MOVE-NEW');

        $response = $this->move($source, $pid, 3, 10);

        $this->assertRedirectsTo($response, 'stock/move/1');
        // source: 10 - 3 = 7, still in use (no dead volume added by a move)
        $src = $this->find('stock', $source);
        $this->assertEqualsWithDelta(7.0, (float) $src['volume'], 0.001);
        $this->assertSame(1, (int) $src['state']);
        // destination at location 1: a new row with the same lot data
        $dest = $this->atLocation($pid, '1', 'MOVE-NEW');
        $this->assertCount(1, $dest);
        $this->assertEqualsWithDelta(3.0, (float) $dest[0]['volume'], 0.001);
        $this->assertSame(1, (int) $dest[0]['state']);
        $this->assertEqualsWithDelta(1.25, (float) $dest[0]['in_price'], 0.001);
        $this->assertSame('2031-03-04', $dest[0]['eol']);
        $this->assertSame('MoveSup', $dest[0]['supplier']);
    }

    public function testMovingAddsToAnIdenticalRowAtTheDestination(): void
    {
        $pid = Factory::product();
        $source = $this->source($pid, 10, 'MOVE-ADD');
        $existing = Factory::stock($pid, [
            'location' => '1', 'volume' => 4, 'lotnr' => 'MOVE-ADD', 'in_price' => 1.25,
            'eol' => '2031-03-04', 'supplier' => 'MoveSup',
        ]);

        $this->move($source, $pid, 3, 10);

        // destination 4 + 3 = 7 in the same row, no extra row; source 10 - 3 = 7
        $this->assertCount(1, $this->atLocation($pid, '1', 'MOVE-ADD'));
        $this->assertEqualsWithDelta(7.0, (float) $this->find('stock', $existing)['volume'], 0.001);
        $this->assertEqualsWithDelta(7.0, (float) $this->find('stock', $source)['volume'], 0.001);
    }

    public function testADifferentPriceAtTheDestinationIsNotMerged(): void
    {
        $pid = Factory::product();
        $source = $this->source($pid, 10, 'MOVE-PRICE');
        $other = Factory::stock($pid, [
            'location' => '1', 'volume' => 4, 'lotnr' => 'MOVE-PRICE', 'in_price' => 9.99,
            'eol' => '2031-03-04', 'supplier' => 'MoveSup',
        ]);

        $this->move($source, $pid, 3, 10);

        $this->assertEqualsWithDelta(4.0, (float) $this->find('stock', $other)['volume'], 0.001);
        $this->assertCount(2, $this->atLocation($pid, '1', 'MOVE-PRICE'));
    }

    public function testMovingTheFullVolumePutsTheSourceInHistory(): void
    {
        $pid = Factory::product();
        $source = $this->source($pid, 10, 'MOVE-FULL');

        $this->move($source, $pid, 10, 10);

        $src = $this->find('stock', $source);
        $this->assertEqualsWithDelta(0.0, (float) $src['volume'], 0.001);
        $this->assertSame(2, (int) $src['state']);
        $dest = $this->atLocation($pid, '1', 'MOVE-FULL');
        $this->assertCount(1, $dest);
        $this->assertEqualsWithDelta(10.0, (float) $dest[0]['volume'], 0.001);
        $this->assertSame(1, (int) $dest[0]['state']);
    }

    #[Group('known-bug')]
    public function testMovingTheFullVolumeDoesNotMergeIntoAnExistingDestinationRow(): void
    {
        // KNOWN BUG: move_add looks for a destination row in the same state as the source row
        // AFTER the reduction (2 = history), so an identical IN_USE row is not found and a
        // second row is created — decide in CI4 whether to keep
        $pid = Factory::product();
        $source = $this->source($pid, 10, 'MOVE-FULL2');
        $existing = Factory::stock($pid, [
            'location' => '1', 'volume' => 4, 'lotnr' => 'MOVE-FULL2', 'in_price' => 1.25,
            'eol' => '2031-03-04', 'supplier' => 'MoveSup',
        ]);

        $this->move($source, $pid, 10, 10);

        $this->assertEqualsWithDelta(4.0, (float) $this->find('stock', $existing)['volume'], 0.001);
        $dest = $this->atLocation($pid, '1', 'MOVE-FULL2');
        $this->assertCount(2, $dest);
        $this->assertEqualsWithDelta(10.0, (float) $dest[1]['volume'], 0.001);
    }

    #[Group('known-bug')]
    public function testMovingMoreThanTheMaximumDrivesTheSourceIntoErrorAndDoesNotRecordTheInfo(): void
    {
        // max_move 10 < volume 12 -> the controller passes info 'OVERDRAW_MOVE', but the model only
        // stores it when the reduction itself fails, so the column stays NULL.
        // KNOWN BUG: the overdraw marker is never stored — decide in CI4 whether to keep
        $pid = Factory::product();
        $source = $this->source($pid, 10, 'MOVE-OVER');

        $this->move($source, $pid, 12, 10);

        // source 10 - 12 = -2 -> state ERROR (3)
        $src = $this->find('stock', $source);
        $this->assertEqualsWithDelta(-2.0, (float) $src['volume'], 0.001);
        $this->assertSame(3, (int) $src['state']);
        $this->assertNull($src['info']);
        $dest = $this->atLocation($pid, '1', 'MOVE-OVER');
        $this->assertCount(1, $dest);
        $this->assertEqualsWithDelta(12.0, (float) $dest[0]['volume'], 0.001);
        // and the problem shows up on the admin stock-problems screen
        $this->cookies->clear();
        $this->loginAs('admin');
        $this->assertSee($this->get('stock/stock_check'), 'MOVE-OVER');
    }

    public function testTheMoveIsWrittenToTheStockLog(): void
    {
        $pid = Factory::product();
        $source = $this->source($pid, 10, 'MOVE-LOG');

        $this->move($source, $pid, 3, 10);

        $from = $this->row("SELECT * FROM log_stock WHERE event = 'move_stock_from' AND product = ?", [$pid]);
        $to = $this->row("SELECT * FROM log_stock WHERE event = 'move_stock_to' AND product = ?", [$pid]);
        $this->assertEqualsWithDelta(-3.0, (float) $from['volume'], 0.001);
        $this->assertSame(2, (int) $from['location']);
        $this->assertEqualsWithDelta(3.0, (float) $to['volume'], 0.001);
        $this->assertSame(1, (int) $to['location']);
    }

    public function testAZeroVolumeMovesNothing(): void
    {
        $pid = Factory::product();
        $source = $this->source($pid, 10, 'MOVE-ZERO');

        $response = $this->move($source, $pid, 0, 10);

        $this->assertRedirectsTo($response, 'stock/move/1');
        $this->assertEqualsWithDelta(10.0, (float) $this->find('stock', $source)['volume'], 0.001);
        $this->assertCount(0, $this->atLocation($pid, '1', 'MOVE-ZERO'));
    }

    public function testWithoutSubmitNothingIsMoved(): void
    {
        $pid = Factory::product();
        $source = $this->source($pid, 10, 'MOVE-NOSUB');

        $response = $this->post('stock/move_verification', ['from' => 2, 'move_volume' => [$source => 3], 'max_move' => [$source => 10], 'move_prod' => [$source => $pid]]);

        $this->assertRedirectsTo($response, 'stock/move/1');
        $this->assertEqualsWithDelta(10.0, (float) $this->find('stock', $source)['volume'], 0.001);
    }
}
