<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T302 - Admin::booking / booking_rm (admin only) and Restore::booking.
 *
 * Rules: submit=add_booking_code with category, code and btw inserts a code; the page shows how
 * many products and procedures use each code; booking_rm/{id} soft deletes; restore/booking/{id}
 * brings it back. (category, code) is unique in the database.
 */
final class BookingCodesTest extends HttpTestCase
{
    private function code(string $category, string $code = '700001', int $btw = 6, bool $deleted = false): int
    {
        return Factory::insert('booking_codes', [
            'category' => $category,
            'code' => $code,
            'btw' => $btw,
            'created_at' => Factory::raw('NOW()'),
            'deleted_at' => $deleted ? Factory::raw('NOW()') : null,
        ]);
    }

    public function testAddingACodeInsertsItWithItsVatRate(): void
    {
        $this->loginAs('admin');
        $category = Factory::unique('Cat');

        $page = $this->post('admin/booking', ['submit' => 'add_booking_code', 'category' => $category, 'code' => '712345', 'btw' => '12']);

        $this->assertStatus($page, 200);
        $row = $this->row('SELECT * FROM booking_codes WHERE category = ?', [$category]);
        $this->assertNotNull($row);
        $this->assertSame('712345', $row['code']);
        $this->assertSame(12, (int) $row['btw']);
        $this->assertNull($row['deleted_at']);
        $this->assertSee($page, $category);
        $this->assertSee($page, '712345');
    }

    public function testPostingWithoutTheSubmitValueInsertsNothing(): void
    {
        $this->loginAs('admin');
        $category = Factory::unique('Cat');

        $this->post('admin/booking', ['category' => $category, 'code' => '1', 'btw' => '6']);

        $this->assertRowMissing('booking_codes', ['category' => $category]);
    }

    public function testThePageShowsProductAndProcedureCountsAndHidesDeletedCodes(): void
    {
        $this->loginAs('admin');
        $category = Factory::unique('Counted');
        $id = $this->code($category, '799001');
        Factory::product(['booking_code' => $id]);
        Factory::product(['booking_code' => $id]);
        Factory::product(['booking_code' => $id]);
        Factory::procedure(['booking_code' => $id]);
        $gone = Factory::unique('Deleted');
        $this->code($gone, '799002', 6, true);

        $page = $this->get('admin/booking');

        $this->assertStatus($page, 200);
        $this->assertSee($page, $category);
        $this->assertSee($page, '3 products');
        $this->assertSee($page, '1 procedures');
        $this->assertDontSee($page, $gone);
    }

    public function testDeletingSoftDeletesTheCode(): void
    {
        $this->loginAs('admin');
        $id = $this->code(Factory::unique('Rm'));

        $response = $this->get('admin/booking_rm/' . $id);

        $this->assertRedirectsTo($response, 'admin/booking');
        $this->assertNotNull($this->find('booking_codes', $id)['deleted_at']);
    }

    public function testRestoreListsDeletedCodesAndBringsOneBack(): void
    {
        $this->loginAs('admin');
        $a = Factory::unique('RestoreA');
        $b = Factory::unique('RestoreB');
        $idA = $this->code($a, '798001', 6, true);
        $idB = $this->code($b, '798002', 21, true);
        $live = Factory::unique('Live');
        $this->code($live, '798003');

        $list = $this->get('restore/booking');
        $this->assertStatus($list, 200);
        $this->assertSee($list, $a);
        $this->assertSee($list, $b);
        $this->assertDontSee($list, $live);

        $after = $this->get('restore/booking/' . $idA);

        $this->assertNull($this->find('booking_codes', $idA)['deleted_at']);
        $this->assertNotNull($this->find('booking_codes', $idB)['deleted_at']);
        $this->assertDontSee($after, $a);
        $this->assertSee($after, $b);
        $this->assertSee($this->get('admin/booking'), $a);
    }

    public function testRestoreShowsAMessageWhenNothingIsTrashed(): void
    {
        $this->loginAs('admin');
        $this->db()->exec('UPDATE booking_codes SET deleted_at = NULL');

        $this->assertSee($this->get('restore/booking'), 'No removed booking codes');
    }

    #[Group('known-bug')]
    public function testAddingTheSameCategoryAndCodeTwiceIsNotHandled(): void
    {
        // KNOWN BUG: (category, code) is unique in the database, the controller does not check; the
        // second insert is a database error and no second row appears - decide in CI4 whether to keep
        $this->loginAs('admin');
        $category = Factory::unique('Dup');
        $form = ['submit' => 'add_booking_code', 'category' => $category, 'code' => '797001', 'btw' => '6'];
        $this->post('admin/booking', $form);

        $second = $this->post('admin/booking', $form);

        $this->assertSame(1, $this->countWhere('booking_codes', ['category' => $category]));
        $this->assertContains($second->getStatusCode(), [200, 500]);
    }

    public function testOnlyAdminsMayManageBookingCodes(): void
    {
        $id = $this->code(Factory::unique('Keep'));
        $category = Factory::unique('Denied');
        $this->loginAs('acc');
        $this->assertRedirectsTo($this->post('admin/booking', ['submit' => 'add_booking_code', 'category' => $category, 'code' => '1', 'btw' => '6']), '');
        $this->assertRedirectsTo($this->get('admin/booking_rm/' . $id), '');
        $this->assertRedirectsTo($this->get('restore/booking'), '');
        $this->assertRowMissing('booking_codes', ['category' => $category]);
        $this->assertNull($this->find('booking_codes', $id)['deleted_at']);

        $this->cookies->clear();
        $this->loginAs('admin');
        $this->get('admin/booking_rm/' . $id);
        $this->assertNotNull($this->find('booking_codes', $id)['deleted_at'], 'positive control');
    }
}
