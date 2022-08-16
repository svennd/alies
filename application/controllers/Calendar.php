<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Calendar extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();
	}

	public function index(int $off = 1)
	{
        $current_month = new DateTime('first day of this month');
        $current_last_month = new DateTime('first day of this month');

        $current_month->modify('+'. $off . ' month');
        $current_last_month->modify('+'. $off . ' month');

        # store current month as int
        $this_month = $current_month->format('m');
        
        # if the 1st is not a monday, calculate back till we find a monday
        if ($current_month->format('N') != 1)
        {
            $current_month->modify('last monday');
        }
        

        $add_prev_month = $current_month->diff($current_last_month)->format('%a');
        $days_in_this_month = $add_prev_month+$current_month->format('t');

        if ($days_in_this_month < 28) { $number_of_days = 28; }
        elseif($days_in_this_month < 35) { $number_of_days = 35; }
        else { $number_of_days = 42; }
        
        $data = array(
                'cdate' => $current_month,
                'off' => $off,
                'days_in_this_month' => $days_in_this_month,
                'this_month' => $this_month,
                'ndays' => $number_of_days,
            );
		$this->_render_page('calendar/index', $data);
	}

}
