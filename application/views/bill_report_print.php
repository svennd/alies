<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bill #<?php
				$date = date_create_from_format ('Y-m-d H:i:s', $bill['created_at']); 
					echo date_format($date, 'Y') . str_pad($bill['id'], 5, '0', STR_PAD_LEFT); ?></title>

<style type="text/css">
    * {
        font-family: Verdana, Arial, sans-serif;
    }
    table{
        font-size: x-small;
    }
    tfoot tr td{
        font-weight: bold;
        font-size: x-small;
    }
    .enlarge {
        font-weight: bold;
        font-size: large;
    }
    .gray {
        background-color: lightgray
    }
</style>

</head>
<body>

<?php include "custom/bill_header.php"; ?>
  
	<hr>
  <table width="100%">
    <tr>
        <td>
			<strong>Client</strong> (#<?php echo $owner['id'] ?>)<br/>
			<?php echo $owner['last_name'] . " &nbsp;" . $owner['first_name']; ?><br>
			<?php echo $owner['street'] . ' ' . $owner['nr']; ?><br>
			<?php echo $owner['city'] . ' ' . $owner['zip']; ?><br>
			<br>
			<?php if ($owner['telephone']) : ?><abbr title="Phone">P:</abbr> <?php echo $owner['telephone']; ?><br/><?php endif; ?>
			<?php if ($owner['mobile']) : ?><abbr title="Mobile">M:</abbr> <?php echo $owner['mobile']; ?><br/><?php endif; ?>
			<?php if ($owner['mail']) : ?><a href="mailto:<?php echo $owner['mail']; ?>"><?php echo $owner['mail']; ?></a><br/><?php endif; ?>
			<?php if ($owner['btw_nr']) : ?>BTW : <?php echo $owner['btw_nr']; ?><br/><?php endif; ?>
			<?php if ($owner['invoice_addr']) : ?>
				<strong>Invoice</strong>
				<?php if ($owner['invoice_contact']) : ?><b><?php echo $owner['invoice_contact']; ?></b><br/><?php endif; ?>
				<?php if ($owner['invoice_addr']) : ?><?php echo $owner['invoice_addr']; ?><br/><?php endif; ?>
				<?php if ($owner['invoice_tel']) : ?><abbr title="Phone">P:</abbr> <?php echo $owner['invoice_tel']; ?><br/><?php endif; ?>
			<?php endif; ?>
		</td>
        <td align="right" valign="top">
				<strong>Payment Details</strong><br/>
				<?php if ($bill['card'] != 0.00) : ?>Card: &euro; <?php echo $bill['card']; ?><br/><?php endif; ?>
				<?php if ($bill['cash'] != 0.00) : ?>Cash: &euro; <?php echo $bill['cash']; ?><br/><?php endif; ?>
				<br/>
				<?php echo $bill['created_at']; ?>
		</td>
    </tr>

  </table>

  <br/>
  
<?php 
foreach ($print_bill as $pet_id => $event): 
	# resolve name, chip
	$pet_info = $pets[$pet_id];
	
	foreach ($event as $event_id => $vbill):
		list($event_id, $event_location, $payment_bill, $created_at, $updated_at) = array_values($event_info[$pet_id][$event_id]);
		list($prod, $proc, $total, $booking) = array_values($vbill);

		# skip if no products or services
		if (count($prod) + count($proc) == 0) continue;
?>
		
  <table width="100%">
    <tr>
        <td>
			<strong><?php echo ucfirst(strtolower($pet_info['name'])); ?></strong> (#<?php echo $pet_info['id']; ?>)<br/>
			Chip : <?php echo $pet_info['chip']; ?><br/>
			Location : <?php echo $location_i[$event_location]['name']; ?>
		</td>
        <td align="right" valign="top">
			&nbsp;
		</td>
    </tr>

  </table>
  
			
  <table width="100%">
    <thead style="background-color: lightgray;">
      <tr>
        <th>Item</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>BTW</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
	<?php foreach ($prod as $product): ?>
		<tr>
			<td align="right"><?php echo $product['name']; ?></td>
			<td align="right"><?php echo $product['volume']; ?> <?php echo $product['unit_sell']; ?></td>
			<td align="right"><?php echo $product['net_price']; ?> &euro;</td>
			<td align="right"><?php echo $product['btw']; ?> %</td>
			<td align="right"><?php echo $product['price']; ?> &euro;</td>
		</tr>
	<?php endforeach; ?>
	<?php foreach ($proc as $procedure): ?>
		<tr>
			<td align="right"><?php echo $procedure['name']; ?></td>
			<td align="right"><?php echo $procedure['amount']; ?></td>
			<td align="right"><?php echo $procedure['net_price']; ?> &euro;</td>
			<td align="right"><?php echo $procedure['btw']; ?> %</td>
			<td align="right"><?php echo $procedure['price']; ?> &euro;</td>
		</tr>
	<?php endforeach; ?>
		<tr>
		<td colspan="4">&nbsp;</td>
		</tr>
    </tbody>

    <tfoot>
	
		<?php foreach ($bill_total_tally as $btw => $total): ?>
			<tr>
				<td colspan="2"></td>
				<td colspan="2" align="right"><?php echo $btw; ?>% btw over <?php echo $total; ?> &euro;</td>
				<td align="right"><?php echo round($total*($btw/100), 2); ?> &euro;</td>
			</tr>
		<?php endforeach; ?>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td colspan="2" align="right" class="enlarge">Total &euro;</td>
            <td align="right" class="gray enlarge"><?php echo $bill['amount']; ?> &euro;</td>
        </tr>
    </tfoot>
  </table>

	<?php endforeach; ?>
	<?php endforeach; ?>
<hr>
  <table width="100%">
    <tr>
        <td>

		</td>
        <td align="right" valign="top">

		</td>
    </tr>

  </table>
<br/>
<br/>
<br/>
<br/>

	</body>

</html>