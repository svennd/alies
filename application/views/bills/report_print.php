<?php
$transfer_complete = ($bill['transfer'] != 0 && $bill['transfer_verified'] == 1 || $bill['transfer'] == 0);

# design push
define("PUSH_QR", 20);
define("PUSH_TOTAL", 26);

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $str_invoice_or_bill . ' #' . $fact_invoice_or_bill; ?></title>

<style type="text/css">

	@page { 
		margin: 310px 35px 150px 35px;
	} 

	body {
		font-family: Verdana, Arial, sans-serif;
		margin: 0;
		padding: 0;
  	}
	.header {
      position: fixed;
      top: -270px;
      left: 0;
      right: 0;
      /* height: 250px; */
      padding: 10px;
	  /* background-color:red; */
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
        border-radius: 1.2em;
    }

    .enlarge {
        font-weight: bold;
        font-size: 18px;
    }
	.letterhead {
		font-size: 14px;
	}
	.nobold {
		font-weight: normal;
	}
    .gray {
        background-color: #eeeeee;
		border-radius: 4px;
    }
</style>

</head>
<body>

<div class="header">
<?php if (file_exists(dirname(__FILE__) . "/../custom/bill_header.php")) { include dirname(__FILE__) . "/../custom/bill_header.php"; }  ?>

  <table width="100%">
    <tr>
		<td valign="top" width="60%">
			<span class="enlarge"><?php echo (is_null($bill['invoice_id'])) ? $this->lang->line('nota') : $this->lang->line('bill_header'); ?></span>
			<br/>
			<br/>
			<br/>
			<?php echo $this->lang->line('client_id'); ?> : #<?php echo $owner['id']; ?><br/>
			<?php if ($owner['btw_nr']) : ?><?php echo $this->lang->line('client')  . " ". $this->lang->line('VAT'); ?> : <?php echo $owner['btw_nr']; ?><br/><?php endif; ?>
			
		</td> 
		<td align="left" valign="bottom" class="letterhead">
			<br/>
			<br/>

			<?php if (!$owner['invoice_addr']) : ?>
			<?php echo $owner['last_name'] . "&nbsp;" . $owner['first_name']; ?><br/>
			<?php echo $owner['street'] . ', ' . $owner['nr']; ?><br>
			<?php echo $owner['zip'] . ' ' . $owner['city']; ?><br>
			<?php endif; ?>
			<br>
			<?php if ($owner['invoice_addr']) : ?>
				<?php if ($owner['invoice_contact']) : ?><?php echo $owner['invoice_contact']; ?><br/><?php endif; ?>
				<?php if ($owner['invoice_addr']) : ?><?php echo nl2br($owner['invoice_addr']); ?><br/><?php endif; ?>
			<?php endif; ?>
		</td>
    </tr>
  </table>
	<hr style="min-height: 1px; margin-top:50px; border:0px; background: #DCDDE1;"/>

	<table width="100%">
		<tr>
			<th><?php echo (is_null($bill['invoice_id'])) ? $this->lang->line('nota') : $this->lang->line('bill_id'); ?></th>
			<th><?php echo (is_null($bill['invoice_id'])) ? $this->lang->line('date') : $this->lang->line('bill_date'); ?></th>
			<th><?php echo $this->lang->line('bill_due_date'); ?></th>
			<th><?php echo (is_null($bill['invoice_id']) || $bill['status'] != BILL_PAID) ? "&nbsp;" : $this->lang->line('payment_detail'); ?></th>
		</tr>
		<tr>
			<td align="center"><?php echo (is_null($bill['invoice_id'])) ? get_bill_id($bill['id']) : get_invoice_id($bill['invoice_id'], $bill['invoice_date'], $this->conf['invoice_prefix']['value']); ?></td>
			<td align="center"><?php echo (is_null($bill['invoice_id'])) ? date_format(date_create($bill['created_at']), "d-m-Y") : date_format(date_create($bill['invoice_date']), "d-m-Y"); ?></td>
			<td align="center">
				<?php if ($bill['status'] != BILL_PAID || !$transfer_complete): ?>
					<?php echo date('d-m-Y', strtotime($bill['invoice_date']. ' +'. $due_date_days .' days')); ?>
				<?php else: ?>
					<i><?php echo ($bill['status'] == BILL_PAID) ? $this->lang->line('payment_complete') : ''; ?></i>
				<?php endif; ?>
			</td>
			<td align="center">
				<?php if($bill['card'] > 0 || $bill['cash'] > 0 || $bill['transfer'] > 0): ?>
					<?php if ($bill['card'] != 0.00) : ?><?php echo $this->lang->line('card'); ?>: &euro; <?php echo $bill['card']; ?><br/><?php endif; ?>
					<?php if ($bill['cash'] != 0.00) : ?><?php echo $this->lang->line('cash'); ?>: &euro; <?php echo $bill['cash']; ?><br/><?php endif; ?>
					<?php if ($bill['transfer'] != 0.00 && $transfer_complete) : ?><?php echo $this->lang->line('transfer'); ?>: &euro; <?php echo $bill['transfer']; ?><?php endif; ?>
				<?php endif; ?>
			</td>
		</tr>
	</table>
</div>
<?php if($bill['msg_invoice']): ?>
<br/>
<div style="font-weight: normal;padding:5px;" class="gray letterhead">
<?php echo nl2br($bill['msg_invoice']); ?>
</div>
<?php endif; ?>
<br/>
<!-- pets name + id list -->
<?php
	$pets_list = "";
	if (!empty($is_write_off_bill)) {
		$pets_list = $bill_context_reference;
	}
	else {
		foreach ($print as $pet_id => $event_details) {
			$pet 		= $event_details['pet'];
			$pets_list .= '<b>'. ucfirst(strtolower($pet['name'])) . '</b> (#' . $pet['id'] . ') ';
		}
	}
?>
<table width="100%">
  <tr>
	  <td>
		  <?php echo (!empty($is_write_off_bill)) ? $this->lang->line('write_off_reference') : $this->lang->line('pet_info'); ?> : <?php echo $pets_list; ?><br/>
	  </td>
	  <td align="right" valign="top"><?php echo $this->lang->line('bill_location'); ?> : <?php echo (!empty($is_write_off_bill)) ? $bill_context_location : $bill['location']['name']; ?></td>
  </tr>
</table>

<table width="100%" style="border-left:solid #eeeeee 1px; border-right:solid #eeeeee 1px;">
    <thead class="gray">
      <tr>
        <th align="left"><?php echo $this->lang->line('description'); ?></th>
        <th align="right"><?php echo $this->lang->line('Quantity'); ?></th>
        <th align="right"><?php echo $this->lang->line('Unit_price'); ?></th>
        <th align="right"><?php echo $this->lang->line('VAT'); ?></th>
        <th align="right"><?php echo $this->lang->line('Total'); ?>&nbsp;</th>
      </tr>
    </thead>
    <tbody>

<?php $total_net = 0; ?>
<?php 
foreach ($print as $pet_id => $event): 

	$pet 		= $event['pet'] ?? null;
	$prod 		= $event['products'];
	$proc 		= $event['procedures'];

	# skip if no products or procedures
	# in the event
	if (count($prod) + count($proc) == 0) continue;
	
	# note that if multiple pets have multiple bills it could become tricky
	$bill_lines = count($prod) + count($proc);

	?>
	<?php foreach ($proc as $procedure): ?>
		<tr><?php $date_proc = date_format(date_create($procedure['created_at']), "d-m-y"); ?>
			<td align="left"><?php echo $procedure['name']; ?><?php echo (strtotime($date_proc) === strtotime($bill['created_at'])) ? " <small>(" . $date_proc. ")</small>" : ""; ?></td>
			<td align="right">
				<div style="display: inline-block;"><?php echo number_format($procedure['volume'], 2); ?></div>
				<div style="display: inline-block; width:30px;">&nbsp;</div>
			</td>
			<td align="right">
				<div style="display: inline-block;"><?php echo number_format($procedure['unit_price'], 2); ?></div>
			</td>
			<td align="right"><?php echo $procedure['btw']; ?> %</td>
			<td align="right"><?php echo number_format($procedure['price_net'],2); $total_net += $procedure['price_net']; ?>&nbsp;</td>
		</tr>
	<?php endforeach; ?>
	
	<?php foreach ($prod as $product): ?>
		<tr>
			<td align="left"><?php echo $product['name']; ?></td>
			<td align="right">
				<div style="display: inline-block;"><?php echo number_format($product['volume'],2); ?></div>
				<div style="display: inline-block; width:30px;"><?php echo $product['unit_sell']; ?></div>
			</td>
			<td align="right">
				<div style="display: inline-block;"><?php echo number_format($product['unit_price'], 2); ?></div>
			</td>
			<td align="right"><?php echo $product['btw']; ?> %</td>
			<td align="right"><?php echo number_format($product['price_net'],2); $total_net += $product['price_net']; ?>&nbsp;</td>
		</tr>
	<?php endforeach; ?>

<?php endforeach; ?>

<tr>
<td colspan="5">&nbsp;</td>
</tr>
</tbody>
	</table>
	<?php if(isset($bill_lines) && $bill_lines > PUSH_TOTAL): ?><div style="page-break-before: always;"></div><hr><?php endif; ?>
	<table width="100%" style="border-collapse: collapse;">
        <tr>
			<td rowspan="4" valign="top">
				<table width="65%" style="text-align:center;">
  					<thead style="border-bottom:solid black 1px;">
						<tr>
							<th class="nobold"><?php echo $this->lang->line('VAT'); ?>%</th>
							<th class="nobold"><?php echo $this->lang->line('VAT_OVER'); ?></th>
							<th class="nobold"><?php echo $this->lang->line('VAT_AMOUNT'); ?></th>
						</tr>
					<thead>
  					<tbody style="border-bottom:solid black 1px;">
					<?php 
						foreach ($btw_details as $btw => $info): 
							if($info['calculated'] == 0) continue;
					?>
						<tr>
							<td><?php echo $btw; ?>%</td>
							<td><?php echo $info['over']; ?></td>
							<td><?php echo $info['calculated']; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
					</table>
			</td>
            <td align="right" valign="top"><?php echo $this->lang->line('Total_net_excl'); ?></td>
            <td align="right" valign="top"><?php echo number_format($total_net, 2); ?> &euro;</td>
        </tr>
		<tr>
			<td align="right" valign="top"><?php echo $this->lang->line('Total'). ' ' .$this->lang->line('VAT'); ?></td>
			<td align="right" valign="top">
			<?php $sum_btw = 0.0; foreach($btw_details as $x => $y): $sum_btw += $y['calculated']; endforeach; echo number_format($sum_btw, 2); ?> &euro;</td>
		</tr>
		<tr>
            <td align="right" class="enlarge" style="border:dashed grey 1px;border-right:0px;padding:6px;"><?php echo $this->lang->line('Total'); ?></td>
            <td align="right" class="enlarge" style="border:dashed grey 1px;border-left:0px;padding:6px;"><span class="gray" style="padding:5px;"><?php echo number_format($bill['total_brut'], 2); ?> &euro;</span></td>
        </tr>
  </table>

<br/>
<br/>
<?php if(($bill['status'] != BILL_PAID || !$transfer_complete) && isset($IBAN)): ?>
<?php if($bill_lines > PUSH_QR && $bill_lines < PUSH_TOTAL): ?><div style="page-break-before: always;"></div><?php endif; ?>
<div style="border:1px solid #B9B9B9; border-radius:15px; padding:5px; min-height:100px;">
<table width="100%">
	<tr>
		<td>
		<table width="100%">
			<tr>
				<td><?php echo $this->lang->line('to_pay'); ?></td>
				<td><?php echo number_format($bill['total_brut']-($bill['card'] + $bill['cash'] + (($bill['transfer'] && $transfer_complete) ? $bill['transfer'] : 0)), 2); ?> &euro;</td>
			</tr>
			<tr>
				<td><?php echo $this->lang->line('account_number'); ?></td>
				<td><?php echo $IBAN; ?><?php if(!empty($BIC)): ?><br/><small>BIC: <?php echo $BIC; ?></small><?php endif; ?></td>
			</tr>
			<tr>
				<td><?php echo $this->lang->line('account_name'); ?></td>
				<td><?php echo $name_owner;?></td>
			</tr>
			<tr>
				<td><?php echo $this->lang->line('message'); ?></td>
				<td><?php echo $struct; ?></td>
			</tr>
		</table>
		</td>
		<td><img src='<?php echo $qr; ?>' style="width:100px; height:100px; float:right;" alt="QR code" /></td>
	</tr>
	</table>
</div>
<?php endif; ?>
</body>
</html>
