<?php
$state = array(
				"OPEN",
				"UNPAID",
				"NOT COMPLETE",
				"PAID",
				"NON_COLLECTABLE",
				);

	foreach ($location_i as $loc)
	{
		$l[$loc['id']] = $loc['name'];
	}
?>
<div class="row">
	<div class="col-lg-12 mb-4">

	<?php if ($open_bills): ?>
	<div class="alert alert-danger" role="alert">
	Open invoices !<br/>
		<?php foreach($open_bills as $b): ?>
			<a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $b['id']; ?>"><?php echo $b['amount']; ?> &euro;</a><br/>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="card mb-4">
		<div class="card-header">
			<a href="<?php echo base_url(); ?>invoice">Invoice</a> / <a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> /
			Bill (#<?php
				$date = date_create_from_format ('Y-m-d H:i:s', $bill['created_at']);
					echo date_format($date, 'Y') . str_pad($bill['id'], 5, '0', STR_PAD_LEFT); ?>)
		</div>
		<div class="card-body">


			<div class="card-body p-0">

				<div class="row">
					<div class="col-lg-3 mb-4">

	<?php if ($bill['status'] != PAYMENT_PAID): ?>
					<p class="lead">Payment : <?php echo $state[$bill['status']]; ?></p>
					<?php
						$cash = round((float) $bill['cash'], 2);
						$card = round((float) $bill['card'], 2);
						if ($card + $cash != 0) :
							$total_short = round( (float) $bill['amount'] - ($card + $cash) , 2);
							?>
								<p>
									Short : <?php echo $total_short; ?> &euro; (card : <?php echo $card; ?> &euro;, cash : <?php echo $cash; ?> &euro;)
								</p>
						<?php endif; ?>

					<form action="<?php echo base_url(); ?>invoice/bill_pay/<?php echo $bill_id ?>" method="post" autocomplete="off">
		<div class="input-group mb-3">
			<div class="input-group-prepend">
				<span class="input-group-text" for="exampleCheck1"><a href="#" id="select_card" onclick="event.preventDefault()"><i class="fab fa-cc-visa"></i>&nbsp;Card</a></span>
			</div>
			<input type="text" class="form-control" id="card_value" name="card_value" value="<?php echo ($card != 0) ? $card:'';?>" />
			<div class="input-group-append">
				<span class="input-group-text">&euro;</span>
			</div>
		</div>

		<div class="input-group mb-3">
		  <div class="input-group-prepend">
			<span class="input-group-text" for="exampleCheck1"><a href="#" id="select_cash" onclick="event.preventDefault()"><i class="fas fa-euro-sign"></i>&nbsp; Cash</a></span>
		  </div>
		  <input type="text" class="form-control" id="cash_value" name="cash_value" value="<?php echo ($cash != 0) ? $cash:'';?>" />
		<div class="input-group-append">
			<span class="input-group-text">&euro;</span>
		</div>
		<div class="input-group-append">
			<span class="input-group-text" id="calculate"><a href="#"><i class="fas fa-calculator"></i></a></span>
		</div>
		</div>
		<i><small id="payment_info" class="form-text text-muted ml-2">&nbsp;</small></i>
			<button type="submit" name="submit" value="1" class="btn btn-outline-success"><i class="fas fa-file-invoice-dollar"></i> Payment Complete</button>

		<?php endif; ?>
		<?php if ($bill['status'] == PAYMENT_PAID): ?>
		<p class="lead">Payment : <?php echo $state[$bill['status']]; ?>!</p>
		<?php
			$cash = round((float) $bill['cash'], 2);
			$card = round((float) $bill['card'], 2);
		?>
		Payed : <?php echo $bill['amount']; ?> &euro; (card : <?php echo $card; ?> &euro;, cash : <?php echo $cash; ?> &euro;)

		<?php endif; ?>
			<?php if ($bill['status'] != PAYMENT_PAID): ?>
			<a href="<?php echo base_url(); ?>invoice/bill_unpay/<?php echo $bill_id; ?>" class="btn btn-outline-danger mx-2"><i class="fas fa-syringe"></i> Drop from stock</a>
		<?php endif; ?>
		  </form>

		</div>
		<div class="col-lg-9 mb-4" style="border-left: 1px solid #dcdcdc;">
					<div class="row">
					<div class="col-md-6">
						<?php echo $owner['last_name']. " &nbsp;" . $owner['first_name']; ?><br/>
						#<?php echo $owner['id'] ?>
					</div>
					<div class="col-md-6 text-right">
						<a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $bill['id']; ?>/print" target="_blank" class="btn btn-success"><i class="fas fa-print"></i> print</a><br/>
					</div>
					</div>
	                <div class="row px-5 py-3">
                        <div class="col-md-12">
							<?php
							// $full_total = 0.0;
							foreach ($print_bill as $pet_id => $event):
								# resolve name, chip
								$pet_info = $pets[$pet_id];

								foreach ($event as $event_id => $vbill):
									list($event_id, $event_location, $payment_bill, $created_at, $updated_at) = array_values($event_info[$pet_id][$event_id]);
									list($prod, $proc, $total) = array_values($vbill);

									# skip if no products or services
									if (count($prod) + count($proc) == 0) continue;
							?>
							<div class="row">
								<div class="col-md-6">
									<h4><?php echo ucfirst(strtolower($pet_info['name'])); ?>
									<small>
									<a href="<?php echo base_url(); ?>events/event/<?php echo $event_id; ?>" class="btn btn-sm btn-outline-primary">consult</a><br/>
									</small></h4>
								</div>

								<div class="col-md-6 text-right">
								<small><i><?php echo user_format_date($bill['created_at'], $user->user_date); ?></i></small>
								</div>
							</div>
								<hr>
								<table class="table">
									<thead>
										<tr>
											<th class="border-0 text-uppercase small font-weight-bold">Item</th>
											<th class="border-0 text-uppercase small font-weight-bold">Quantity</th>
											<th class="border-0 text-uppercase small font-weight-bold">Price</th>
											<th class="border-0 text-uppercase small font-weight-bold">BTW</th>
											<th class="border-0 text-uppercase small font-weight-bold">Total</th>
										</tr>
									</thead>
									<tbody>
									<?php foreach ($proc as $procedure): ?>
										<tr>
											<td><?php echo $procedure['name']; ?></td>
											<td><?php echo $procedure['amount']; ?></td>
											<td><?php echo $procedure['net_price']; ?> &euro;</td>
											<td><?php echo $procedure['btw']; ?> %</td>
											<td><?php echo $procedure['price']; ?> &euro;</td>
										</tr>
									<?php endforeach; ?>
									<?php foreach ($prod as $product): ?>
										<tr>
											<td><?php echo $product['name']; ?></td>
											<td><?php echo $product['volume']; ?> <?php echo $product['unit_sell']; ?></td>
											<td><?php echo $product['net_price']; ?> &euro;</td>
											<td><?php echo $product['btw']; ?> %</td>
											<td><?php echo $product['price']; ?> &euro;</td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>

						<?php endforeach; ?>
						<?php endforeach; ?>
                        </div>
                    </div>
					<hr/>
	                <div class="row px-5 py-3">
                        <div class="col-md-4 offset-md-8">
							<table class="table">
							<tr>
								<th class="border-0 text-uppercase small font-weight-bold">BTW%</th>
								<th class="border-0 text-uppercase small font-weight-bold">Total net</th>
								<th class="border-0 text-uppercase small font-weight-bold">Bedrag</th>
							</tr>
							<?php foreach ($bill_total_tally as $btw => $total): ?>
								<tr>
									<td><?php echo $btw; ?> %</td>
									<td><?php echo $total; ?> &euro;</td>
									<td><?php echo round($total*($btw/100), 2); ?> &euro;</td>
								</tr>
							<?php endforeach; ?>
							</table>
                        </div>
                    </div>
					<hr>
                    <div class="d-flex flex-row-reverse bg-dark text-white p-4">
                        <div class="py-3 px-5 text-right">
                            <div class="mb-2">Total</div>
                            <div class="h2 font-weight-light"><?php echo $bill['amount']; ?> &euro;</div>
                        </div>
                    </div>
			</div>
		</div>
		</div>
	</div>
	</div>
</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#invoice").addClass('active');
	$("#select_card").click(function(){
		$("#card_selector").prop("checked", true);
		$("#card_value").val("<?php echo $bill['amount']; ?>");
		$("#cash_value").val("");
	});
	$("#select_cash").click(function(){
		$("#cash_selector").prop("checked", true);
		$("#cash_value").val("<?php echo $bill['amount']; ?>");
		$("#card_value").val("");
	});

	$("#calculate").click(function() {
		var cash = parseFloat($("#cash_value").val());
		var card = parseFloat($("#card_value").val());
		var total = parseFloat(<?php echo $bill['amount']; ?>);

		if (isNaN(cash)) {cash = 0.0}
		if (isNaN(card)) {card = 0.0}
		if (isNaN(total)) {return false;}
		var total_in = cash+card;

		$("#payment_info").html("current: " + Math.round((total-total_in)*100)/100 + " &euro;");
	});
});
</script>
