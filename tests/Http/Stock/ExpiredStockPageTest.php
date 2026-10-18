<?php

declare(strict_types=1);

/**
 * T173 - Stock::expired_stock lists IN_USE rows whose eol lies between 360 days ago and 90 days
 * from now, ordered by eol (oldest first).
 */
final class ExpiredStockPageTest extends HttpTestCase
{
    private function lot(int $pid, string $lot, string $offset, array $over = []): int
    {
        return Factory::stock($pid, $over + ['lotnr' => $lot, 'eol' => date('Y-m-d', strtotime($offset))]);
    }

    public function testOnlyRowsInTheWindowAreListedOldestFirst(): void
    {
        $pid = Factory::product(['name' => 'Expiry Product']);
        $this->lot($pid, 'EXP-SOON', '+60 days');
        $this->lot($pid, 'EXP-PAST', '-100 days');
        $this->lot($pid, 'EXP-OLDEST', '-300 days');
        $this->lot($pid, 'EXP-FAR', '+1 year');        // later than 90 days
        $this->lot($pid, 'EXP-ANCIENT', '-400 days');  // earlier than 360 days ago
        $this->lot($pid, 'EXP-HISTORY', '-50 days', ['state' => 2]);
        $this->lot($pid, 'EXP-CHECK', '-50 days', ['state' => 0]);

        $this->loginAs('vet');
        $page = $this->get('stock/expired_stock');

        $this->assertStatus($page, 200);
        foreach (['EXP-SOON', 'EXP-PAST', 'EXP-OLDEST'] as $lot) {
            $this->assertSee($page, $lot);
        }
        foreach (['EXP-FAR', 'EXP-ANCIENT', 'EXP-HISTORY', 'EXP-CHECK'] as $lot) {
            $this->assertDontSee($page, $lot);
        }
        $text = $this->text($page);
        $this->assertLessThan(strpos($text, 'EXP-PAST'), strpos($text, 'EXP-OLDEST'));
        $this->assertLessThan(strpos($text, 'EXP-SOON'), strpos($text, 'EXP-PAST'));
    }

    public function testTheSeededExpiredRowIsListed(): void
    {
        $this->loginAs('vet');
        $this->assertSee($this->get('stock/expired_stock'), 'AMX-OLD');
    }

    public function testAWrittenOffRowDisappearsFromThePage(): void
    {
        $pid = Factory::product();
        $row = $this->lot($pid, 'EXP-GONE', '-20 days');
        $this->loginAs('vet');
        $this->assertSee($this->get('stock/expired_stock'), 'EXP-GONE');

        $this->get('stock/write_off_full/' . $row);

        $this->assertDontSee($this->get('stock/expired_stock'), 'EXP-GONE');
    }
}
