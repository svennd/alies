<?php
/*
    testing the Ikems identification resolution
*/
require 'application/third_party/api/devices/Ikems.php';

$adapter = new Ikems();

$cases = [
    [ // case unnresolvable (no full name, no correct id)
        'pet_id' => 'ravier',
        'pet_name' => '28',
        'patient_number' => '12'
    ],
    [  // 4678 - no match
        'pet_id' => 'lolo',
        'pet_name' => '4678',
        'patient_number' => '345'
    ],
    [ // 998414 
        'pet_id' => null,
        'pet_name' => '998414',
        'patient_number' => null
    ],
    [ // 998414 
        'pet_id' => null,
        'pet_name' => null,
        'patient_number' => '998414'
    ], 
    [ // 192032 
        'pet_id' => null,
        'pet_name' => 'BELLE',
        'patient_number' => '192032'
    ],    
    [  
        'pet_id' => '192032',
        'pet_name' => 'BELLE',
        'patient_number' => 'vanhoye'
    ],   
    [ 
        'pet_id' => '176072',
        'pet_name' => 'CANDRIES/KUZCO',
        'patient_number' => null
    ],
];

foreach ($cases as $i => $payload) {
    $r = (new ReflectionMethod($adapter, 'resolveIdentification'))->invoke($adapter, $payload);

    echo "CASE $i\n";
    print_r($r);
    echo "----------------\n";
}
