<?php

/*
	try to remove any formating from a phone number
*/
function format_phone($phoneNumber) {

	# catch early
	if (empty($phoneNumber)) {
		return false;
	}

	// Remove spaces, dots, and slashes
	$phoneNumber = str_replace([' ','+', '.', '/'], '', $phoneNumber);
	
	// Remove any non-digit characters at the end
	$phoneNumber = preg_replace('/[^0-9]+$/', '', $phoneNumber);

	return $phoneNumber;
}


/*
	based on https://onzetaal.nl/taalloket/telefoonnummers-noteren
	only spaces are common place
*/
function print_phone(string $phoneNumber) {	
	if (empty($phoneNumber)) { return ""; }
	$length = strlen($phoneNumber);

	// mobiel
	if ($length == 10) {
		return preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})/", "$1 $2 $3 $4", $phoneNumber);
	
	// vast nummer
	} elseif ($length == 9) {
		$prefixes = ['02', '03', '04'];
		$prefix = substr($phoneNumber, 0, 2);

		// antwerp / blx / gent
		if (in_array($prefix, $prefixes)) {
			return preg_replace("/([0-9]{2})([0-9]{3})([0-9]{2})([0-9]{2})/", "$1 $2 $3 $4", $phoneNumber);
		
		// normal
		} else {
			return preg_replace("/([0-9]{3})([0-9]{2})([0-9]{2})([0-9]{2})/", "$1 $2 $3 $4", $phoneNumber);
		}
	// international
	} elseif ($length == 11) {
		return preg_replace("/([0-9]{2})([0-9]{3})([0-9]{2})([0-9]{2})([0-9]{2})/", "$1 $2 $3 $4 $5", $phoneNumber);
	}

	// we don't know
	return $phoneNumber;
}
