<?php

declare(strict_types=1);

final class LabAssignmentLookupModelTest extends CodeIgniterDatabaseTestCase
{
	public function testOwnerSearchAndOwnerScopedPetSearchReturnOnlyAssignablePair(): void
	{
		$pet = $this->ci->db->select('pets.id, pets.name, pets.owner, owners.last_name')
			->from('pets')->join('owners', 'owners.id = pets.owner')
			->where('pets.death', 0)->where('pets.transfered', 0)
			->where('pets.deleted_at IS NULL', null, false)->where('owners.deleted_at IS NULL', null, false)
			->order_by('pets.id', 'ASC')->get()->row_array();
		$this->assertNotNull($pet);

		$owners = $this->model('Owners_model', 'lab_lookup_owners')->search_for_lab_assignment((string) $pet['last_name']);
		$this->assertContains((int) $pet['owner'], array_map('intval', array_column($owners, 'id')));
		$pets = $this->model('Pets_model', 'lab_lookup_pets')->search_assignable_for_owner((int) $pet['owner'], (string) $pet['name']);
		$this->assertContains((int) $pet['id'], array_map('intval', array_column($pets, 'id')));
		$this->assertTrue($this->ci->lab_lookup_pets->is_assignable_to_owner((int) $pet['id'], (int) $pet['owner']));

		$otherOwner = $this->ci->db->select('id')->where('id !=', (int) $pet['owner'])->where('deleted_at IS NULL', null, false)->get('owners')->row_array();
		if ($otherOwner) {
			$this->assertFalse($this->ci->lab_lookup_pets->is_assignable_to_owner((int) $pet['id'], (int) $otherOwner['id']));
			$this->assertSame([], $this->ci->lab_lookup_pets->search_assignable_for_owner((int) $otherOwner['id'], (string) $pet['name']));
		}
	}
}
