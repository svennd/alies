<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;

/**
 * T250 - lab/index (Lab_controller::index, LabReport_model::get_labs).
 *
 * Rules: without a POST the list shows the reports created in the last 2 months (by created_at,
 * date part, inclusive); a POST with search_from / search_to replaces that window; the newest id
 * comes first; each row shows the owner's last name and the pet name.
 */
final class LabIndexTest extends HttpTestCase
{
    private int $petId;
    private string $ownerName;
    private string $petName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ownerName = Factory::unique('LabOwner');
        $this->petName = Factory::unique('LabPet');
        $owner = Factory::owner(['last_name' => $this->ownerName]);
        $this->petId = Factory::pet($owner, ['name' => $this->petName]);
        $this->loginAs('vet');
    }

    private function sourceIdsInList($response): array
    {
        preg_match_all('#lab/detail/(\d+)#', (string) $response->getBody(), $m);

        return array_map('intval', array_unique($m[1]));
    }

    public function testRecentReportsAreListedWithOwnerAndPetAndOldOnesAreNot(): void
    {
        $recent = Factory::labReport($this->petId, [], ['created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))]);
        $old = Factory::labReport($this->petId, [], ['created_at' => date('Y-m-d H:i:s', strtotime('-5 months'))]);

        $response = $this->get('lab');

        $this->assertStatus($response, 200);
        $ids = $this->sourceIdsInList($response);
        $this->assertContains($recent, $ids);
        $this->assertNotContains($old, $ids, 'older than 2 months');
        $this->assertSee($response, $this->ownerName);
        $this->assertSee($response, $this->petName);
    }

    public function testTheSeededReportIsListedAndTheNewestIdComesFirst(): void
    {
        $first = Factory::labReport($this->petId);
        $second = Factory::labReport($this->petId);

        $ids = $this->sourceIdsInList($this->get('lab'));

        $this->assertContains(10001, $ids);
        $this->assertLessThan(array_search($first, $ids, true), array_search($second, $ids, true), 'order by id desc');
    }

    public function testPostedRangeReplacesTheDefaultWindow(): void
    {
        $oldDate = date('Y-m-d H:i:s', strtotime('-5 months'));
        $old = Factory::labReport($this->petId, [], ['created_at' => $oldDate]);
        $recent = Factory::labReport($this->petId);

        $response = $this->post('lab/index', [
            'search_from' => date('Y-m-d', strtotime('-6 months')),
            'search_to' => date('Y-m-d', strtotime('-4 months')),
        ]);

        $ids = $this->sourceIdsInList($response);
        $this->assertContains($old, $ids);
        $this->assertNotContains($recent, $ids);
    }

    public function testRangeBoundariesAreInclusiveOnTheDatePart(): void
    {
        $day = date('Y-m-d', strtotime('-4 months'));
        $late = Factory::labReport($this->petId, [], ['created_at' => $day . ' 23:59:59']);
        $before = Factory::labReport($this->petId, [], ['created_at' => date('Y-m-d 12:00:00', strtotime($day . ' -1 day'))]);

        $ids = $this->sourceIdsInList($this->post('lab/index', ['search_from' => $day, 'search_to' => $day]));

        $this->assertContains($late, $ids);
        $this->assertNotContains($before, $ids);
    }

    #[Group('known-bug')]
    public function testSoftDeletedReportIsStillListed(): void
    {
        // KNOWN BUG: get_labs has the deleted_at filter commented out — decide in CI4 whether to keep
        $deleted = Factory::labReport($this->petId, [], ['deleted_at' => date('Y-m-d H:i:s')]);

        $this->assertContains($deleted, $this->sourceIdsInList($this->get('lab')));
    }

    public function testReportOfAPetThatDoesNotExistIsListedWithoutAClient(): void
    {
        $orphan = Factory::labReport(999999);

        $response = $this->get('lab');

        $this->assertContains($orphan, $this->sourceIdsInList($response));
        $this->assertMatchesRegularExpression('#lab/detail/' . $orphan . '"[^>]*btn-outline-danger#', (string) $response->getBody(), 'unlinked reports are shown red');
    }

    public function testMembersWithoutStaffGroupAreRefused(): void
    {
        $this->cookies->clear();
        $this->loginAs('member');

        $this->assertStatus($this->get('lab'), 500);
    }
}
