<?php

declare(strict_types=1);

require_once __DIR__ . '/VamregHelper.php';

use PHPUnit\Framework\Attributes\Group;

/**
 * T282 - lock, unlock, remove and restore (Vamreg::lock, unlock, remove, restore).
 *
 * Rules: lock sets an IN row to INVALID, unlock sets it back to DRAFT; remove sets an OUT row to
 * INVALID, restore sets it to DRAFT. IN actions redirect to `vamreg`, OUT actions to the detail page
 * of the cnk. None of them looks at the current status, and all four are plain GET requests.
 */
final class VamregStatusTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    private function rowStatus(string $table, int $id): string
    {
        return (string) $this->find($table, $id)['status'];
    }

    #[Group('known-bug')]
    public function testLockInvalidatesAnInRowAndUnlockMakesItADraftAgain(): void
    {
        // KNOWN BUG: a GET request changes data (no POST, no token) - decide in CI4 whether to keep
        $id = VamregHelper::in('2031-02-10');
        $other = VamregHelper::in('2031-02-11');

        $lock = $this->get("vamreg/lock/$id");
        $this->assertRedirectsTo($lock, 'vamreg');
        $this->assertSame('INVALID', $this->rowStatus('vamreg_in_buffer', $id));
        $this->assertSame('DRAFT', $this->rowStatus('vamreg_in_buffer', $other), 'other rows are untouched');

        $unlock = $this->get("vamreg/unlock/$id");
        $this->assertRedirectsTo($unlock, 'vamreg');
        $this->assertSame('DRAFT', $this->rowStatus('vamreg_in_buffer', $id));
    }

    #[Group('known-bug')]
    public function testUnlockOnASentRowSetsItBackToDraftAndKeepsTheDeclarationId(): void
    {
        // KNOWN BUG: unlock does not check the status, a SENT row (already at the authority) becomes
        // a DRAFT again and would be sent twice - decide in CI4 whether to keep
        $id = VamregHelper::in('2031-02-10', ['status' => 'SENT', 'api_declaration_id' => 'abc123abc123abc123abc123', 'sent_at' => '2031-04-02 10:00:00']);

        $this->get("vamreg/unlock/$id");

        $row = $this->find('vamreg_in_buffer', $id);
        $this->assertSame('DRAFT', $row['status']);
        $this->assertSame('abc123abc123abc123abc123', $row['api_declaration_id']);
    }

    #[Group('known-bug')]
    public function testLockAlsoInvalidatesASentRow(): void
    {
        // KNOWN BUG: lock does not check the status either - decide in CI4 whether to keep
        $id = VamregHelper::in('2031-02-10', ['status' => 'SENT']);

        $this->get("vamreg/lock/$id");

        $this->assertSame('INVALID', $this->rowStatus('vamreg_in_buffer', $id));
    }

    #[Group('known-bug')]
    public function testRemoveInvalidatesAnOutRowAndRestoreMakesItADraftAgain(): void
    {
        // KNOWN BUG: GET requests change data - decide in CI4 whether to keep
        $id = VamregHelper::out('2031-02-10', ['cnk' => '4444010']);
        $other = VamregHelper::out('2031-02-11', ['cnk' => '4444010']);

        $remove = $this->get("vamreg/remove/$id/4444010/2031/1");
        $this->assertRedirectsTo($remove, 'vamreg/out_detail/4444010/2031/1');
        $this->assertSame('INVALID', $this->rowStatus('vamreg_out_buffer', $id));
        $this->assertSame('DRAFT', $this->rowStatus('vamreg_out_buffer', $other));

        $restore = $this->get("vamreg/restore/$id/4444010/2031/1");
        $this->assertRedirectsTo($restore, 'vamreg/out_detail/4444010/2031/1');
        $this->assertSame('DRAFT', $this->rowStatus('vamreg_out_buffer', $id));
    }

    #[Group('known-bug')]
    public function testRestoreOnASentOutRowSetsItBackToDraft(): void
    {
        // KNOWN BUG: restore does not check the status - decide in CI4 whether to keep
        $id = VamregHelper::out('2031-02-10', ['status' => 'SENT', 'cnk' => '4444011']);

        $this->get("vamreg/restore/$id/4444011/2031/1");

        $this->assertSame('DRAFT', $this->rowStatus('vamreg_out_buffer', $id));
    }

    public function testTheListsOfferTheMatchingAction(): void
    {
        $draft = VamregHelper::in('2031-02-10', ['status' => 'DRAFT', 'in_quantity_pack_count' => 31]);
        $invalid = VamregHelper::in('2031-02-11', ['status' => 'INVALID', 'in_quantity_pack_count' => 32]);

        $page = $this->text($this->get('vamreg/in/2031/1'));

        $this->assertStringContainsString("vamreg/lock/$draft\"", $page);
        $this->assertStringContainsString("vamreg/edit/$draft\"", $page);
        $this->assertStringContainsString("vamreg/unlock/$invalid\"", $page);
        $this->assertStringNotContainsString("vamreg/lock/$invalid\"", $page);
        $this->assertStringNotContainsString("vamreg/edit/$invalid\"", $page);
    }

    public function testTheOutDetailOffersRemoveRestoreAndNothingForSentRows(): void
    {
        Factory::product(['name' => 'Status Antibiotic', 'cnk' => '4444012']);
        $draft = VamregHelper::out('2031-02-10', ['cnk' => '4444012', 'status' => 'DRAFT']);
        $invalid = VamregHelper::out('2031-02-11', ['cnk' => '4444012', 'status' => 'INVALID']);
        $sent = VamregHelper::out('2031-02-12', ['cnk' => '4444012', 'status' => 'SENT']);

        $page = $this->text($this->get('vamreg/out_detail/4444012/2031/1'));

        $this->assertStringContainsString("vamreg/remove/$draft/4444012/2031/1\"", $page);
        $this->assertStringContainsString("vamreg/restore/$invalid/4444012/2031/1\"", $page);
        $this->assertStringContainsString("vamreg/edit_out/$draft\"", $page);
        $this->assertStringNotContainsString("vamreg/remove/$invalid/", $page);
        $this->assertStringNotContainsString("vamreg/remove/$sent/", $page);
        $this->assertStringNotContainsString("vamreg/restore/$sent/", $page);
        $this->assertStringNotContainsString("vamreg/edit_out/$sent\"", $page);
    }

    public function testAVetCannotLockOrRemove(): void
    {
        $in = VamregHelper::in('2031-02-10');
        $out = VamregHelper::out('2031-02-10', ['cnk' => '4444013']);
        $this->cookies->clear();
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->get("vamreg/lock/$in"), '');
        $this->assertRedirectsTo($this->get("vamreg/remove/$out/4444013/2031/1"), '');

        $this->assertSame('DRAFT', $this->rowStatus('vamreg_in_buffer', $in));
        $this->assertSame('DRAFT', $this->rowStatus('vamreg_out_buffer', $out));
    }
}
