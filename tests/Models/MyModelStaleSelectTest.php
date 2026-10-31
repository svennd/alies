<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * MY_Model keeps the columns of a fields() call until a read has returned rows. This is the cause of a
 * real order dependence in this suite and a trap for the CodeIgniter 4 port: do not copy the behaviour.
 * (Found by EventsModifiedTest leaving Events_model::$_select = id, which then broke CoreWorkflowTest.)
 */
final class MyModelStaleSelectTest extends CodeIgniterDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Factory::useMysqli($this->ci->db->conn_id);
    }

    #[Group('known-bug')]
    public function testASelectionOfAReadThatFoundNothingStaysForTheNextRead(): void
    {
        // KNOWN BUG: get_all() returns false for "no rows" before the selection is reset
        $sticky = $this->model('Sticky_model', 'stale_select_sticky');
        $id = Factory::insert('sticky', ['user_id' => 2, 'location' => 1, 'note' => 'stale select probe', 'private' => 0, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);

        $this->assertFalse($sticky->fields('id')->where('note', 'matches nothing at all')->get_all());
        $next = $sticky->get($id);

        $this->assertSame(['id'], array_keys($next), 'the next read only returns the stale selection');
    }

    public function testAfterASuccessfulReadTheSelectionIsGone(): void
    {
        $sticky = $this->model('Sticky_model', 'fresh_select_sticky');
        $id = Factory::insert('sticky', ['user_id' => 2, 'location' => 1, 'note' => 'fresh select probe', 'private' => 0, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);

        $this->assertSame(['id'], array_keys($sticky->fields('id')->get($id)));

        $this->assertGreaterThan(1, count($sticky->get($id)), 'the second read returns every column again');
    }

    public function testTheBaseClassClearsTheStaleSelectionBetweenTests(): void
    {
        // the model is shared per alias for the whole run: the previous test left its selection behind
        // and tearDown cleared it, so a fresh read gets every column
        $sticky = $this->model('Sticky_model', 'stale_select_sticky');
        $id = Factory::insert('sticky', ['user_id' => 2, 'location' => 1, 'note' => 'cleared probe', 'private' => 0, 'created_at' => Factory::raw('NOW()'), 'updated_at' => Factory::raw('NOW()')]);

        $this->assertGreaterThan(1, count($sticky->get($id)));
    }
}
