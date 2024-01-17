<form action="<?php echo base_url('invoice/bill_pay/' . $bill['id']); ?>" method="post" autocomplete="off">
<div class="row">
	<div class="col-lg-12 mb-4">
		<?php include "blocks/open_bills.php"; ?>
	<div class="card mb-4">
		<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div>
			<a href="<?php echo base_url('invoice'); ?>"><?php echo $this->lang->line('Invoice'); ?></a> /
			<a href="<?php echo base_url('owners/detail/' . $owner['id']); ?>"><?php echo $owner['last_name'] ?></a> /
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

				<?php if($bill['status'] == BILL_PAID || !is_null($bill['invoice_id'])): ?>
					<strong class="ml-3">#<?php echo get_invoice_id($bill['invoice_id'], $bill['invoice_date'], $this->conf['invoice_prefix']['value']); ?></strong>
				<?php else: ?>
					<a href="<?php echo base_url('invoice/make_invoice_id/' .  $bill['id']); ?>" class="btn btn-outline-danger btn-sm ml-3"><i class="fa-solid fa-location-crosshairs"></i></a>
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
							$total_already_payed = $bill['cash'] + $bill['transfer'] + $bill['card'];
							$sum = 0.0;
							foreach ($print as $pet_id => $event_details):
								
								$pet 		= $event_details['pet'];
								$prod 		= $event_details['products'];
								$proc 		= $event_details['procedures'];

								# skip if no products or procedures
								# in the event
								if (count($prod) + count($proc) == 0) continue;

							?>
							<div class="row">
								<div class="col-md-6">
									<h5><?php echo ucfirst(strtolower($pet['name'])); ?>
									<?php foreach($event_details['events'] as $event_id): ?>
										<a href="<?php echo base_url('events/event/' . $event_id); ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-circle-arrow-left"></i> <?php echo $this->lang->line('consult'); ?></a>
									<?php endforeach; ?>
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
											<th class="border-0 text-uppercase small font-weight-bold"><?php echo $this->lang->line('Unit_price'); ?></th>
											<th class="border-0 text-uppercase small font-weight-bold"><?php echo $this->lang->line('VAT'); ?></th>
											<th class="border-0 text-uppercase small font-weight-bold text-right"><?php echo $this->lang->line('net_price'); ?></th>
										</tr>
									</thead>
									<tbody>
									<?php foreach ($proc as $procedure): ?>
										<tr>
											<td>
												<?php if($this->ion_auth->in_group("admin") && !is_null($procedure['reduction_reason']) && !empty($procedure['reduction_reason'])): ?>
													<i class="fa-solid fa-skull-crossbones" data-toggle="tooltip" data-placement="left" title="<?php echo $procedure['reduction_reason']; ?>"></i>
												<?php endif; ?>
												<?php echo $procedure['name']; ?></td>
											<td><?php echo $procedure['volume']; ?></td>
											<td><?php echo number_format($procedure['unit_price'], 2); ?> &euro;</td>
											<td><?php echo $procedure['btw']; ?> %</td>
											<td class="text-right"><?php echo $procedure['price_net']; ?> &euro;</td>
											<?php $sum += $procedure['price_net']; ?>
										</tr>
									<?php endforeach; ?>
									<?php foreach ($prod as $product): ?>
										<tr>
											<td>
												<?php if($this->ion_auth->in_group("admin") && !is_null($product['reduction_reason']) && !empty($product['reduction_reason'])): ?>
													<i class="fa-solid fa-skull-crossbones" data-toggle="tooltip" data-placement="left" title="<?php echo $product['reduction_reason']; ?>"></i>
												<?php endif; ?>
												<?php echo $product['name']; ?></td>
											<td><?php echo $product['volume']; ?> <?php echo $product['unit_sell']; ?></td>
											<td><?php echo number_format($product['unit_price'], 2); ?> &euro;</td>
											<td><?php echo $product['btw']; ?> %</td>
											<td class="text-right"><?php echo $product['price_net']; ?> &euro;</td>
											<?php $sum += $product['price_net']; ?>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
						<?php endforeach; ?>
                        </div>
                    </div>
					<hr>

	                <div class="row px-5 py-2">
                        <div class="col-md-6"><?php echo $this->lang->line('Total_excl_VAT'); ?></div>
                        <div class="col-md-6 text-right"><?php echo number_format($sum, 2); ?> &euro;</div>
					</div>
					
	                <div class="row px-5 py-2">
                        <div class="col-md-6 text-uppercase"><a data-toggle="collapse" href="#collapseBTW" role="button" aria-expanded="false" aria-controls="collapseBTW"><?php echo $this->lang->line('Total'). ' ' .$this->lang->line('VAT'); ?></a></div>
                        <div class="col-md-6 text-right"><?php $sum_btw = 0.0; foreach($btw_details as $x => $y): $sum_btw += $y['calculated']; endforeach; echo number_format($sum_btw, 2); ?> &euro;</div>
					</div>
           			<div class="collapse" id="collapseBTW">
						<div class="row px-5 pt-2">
							<div class="col-md-6">
								<table class="table table-bordered table-sm text-right small">
										<tr>
											<th><?php echo $this->lang->line('VAT'); ?>%</th>
											<th><?php echo $this->lang->line('VAT_OVER'); ?></th>
											<th><?php echo $this->lang->line('VAT_AMOUNT'); ?></th>
										</tr>
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
									</table>
							</div>
						</div>
					</div>
					<?php if ($total_already_payed > 0): ?>
	                <div class="row px-5 py-2">
                        <div class="col-md-6"><?php echo $this->lang->line('payed'); ?></div>
                        <div class="col-md-6 text-right"><strong><?php echo number_format($total_already_payed, 2); ?> &euro;</strong></div>
					</div>
					<?php endif; ?> 

					<div class="d-flex justify-content-between bg-dark text-white <?php echo ($bill['status'] == BILL_PAID) ? "p-2" : "p-4"; ?>">
						<div>&nbsp;</div>
                        <div class="px-5 text-right">
                            <div class="mb-2"><?php echo ($bill['status'] == BILL_PAID) ? '<i>' . $this->lang->line('payed') . '</i>' : $this->lang->line('to_pay'); ?></div>
                            <div class="h2 font-weight-light"><?php echo ($bill['status'] != BILL_PAID) ? number_format($bill['total_brut'] - $total_already_payed, 2) : $bill['total_brut']; ?> &euro;</div>
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

<script>
	const URL_STORE_MESSAGE_API = '<?php echo base_url('invoice/store_bill_msg/' . $bill['id']); ?>';
	const URL_SENDMAIL_API = '<?php echo base_url('invoice/get_bill/' . $bill['id'] . '/2'); ?>';
	const URL_STORE_MAIL = '<?php echo base_url('owners/set_email/'. $bill['owner_id']); ?>';	
</script>

<script src="<?php echo base_url('assets/js/invoice.js'); ?>"></script>