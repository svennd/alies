<form action="<?php echo base_url(); ?>invoice/bill_pay/<?php echo $bill_id ?>" method="post" autocomplete="off">
<div class="row">
	<div class="col-lg-12 mb-4">
		<?php include "blocks/open_bills.php"; ?>
	<div class="card mb-4">
		<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div>
			<a href="<?php echo base_url(); ?>invoice"><?php echo $this->lang->line('Invoice'); ?></a> /
			<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> /
			<?php echo $this->lang->line('check'); ?>
			</div>
			<div class="dropdown no-arrow">

				<a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $bill['id']; ?>/1" target="_blank" class="btn btn-outline-success btn-sm"><i class="fas fa-print"></i> print</a> 

				<?php if($bill['mail'] == 0): ?>
					<a href="#" <?php if(!empty($owner['mail'])): ?>id="sendmail"<?php else: ?>id="get_mail"<?php endif; ?> class="btn <?php echo (!empty($owner['mail'])) ? "btn-outline-primary" : "btn-outline-secondary"; ?> btn-sm ml-1">
						<?php echo (!empty($owner['mail'])) ? '<i class="fas fa-paper-plane"></i>' : '<i class="far fa-frown"></i>'; ?>
						mail</a>
				<?php else: ?>
					<a href="#" class="btn btn-sm btn-secondary disabled ml-3" role="button" aria-disabled="true"><i class="fas fa-paper-plane"></i> Send!</a>
				<?php endif; ?>

				<?php if($bill['status'] == PAYMENT_PAID || !is_null($bill['invoice_id'])): ?>
					<strong class="ml-3">#<?php echo get_invoice_id($bill['invoice_id'], $bill['created_at']); ?></strong>
				<?php else: ?>
					<a href="<?php echo base_url('invoice/make_invoice_id/' . (int) $bill['id']); ?>" class="btn btn-outline-danger btn-sm ml-3"><i class="fa-solid fa-location-crosshairs"></i> <?php echo $this->lang->line('create_invoice'); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="card-body">
			<div class="card-body p-0">
			<div class="row">
			<?php include "blocks/sidebar.php" ?>

		<div class="col-lg-9 mb-4" style="border-left: 1px solid #dcdcdc;">
	                <div class="row px-5 py-2">
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
									<h5><?php echo ucfirst(strtolower($pet_info['name'])); ?>
									<a href="<?php echo base_url(); ?>events/event/<?php echo $event_id; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-circle-arrow-left"></i> <?php echo $this->lang->line('consult'); ?></a>
								</h5>
								</div>

								<div class="col-md-6 text-right">
								<small><i><?php echo user_format_date($bill['created_at'], $user->user_date); ?></i></small>
								</div>
							</div>
								<hr>
								<table class="table table-sm table-hover">
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
</form>
</div>

<script type="text/javascript">

function store_msg()
{
	$.ajax({
			method: 'POST',
			url: '<?php echo base_url(); ?>invoice/store_bill_msg/' + <?php echo $bill['id']; ?>,
			data: {
				msg: $("#msg").val(),
				msg_invoice: $("#msg_invoice").val(),
			}
		});
}

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

	$("#store_messages").click(function() {
		$("#store_messages").html('<i class="fas fa-sync fa-spin"></i> Loading');
		store_msg();
		setTimeout(function() { $("#store_messages").html('<i class="fa-solid fa-floppy-disk"></i> Stored !'); }, 1000);
	});


	// store message before we send on
	$("#bill_unpay").click(function() {
		$("#bill_unpay").html('<i class="fas fa-sync fa-spin"></i> Loading');
		store_msg();
		window.location.href = this.href;
	});


	$("#sendmail").click(function() {
		// confirm sending mail
		Swal.fire({
			title: 'Send mail ?',
			showCancelButton: true,
			confirmButtonText: 'Send',
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					method: 'POST',
					url: '<?php echo base_url(); ?>invoice/get_bill/' + <?php echo $bill['id']; ?> + '/2'
				}).done(function() {
					$("#sendmail").html('<i class="fas fa-paper-plane"></i> Send!').addClass("btn-secondary disabled");
				});
				Swal.fire(`email sent!`);
			} 
		});
	});

	$("#get_mail").click(async function() {
		const { value: email } = await Swal.fire({
			title: 'Input email address',
			input: 'email',
			inputLabel: 'Your email address',
			inputPlaceholder: 'Enter your email address'
		});

		if (email) {
			// store email
			$.ajax({
				method: 'POST',
				url: '<?php echo base_url(); ?>owners/set_email/' + <?php echo $bill['owner_id']; ?>,
				data: { email: email }
			});
			
			// send mail
			$.ajax({
				method: 'POST',
				url: '<?php echo base_url(); ?>invoice/get_bill/' + <?php echo $bill['id']; ?> + '/2'
			}).done(function() {
				$("#sendmail").html('<i class="fas fa-paper-plane"></i> Send!').addClass("btn-secondary disabled");
			});

			Swal.fire(`email sent!`);
		}
		});


		// spielerei
		$('.shakeit').hover(
			function() {
				$(this).find('i').addClass('fa-shake');
			},
			function() {
				$(this).find('i').removeClass('fa-shake');
			}
		);
		$('.bounceit').hover(
			function() {
				$(this).find('i').addClass('fa-bounce');
			},
			function() {
				$(this).find('i').removeClass('fa-bounce');
			}
		);
});

</script>
