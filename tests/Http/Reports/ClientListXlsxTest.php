<?php

declare(strict_types=1);

require_once __DIR__ . '/ReportsHelper.php';

/**
 * T272 - Reports::clients_list (client list as xlsx, used for the end-of-year bookkeeping).
 *
 * Rules: reports/clients_list exports every non-deleted owner (columns id, first_name, last_name, street, nr,
 * city, zip, last_bill; header row upper-cased on the first letter). reports/clients_list/1 only exports owners
 * whose last_bill lies within the last 3 years (an owner without last_bill is not active).
 */
final class ClientListXlsxTest extends HttpTestCase
{
    private static int $recent;
    private static int $old;
    private static int $never;
    private static int $deleted;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$recent = Factory::owner(['last_name' => 'CLX Recent', 'street' => 'Recentlaan', 'nr' => '11', 'zip' => '2018', 'last_bill' => Factory::raw('DATE_SUB(CURDATE(), INTERVAL 1 YEAR)')]);
        self::$old = Factory::owner(['last_name' => 'CLX Old', 'last_bill' => Factory::raw('DATE_SUB(CURDATE(), INTERVAL 4 YEAR)')]);
        self::$never = Factory::owner(['last_name' => 'CLX Never', 'last_bill' => null]);
        self::$deleted = Factory::owner(['last_name' => 'CLX Deleted', 'deleted_at' => Factory::raw('NOW()')]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    private const HEADER = ['Id', 'First_name', 'Last_name', 'Street', 'Nr', 'City', 'Zip', 'Last_bill'];

    public function testTheFullListHasOneRowPerOwnerThatIsNotDeleted(): void
    {
        $response = $this->get('reports/clients_list');

        $this->assertDownload($response, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'client_data.xlsx');
        $this->assertSame('PK', substr((string) $response->getBody(), 0, 2));
        $rows = ReportsHelper::xlsxRows($response);

        $this->assertSame(self::HEADER, $rows[0]);
        $this->assertCount($this->countWhere('owners', ['deleted_at' => null]), array_slice($rows, 1));
        $byName = array_column(array_slice($rows, 1), null, 2);
        $this->assertArrayHasKey('CLX Recent', $byName);
        $this->assertArrayHasKey('CLX Old', $byName);
        $this->assertArrayHasKey('CLX Never', $byName);
        $this->assertArrayNotHasKey('CLX Deleted', $byName);

        $recent = $byName['CLX Recent'];
        $this->assertEquals(self::$recent, $recent[0]);
        $this->assertSame('Test', $recent[1]);
        $this->assertSame('Recentlaan', $recent[3]);
        $this->assertEquals(11, $recent[4]);
        $this->assertSame('Antwerpen', $recent[5]);
        $this->assertEquals(2018, $recent[6]);
        $this->assertEquals(date('Y-m-d', strtotime('-1 year')), $recent[7]);
    }

    public function testTheActiveListOnlyHasOwnersBilledWithinThreeYears(): void
    {
        $response = $this->get('reports/clients_list/1');

        $this->assertDownload($response, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'client_data.xlsx');
        $rows = ReportsHelper::xlsxRows($response);
        $names = array_column(array_slice($rows, 1), 2);

        $this->assertSame(self::HEADER, $rows[0]);
        $this->assertContains('CLX Recent', $names);
        $this->assertNotContains('CLX Old', $names);
        $this->assertNotContains('CLX Never', $names);
        $this->assertNotContains('CLX Deleted', $names);
        $expected = (int) $this->scalar('SELECT COUNT(*) FROM owners WHERE deleted_at IS NULL AND last_bill > DATE_SUB(NOW(), INTERVAL 3 YEAR)');
        $this->assertGreaterThanOrEqual(2, $expected, 'seed owners (billed today) and the CLX owner');
        $this->assertCount($expected, array_slice($rows, 1));
        $this->assertLessThan(count(ReportsHelper::xlsxRows($this->get('reports/clients_list'))), count($rows));
    }
}
