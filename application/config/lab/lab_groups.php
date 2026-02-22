<?php
#
# this defines the grouping tree
# 
# other configs in this directory define mapping to these categories
# 
return [
  'hematologie' => [
    'Celhematologie' => [
        'WBC' => ['WBC', 'LYM', 'NEU', 'LYM_ABS', 'MON_ABS', 'STAF_ABS', 'NEU_ABS', 'EO_ABS'],
        'RBC' => ['RBC', 'HTC', 'MCV', 'MCH', 'MCHC', 'RDW', 'RETIC'],
        'PLT' => ['THR', 'PCT', 'MPV', 'PDW'],
    ],
  ],
];