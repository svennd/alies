<?php

# based on the gs1 code we can get information on the product from the database
# even if not we can get date & lotnr and "product id" (not internal product_id)
# examples :

/*
  https://www.barcodefaq.com/barcode-properties/definitions/gs1-application-identifiers/
  the one we see here :
  (01) GTIN --> 13 + mod10 check
  (10) batch / lotnr --> up to 20 digits
  (11) production date
  (12) due date
  (13) packaging date
  (15) best before date
  (16) sell by date
  (17) expiry date

(01)05420036936138 (17)211000 (10)19KQ173
0105420036936138172110001019KQ173
01054200369036351721040010111219
01054147360028281722080010B449703
0108714225153497172207001020A73
01087142251534731721070010120131
010871318415409517220630102292480
010871318415409517220630102292480
010360587409584717220619109C1924B
01040072210261671722050010KP0EDBR
01036611030078451721010810L474281
01087131841145491721010010A023C01
01036611030078381722030910L479175
01054147360441251721080810401461
*/
function parse_gs1($barcode)
{
  # this accepts 2 formats of gs1 code
  if (preg_match('/01([0-9]{14})(10(.*?)17([0-9]{6})21(.*)|17([0-9]{6})10(.*))/', $barcode, $result))
  {
    $pid = $result[1];
    $date = (!$result[3]) ? $result[6] : $result[4];
    $lotnr = (!$result[3]) ? $result[7] : $result[3];

    $day = (substr($date, 4, 2) == "00") ? "01" : substr($date, 4, 2);

    return array(
          'date' 	=> "20" . substr($date, 0, 2) . "-" . substr($date, 2,2) . "-" . $day,
          'lotnr' => $lotnr,
          'pid' 	=> $pid
        );
  }

  return false;
}
