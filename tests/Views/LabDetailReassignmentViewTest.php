<?php

declare(strict_types=1);

final class LabDetailReassignmentViewTest extends CodeIgniterDatabaseTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		$this->ci->lang->load('vet', 'dutch');
	}

	public function testNormalDetailRendersPreselectedOwnerFirstReassignmentWithConfirmation(): void
	{
		$html = $this->ci->load->view('lab/detail', [
			'lab_id' => 90,
			'lab_info' => ['pet' => ['id' => 20, 'name' => 'Fido'], 'device' => 'lmscan', 'source' => 'remote', 'source_id' => 'S-90', 'sample_date' => '2026-08-28', 'software_version' => null, 'metadata' => '{}'],
			'pet_info' => ['id' => 20, 'name' => 'Fido', 'owner' => 10],
			'owner' => ['id' => 10, 'first_name' => 'Test', 'last_name' => 'Owner'],
			'lab_details' => [], 'plots' => [], 'can_manage_lab_assignment' => true,
			'lab_message' => null, 'lab_message_type' => null,
		], true);

		$this->assertStringContainsString('lab/reassign/90', $html);
		$this->assertStringContainsString('name="owner_id"', $html);
		$this->assertStringContainsString('value="10" selected', $html);
		$this->assertStringContainsString('name="pet_id"', $html);
		$this->assertStringContainsString('value="20" selected', $html);
		$this->assertStringContainsString('return confirm(', $html);
		$this->assertStringContainsString('vorige dier', $html);
		$this->assertStringContainsString("reassignPet.val(null).trigger('change')", $html);
		$this->assertStringContainsString('lab\\/search_owners', $html);
		$this->assertStringContainsString('lab\\/search_pets', $html);
		$this->assertStringContainsString('lab/print/90', $html);
		$this->assertStringNotContainsString('reset_lab_link', $html);
	}

	public function testUnauthorizedDetailDoesNotRenderReassignmentForm(): void
	{
		$html = $this->ci->load->view('lab/detail', [
			'lab_id' => 91,
			'lab_info' => ['pet' => false, 'device' => 'lmscan', 'source' => null, 'source_id' => 'S-91', 'sample_date' => '2026-08-28', 'software_version' => null, 'metadata' => '{}'],
			'pet_info' => false, 'owner' => false, 'lab_details' => [], 'plots' => [],
			'can_manage_lab_assignment' => false, 'lab_message' => null, 'lab_message_type' => null,
		], true);

		$this->assertStringNotContainsString('lab/reassign/91', $html);
		$this->assertStringNotContainsString('name="owner_id"', $html);
	}
}
