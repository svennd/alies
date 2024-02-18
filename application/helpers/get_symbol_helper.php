<?php

/*
  draw the correct symbol for the pet_type
*/
function get_symbol($type, $name = false)
{
	switch($type)
	{
		case DOG:
			return '<span style="color:#628395"><i class="fas fa-fw fa-dog" data-toggle="tooltip" data-placement="left" title="Dog"></i></span>' . ($name ? " Dog" : "");
		case CAT:
			return '<span style="color:#96897B"><i class="fas fa-fw fa-cat" data-toggle="tooltip" data-placement="left" title="Cat"></i></span>'. ($name ? " Cat" : "");
		case HORSE:
			return '<span style="color:#CF995F"><i class="fas fa-fw fa-horse" data-toggle="tooltip" data-placement="left" title="Horse"></i></span>'. ($name ? " Horse" : "");
		case BIRD:
			return '<span style="color:#DBAD6A"><i class="fas fa-fw fa-dove" data-toggle="tooltip" data-placement="left" title="Bird"></i></span>'. ($name ? " Bird" : "");
		case RABBIT:
			return '<span style="color:#dec5a1"><i class="fas fa-paw fa-fw" data-toggle="tooltip" data-placement="left" title="Rabbit"></i></span>'. ($name ? " Rabbit" : "");
		default:
			return '<span style="color:#DFD5A5"><i class="fas fa-ghost fa-fw"></i></span>'. ($name ? " Other" : "");
	}
}

function get_name($type)
{
	switch($type)
	{
		case DOG:
			return "Dog";
		case CAT:
			return "Cat";
		case HORSE:
			return "Horse";
		case BIRD:
			return "Bird";
		case RABBIT:
			return "Rabbit";
		default:
			return "Other";
	}
}

/*
  draw the correct symbol for the pet_type
*/
function get_event_type($type, $name = false)
{
	switch($type)
	{
		case DISEASE:
			return '<span style="color:#628395" data-name="disease"><i class="fas fa-virus"></i></span>'. ($name ? " disease" : "");
		case OPERATION:
			return '<span style="color:#96897B" data-name="operatie"><i class="fas fa-hand-holding-medical"></i></span>'. ($name ? " operation" : "");
		case MEDICINE:
			return '<span style="color:#CF995F" data-name="medicine"><i class="fas fa-prescription-bottle-alt"></i></span>'. ($name ? " medicine" : "");
		case LAB:
			return '<span style="color:#1da855" data-name="medicine"><i class="fas fa-flask"></i></span>'. ($name ? " lab" : "");
		default:
			return '<span style="color:#DFD5A5" data-name="other"><i class="fas fa-fw"></i></span>';
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
function get_bill_status(int $status, bool $icon = false)
{
	if ($icon)
	{
		switch ($status)
		{
			case BILL_INVALID:
			case BILL_DRAFT:
			case BILL_PENDING:
			case BILL_UNPAID:
			case BILL_INCOMPLETE:
				case BILL_ONHOLD:
				return '<i class="fa-solid fa-fw fa-arrows-spin fa-spin"></i>';
			case BILL_PAID:
				return '<i class="fa-solid fa-fw fa-check"></i>';
			case BILL_HISTORICAL:
				return '<i class="fa-solid fa-fw fa-file-import"></i>';
			default:
				return '<i class="fa-solid fa-fw fa-clipboard-question"></i>';
		}
	}

	switch ($status)
	{
		case BILL_INVALID:
			return "invalid";
		case BILL_DRAFT:
			return "draft";
		case BILL_PENDING:
			return "pending";
		case BILL_UNPAID:
			return "unpaid";
		case BILL_INCOMPLETE:
			return "incomplete";
		case BILL_PAID:
			return "Paid";
		case BILL_OVERDUE:
			return "overdue";
		case BILL_ONHOLD:
			return "onhold";
		case BILL_HISTORICAL:
			return "historical";
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
		case STOCK_MERGE:
			return "stock_merged";
		default:
			return "unknown";
	}
}
/*
	ERROR levels
*/
function get_error_level(int $error, bool $icon = false) {
	if ($icon) {
		switch ($error) {
			case DEBUG:
				return '<i class="fa-solid fa-fw fa-bug" style="color: #f8afaf;"></i>';
			case INFO:
				return '<i class="fa-solid fa-fw  fa-info" style="color: #74C0FC;"></i>';
			case WARN:
				return '<i class="fa-solid fa-fw  fa-triangle-exclamation" style="color: #ee5353;"></i>';
			case ERROR:
				return '<i class="fa-solid fa-fw fa-circle-exclamation fa-beat" style="color: #ff0a0a;"></i>';
			case FATAL:
				return '<i class="fa-solid fa-fw fa-skull-crossbones fa-fade" style="color: #000000;"></i>';
			default:
				return '<i class="fa-solid fa-fw fa-question"></i>';
		}
	}
	switch ($error) {
		case DEBUG:
			return "DEBUG";
		case INFO:
			return "INFO";
		case WARN:
			return "WARN";
		case ERROR:
			return "ERROR";
		case FATAL:
			return "FATAL";
		default:
			return "unknown";
	}
}