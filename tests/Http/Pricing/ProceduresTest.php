<?php

declare(strict_types=1);

/**
 * T191 - Pricing::proc, proc_edit, delete_proc and Restore::procedures.
 *
 * `pricing/proc` lists the procedures; POST submit=add_proc with a non-empty name inserts one (and logs
 * new_procedure); POST action=edit_proc with id updates name, booking code and price (log update_procedure).
 * `proc_edit/{id}` shows the income statistics for the last 3 months and the last year (admins only).
 * delete_proc soft deletes and answers with a "refresh" redirect to pricing/proc. The admin-only
 * `restore/procedures[/id]` lists the trashed procedures and restores one.
 */
final class ProceduresTest extends HttpTestCase
{
    public function testTheListShowsProceduresButNotDeletedOnes(): void
    {
        $this->loginAs('acc');
        $live = Factory::procedure(['name' => Factory::unique('ProcLive')]);
        $gone = Factory::procedure(['name' => Factory::unique('ProcGone'), 'deleted_at' => Factory::raw('NOW()')]);

        $page = $this->get('pricing/proc');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $this->find('procedures', $live)['name']);
        $this->assertDontSee($page, $this->find('procedures', $gone)['name']);
    }

    public function testAProcedureCanBeAdded(): void
    {
        $this->loginAs('acc');
        $name = Factory::unique('ProcNew');
        $this->db()->exec('DELETE FROM log');

        $page = $this->post('pricing/proc', [
            'submit' => 'add_proc', 'name' => $name, 'booking_code' => (string) Fixtures::BOOKING_SERVICES, 'price' => '42.50',
        ]);

        $this->assertStatus($page, 200);
        $p = $this->row('SELECT * FROM procedures WHERE name = ?', [$name]);
        $this->assertNotNull($p);
        $this->assertEqualsWithDelta(42.5, (float) $p['price'], 0.001);
        $this->assertSame(Fixtures::BOOKING_SERVICES, (int) $p['booking_code']);
        $this->assertNull($p['deleted_at']);
        $this->assertSee($page, $name, 'the list shows it right away');
        $log = $this->row("SELECT * FROM log WHERE event = 'new_procedure'");
        $this->assertNotNull($log);
        $this->assertStringContainsString($name, $log['msg']);
    }

    public function testAProcedureWithoutANameIsNotAdded(): void
    {
        $this->loginAs('admin');
        $before = $this->countWhere('procedures');

        $this->post('pricing/proc', ['submit' => 'add_proc', 'name' => '', 'booking_code' => '1', 'price' => '5']);
        $this->post('pricing/proc', ['submit' => 'something_else', 'name' => 'Not added', 'booking_code' => '1', 'price' => '5']);

        $this->assertSame($before, $this->countWhere('procedures'));
    }

    public function testAProcedureCanBeEdited(): void
    {
        $this->loginAs('acc');
        $id = Factory::procedure(['name' => Factory::unique('ProcOld'), 'price' => 10, 'booking_code' => Fixtures::BOOKING_SERVICES]);
        $other = Factory::procedure(['name' => 'Untouched proc', 'price' => 7]);
        $name = Factory::unique('ProcEdited');
        $this->db()->exec('DELETE FROM log');

        $this->post('pricing/proc', [
            'action' => 'edit_proc', 'id' => (string) $id, 'name' => $name, 'booking_code' => (string) Fixtures::BOOKING_PRODUCTS, 'price' => '12.25',
        ]);

        $p = $this->find('procedures', $id);
        $this->assertSame($name, $p['name']);
        $this->assertEqualsWithDelta(12.25, (float) $p['price'], 0.001);
        $this->assertSame(Fixtures::BOOKING_PRODUCTS, (int) $p['booking_code']);
        $this->assertSame('Untouched proc', $this->find('procedures', $other)['name']);
        $this->assertEqualsWithDelta(7.0, (float) $this->find('procedures', $other)['price'], 0.001);
        $this->assertSame(1, $this->logCount('update_procedure'));
    }

    public function testTheEditPageShowsTheProcedure(): void
    {
        $this->loginAs('acc');

        $page = $this->get('pricing/proc_edit/' . Fixtures::PROCEDURE_CONSULT);

        $this->assertStatus($page, 200);
        $this->assertSee($page, '25.00');
    }

    public function testTheEditPageShowsTheIncomeStatisticsToAdminsOnly(): void
    {
        $id = Factory::procedure(['name' => Factory::unique('ProcStat')]);
        $other = Factory::procedure();
        $event = Factory::event(Fixtures::PET_REX, 1, 1);
        $ago = static fn (string $interval): string => date('Y-m-d H:i:s', strtotime('-' . $interval));
        // 3 months: net 10 + 20 = 30, volume 1 + 2 = 3, 2 lines; 1 year: + 40 (volume 4) = 70, volume 7, 3 lines
        Factory::eventProcedure($event, $id, 1, ['unit_price' => 10, 'created_at' => $ago('10 days')]);
        Factory::eventProcedure($event, $id, 2, ['unit_price' => 10, 'created_at' => $ago('20 days')]);
        Factory::eventProcedure($event, $id, 4, ['unit_price' => 10, 'created_at' => $ago('6 months')]);
        Factory::eventProcedure($event, $id, 8, ['unit_price' => 10, 'created_at' => $ago('2 years')]);
        Factory::eventProcedure($event, $other, 1, ['unit_price' => 1000, 'created_at' => $ago('1 days')]);

        $this->loginAs('admin');
        $admin = preg_replace('/\s+/', ' ', strip_tags($this->text($this->get('pricing/proc_edit/' . $id))));
        $this->assertMatchesRegularExpression('/3 Months 30 .{1,3} 3(\.00)? 2 /', $admin);
        $this->assertMatchesRegularExpression('/1 Year 70 .{1,3} 7(\.00)? 3 /', $admin);

        $this->cookies->clear();
        $this->loginAs('acc');
        $acc = $this->get('pricing/proc_edit/' . $id);
        $this->assertStatus($acc, 200);
        $this->assertDontSee($acc, '3 Months');
    }

    public function testDeletingAProcedureSoftDeletesIt(): void
    {
        $this->loginAs('acc');
        $id = Factory::procedure();

        $response = $this->get('pricing/delete_proc/' . $id);

        $this->assertRedirectsTo($response, 'pricing/proc');
        $this->assertNotNull($this->find('procedures', $id)['deleted_at']);
        $this->assertNotNull($this->find('procedures', $id), 'the row stays');
    }

    public function testAnAdminCanRestoreAProcedure(): void
    {
        $this->loginAs('admin');
        $id = Factory::procedure(['name' => Factory::unique('ProcTrash')]);
        $name = $this->find('procedures', $id)['name'];
        $this->get('pricing/delete_proc/' . $id);

        $list = $this->get('restore/procedures');
        $this->assertStatus($list, 200);
        $this->assertSee($list, $name, 'trashed procedures are listed');

        $after = $this->get('restore/procedures/' . $id);
        $this->assertNull($this->find('procedures', $id)['deleted_at']);
        $this->assertDontSee($after, $name, 'no longer in the trash list');
        $this->assertSee($this->get('pricing/proc'), $name);
    }

    public function testRestoringIsForAdminsOnly(): void
    {
        $id = Factory::procedure(['deleted_at' => Factory::raw('NOW()')]);
        $this->loginAs('acc');

        $this->assertRedirectsTo($this->get('restore/procedures/' . $id), '');

        $this->assertNotNull($this->find('procedures', $id)['deleted_at']);
    }
}
