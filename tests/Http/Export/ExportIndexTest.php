<?php

declare(strict_types=1);

/**
 * T260 - export (Export::index, admin only).
 *
 * Rules: the range defaults to the current month; the table shows the number of invoiced bills in the
 * range, the sum of total_net and total_brut (number_format with "," decimals and " " thousands) of
 * bills with an invoice_id that are not soft deleted (invoice_date strictly between "from 00:00" and
 * "to 23:59"), and links to export/pdf, export/kluwer and export/csv for the same range. A red notice
 * counts the bills without invoice_id created in the range (DATE(created_at), inclusive; soft-deleted
 * bills are not counted). A POST with search_from / search_to replaces the range.
 */
final class ExportIndexTest extends HttpTestCase
{
    private const FROM = '2016-03-01';
    private const TO = '2016-03-31';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $owner = Factory::owner();
        // invoiced: net 1000.00 + 2000.50 = 3000.50, brut 1060.00 + 2420.61 = 3480.61, two invoices
        Factory::bill($owner, 4, ['invoice_id' => 1, 'invoice_date' => '2016-03-10 10:00:00', 'created_at' => '2016-03-10 10:00:00', 'total_net' => 1000.00, 'total_brut' => 1060.00]);
        Factory::bill($owner, 4, ['invoice_id' => 2, 'invoice_date' => '2016-03-20 10:00:00', 'created_at' => '2016-03-20 10:00:00', 'total_net' => 2000.50, 'total_brut' => 2420.61]);
        // not counted: deleted, window edges, other month
        Factory::bill($owner, 4, ['invoice_id' => 3, 'invoice_date' => '2016-03-11 10:00:00', 'created_at' => '2016-03-11 10:00:00', 'total_net' => 500.00, 'total_brut' => 500.00, 'deleted_at' => '2016-03-12 10:00:00']);
        Factory::bill($owner, 4, ['invoice_id' => 4, 'invoice_date' => '2016-03-01 00:00:00', 'created_at' => '2016-03-01 00:00:00', 'total_net' => 400.00, 'total_brut' => 400.00]);
        Factory::bill($owner, 4, ['invoice_id' => 5, 'invoice_date' => '2016-03-31 23:59:30', 'created_at' => '2016-03-31 23:59:30', 'total_net' => 300.00, 'total_brut' => 300.00]);
        Factory::bill($owner, 4, ['invoice_id' => 6, 'invoice_date' => '2016-04-02 10:00:00', 'created_at' => '2016-04-02 10:00:00', 'total_net' => 200.00, 'total_brut' => 200.00]);
        // bills that were never invoiced: 2 in March (first and last day count), 1 deleted, 1 in April
        Factory::bill($owner, 1, ['created_at' => '2016-03-01 00:00:00']);
        Factory::bill($owner, 0, ['created_at' => '2016-03-31 23:58:00']);
        Factory::bill($owner, 1, ['created_at' => '2016-03-15 12:00:00', 'deleted_at' => '2016-03-16 12:00:00']);
        Factory::bill($owner, 1, ['created_at' => '2016-04-05 12:00:00']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    /** The invoice count, net total and gross total cells of the summary row. */
    private function summary(\Psr\Http\Message\ResponseInterface $response): array
    {
        $this->assertSame(1, preg_match('#<tbody>\s*<tr>(.*?)</tr>#s', (string) $response->getBody(), $row), 'summary row');
        preg_match_all('#<td>(.*?)</td>#s', $row[1], $cells);

        return array_map(static fn (string $c): string => trim(html_entity_decode(strip_tags($c), ENT_QUOTES | ENT_HTML5, 'UTF-8')), array_slice($cells[1], 0, 3));
    }

    private function range(): \Psr\Http\Message\ResponseInterface
    {
        return $this->post('export', ['search_from' => self::FROM, 'search_to' => self::TO, 'submit' => 'usage']);
    }

    public function testPostedRangeShowsCountTotalsAndTheNoticeForNonInvoicedBills(): void
    {
        $response = $this->range();

        $this->assertStatus($response, 200);
        // two invoices; net 3000.50 -> "3 000,50"; brut 3480.61 -> "3 480,61"
        $this->assertSame(['2', '3 000,50', '3 480,61'], $this->summary($response));
        $this->assertStringContainsString('Found <i>2 ', (string) $response->getBody());
    }

    public function testTheLinksCarryTheSelectedRange(): void
    {
        $body = (string) $this->range()->getBody();

        foreach (['pdf', 'kluwer', 'csv'] as $export) {
            $this->assertStringContainsString('export/' . $export . '/' . self::FROM . '/' . self::TO, $body);
        }
        $this->assertStringContainsString('value="' . self::FROM . '"', $body);
        $this->assertStringContainsString('value="' . self::TO . '"', $body);
    }

    public function testARangeWithoutInvoicesShowsZeroAndNoNotice(): void
    {
        $response = $this->post('export', ['search_from' => '2010-01-01', 'search_to' => '2010-01-31']);

        $this->assertStatus($response, 200);
        $this->assertSame(['0', '0,00', '0,00'], $this->summary($response));
        $this->assertStringNotContainsString('Found <i>', (string) $response->getBody());
    }

    public function testTheRangeChangesWhenAnotherOneIsPosted(): void
    {
        $april = $this->post('export', ['search_from' => '2016-04-01', 'search_to' => '2016-04-30']);

        // only invoice 6 (200.00) and one never invoiced bill
        $this->assertSame(['1', '200,00', '200,00'], $this->summary($april));
        $this->assertStringContainsString('Found <i>1 ', (string) $april->getBody());
    }

    public function testWithoutAPostTheCurrentMonthIsUsed(): void
    {
        $owner = Factory::owner();
        $bill = Factory::bill($owner, 4, ['invoice_id' => 77, 'invoice_date' => date('Y-m-d') . ' 12:00:00', 'total_net' => 10.00, 'total_brut' => 10.60]);
        $first = date('Y-m-01');
        $last = date('Y-m-t');

        $response = $this->get('export');

        $this->assertStatus($response, 200);
        $body = (string) $response->getBody();
        $this->assertStringContainsString('export/csv/' . $first . '/' . $last, $body);
        // independent oracle for the number of invoices of this month
        $count = (int) $this->scalar(
            "SELECT COUNT(*) FROM bills WHERE invoice_id IS NOT NULL AND deleted_at IS NULL AND invoice_date > '$first 00:00:00' AND invoice_date < '$last 23:59:00'"
        );
        $this->assertGreaterThanOrEqual(1, $count, 'the bill created above is part of the month (bill ' . $bill . ')');
        $this->assertSame((string) $count, $this->summary($response)[0]);
    }

    public function testOnlyAdminsMaySeeTheExportScreen(): void
    {
        $this->cookies->clear();
        $this->loginAs('vet');

        $response = $this->get('export');

        $this->assertRedirectsTo($response, '');
        $this->assertStringNotContainsString('export/kluwer', (string) $response->getBody());
    }
}
