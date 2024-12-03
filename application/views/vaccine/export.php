<?php
# i don't know any other proper way to do this
setlocale(LC_TIME, 'nl_BE.UTF-8');
$is_minimal = true;

$header = array (
        "owner_id",
        "first_name",
        "last_name",
        "street",
        "nr",
        "city",
        "province",
        "zip",

        "product_name",
        "disease",

        "injection_date",
        "redo_date",
        
        "pet_name",
        "pet_type",

        "owner_mail",
        "last_bill",
        "debts",

        "vet_name"
    );

// can be pushed to config at some point
$minimal = array("last_name", "street", "nr", "city", "zip", "disease", "injection_date", "pet_name", "pet_type");

// minimal or full
$columns = $is_minimal ? $minimal : $header;

$pet_type_map = array(
    DOG     => $this->lang->line('dog'),
    CAT     => $this->lang->line('cat'),
    HORSE   => $this->lang->line('horse'),
    BIRD    => $this->lang->line('bird'),
    OTHER   => $this->lang->line('other'),
    RABBIT  => $this->lang->line('rabbit')
);

// print header
echo sprintf('"%s"', implode('","', $columns)). PHP_EOL;

// loop data
foreach($expiring_vacs as $v):
    
// change id to pet_name
if (isset($pet_type_map[$v['pet_type']])) {
    $v['pet_type'] = strtolower($pet_type_map[$v['pet_type']]);
}

// transform dates
$v['injection_date'] = strftime($date_format, strtotime($v['injection_date']));
$v['redo_date'] = strftime($date_format, strtotime($v['redo_date']));

// Filter data based on $columns
$filtered_data = array_intersect_key($v, array_flip($columns));

// Print filtered data
echo sprintf('"%s"', implode('","', $filtered_data)) . PHP_EOL;

endforeach;

?>