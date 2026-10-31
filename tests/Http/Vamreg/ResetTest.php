<?php

declare(strict_types=1);

require_once __DIR__ . '/VamregHelper.php';

/**
 * T285 (HTTP part) - Vamreg::reset, the debug tool that sets the buffers back to DRAFT.
 *
 * Only the part that never calls the register is tested here: rows WITHOUT api_declaration_id are
 * not deleted remotely (the controller only calls DELETE for rows that have one). Rows with an id
 * would make the server call the real Vamreg API; that part is tested in-process with a recording
 * client in tests/Models/VamregPostAllTest.php.
 *
 * Rules: at the end every ERROR row of both buffers becomes DRAFT; DRAFT, SENT and INVALID rows
 * without a declaration id keep their status. The page prints "IN:" and "OUT:" and nothing else.
 */
final class ResetTest extends HttpTestCase
{
    public function testResetTurnsErrorRowsIntoDraftsAndLeavesTheOthersAlone(): void
    {
        $this->loginAs('admin');
        $inError = VamregHelper::in('2031-02-10', ['status' => 'ERROR', 'api_error' => 'something']);
        $inSent = VamregHelper::in('2031-02-11', ['status' => 'SENT']);
        $inDraft = VamregHelper::in('2031-02-12', ['status' => 'DRAFT']);
        $inInvalid = VamregHelper::in('2031-02-13', ['status' => 'INVALID']);
        $outError = VamregHelper::out('2031-02-10', ['status' => 'ERROR', 'api_error' => 'something']);
        $outSent = VamregHelper::out('2031-02-11', ['status' => 'SENT']);
        $outInvalid = VamregHelper::out('2031-02-13', ['status' => 'INVALID']);

        $response = $this->get('vamreg/reset');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'IN:');
        $this->assertSee($response, 'OUT:');
        $this->assertSame('DRAFT', $this->find('vamreg_in_buffer', $inError)['status']);
        $this->assertSame('SENT', $this->find('vamreg_in_buffer', $inSent)['status']);
        $this->assertSame('DRAFT', $this->find('vamreg_in_buffer', $inDraft)['status']);
        $this->assertSame('INVALID', $this->find('vamreg_in_buffer', $inInvalid)['status']);
        $this->assertSame('DRAFT', $this->find('vamreg_out_buffer', $outError)['status']);
        $this->assertSame('SENT', $this->find('vamreg_out_buffer', $outSent)['status']);
        $this->assertSame('INVALID', $this->find('vamreg_out_buffer', $outInvalid)['status']);
        // the error text is kept (only status changes in this last step)
        $this->assertSame('something', $this->find('vamreg_in_buffer', $inError)['api_error']);
    }

    public function testAVetCannotReset(): void
    {
        $this->loginAs('vet');
        $id = VamregHelper::in('2031-02-10', ['status' => 'ERROR']);

        $this->assertRedirectsTo($this->get('vamreg/reset'), '');
        $this->assertSame('ERROR', $this->find('vamreg_in_buffer', $id)['status']);
    }
}
