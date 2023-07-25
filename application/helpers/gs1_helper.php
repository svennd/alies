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

/*
	if (barcode.length > 26)
	{
		result = barcode.match(/01([0-9]{14})(10(.*?)17([0-9]{6})21(.*)|17([0-9]{6})10(.*))/);
		if(result)
		{
			var gsbarcode = result[1];
			var date = (typeof(result[3]) === 'undefined') ? result[6] : result[4];
			var lotnr = (typeof(result[3]) === 'undefined') ?  result[7] : result[3];
			var day = (date.substr(4,2) == "00") ? "01" : date.substr(4,2);
			
			// enter lotnr + date and disable them
			$("#lotnr").val(lotnr).prop("readonly", true);
			$("#date").val("20" + date.substr(0, 2) + "-" + date.substr(2,2) + "-" + day).prop("readonly", true);
			
			$.getJSON("<?php echo base_url(); ?>products/gs1_to_product?gs1=" + gsbarcode , function(data, status){
				if (data.state)
				{
					$("#pid").val(data[0].id);
					$("#autocomplete").val(data[0].name).prop("readonly", true);
					$("#sell").val(1);
					$("#buy").focus();

					$("#unit_buy").html(data[0].unit_buy);
					$("#unit_sell").html(data[0].unit_sell);
					$("#tip").html("Min buy volume, " + data[0].buy_volume + " " + data[0].unit_buy + " => sell volume, " + data[0].sell_volume + " " + data[0].unit_sell);
			
					$("#catalog_price").val(data[0].buy_price + " € / " + data[0].buy_volume + " " + data[0].unit_sell);
					$("#current_buy_price").val(data[0].buy_price);
					$("#sell").focus();

					$('#autocomplete').autocomplete().disable();
				}
				else 
				{
					// need to re-enable everything.
					$("#new_barcode_input").val(1);
					$("#barcode_gs1").val(gsbarcode);
					$("#product_tip").html("unknown GS1, please select product!"); 
				
					$("#autocomplete").val("").focus();
					$("#gs1_datamatrix").val(barcode);
					$("#matrix").show();
				}
			});
			
			// getJSON is out of sync
			return true;
		}
		else 
		{
			$("#product_tip").html("invalid code; not recognized"); 
		}
	}
	return false;
*/