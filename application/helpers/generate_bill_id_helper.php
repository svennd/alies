<?php

function get_bill_id(int $id, $input_date = false)
{
    $date = ($input_date) ? DateTime::createFromFormat('Y-m-d H:i:s', $input_date) : new DateTime();
    return $date->format("Y") . str_pad($id, 5, '0', STR_PAD_LEFT);
}