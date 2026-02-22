<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function get_vamreg_out_unit(string $unit, float $volume): array
{
	return [
		"out_quantity_type"			=> ($unit == "PACKS") ? "PACKS" : "UNITS",
		"out_quantity_pack_count"	=> ($unit == "PACKS") ? $volume : null,
		"out_quantity_unit_count"	=> ($unit != "PACKS") ? $volume : null,
		"out_quantity_unit"			=> ($unit == "PACKS") ? null : $unit
	];
}


# transfer internal pet_tupe to vamreg target species
function get_vamreg_target_species(int $pet_type): string
{
   switch ($pet_type) {
		case DOG:
			return "DOG";
		case CAT:
			return "CAT";
		case HORSE:
			return "HORSE";
		default:
			return "OTHER_NON_FOOD";
   }
}

# check if valid vamreg indication, return indication if valid, null if not
function valid_vamreg_indication(?string $indication): ?string
{
	$valid_indications = 
 	[
		"DIGEST",
		"EYE",
		"LOCO",
		"MAST",
		"NERVE",
		"PERI_OP",
		"RESP",
		"DERMA",
		"SYST",
		"URO_GEN",
	];
	
	return in_array($indication, $valid_indications) ? $indication : null;
}