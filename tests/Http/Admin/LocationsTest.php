<?php

declare(strict_types=1);

/**
 * T301 - Admin::locations / locations_rm (admin only) and the location half of Restore.
 *
 * Rules: submit=add_location inserts a location, submit=update_location_name renames it by id,
 * admin/locations_rm/{id} soft deletes it (deleted_at), the list only shows live locations and
 * restore/locations/{id} brings a trashed one back. Every test works on its own location.
 */
final class LocationsTest extends HttpTestCase
{
    private function location(string $name, bool $deleted = false): int
    {
        return Factory::insert('stock_location', [
            'name' => $name,
            'color' => '#112233',
            'created_at' => Factory::raw('NOW()'),
            'deleted_at' => $deleted ? Factory::raw('NOW()') : null,
        ]);
    }

    public function testThePageListsLiveLocationsAndHidesTrashedOnes(): void
    {
        $this->loginAs('admin');
        $live = Factory::unique('Live');
        $gone = Factory::unique('Gone');
        $this->location($live);
        $this->location($gone, true);

        $page = $this->get('admin/locations');

        $this->assertStatus($page, 200);
        $this->assertSee($page, 'Praktijk');
        $this->assertSee($page, $live);
        $this->assertDontSee($page, $gone);
    }

    public function testAddingALocationInsertsARow(): void
    {
        $this->loginAs('admin');
        $name = Factory::unique('Nieuw');

        $page = $this->post('admin/locations', ['submit' => 'add_location', 'name' => $name]);

        $this->assertStatus($page, 200);
        $this->assertRowCount(1, 'stock_location', ['name' => $name]);
        $this->assertNull($this->row('SELECT deleted_at FROM stock_location WHERE name = ?', [$name])['deleted_at']);
        $this->assertSee($page, $name, 'the answer is the refreshed list');
    }

    public function testPostingWithoutTheAddSubmitInsertsNothing(): void
    {
        $this->loginAs('admin');
        $name = Factory::unique('Nope');

        $this->post('admin/locations', ['name' => $name]);

        $this->assertRowMissing('stock_location', ['name' => $name]);
    }

    public function testRenamingChangesOnlyTheNamedLocation(): void
    {
        $this->loginAs('admin');
        $a = $this->location(Factory::unique('A'));
        $b = $this->location('Blijft');
        $new = Factory::unique('Hernoemd');

        $this->post('admin/locations', ['submit' => 'update_location_name', 'id' => $a, 'name' => $new]);

        $this->assertSame($new, $this->find('stock_location', $a)['name']);
        $this->assertSame('Blijft', $this->find('stock_location', $b)['name']);
    }

    public function testDeletingSoftDeletesTheLocation(): void
    {
        $this->loginAs('admin');
        $id = $this->location(Factory::unique('Weg'));

        $response = $this->get('admin/locations_rm/' . $id);

        $this->assertRedirectsTo($response, 'admin/locations');
        $row = $this->find('stock_location', $id);
        $this->assertNotNull($row, 'the row is kept');
        $this->assertNotNull($row['deleted_at']);
    }

    public function testRestoreListsTrashedLocationsAndRestoringBringsOneBack(): void
    {
        $this->loginAs('admin');
        $name = Factory::unique('Trash');
        $other = Factory::unique('TrashOther');
        $id = $this->location($name, true);
        $otherId = $this->location($other, true);
        $live = (string) $this->location(Factory::unique('LiveLoc'));

        $list = $this->get('restore/locations');
        $this->assertStatus($list, 200);
        $this->assertSee($list, $name);
        $this->assertSee($list, $other);
        $this->assertStringNotContainsString('restore/locations/' . $live, (string) $list->getBody());

        $after = $this->get('restore/locations/' . $id);

        $this->assertNull($this->find('stock_location', $id)['deleted_at']);
        $this->assertNotNull($this->find('stock_location', $otherId)['deleted_at'], 'only the chosen one');
        $this->assertStringNotContainsString('restore/locations/' . $id . '"', (string) $after->getBody());
        $this->assertStringContainsString('restore/locations/' . $otherId . '"', (string) $after->getBody());
        $this->assertSee($this->get('admin/locations'), $name);
    }

    public function testOnlyAdminsMayManageLocations(): void
    {
        $name = Factory::unique('Denied');
        $this->loginAs('vet');
        $this->assertRedirectsTo($this->post('admin/locations', ['submit' => 'add_location', 'name' => $name]), '');
        $this->assertRedirectsTo($this->get('restore/locations'), '');
        $id = $this->location(Factory::unique('Keep'));
        $this->assertRedirectsTo($this->get('admin/locations_rm/' . $id), '');
        $this->assertRowMissing('stock_location', ['name' => $name]);
        $this->assertNull($this->find('stock_location', $id)['deleted_at']);

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->post('admin/locations', ['submit' => 'add_location', 'name' => $name]);
        $this->assertRowExists('stock_location', ['name' => $name], 'positive control');
    }
}
