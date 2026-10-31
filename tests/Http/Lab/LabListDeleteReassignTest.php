<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T252, T253, T254 - lab/list_lab/{pet}, lab/delete/{id}, lab/reassign/{id}.
 *
 * list_lab: the pet's reports, newest sample_date first (ties: highest id first); unknown pet -> home.
 * delete: admin only (vet is redirected to lab without deleting). The model has the typo
 *   `$soft_delete` (MY_Model reads `$soft_deletes`), so the row is really deleted and its
 *   lab_results / lab_plots / events_labs rows stay behind.
 * reassign: POST only (405 otherwise); moves the report to another owner's pet, drops the
 *   events_labs links to events of the old pet, logs lab_report_reassigned and redirects to the detail.
 */
final class LabListDeleteReassignTest extends HttpTestCase
{
    private int $ownerA;
    private int $petA;
    private int $ownerB;
    private int $petB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ownerA = Factory::owner();
        $this->petA = Factory::pet($this->ownerA);
        $this->ownerB = Factory::owner();
        $this->petB = Factory::pet($this->ownerB);
    }

    private function listedIds($response): array
    {
        preg_match_all('#lab/detail/(\d+)#', (string) $response->getBody(), $m);

        return array_map('intval', $m[1]);
    }

    /** Type of the flash alert on a response (the detail page needs at least one result, else it is a 500). */
    private function excerptOfAlert($response): string
    {
        return preg_match('#<div class="alert alert-(\w+)">#', (string) $response->getBody(), $m) ? $m[1] : '';
    }

    // ---- T252 -------------------------------------------------------------------------------

    public function testListShowsOnlyThePetsReportsNewestSampleFirst(): void
    {
        $this->loginAs('vet');
        $older = Factory::labReport($this->petA, [], ['sample_date' => '2026-01-10 10:00:00']);
        $newer = Factory::labReport($this->petA, [], ['sample_date' => '2026-02-20 10:00:00']);
        $sameDay = Factory::labReport($this->petA, [], ['sample_date' => '2026-01-10 10:00:00']);
        $other = Factory::labReport($this->petB);

        $response = $this->get('lab/list_lab/' . $this->petA);

        $this->assertStatus($response, 200);
        $this->assertSame([$newer, $sameDay, $older], $this->listedIds($response));
        $this->assertNotContains($other, $this->listedIds($response));
    }

    public function testListOfAPetWithoutReportsSaysSo(): void
    {
        $this->loginAs('vet');

        $response = $this->get('lab/list_lab/' . $this->petB);

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'no lab results found');
    }

    public function testListOfAMissingPetGoesHome(): void
    {
        $this->loginAs('vet');

        $this->assertRedirectsTo($this->get('lab/list_lab/99999999'), '');
    }

    public function testListIncludesSoftDeletedReports(): void
    {
        $this->loginAs('vet');
        $deleted = Factory::labReport($this->petA, [], ['deleted_at' => date('Y-m-d H:i:s')]);

        // KNOWN BUG: get_for_pet has no deleted_at filter — decide in CI4 whether to keep
        $this->assertContains($deleted, $this->listedIds($this->get('lab/list_lab/' . $this->petA)));
    }

    // ---- T253 -------------------------------------------------------------------------------

    #[Group('known-bug')]
    public function testAdminDeleteHardDeletesTheReportAndLeavesItsResultsBehind(): void
    {
        // KNOWN BUG: `$soft_delete` typo makes MY_Model hard delete; results, plots and event links are orphaned — decide in CI4 whether to keep
        $report = Factory::labReport($this->petA, [['code' => 'ORPH', 'value_num' => 1.0]]);
        $event = Factory::event($this->petA);
        Factory::insert('events_labs', ['event_id' => $event, 'lab_id' => $report]);
        Factory::insert('lab_plots', ['report_id' => $report, 'type' => 'wbc', 'data' => '[1,2,3]']);
        $this->loginAs('admin');

        $response = $this->get('lab/delete/' . $report);

        $this->assertRedirectsTo($response, 'lab');
        $this->assertRowMissing('lab_report', ['id' => $report]);
        $this->assertRowCount(1, 'lab_results', ['report_id' => $report]);
        $this->assertRowCount(1, 'lab_plots', ['report_id' => $report]);
        $this->assertRowCount(1, 'events_labs', ['lab_id' => $report]);
    }

    public function testDeleteOnlyRemovesTheGivenReport(): void
    {
        $report = Factory::labReport($this->petA);
        $keep = Factory::labReport($this->petA);
        $this->loginAs('admin');

        $this->get('lab/delete/' . $report);

        $this->assertRowMissing('lab_report', ['id' => $report]);
        $this->assertRowExists('lab_report', ['id' => $keep]);
    }

    public function testVetIsRedirectedAndNothingIsDeleted(): void
    {
        $report = Factory::labReport($this->petA);
        $this->loginAs('vet');

        $response = $this->get('lab/delete/' . $report);

        $this->assertRedirectsTo($response, 'lab');
        $this->assertRowExists('lab_report', ['id' => $report]);
    }

    // ---- T254 -------------------------------------------------------------------------------

    public function testReassignByGetIsMethodNotAllowed(): void
    {
        $report = Factory::labReport($this->petA);
        $this->loginAs('vet');

        $this->assertStatus($this->get('lab/reassign/' . $report), 405);
        $this->assertSame($this->petA, (int) $this->find('lab_report', $report)['pet_id']);
    }

    public function testReassignMovesTheReportAndDropsOnlyTheLinksToTheOldPetsEvents(): void
    {
        $report = Factory::labReport($this->petA, [['code' => 'RES', 'value_num' => 1.0]]);
        $oldEvent = Factory::event($this->petA);
        $otherPetEvent = Factory::event($this->petB);
        Factory::insert('events_labs', ['event_id' => $oldEvent, 'lab_id' => $report]);
        Factory::insert('events_labs', ['event_id' => $otherPetEvent, 'lab_id' => $report]);
        $this->loginAs('vet');
        $this->db()->exec('DELETE FROM log');

        $response = $this->post('lab/reassign/' . $report, ['owner_id' => $this->ownerB, 'pet_id' => $this->petB]);

        $this->assertRedirectsTo($response, 'lab/detail/' . $report);
        $this->assertSame($this->petB, (int) $this->find('lab_report', $report)['pet_id']);
        $this->assertRowMissing('events_labs', ['event_id' => $oldEvent, 'lab_id' => $report]);
        $this->assertRowExists('events_labs', ['event_id' => $otherPetEvent, 'lab_id' => $report]);
        $log = $this->row("SELECT * FROM log WHERE event = 'lab_report_reassigned'");
        $this->assertNotNull($log);
        $this->assertStringContainsString('report_id: ' . $report, $log['msg']);
        $this->assertStringContainsString('old_pet_id: ' . $this->petA, $log['msg']);
        $this->assertStringContainsString('pet_id: ' . $this->petB, $log['msg']);
        $this->assertStringContainsString('removed_event_links: 1', $log['msg']);
        $this->assertSame('success', $this->excerptOfAlert($this->get('lab/detail/' . $report)));
    }

    public function testReassignToTheSamePetChangesNothingAndShowsTheInfoMessage(): void
    {
        $report = Factory::labReport($this->petA, [['code' => 'RES', 'value_num' => 1.0]]);
        $event = Factory::event($this->petA);
        Factory::insert('events_labs', ['event_id' => $event, 'lab_id' => $report]);
        $this->loginAs('vet');
        $this->db()->exec('DELETE FROM log');

        $response = $this->post('lab/reassign/' . $report, ['owner_id' => $this->ownerA, 'pet_id' => $this->petA]);

        $this->assertRedirectsTo($response, 'lab/detail/' . $report);
        $this->assertRowExists('events_labs', ['event_id' => $event, 'lab_id' => $report]);
        $this->assertSame(0, $this->logCount('lab_report_reassigned'));
        $this->assertSame('info', $this->excerptOfAlert($this->get('lab/detail/' . $report)));
    }

    public function testReassignToAPetOfAnotherOwnerIsRefusedWithADangerMessage(): void
    {
        $report = Factory::labReport($this->petA, [['code' => 'RES', 'value_num' => 1.0]]);
        $this->loginAs('vet');

        // pet B belongs to owner B, not to owner A
        $this->post('lab/reassign/' . $report, ['owner_id' => $this->ownerA, 'pet_id' => $this->petB]);

        $this->assertSame($this->petA, (int) $this->find('lab_report', $report)['pet_id']);
        $this->assertSame('danger', $this->excerptOfAlert($this->get('lab/detail/' . $report)));
    }

    public function testReassignToADeadPetIsRefused(): void
    {
        $report = Factory::labReport($this->petA);
        $dead = Factory::pet($this->ownerB, ['death' => 1]);
        $this->loginAs('vet');

        $this->post('lab/reassign/' . $report, ['owner_id' => $this->ownerB, 'pet_id' => $dead]);

        $this->assertSame($this->petA, (int) $this->find('lab_report', $report)['pet_id']);
    }
}
