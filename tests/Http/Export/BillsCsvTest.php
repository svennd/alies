<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T263 - export/csv/{from}/{to} (Export::csv, admin only).
 *
 * Rules: one line per bill that has an invoice_id and whose invoice_date lies strictly after "from 00:00"
 * and strictly before "to 23:59", oldest invoice first; soft-deleted bills and bills without invoice_id
 * are left out. Columns are joined with plain commas (no quoting). The invoice number is year + 5
 * digits because the seeded setting invoice_prefix is set.
 */
final class BillsCsvTest extends HttpTestCase
{
    private const HEADER = 'invoice_id,invoice_date,total_brut,total_net,BTW_0,BTW_6,BTW_21,cash,card,transfer,modified,owner_id,owner_last_name,vet_first_name,location_name';
    private const FROM = '2016-07-01';
    private const TO = '2016-07-31';

    /** @var array<string, int> */
    private static array $bills = [];
    private static int $owner;
    private static int $commaOwner;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$owner = Factory::owner(['last_name' => 'Csvowner']);
        self::$commaOwner = Factory::owner(['last_name' => 'Smith, John']);
        $base = ['created_at' => '2016-07-01 08:00:00'];

        // created in reverse order of invoice date to prove the ordering
        self::$bills['late'] = Factory::bill(self::$owner, 4, $base + [
            'invoice_id' => 12, 'invoice_date' => '2016-07-20 15:30:00', 'total_brut' => 121.00, 'total_net' => 100.00,
            'BTW_0' => 0, 'BTW_6' => 0, 'BTW_21' => 21.00, 'cash' => 0, 'card' => 121.00, 'transfer' => 0, 'modified' => 1,
        ]);
        self::$bills['early'] = Factory::bill(self::$owner, 4, $base + [
            'invoice_id' => 11, 'invoice_date' => '2016-07-05 09:15:00', 'total_brut' => 106.00, 'total_net' => 100.00,
            'BTW_0' => 0, 'BTW_6' => 6.00, 'BTW_21' => 0, 'cash' => 106.00, 'card' => 0, 'transfer' => 0, 'vet' => Fixtures::USER_VET2,
        ]);
        self::$bills['comma'] = Factory::bill(self::$commaOwner, 4, $base + [
            'invoice_id' => 13, 'invoice_date' => '2016-07-25 10:00:00', 'total_brut' => 10.00, 'total_net' => 10.00, 'transfer' => 10.00,
            'location' => Fixtures::LOCATION_CAR,
        ]);
        // left out
        self::$bills['no_invoice'] = Factory::bill(self::$owner, 1, $base + ['invoice_date' => '2016-07-10 10:00:00']);
        self::$bills['deleted'] = Factory::bill(self::$owner, 4, $base + ['invoice_id' => 14, 'invoice_date' => '2016-07-11 10:00:00', 'deleted_at' => '2016-07-12 10:00:00']);
        self::$bills['opening'] = Factory::bill(self::$owner, 4, $base + ['invoice_id' => 15, 'invoice_date' => '2016-07-01 00:00:00']);
        self::$bills['closing'] = Factory::bill(self::$owner, 4, $base + ['invoice_id' => 16, 'invoice_date' => '2016-07-31 23:59:30']);
        self::$bills['before'] = Factory::bill(self::$owner, 4, $base + ['invoice_id' => 17, 'invoice_date' => '2016-06-30 23:00:00']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    /** @return list<string> */
    private function lines(string $from = self::FROM, string $to = self::TO): array
    {
        $response = $this->get('export/csv/' . $from . '/' . $to);
        $this->assertStatus($response, 200);

        return explode("\n", rtrim((string) $response->getBody(), "\n"));
    }

    public function testFirstLineIsExactlyTheHeader(): void
    {
        $this->assertSame(self::HEADER, $this->lines()[0]);
    }

    public function testOneLineForEveryInvoicedBillOldestInvoiceFirstWithTheValuesFromTheDatabase(): void
    {
        $lines = $this->lines();

        $this->assertSame([
            '201600011,2016-07-05 09:15:00,106.00,100.00,0.00,6.00,0.00,106.00,0.00,0.00,0,' . self::$owner . ',Csvowner,' . $this->vetFirstName(Fixtures::USER_VET2) . ',Praktijk',
            '201600012,2016-07-20 15:30:00,121.00,100.00,0.00,0.00,21.00,0.00,121.00,0.00,1,' . self::$owner . ',Csvowner,' . $this->vetFirstName(Fixtures::USER_VET) . ',Praktijk',
            '201600013,2016-07-25 10:00:00,10.00,10.00,0.00,0.00,0.00,0.00,0.00,10.00,0,' . self::$commaOwner . ',Smith, John,' . $this->vetFirstName(Fixtures::USER_VET) . ',' . 'Wagen',
        ], array_slice($lines, 1));
    }

    private function vetFirstName(int $id): string
    {
        return (string) $this->scalar('SELECT first_name FROM users WHERE id = ?', [$id]);
    }

    public function testBillsWithoutInvoiceNumberDeletedBillsAndBillsOnTheWindowEdgesAreLeftOut(): void
    {
        $body = implode("\n", $this->lines());

        foreach (['201600014', '201600015', '201600016', '201600017'] as $number) {
            $this->assertStringNotContainsString($number, $body);
        }
        $this->assertCount(3, array_slice($this->lines(), 1), 'no_invoice, deleted, opening, closing and before are all out');
    }

    public function testTheLastMinuteOfTheLastDayAndTheStartOfTheFirstDayAreMissed(): void
    {
        // pin: "> from 00:00" and "< to 23:59" are strict, so a bill at 00:00:00 on the first day is lost,
        // and so is one at 23:59:30 on the last day; widening the window by a day brings them back
        $wide = implode("\n", $this->lines('2016-06-30', '2016-08-01'));

        $this->assertStringContainsString('201600015,2016-07-01 00:00:00', $wide);
        $this->assertStringContainsString('201600016,2016-07-31 23:59:30', $wide);
        $this->assertStringContainsString('201600017,2016-06-30 23:00:00', $wide, 'the day before is in the wide window');
        $this->assertStringNotContainsString('201600015', implode("\n", $this->lines()));
        $this->assertStringNotContainsString('201600016', implode("\n", $this->lines()));
    }

    public function testACommaInTheLastNameBreaksTheColumnCount(): void
    {
        // there is no quoting, so "Smith, John" becomes two columns: 16 instead of 15
        $lines = $this->lines();

        $this->assertSame(15, count(explode(',', $lines[0])));
        $comma = array_values(array_filter($lines, static fn (string $l): bool => str_starts_with($l, '201600013')))[0];
        $this->assertSame(16, count(explode(',', $comma)));
    }

    public function testAnEmptyWindowGivesOnlyTheHeaderAndNoWarning(): void
    {
        $response = $this->get('export/csv/2010-01-01/2010-01-02');

        $this->assertStatus($response, 200);
        $this->assertSame(self::HEADER . "\n", (string) $response->getBody(), 'foreach over false only raises a hidden warning');
    }

    public function testOnlyAdminsMayExport(): void
    {
        $this->cookies->clear();
        $this->loginAs('vet');

        $response = $this->get('export/csv/' . self::FROM . '/' . self::TO);

        $this->assertRedirectsTo($response, '');
        $this->assertStringNotContainsString('invoice_id,', (string) $response->getBody());
    }
}
