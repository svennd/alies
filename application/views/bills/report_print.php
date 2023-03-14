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
	tfoot {
		border-top: 1px solid #B9B9B9;
		margin-top: -15px;
	}
    tfoot tr td{
        font-size: x-small;
    }
    .enlarge {
        font-weight: bold;
        font-size: medium;
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
	<h3><?php echo (is_null($bill['invoice_id'])) ? $this->lang->line('check') : $this->lang->line('bill_header'); ?></h3>
  <table width="100%">
    <tr>
        <td>
			<?php echo $owner['last_name'] . "&nbsp;" . $owner['first_name']; ?> (#<?php echo $owner['id'] ?>)<br>
			<?php echo $owner['street'] . ' ' . $owner['nr']; ?><br>
			<?php echo $owner['zip'] . ' ' . $owner['city']; ?><br>
			<br>
			<?php if ($owner['btw_nr']) : ?><?php echo $this->lang->line('VAT'); ?> : <?php echo $owner['btw_nr']; ?><br/><?php endif; ?>
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
			<?php endif; ?>
		</td>
    </tr>
  </table>

<table width="100%">
	<tr>
		<th><?php echo (is_null($bill['invoice_id'])) ? $this->lang->line('check') : $this->lang->line('bill_id'); ?></th>
		<th><?php echo (is_null($bill['invoice_id'])) ? $this->lang->line('date') : $this->lang->line('bill_date'); ?></th>
		<th><?php echo $this->lang->line('bill_due_date'); ?></th>
	</tr>
	<tr>
		<td align="center"><?php echo (is_null($bill['invoice_id'])) ? get_bill_id($bill['id'], $bill['created_at']) : get_invoice_id($bill['invoice_id'], $bill['created_at']); ?></td>
		<td align="center"><?php echo date_format(date_create($bill['created_at']), "d-m-Y"); ?></td>
		<td align="center">
			<?php if ($bill['status'] != PAYMENT_PAID): ?>
				<?php echo date('d-m-Y', strtotime($bill['created_at']. ' +'. $due_date_days .' days')); ?>
			<?php else: ?>
				<i><?php echo ($bill['status'] == PAYMENT_PAID) ? $this->lang->line('payment_complete') : ''; ?></i>
			<?php endif; ?>
		</td>
	</tr>
</table>
  
<br/>
<!-- pets name + id list -->
<?php
	$pets_list = "";
	foreach ($pets as $p)
	{
		$pets_list .= '<b>'. ucfirst(strtolower($p['name'])) . '</b> (#' . $p['id'] . ') ';
	}
?>
<table width="100%">
  <tr>
	  <td>
		  <?php echo $this->lang->line('pet_info'); ?> : <?php echo $pets_list; ?><br/>
	  </td>
	  <td align="right" valign="top"><?php echo $this->lang->line('bill_location'); ?> : <?php //echo $location_i[$event_location]['name']; ?></td>
  </tr>
</table>

<table width="100%">
    <thead class="gray">
      <tr>
        <th align="left"><?php echo $this->lang->line('description'); ?></th>
        <th align="right"><?php echo $this->lang->line('Quantity'); ?></th>
        <th align="right"><?php echo $this->lang->line('Unit_price'); ?></th>
        <th align="right"><?php echo $this->lang->line('Price'); ?></th>
        <th align="right"><?php echo $this->lang->line('VAT'); ?></th>
      </tr>
    </thead>
    <tbody>

<?php 
foreach ($print_bill as $pet_id => $event): 
	# resolve name, chip
	$pet_info = $pets[$pet_id];

	?>
  
	<?php
	foreach ($event as $event_id => $vbill):
		list($event_id, $event_location, $payment_bill, $created_at, $updated_at) = array_values($event_info[$pet_id][$event_id]);
		list($prod, $proc, $total, $booking) = array_values($vbill);

		# skip if no products or services
		if (count($prod) + count($proc) == 0) continue;
?>

<?php $total_net = 0; ?>
	<?php foreach ($proc as $procedure): ?>
		<tr>
			<td align="left"><?php echo $procedure['name']; ?> <small>(<?php echo date_format(date_create($procedure['created_at']), "d-m-y"); ?>)</small></td>
			<td align="right">
				<div style="display: inline-block;"><?php echo number_format($procedure['amount'], 2); ?></div>
				<div style="display: inline-block; width:15px;">&nbsp;</div>
			</td>
			<td align="right">
				<div style="display: inline-block;"><?php echo number_format(round($procedure['net_price']/$procedure['amount'], 2), 2); ?></div>
			</td>
			<td align="right"><?php echo number_format($procedure['net_price'],2); $total_net += $procedure['net_price']; ?></td>
			<td align="right"><?php echo $procedure['btw']; ?> %</td>
		</tr>
	<?php endforeach; ?>
	<?php foreach ($prod as $product): ?>
		<tr>
			<td align="left"><?php echo $product['name']; ?></td>
			<td align="right">
				<div style="display: inline-block;"><?php echo number_format($product['volume'],2); ?></div>
				<div style="display: inline-block; width:15px;"><?php echo $product['unit_sell']; ?></div>
			</td>
			<td align="right">
				<div style="display: inline-block;"><?php echo number_format(round($product['net_price']/$product['volume'], 2), 2); ?></div>
			<!--	<div style="display: inline-block;width:35px;">&euro; / <?php echo $product['unit_sell']; ?></div>-->
			</td>
			<td align="right"><?php echo number_format($product['net_price'],2); $total_net += $product['net_price']; ?></td>
			<td align="right"><?php echo $product['btw']; ?> %</td>
		</tr>
	<?php endforeach; ?>
<?php endforeach; ?>
<?php endforeach; ?>

<tr>
<td colspan="4">&nbsp;</td>
</tr>
</tbody>
	</table>
	<table width="100%">
    <tfoot>
		<?php $design = 0; foreach ($bill_total_tally as $btw => $total): ?>
			<tr>
				<?php if($design == 0): ?>
					<?php 
						if (file_exists(dirname(__FILE__) . "/../custom/block_iban.php") && $bill['status'] != PAYMENT_PAID) { 
							include dirname(__FILE__) . "/../custom/block_iban.php"; 
						} else { ?>
							<td colspan="2" rowspan="4">&nbsp;</td>
						<?php } ?>  
				<?php endif; ?>

				<td colspan="2" align="right"><div style="display: inline-block;"><?php echo $btw; ?>% btw over</div><div style="display: inline-block;width:55px;"><?php echo number_format($total, 2); ?> &euro;</div></td>
				<td align="right"><?php echo round($total*($btw/100), 2); ?> &euro;</td>
			</tr>
		<?php $design++; endforeach; ?>
        <tr>
            <td colspan="2" align="right"><?php echo $this->lang->line('Total_net_excl'); ?></td>
            <td align="right"><?php echo number_format($total_net, 2); ?> &euro;</td>
        </tr>
        <tr>
            <td colspan="2" align="right" class="enlarge"><?php echo $this->lang->line('Total_inc'); ?></td>
            <td align="right" class="gray enlarge"><?php echo number_format($bill['amount'], 2); ?> &euro;</td>
        </tr>
    </tfoot>
  </table>


<br/>
<br/>
<br/>
<br/>

<footer>
<?php if (file_exists(dirname(__FILE__) . "/../custom/bill_footer.php")) { include dirname(__FILE__) . "/../custom/bill_footer.php"; }  ?>  
</footer>
	</body>

</html>