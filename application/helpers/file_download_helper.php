<?php
/*
  generate a csv file for stock list
*/
function array_to_csv_download($array, $filename = "export.csv") {
  header('Content-Type: application/csv');
  header('Content-Disposition: attachment; filename="'.$filename.'";');

  // open the "output" stream
  $f = fopen('php://output', 'w');
  foreach (explode("\n", $array) as $line) {
    fwrite($f, $line . "\n");
  }
}

/*
  simple function to generate csv
  (used in stocklist generation)
*/
function array_to_csv($array, $filename = "export.csv") {
  header('Content-Type: application/csv');
  header('Content-Disposition: attachment; filename="'.$filename.'";');

  // open the "output" stream
  $f = fopen('php://output', 'w');
  foreach ($array as $list) {
    $line = implode(",", $list);
    fwrite($f, $line . "\n");
  }
}
