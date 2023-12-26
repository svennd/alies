<?php

function get_bill_id(int $id)
{
    return str_pad($id, 6, '0', STR_PAD_LEFT);
}

function get_invoice_id(int $invoice_id, $input_date = false, string $invoice_prefix = '')
{
    $date = ($input_date) ? DateTime::createFromFormat('Y-m-d H:i:s', $input_date) : new DateTime();

    return (base64_decode($invoice_prefix) == "YYYY") ? 
        $date->format("Y") . str_pad($invoice_id, 5, '0', STR_PAD_LEFT) 
        : 
        str_pad($invoice_id, 5, '0', STR_PAD_LEFT);
   
}

function generate_struct_message(int $client_id, int $bill_id)
{
    // check if the length isn't exceeding 10 characters
    if (strlen($client_id . $bill_id) > 10 )
    {
        $message = str_pad($client_id, 10, '0');
    }
    else {
        $message = str_pad($client_id, 10 - strlen($bill_id), '0') . $bill_id;
    }
    
    // do modulo 97 on message and if it 0 then add 97
    $check = (int) $message % 97;
    $check = ($check == 0) ? 97 : str_pad($check, 2, '0');

    $full_msg = $message . $check;
    return('+++' . substr($full_msg, 0, 3) . '/' . substr($full_msg, 3, 4) . '/' . substr($full_msg, 7, 5)  . '+++');
}