<?php

# based on the gs1 code we can get information on the product from the database
# even if not we can get date & lotnr and "product id" (not internal product_id)
# examples :

/*
  https://www.barcodefaq.com/barcode-properties/definitions/gs1-application-identifiers/
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
          'lotnr' 	=> $lotnr,
          'pid' 	=> $pid
        );
  }

  return false;
}

/*
demo data : 
	gs1('010123456789012310ABCD17YYMMDD21ABCD'); // false
	gs1('0105420036936138172110001019KQ173');
	gs1('(01)05420036936138(17)211000(10)19KQ173');
	gs1('01054200369036351721040010111219');
	gs1('01054147360028281722080010B449703');
	gs1('0108714225153497172207001020A73');
	gs1('01087142251534731721070010120131');
	gs1('010871318415409517220630102292480');
	gs1('010360587409584717220619109C1924B');
	gs1('01040072210261671722050010KP0EDBR');
	gs1('01036611030078451721010810L474281');
	gs1('01087131841145491721010010A023C01');
	gs1('01036611030078381722030910L479175');
	gs1('010084016450365421139167056223[GS]1725022810W009516');
	gs1('010084016450685321200429323663[GS]1726093010A104212');
	gs1('010357466114011721136230334509[GS]1722013110JBB1G00');
	gs1('01054005810046501723083110LC50364[GS]2110FXGAW0PX');
	gs1('01054147360441251721080810401461');

	the [GS] is what Honeywell 1900 spit out for function codes
*/
function gs1(string $code, bool $text = true): array {

	$ai_text = array(
		'01' => 'GTIN',
		'10' => 'LOTNR',
		'11' => 'PROD_DATE',
		'12' => 'DUE_DATE',
		'13' => 'PACK_DATE',
		'15' => 'BEST_BEFORE_DATE',
		'16' => 'SELL_BY_DATE',
		'17' => 'EXP_DATE',
		'20' => 'PROD_VAR',
		'21' => 'SERIAL'
	);

	# init
	$data = array();

	# remove spaces and brackets
	$code = preg_replace('/[() ]/', '', $code);

	# split the code into parts
	$parts = explode('[GS]', $code);

	# [GS] is the function code that splits up data elements that have no static length
	foreach ($parts as $part)
	{
		# every part should have AI (Application Identifier) and data
        while (strlen($part) > 0) {

            # get the AI
            $ai = substr($part, 0, 2);

			# the key of AI
            $key = (isset($ai_text[$ai]) && $text) ? $ai_text[$ai] : $ai;

            ## GTIN
            # has to be 14 & control check
            if ($ai == '01' && gtincheck(substr($part, 2, 14))) {
				
                $data[$key] = substr($part, 2, 14);
                $part = substr($part, 16);
            }
            ## date
            else if (in_array($ai, array(11, 12, 13, 15, 16, 17)) && preg_match('/^[0-9]{6}$/', substr($part, 2, 6))) {
                $data[$key] = "20" . substr($part, 2, 2) . "-" . substr($part, 4, 2) . "-" . substr($part, 6, 2);
                $part = substr($part, 8);
            }
            # product variant
            else if ($ai == '20' && preg_match('/^[0-9]{2}$/', substr($part, 2, 2))) {
                $data[$key] = substr($part, 2, 2);
                $part = substr($part, 4);
            }   
            # random up to 20
            else if (in_array($ai, array(10, 21)) && preg_match('/^.{1,20}$/', substr($part, 2, 20))) {
                $data[$key] = substr($part, 2, 20);
                $part = substr($part, 22);
            }
            else 
            {
                // unknown piece of data
                // can't trust anything
                return array();
            }
        }
	}

	return ($data) ? $data : array();
}

function gtincheck(string $gs1Data): bool {
    $reversedDigits = str_split(substr($gs1Data, 0, -1));
    $reversedDigits = array_reverse($reversedDigits);
    $sum = 0;

    for ($i = 0; $i < count($reversedDigits); $i++) {
        $digit = intval($reversedDigits[$i]);

        if ($i % 2 === 0) {
            $sum += $digit * 3;
        } else {
            $sum += $digit;
        }
    }

    $mod10 = $sum % 10;
    $checkDigit = ($mod10 !== 0) ? (10 - $mod10) : 0;

    return ($checkDigit == substr($gs1Data, -1));
}