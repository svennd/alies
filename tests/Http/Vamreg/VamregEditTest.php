<?php

declare(strict_types=1);

require_once __DIR__ . '/VamregHelper.php';

/**
 * T281 - edit the IN and OUT buffer rows (Vamreg::edit, save, edit_out, save_out).
 *
 * Rules: `save` stores the new pack count of an IN row as an integer, but only for a number above
 * zero (0, -1, text and an empty value change nothing); it always redirects to vamreg/index/{y}/{q}.
 * `save_out` rewrites the quantity columns of an OUT row from the amount and the unit
 * (PACKS -> pack count, anything else -> unit count + unit) and the out_date, then redirects to the
 * detail page of the cnk. The OUT amount is not validated: any text becomes 0.
 */
final class VamregEditTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    // ---- IN ----------------------------------------------------------------------------------

    public function testTheInEditPageShowsTheRow(): void
    {
        $wholesale = Factory::wholesale(['description' => 'Edit Page Article']);
        $id = VamregHelper::in('2031-02-10', ['cnk' => '4444001', 'wholesale_id' => $wholesale, 'in_quantity_pack_count' => 12]);

        $response = $this->get("vamreg/edit/$id/2031/1");

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Edit Page Article');
        $this->assertSee($response, '4444001');
        $this->assertSee($response, '2031-02-10');
        $this->assertStringContainsString('value="12"', $this->text($response));
        $this->assertStringContainsString("vamreg/save/$id/2031/1", $this->text($response));
    }

    public function testSaveStoresTheVolumeAsAnIntegerAndRedirectsToTheDashboard(): void
    {
        $id = VamregHelper::in('2031-02-10', ['in_quantity_pack_count' => 5]);

        $response = $this->post("vamreg/save/$id/2031/1", ['submit' => 1, 'new_volume' => '3.7']);

        $this->assertRedirectsTo($response, 'vamreg/index/2031/1');
        // 3.7 is cut to 3, not rounded to 4
        $this->assertSame(3, (int) $this->find('vamreg_in_buffer', $id)['in_quantity_pack_count']);
        // nothing else changed
        $this->assertSame('DRAFT', $this->find('vamreg_in_buffer', $id)['status']);
        $this->assertSame('2031-02-10', $this->find('vamreg_in_buffer', $id)['delivery']);

        // positive control for the next test: a whole number is stored as is
        $this->post("vamreg/save/$id/2031/1", ['submit' => 1, 'new_volume' => '42']);
        $this->assertSame(42, (int) $this->find('vamreg_in_buffer', $id)['in_quantity_pack_count']);
    }

    public function testInvalidVolumesAreIgnored(): void
    {
        $id = VamregHelper::in('2031-02-10', ['in_quantity_pack_count' => 8]);

        foreach (['0', '-1', 'abc', '', '0.9', '-3.5'] as $bad) {
            $response = $this->post("vamreg/save/$id/2031/1", ['submit' => 1, 'new_volume' => $bad]);

            $this->assertRedirectsTo($response, 'vamreg/index/2031/1', "value '$bad'");
            $this->assertSame(8, (int) $this->find('vamreg_in_buffer', $id)['in_quantity_pack_count'], "value '$bad' must change nothing");
        }

        // a post without the field is ignored too
        $this->assertRedirectsTo($this->post("vamreg/save/$id/2031/1", ['submit' => 1]), 'vamreg/index/2031/1');
        $this->assertSame(8, (int) $this->find('vamreg_in_buffer', $id)['in_quantity_pack_count']);

        // positive control: the smallest accepted value is 1
        $this->post("vamreg/save/$id/2031/1", ['new_volume' => '1']);
        $this->assertSame(1, (int) $this->find('vamreg_in_buffer', $id)['in_quantity_pack_count']);
    }

    public function testSaveOnlyTouchesTheGivenRow(): void
    {
        $a = VamregHelper::in('2031-02-10', ['in_quantity_pack_count' => 5]);
        $b = VamregHelper::in('2031-02-11', ['in_quantity_pack_count' => 6]);

        $this->post("vamreg/save/$a/2031/1", ['new_volume' => '9']);

        $this->assertSame(9, (int) $this->find('vamreg_in_buffer', $a)['in_quantity_pack_count']);
        $this->assertSame(6, (int) $this->find('vamreg_in_buffer', $b)['in_quantity_pack_count']);
    }

    // ---- OUT ---------------------------------------------------------------------------------

    public function testTheOutEditPageShowsTheRow(): void
    {
        $id = VamregHelper::out('2031-02-10', ['cnk' => '4444002', 'out_quantity_pack_count' => 2]);

        $response = $this->get("vamreg/edit_out/$id/2031/1");

        $this->assertStatus($response, 200);
        $this->assertSee($response, '4444002');
        $this->assertSee($response, '2031-02-10');
        $this->assertStringContainsString("vamreg/save_out/$id/2031/1", $this->text($response));
    }

    public function testSaveOutInPacksStoresThePackCountAndClearsTheUnitColumns(): void
    {
        $id = VamregHelper::out('2031-02-10', [
            'cnk' => '4444003', 'out_quantity_type' => 'UNITS', 'out_quantity_pack_count' => null,
            'out_quantity_unit_count' => 7, 'out_quantity_unit' => 'ML',
        ]);

        $response = $this->post("vamreg/save_out/$id/2031/1", [
            'new_volume' => '2', 'ab_unit' => 'PACKS', 'cnk' => '4444003', 'out_date' => '2031-03-05',
        ]);

        $this->assertRedirectsTo($response, 'vamreg/out_detail/4444003/2031/1');
        $row = $this->find('vamreg_out_buffer', $id);
        $this->assertSame('PACKS', $row['out_quantity_type']);
        $this->assertEqualsWithDelta(2.0, (float) $row['out_quantity_pack_count'], 0.001);
        $this->assertNull($row['out_quantity_unit_count']);
        $this->assertNull($row['out_quantity_unit']);
        $this->assertSame('2031-03-05', $row['out_date']);
    }

    public function testSaveOutInUnitsStoresTheUnitCountAndTheUnit(): void
    {
        $id = VamregHelper::out('2031-02-10', ['cnk' => '4444004', 'out_quantity_type' => 'PACKS', 'out_quantity_pack_count' => 3]);

        $response = $this->post("vamreg/save_out/$id/2031/1", [
            'new_volume' => '2.5', 'ab_unit' => 'ML', 'cnk' => '4444004', 'out_date' => '2031-02-12',
        ]);

        $this->assertRedirectsTo($response, 'vamreg/out_detail/4444004/2031/1');
        $row = $this->find('vamreg_out_buffer', $id);
        $this->assertSame('UNITS', $row['out_quantity_type']);
        $this->assertNull($row['out_quantity_pack_count']);
        $this->assertEqualsWithDelta(2.5, (float) $row['out_quantity_unit_count'], 0.001);
        $this->assertSame('ML', $row['out_quantity_unit']);
        $this->assertSame('2031-02-12', $row['out_date']);
    }

    public function testSaveOutTreatsEveryUnitExceptPacksAsUnits(): void
    {
        $id = VamregHelper::out('2031-02-10', ['cnk' => '4444005']);

        $this->post("vamreg/save_out/$id/2031/1", ['new_volume' => '4', 'ab_unit' => 'PIECE', 'cnk' => '4444005', 'out_date' => '2031-02-10']);

        $row = $this->find('vamreg_out_buffer', $id);
        $this->assertSame('UNITS', $row['out_quantity_type']);
        $this->assertEqualsWithDelta(4.0, (float) $row['out_quantity_unit_count'], 0.001);
        $this->assertSame('PIECE', $row['out_quantity_unit']);
    }

    public function testSaveOutDoesNotValidateTheAmount(): void
    {
        // the amount goes through (float): text and negative numbers are stored as 0 and as is
        $id = VamregHelper::out('2031-02-10', ['cnk' => '4444006', 'out_quantity_pack_count' => 3]);

        $this->post("vamreg/save_out/$id/2031/1", ['new_volume' => 'abc', 'ab_unit' => 'PACKS', 'cnk' => '4444006', 'out_date' => '2031-02-10']);
        $this->assertEqualsWithDelta(0.0, (float) $this->find('vamreg_out_buffer', $id)['out_quantity_pack_count'], 0.001);

        $this->post("vamreg/save_out/$id/2031/1", ['new_volume' => '-2', 'ab_unit' => 'PACKS', 'cnk' => '4444006', 'out_date' => '2031-02-10']);
        $this->assertEqualsWithDelta(-2.0, (float) $this->find('vamreg_out_buffer', $id)['out_quantity_pack_count'], 0.001);
    }
}
