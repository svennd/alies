<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T217 - application/models/Logs_model.php (autoloaded as $this->logs).
 *
 * Rules: logger() writes to table `log` (not `logs`) with the session's user_id and location, or
 * SYSTEM (-1) when the session has none; levels above min_log_level (default DEBUG = 5, so all) are
 * dropped and answered with true. stock() writes to `log_stock` with user id of $this->user->id,
 * and a location of 0/false falls back to the session location. Both also write to the CI file
 * log. Column facts: log.user_id is TINYINT UNSIGNED, so SYSTEM (-1) is stored as 0 there;
 * log_stock.user_id is signed and keeps -1.
 * Not testable: stock() without a location argument and without a session location inserts NULL
 * into log_stock.location (NOT NULL), and CodeIgniter's db_debug then ends the PHP process.
 */
final class LogsModelTest extends CodeIgniterDatabaseTestCase
{
    private Logs_model $logs;
    private int $minLevel;
    private mixed $originalUser;

    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);

        $this->logs = $this->ci->logs;
        $this->minLevel = $this->logs->min_log_level;
        $this->originalUser = $this->logs->user ?? null;
        $this->logs->user = (object) ['id' => 2];
        $this->ci->session->unset_userdata(['user_id', 'location']);
    }

    protected function tearDown(): void
    {
        $this->logs->min_log_level = $this->minLevel;
        $this->logs->user = $this->originalUser;
        $this->ci->session->unset_userdata(['user_id', 'location']);

        parent::tearDown();
    }

    private function lastLog(string $event): ?array
    {
        return $this->ci->db->where('event', $event)->order_by('id', 'DESC')->limit(1)->get('log')->row_array();
    }

    private function lastStockLog(int $product): ?array
    {
        return $this->ci->db->where('product', $product)->order_by('id', 'DESC')->limit(1)->get('log_stock')->row_array();
    }

    public function testTheModelWritesToTheSingularTables(): void
    {
        $this->assertSame('log', $this->logs->table);
        $this->assertTrue($this->ci->db->table_exists('log'));
        $this->assertTrue($this->ci->db->table_exists('log_stock'));
        $this->assertFalse($this->ci->db->table_exists('logs'));
    }

    public function testLoggerInsertsEventLevelMessageUserAndLocationOfTheSession(): void
    {
        $this->ci->session->set_userdata(['user_id' => 2, 'location' => 1]);
        $event = $this->uniqueString('evt');

        $result = $this->logs->logger(INFO, $event, 'a message');

        $this->assertNotFalse($result);
        $row = $this->lastLog($event);
        $this->assertNotNull($row);
        $this->assertSame(INFO, (int) $row['level']);
        $this->assertSame('a message', $row['msg']);
        $this->assertSame(2, (int) $row['user_id']);
        $this->assertSame(1, (int) $row['location']);
        $this->assertNotEmpty($row['created_at']);
    }

    public function testLoggerReturnsTheNewRowId(): void
    {
        $event = $this->uniqueString('evt');

        $id = $this->logs->logger(WARN, $event, 'm');

        $this->assertSame((int) $this->lastLog($event)['id'], (int) $id);
    }

    public function testWithoutASessionTheUserAndLocationAreSystem(): void
    {
        $event = $this->uniqueString('evt');

        $this->logs->logger(ERROR, $event, 'cli run');

        $row = $this->lastLog($event);
        // SYSTEM is -1, but log.user_id is TINYINT UNSIGNED: the non-strict database stores 0
        $this->assertSame(SYSTEM, -1);
        $this->assertSame(0, (int) $row['user_id']);
        $this->assertSame(SYSTEM, (int) $row['location'], 'log.location is signed and keeps -1');
    }

    #[Group('known-bug')]
    public function testTheUserAndLocationParametersAreNotUsed(): void
    {
        // KNOWN BUG: logger() accepts $user and $location but always reads the session.
        $this->ci->session->set_userdata(['user_id' => 2, 'location' => 1]);
        $event = $this->uniqueString('evt');

        $this->logs->logger(INFO, $event, 'm', 6, 2);

        $row = $this->lastLog($event);
        $this->assertSame(2, (int) $row['user_id']);
        $this->assertSame(1, (int) $row['location']);
    }

    public function testEveryLevelIsStoredWithTheDefaultMinimum(): void
    {
        $this->assertSame(DEBUG, $this->logs->min_log_level);
        foreach ([FATAL, ERROR, WARN, INFO, DEBUG] as $level) {
            $event = $this->uniqueString('lvl' . $level);
            $this->logs->logger($level, $event, 'm');
            $this->assertSame($level, (int) $this->lastLog($event)['level']);
        }
    }

    public function testLevelsAboveTheMinimumAreDroppedAndAnswerTrue(): void
    {
        $this->logs->min_log_level = WARN;
        $dropped = $this->uniqueString('drop');
        $kept = $this->uniqueString('keep');

        $this->assertTrue($this->logs->logger(INFO, $dropped, 'm'));
        $this->assertTrue($this->logs->logger(DEBUG, $dropped, 'm'));
        $this->logs->logger(WARN, $kept, 'm');

        $this->assertNull($this->lastLog($dropped));
        $this->assertNotNull($this->lastLog($kept), 'positive control: a level at the minimum is stored');
    }

    public function testStockInsertsIntoLogStockWithTheUserOfTheModel(): void
    {
        $this->ci->session->set_userdata(['location' => 2]);
        $event = $this->uniqueString('stk');

        $result = $this->logs->stock(DEBUG, $event, Fixtures::PRODUCT_TIERED, -2.5, 1);

        $this->assertTrue((bool) $result);
        $row = $this->lastStockLog(Fixtures::PRODUCT_TIERED);
        $this->assertSame($event, $row['event']);
        $this->assertEqualsWithDelta(-2.5, (float) $row['volume'], 0.001);
        $this->assertSame(DEBUG, (int) $row['level']);
        $this->assertSame(2, (int) $row['user_id']);
        $this->assertSame(1, (int) $row['location'], 'an explicit location wins over the session');
        $this->assertNull($this->lastLog($event), 'nothing goes to the general log');
    }

    public function testStockWithLocationZeroOrFalseUsesTheSessionLocation(): void
    {
        $this->ci->session->set_userdata(['location' => 2]);

        $this->logs->stock(DEBUG, $this->uniqueString('a'), Fixtures::PRODUCT_VACCINE, 1.0);
        $this->assertSame(2, (int) $this->lastStockLog(Fixtures::PRODUCT_VACCINE)['location']);

        $this->ci->db->where('product', Fixtures::PRODUCT_VACCINE)->delete('log_stock');
        $this->logs->stock(DEBUG, $this->uniqueString('b'), Fixtures::PRODUCT_VACCINE, 1.0, 0);
        $this->assertSame(2, (int) $this->lastStockLog(Fixtures::PRODUCT_VACCINE)['location']);
    }

    public function testStockKeepsTheSystemUserAsMinusOne(): void
    {
        $this->logs->user = (object) ['id' => SYSTEM];
        $this->logs->stock(DEBUG, $this->uniqueString('d'), Fixtures::PRODUCT_DEAD_VOLUME, 1.0, 1);

        $this->assertSame(-1, (int) $this->lastStockLog(Fixtures::PRODUCT_DEAD_VOLUME)['user_id']);
    }

    public function testStockHonoursTheMinimumLevelToo(): void
    {
        $this->logs->min_log_level = INFO;

        $this->assertTrue($this->logs->stock(DEBUG, $this->uniqueString('e'), Fixtures::PRODUCT_NO_STOCK, 1.0, 1));
        $this->assertNull($this->lastStockLog(Fixtures::PRODUCT_NO_STOCK));

        $this->logs->stock(INFO, $this->uniqueString('f'), Fixtures::PRODUCT_NO_STOCK, 1.0, 1);
        $this->assertNotNull($this->lastStockLog(Fixtures::PRODUCT_NO_STOCK));
    }

    public function testVolumeKeepsTwoDecimals(): void
    {
        $this->logs->stock(DEBUG, $this->uniqueString('g'), Fixtures::PRODUCT_NOT_SELLABLE, 0.125, 1);

        $this->assertSame('0.13', $this->lastStockLog(Fixtures::PRODUCT_NOT_SELLABLE)['volume'], 'DECIMAL(10,2) rounds 0.125 up');
    }
}
