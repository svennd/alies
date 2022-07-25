<?php

/*
  draw the correct symbol for the pet_type
*/
function get_symbol($type)
{
	switch($type)
	{
		case DOG:
			return '<span style="color:#628395"><i class="fas fa-fw fa-dog"></i></span>';
		case CAT:
			return '<span style="color:#96897B"><i class="fas fa-fw fa-cat"></i></span>';
		case HORSE:
			return '<span style="color:#CF995F"><i class="fas fa-fw fa-horse"></i></span>';
		case BIRD:
			return '<span style="color:#DBAD6A"><i class="fas fa-fw fa-dove"></i></span>';
		case RABBIT:
			return '<span style="color:#dec5a1"><i class="fas fa-paw fa-fw"></i></span>';
		default:
			return '<span style="color:#DFD5A5"><i class="fas fa-fw"></i></span>';
	}
}

/*
  based on the gender draw the correct symbol
*/
function get_gender($gender)
{
	switch($gender)
	{
		case MALE:
			return '<span style="color:#4c6ef5;"><i class="fas fa-mars fa-fw"></i></span> Male';
		case FEMALE:
			return '<span style="color:#f783ac;"><i class="fas fa-venus fa-fw"></i></span> Female';
		case MALE_NEUTERED:
			return '<span style="color:#000;"><i class="fas fa-mars fa-fw"></i></span> Male neutered';
		case FEMALE_NEUTERED:
			return '<span style="color:#000;"><i class="fas fa-venus fa-fw"></i></span> Female neutered';
		default:
			return '<span style="color:#6cce23;"><i class="fas fa-genderless fa-fw"></i></span> Other';
	}
}

/*
 return the bill name instead of the integer
*/
function get_bill_status(int $status)
{
	switch ($status)
	{
		case PAYMENT_PAID:
			return "Paid";
		case PAYMENT_PARTIALLY:
			return "Paid Partially";
		case PAYMENT_UNPAID:
			return"Unpaid";
		case PAYMENT_OPEN:
			return "Open";
		case PAYMENT_NON_COLLECTABLE:
			return "PAYMENT_NON_COLLECTABLE";
		default:
			return "unknown";
	}
}

/*
	stock state to text
*/
function stock_state(int $stock_state) {
	switch ($stock_state) {
		case STOCK_CHECK:
			return "check";
		case STOCK_IN_USE:
			return "in_use";
		case STOCK_HISTORY:
			return "history";
		case STOCK_ERROR:
			return "error";
		default:
			return "unknown";
	}
}