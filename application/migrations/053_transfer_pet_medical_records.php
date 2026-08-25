<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_transfer_pet_medical_records extends CI_Migration
{
	protected $up_version = "053";
	protected $down_version = "052";

	public function up()
	{
		$this->load->model('Pets_model', 'transfer_pets');
		$result = $this->transfer_pets->backfill_transferred_pets();

		if ($result === false) {
			throw new RuntimeException(
				'Pet medical-record transfer backfill failed: ' . $this->transfer_pets->get_transfer_error()
			);
		}

		return $this->up_version;
	}

	public function down()
	{
		// The backfill moves clinical ownership data. Restore the pre-migration
		// database backup if a post-commit rollback is required.
		return $this->down_version;
	}
}
