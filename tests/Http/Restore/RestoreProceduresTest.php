<?php

declare(strict_types=1);

/**
 * Restore::procedures (admin only; the controller also holds restore/booking and restore/locations,
 * covered in BookingCodesTest and LocationsTest).
 *
 * Rules: the page lists soft deleted procedures only; restore/procedures/{id} clears deleted_at of
 * that one procedure and shows the list again.
 */
final class RestoreProceduresTest extends HttpTestCase
{
    public function testThePageListsOnlyTrashedProceduresWithTheirPrice(): void
    {
        $this->loginAs('admin');
        $gone = Factory::unique('GoneProc');
        $live = Factory::unique('LiveProc');
        Factory::procedure(['name' => $gone, 'price' => 42.5, 'deleted_at' => Factory::raw('NOW()')]);
        Factory::procedure(['name' => $live]);

        $page = $this->get('restore/procedures');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $gone);
        $this->assertSee($page, '42.5');
        $this->assertDontSee($page, $live);
    }

    public function testRestoringBringsBackOnlyTheChosenProcedure(): void
    {
        $this->loginAs('admin');
        $a = Factory::procedure(['name' => Factory::unique('RA'), 'deleted_at' => Factory::raw('NOW()')]);
        $b = Factory::procedure(['name' => Factory::unique('RB'), 'deleted_at' => Factory::raw('NOW()')]);

        $page = $this->get('restore/procedures/' . $a);

        $this->assertStatus($page, 200);
        $this->assertNull($this->find('procedures', $a)['deleted_at']);
        $this->assertNotNull($this->find('procedures', $b)['deleted_at']);
        $this->assertStringNotContainsString('restore/procedures/' . $a . '"', (string) $page->getBody());
        $this->assertStringContainsString('restore/procedures/' . $b . '"', (string) $page->getBody());
    }

    public function testOnlyAdminsMayRestore(): void
    {
        $id = Factory::procedure(['deleted_at' => Factory::raw('NOW()')]);
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->get('restore/procedures/' . $id), '');
        $this->assertNotNull($this->find('procedures', $id)['deleted_at']);

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->get('restore/procedures/' . $id);
        $this->assertNull($this->find('procedures', $id)['deleted_at'], 'positive control');
    }
}
