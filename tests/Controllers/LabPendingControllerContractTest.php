<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LabPendingControllerContractTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = file_get_contents(APPPATH . 'controllers/Lab.php');
    }

    public function testQueueExposesDecodedIdentifiersButNotRawPayload(): void
    {
        $this->assertMatchesRegularExpression('/function pending\(\).*?get_active\(\).*?json_decode.*?identifiers.*?_render_page\(\'lab\/pending\'/s', $this->source);
        $pendingMethod = $this->methodSource('pending', 'recover_pending');
        $this->assertStringContainsString("'identifiers'", $pendingMethod);
        $this->assertStringNotContainsString("'raw_payload'", $pendingMethod);
    }

    public function testMutationsArePostOnlyAndRelyOnVetControllerAccess(): void
    {
		$this->assertStringContainsString('class Lab extends Vet_Controller', $this->source);
		$this->assertMatchesRegularExpression('/function pending_detail\(int \$pending_id\).*?get_active_by_id.*?lab_pending_preview->build.*?_render_page\(\'lab\/pending_detail\'/s', $this->source);
		$this->assertMatchesRegularExpression('/function recover_pending\(int \$pending_id\).*?require_pending_mutation\(\).*?post\(\'owner_id\'\).*?post\(\'pet_id\'\).*?recover_pending\(\$pending_id, \$owner_id, \$pet_id/s', $this->source);
        $this->assertMatchesRegularExpression('/function delete_pending\(int \$pending_id\).*?require_pending_mutation\(\).*?soft_delete_pending\(\$pending_id/s', $this->source);
		$this->assertMatchesRegularExpression('/function reassign\(int \$lab_id\).*?require_pending_mutation\(\).*?reassign_report\(\$lab_id, \$owner_id, \$pet_id\)/s', $this->source);
        $this->assertStringContainsString("method(true) !== 'POST'", $this->source);
		$this->assertStringNotContainsString('can_manage_pending', $this->source);
		$this->assertStringNotContainsString('require_pending_access', $this->source);
    }

    public function testSuccessfulActionsAreAuditedWithoutPayloadContent(): void
    {
        $recover = $this->methodSource('recover_pending', 'delete_pending');
		$delete = $this->methodSource('delete_pending', 'set_pending_feedback');

        $this->assertStringContainsString("'pending_lab_recovered'", $recover);
        $this->assertStringContainsString("'pending_id: '", $recover);
        $this->assertStringContainsString("' | report_id: '", $recover);
        $this->assertStringContainsString("' | pet_id: '", $recover);
		$this->assertStringContainsString("' | owner_id: '", $recover);
        $this->assertStringContainsString("' | user_id: '", $recover);
        $this->assertStringContainsString("'pending_lab_deleted'", $delete);
        $this->assertStringContainsString("' | user_id: '", $delete);
        $this->assertStringNotContainsString('raw_payload', $recover . $delete);
    }

	public function testOwnerAndPetLookupsAreSeparateAndOwnerScoped(): void
	{
		$this->assertMatchesRegularExpression('/function search_owners\(\).*?search_for_lab_assignment/s', $this->source);
		$this->assertMatchesRegularExpression('/function search_pets\(\).*?get\(\'owner_id\'\).*?search_assignable_for_owner\(\$owner_id/s', $this->source);
	}

	public function testReassignmentAuditsPreviousAndReplacementAssociationsWithoutPayload(): void
	{
		$reassign = $this->methodSource('reassign', 'print');
		$this->assertStringContainsString("'lab_report_reassigned'", $reassign);
		$this->assertStringContainsString("' | old_owner_id: '", $reassign);
		$this->assertStringContainsString("' | old_pet_id: '", $reassign);
		$this->assertStringContainsString("' | owner_id: '", $reassign);
		$this->assertStringContainsString("' | pet_id: '", $reassign);
		$this->assertStringContainsString("' | removed_event_links: '", $reassign);
		$this->assertStringContainsString("' | user_id: '", $reassign);
		$this->assertStringNotContainsString('raw_payload', $reassign);
	}

    private function methodSource(string $start, string $next): string
    {
        $startAt = strpos($this->source, 'public function ' . $start);
        $endAt = strpos($this->source, 'public function ' . $next, $startAt + 1);
        if ($endAt === false) {
            $endAt = strpos($this->source, 'private function ' . $next, $startAt + 1);
        }
        $this->assertNotFalse($startAt);
        $this->assertNotFalse($endAt);
        return substr($this->source, $startAt, $endAt - $startAt);
    }
}
