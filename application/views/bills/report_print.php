<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $this->lang->line('bill_header'); ?> #<?php echo get_bill_id($bill['id'], $bill['created_at']); ?></title>

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
	.nobold {
		font-weight: normal;
	}
    .gray {
        background-color: #eeeeee;
		color: #443e61;
    }
	footer {
		position: fixed; 
		bottom: -60px; 
		left: 0px; 
		right: 0px;
		height: 150px; 
	}
</style>

</head>
<body>
<?php if (file_exists(dirname(__FILE__) . "/../custom/bill_header.php")) { include dirname(__FILE__) . "/../custom/bill_header.php"; }  ?>  
	<br/>
  <table width="100%">
    <tr>
        <td>
			<?php echo $owner['last_name'] . "&nbsp;" . $owner['first_name']; ?> (#<?php echo $owner['id'] ?>)<br>
			<?php echo $owner['street'] . ' ' . $owner['nr']; ?><br>
			<?php echo $owner['zip'] . ' ' . $owner['city']; ?><br>
			<br>
			<?php if ($owner['btw_nr']) : ?>BTW : <?php echo $owner['btw_nr']; ?><br/><?php endif; ?>
			<?php if ($owner['invoice_addr']) : ?>
				<strong><?php echo $this->lang->line('invoice_addr'); ?></strong>
				<?php if ($owner['invoice_contact']) : ?><b><?php echo $owner['invoice_contact']; ?></b><br/><?php endif; ?>
				<?php if ($owner['invoice_addr']) : ?><?php echo $owner['invoice_addr']; ?><br/><?php endif; ?>
				<?php if ($owner['invoice_tel']) : ?><abbr title="Phone">P:</abbr> <?php echo $owner['invoice_tel']; ?><br/><?php endif; ?>
			<?php endif; ?>
		</td>
        <td align="right" valign="top">
			<?php if($bill['card'] > 0 || $bill['cash'] > 0): ?>
				<strong><?php echo $this->lang->line('payment_detail'); ?></strong><br/>
				<?php if ($bill['card'] != 0.00) : ?><?php echo $this->lang->line('card'); ?>: &euro; <?php echo $bill['card']; ?><br/><?php endif; ?>
				<?php if ($bill['cash'] != 0.00) : ?><?php echo $this->lang->line('cash'); ?>: &euro; <?php echo $bill['cash']; ?><br/><?php endif; ?>
				<br/>
				<?php echo ($bill['status'] == PAYMENT_PAID) ? $this->lang->line('payment_complete') : ''; ?>
			<?php endif; ?>
		</td>
    </tr>
  </table>

<table width="100%">
	<tr>
		<th><?php echo $this->lang->line('bill_id'); ?></th>
		<th><?php echo $this->lang->line('bill_date'); ?></th>
		<th><?php echo $this->lang->line('bill_due_date'); ?></th>
	</tr>
	<tr>
		<td align="center"><?php echo get_bill_id($bill['id'], $bill['created_at']); ?></td>
		<td align="center"><?php echo $bill['created_at']; ?></td>
		<td align="center"><?php echo $bill['created_at']; ?></td>
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
			<?php echo $this->lang->line('pet_info'); ?> : <strong><?php echo ucfirst(strtolower($pet_info['name'])); ?></strong> (#<?php echo $pet_info['id']; ?>)<br/>
		</td>
        <td align="right" valign="top"><?php echo $this->lang->line('bill_location'); ?> : <?php echo $location_i[$event_location]['name']; ?></td>
    </tr>

  </table>
  
<?php $total_net = 0; ?>
  <table width="100%">
    <thead class="gray">
      <tr>
        <th><?php echo $this->lang->line('description'); ?></th>
        <th><?php echo $this->lang->line('Quantity'); ?></th>
        <th><?php echo $this->lang->line('Price'); ?></th>
        <th><?php echo $this->lang->line('VAT'); ?></th>
        <th><?php echo $this->lang->line('Total'); ?></th>
      </tr>
    </thead>
    <tbody>
	<?php foreach ($prod as $product): ?>
		<tr>
			<td align="right"><?php echo $product['name']; ?></td>
			<td align="right"><?php echo $product['volume']; ?> <?php echo $product['unit_sell']; ?></td>
			<td align="right"><?php echo $product['net_price']; $total_net += $product['net_price']; ?> &euro;</td>
			<td align="right"><?php echo $product['btw']; ?> %</td>
			<td align="right"><?php echo round($product['price'], 2); ?> &euro;</td>
		</tr>
	<?php endforeach; ?>
	<?php foreach ($proc as $procedure): ?>
		<tr>
			<td align="right"><?php echo $procedure['name']; ?></td>
			<td align="right"><?php echo $procedure['amount']; ?></td>
			<td align="right"><?php echo $procedure['net_price']; $total_net += $procedure['net_price']; ?> &euro;</td>
			<td align="right"><?php echo $procedure['btw']; ?> %</td>
			<td align="right"><?php echo round($procedure['price'], 2); ?> &euro;</td>
		</tr>
	<?php endforeach; ?>
		<tr>
		<td colspan="4">&nbsp;</td>
		</tr>
    </tbody>

    <tfoot>
	
		<?php $design = 0; foreach ($bill_total_tally as $btw => $total): ?>
			<tr>
				<?php if($design == 0): ?>
					<?php 
						if (file_exists(dirname(__FILE__) . "/../custom/block_iban.php")) { 
							include dirname(__FILE__) . "/../custom/block_iban.php"; 
						} else { ?>
							<td colspan="2" rowspan="4">&nbsp;</td>
						<?php } ?>  
				<?php endif; ?>

				<td colspan="2" align="right"><?php echo $btw; ?>% btw over <?php echo $total; ?> &euro;</td>
				<td align="right"><?php echo round($total*($btw/100), 2); ?> &euro;</td>
			</tr>
		<?php $design++; endforeach; ?>
        <tr>
            <td colspan="2" align="right"><?php echo $this->lang->line('Total'); ?> net</td>
            <td align="right"><?php echo $total_net; ?> &euro;</td>
        </tr>
        <tr>
            <td colspan="2" align="right" class="enlarge"><?php echo $this->lang->line('Total'); ?></td>
            <td align="right" class="gray enlarge"><?php echo $bill['amount']; ?> &euro;</td>
        </tr>
    </tfoot>
  </table>

	<?php endforeach; ?>
	<?php endforeach; ?>

<br/>
<br/>
<br/>
<br/>

<footer>
<?php if (file_exists(dirname(__FILE__) . "/../custom/bill_footer.php")) { include dirname(__FILE__) . "/../custom/bill_footer.php"; }  ?>  
</footer>
	</body>

</html>