<?php
	foreach ($location_i as $loc)
	{
		$l[$loc['id']] = $loc['name'];
	}
?>
<div class="row">
	<div class="col-lg-12 mb-4">

	<?php if ($open_bills): ?>
	<div class="alert alert-danger" role="alert">
	<?php echo $this->lang->line('open_invoices'); ?> !<br/>
		<?php foreach($open_bills as $b): ?>
			<a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $b['id']; ?>"><?php echo $b['amount']; ?> &euro;</a><br/>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="card mb-4">
		<div class="card-header">
			<a href="<?php echo base_url(); ?>invoice"><?php echo $this->lang->line('Invoice'); ?></a> /
			<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> /
			<?php echo $this->lang->line('bill'); ?> (#<?php echo get_bill_id($bill['id'], $bill['created_at']); ?>)
		</div>
		<div class="card-body">


			<div class="card-body p-0">

				<div class="row">
					<div class="col-lg-3 mb-4">

	<?php if ($bill['status'] != PAYMENT_PAID): ?>
					<p class="lead">Payment : <?php echo get_bill_status($bill['status']); ?></p>
					<?php
						$cash = round((float) $bill['cash'], 2);
						$card = round((float) $bill['card'], 2);
						if ($card + $cash != 0) :
							$total_short = round( (float) $bill['amount'] - ($card + $cash) , 2);
							?>
								<p>
									<?php echo $this->lang->line('shortage'); ?> : <?php echo $total_short; ?> &euro; (<?php echo $this->lang->line('card'); ?> : <?php echo $card; ?> &euro;, <?php echo $this->lang->line('cash'); ?> : <?php echo $cash; ?> &euro;)
								</p>
						<?php endif; ?>

		<form action="<?php echo base_url(); ?>invoice/bill_pay/<?php echo $bill_id ?>" method="post" autocomplete="off">
		<div class="input-group mb-3">
			<div class="input-group-prepend">
				<span class="input-group-text" for="exampleCheck1"><a href="#" id="select_card" onclick="event.preventDefault()"><i class="fab fa-cc-visa"></i>&nbsp;<?php echo $this->lang->line('card'); ?></a></span>
			</div>
			<input type="text" class="form-control" id="card_value" name="card_value" value="<?php echo ($card != 0) ? $card:'';?>" />
			<div class="input-group-append">
				<span class="input-group-text">&euro;</span>
			</div>
		</div>

		<div class="input-group mb-3">
		  <div class="input-group-prepend">
			<span class="input-group-text" for="exampleCheck1"><a href="#" id="select_cash" onclick="event.preventDefault()"><i class="fas fa-euro-sign"></i>&nbsp;<?php echo $this->lang->line('cash'); ?></a></span>
		  </div>
		  <input type="text" class="form-control" id="cash_value" name="cash_value" value="<?php echo ($cash != 0) ? $cash:'';?>" />
		<div class="input-group-append">
			<span class="input-group-text">&euro;</span>
		</div>
		<div class="input-group-append">
			<span class="input-group-text" id="calculate"><a href="#"><i class="fas fa-calculator"></i></a></span>
		</div>
		</div>
		<div class="form-group">
			<label for="msg">Notes</label>
			<textarea class="form-control" id="msg" name="msg" rows="3"><?php echo (isset($bill['msg']) ? $bill['msg'] : ""); ?></textarea>
		</div>
		<i><small id="payment_info" class="form-text text-muted ml-2">&nbsp;</small></i>
			<button type="submit" name="submit" value="1" class="btn btn-outline-success"><i class="fas fa-file-invoice-dollar"></i> <?php echo $this->lang->line('payment_complete'); ?></button>

		<?php endif; ?>

		<?php if ($bill['status'] == PAYMENT_PAID): ?>
		<p class="lead"><?php echo $this->lang->line('payment'); ?>: <?php echo get_bill_status($bill['status']); ?>!</p>
		<?php
			$cash = round((float) $bill['cash'], 2);
			$card = round((float) $bill['card'], 2);
		?>
		<?php echo $this->lang->line('payed'); ?> : <?php echo $bill['amount']; ?> &euro; (<?php echo $this->lang->line('card'); ?> : <?php echo $card; ?> &euro;, <?php echo $this->lang->line('cash'); ?> : <?php echo $cash; ?> &euro;)
		<div class="form-group">
			<textarea class="form-control" disabled><?php echo (isset($bill['msg']) ? $bill['msg'] : ""); ?></textarea>
		</div>
		<?php endif; ?>
			<?php if ($bill['status'] != PAYMENT_PAID && $bill['status'] != PAYMENT_PARTIALLY): ?>
			<a href="<?php echo base_url(); ?>invoice/bill_unpay/<?php echo $bill_id; ?>" class="btn btn-outline-danger mx-2" id="bill_unpay" onclick="event.preventDefault()"><i class="fas fa-syringe"></i> <?php echo $this->lang->line('drop_from_stock'); ?></a>
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
						<a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $bill['id']; ?>/1" target="_blank" class="btn btn-success"><i class="fas fa-print"></i> print</a> 
						<?php if($bill['mail'] == 0): ?>
						<a href="#" <?php if(!empty($owner['mail'])): ?>id="sendmail"<?php endif; ?> class="btn <?php echo (!empty($owner['mail'])) ? "btn-primary" : "btn-secondary"; ?> ml-3">
							<?php echo (!empty($owner['mail'])) ? '<i class="fas fa-paper-plane"></i>' : '<i class="far fa-frown"></i>'; ?>
							mail</a><br/>
						<?php else: ?>
							<a href="#" class="btn btn-success" ml-3"><i class="fas fa-paper-plane"></i> Send!</a>
						<?php endif; ?>
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
									<a href="<?php echo base_url(); ?>events/event/<?php echo $event_id; ?>" class="btn btn-sm btn-outline-primary"><?php echo $this->lang->line('consult'); ?></a><br/>
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
											<th class="border-0 text-uppercase small font-weight-bold"><?php echo $this->lang->line('Quantity'); ?></th>
											<th class="border-0 text-uppercase small font-weight-bold"><?php echo $this->lang->line('Price'); ?></th>
											<th class="border-0 text-uppercase small font-weight-bold"><?php echo $this->lang->line('VAT'); ?></th>
											<th class="border-0 text-uppercase small font-weight-bold"><?php echo $this->lang->line('Total'); ?></th>
										</tr>
									</thead>
									<tbody>
									<?php foreach ($proc as $procedure): ?>
										<tr>
											<td><?php echo $procedure['name']; ?></td>
											<td><?php echo $procedure['amount']; ?></td>
											<td><?php echo $procedure['net_price']; ?> &euro;</td>
											<td><?php echo $procedure['btw']; ?> %</td>
											<td><?php echo round($procedure['price'],2); ?> &euro;</td>
										</tr>
									<?php endforeach; ?>
									<?php foreach ($prod as $product): ?>
										<tr>
											<td><?php echo $product['name']; ?></td>
											<td><?php echo $product['volume']; ?> <?php echo $product['unit_sell']; ?></td>
											<td><?php echo $product['net_price']; ?> &euro;</td>
											<td><?php echo $product['btw']; ?> %</td>
											<td><?php echo round($product['price'],2); ?> &euro;</td>
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
                            <div class="mb-2"><?php echo $this->lang->line('Total'); ?></div>
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

	// store message before we send on
	$("#bill_unpay").click(function() {
		$("#bill_unpay").html('<i class="fas fa-sync fa-spin"></i> Loading')
		$.ajax({
			method: 'POST',
			url: '<?php echo base_url(); ?>invoice/store_bill_msg/' + <?php echo $bill['id']; ?>,
			data: {
				msg: $("#msg").val(),
			}
		});
		window.location.href = this.href;
	});

	$("#sendmail").click(function() {
		$("#sendmail").html('<i class="fas fa-sync fa-spin"></i> Sending').addClass("btn-info");
		$.ajax({
			method: 'POST',
			url: '<?php echo base_url(); ?>invoice/get_bill/' + <?php echo $bill['id']; ?> + '/2'
		}).done(function() {
			$("#sendmail").html('<i class="fas fa-paper-plane"></i> Send!').addClass("btn-success");
		});
	});
});
</script>
