<?php

// at the rate of 4k numbers / year we can generate for ~250 year before turnover
function get_bill_id(int $id, $input_date = false)
{
    $date = ($input_date) ? DateTime::createFromFormat('Y-m-d H:i:s', $input_date) : new DateTime();
    return $date->format("Y") . str_pad($id, 6, '0', STR_PAD_LEFT);
}