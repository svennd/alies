<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">


    <link href="<?php echo base_url(); ?>assets/css/sb-admin-2.min.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>assets/css/dataTables.bootstrap4.min.css" rel="stylesheet"> <!-- datatables -->
	<link href="<?php echo base_url(); ?>assets/css/all.min.css" rel="stylesheet"> <!-- font awesome -->
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
		
</head>
<body id="page-top">

<div class="row">
	<div class="col-lg-12 mb-4">
	<div class="card mb-4">
		<div class="card-header py-3">
			<h6 class="m-0 font-weight-bold text-primary">Invoice</h6>
		</div>
		<div class="card-body">
			<div class="card-body p-0">				
			<h4>Bill #<?php echo $bill_id; ?></h4>
                    <div class="row pb-5 p-5">
                        <div class="col-md-6">
							<p class="font-weight-bold mb-2">Client</p>
							<address>
							<strong><?php echo $owner['last_name'] . " &nbsp;" . $owner['first_name']; ?></strong><br>
								<?php echo $owner['street'] . ' ' . $owner['nr']; ?><br>
								<?php echo $owner['city'] . ' ' . $owner['zip']; ?><br>
								<br>
								<?php if ($owner['telephone']) : ?><abbr title="Phone">P:</abbr> <?php echo $owner['telephone']; ?><br/><?php endif; ?>
								<?php if ($owner['mobile']) : ?><abbr title="Mobile">M:</abbr> <?php echo $owner['mobile']; ?><br/><?php endif; ?>
								<?php if ($owner['mail']) : ?><a href="mailto:<?php echo $owner['mail']; ?>"><?php echo $owner['mail']; ?></a><br/><?php endif; ?>
								<?php if ($owner['btw_nr']) : ?>BTW : <?php echo $owner['btw_nr']; ?><br/><?php endif; ?>
								<?php if ($owner['invoice_addr']) : ?>
									<p class="font-weight-bold mb-2">Invoice</p>
									<?php if ($owner['invoice_contact']) : ?><b><?php echo $owner['invoice_contact']; ?></b><br/><?php endif; ?>
									<?php if ($owner['invoice_addr']) : ?><?php echo $owner['invoice_addr']; ?><br/><?php endif; ?>
									<?php if ($owner['invoice_tel']) : ?><abbr title="Phone">P:</abbr> <?php echo $owner['invoice_tel']; ?><br/><?php endif; ?>

								<?php endif; ?>
							</address>
                        </div>

                        <div class="col-md-6 text-right">
                            <p class="font-weight-bold mb-4">Payment Details</p>
                            <p class="mb-1"><span class="text-muted">VAT: </span> 1425782</p>
                            <p class="mb-1"><span class="text-muted">VAT ID: </span> 10253642</p>
                            <p class="mb-1"><span class="text-muted">Payment Type: </span> Root</p>
                            <p class="mb-1"><span class="text-muted">Name: </span> John Doe</p>
                        </div>
                    </div>
	                <div class="row px-5 py-3">
                        <div class="col-md-12">
							<?php 
							$full_total = 0.0;
							foreach ($print_bill as $pet_id => $event): 
								# resolve name, chip
								$pet_info = $pets[$pet_id];
								
								foreach ($event as $event_id => $vbill):
									list($event_id, $event_location, $payment_bill, $created_at, $updated_at) = array_values($event_info[$pet_id][$event_id]);
									list($prod, $proc, $total) = array_values($vbill);
									$full_total += $total;
									# skip if no products or services
									if (count($prod) + count($proc) == 0) continue;
							?>
							<div class="row">
								<div class="col-md-6">
									<h4><?php echo ucfirst(strtolower($pet_info['name'])); ?></h4>
									<small><i><?php echo $pet_info['chip']; ?></i></small>
									<small>
									<a href="<?php echo base_url(); ?>events/event/<?php echo $event_id; ?>">consult</a>, <?php echo $location_i[$event_location]['name']; ?><br/>
									created_at : <?php echo $created_at; ?><br/>
									updated_at : <?php echo $updated_at; ?><br/>
									</small>
								</div>

								<div class="col-md-6 text-right">
								<small><i><?php echo $bill['created_at']; ?></i></small>
								</div>
							</div>
								<hr>
								<table class="table">
									<thead>
										<tr>
											<th class="border-0 text-uppercase small font-weight-bold">Item</th>
											<th class="border-0 text-uppercase small font-weight-bold">Quantity</th>
											<th class="border-0 text-uppercase small font-weight-bold">BTW</th>
											<th class="border-0 text-uppercase small font-weight-bold">Total</th>
										</tr>
									</thead>
									<tbody>
									<?php foreach ($prod as $product): ?>
										<tr>
											<td><?php echo $product['name']; ?></td>
											<td><?php echo $product['volume']; ?> <?php echo $product['unit_sell']; ?></td>
											<td><?php echo $product['btw']; ?> %</td>
											<td><?php echo $product['price']; ?> &euro;</td>
										</tr>
									<?php endforeach; ?>
									<?php foreach ($proc as $procedure): ?>
										<tr>
											<td><?php echo $procedure['name']; ?></td>
											<td><?php echo $procedure['amount']; ?></td>
											<td><?php echo $procedure['btw']; ?> %</td>
											<td><?php echo $procedure['price']; ?> &euro;</td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
								
						<?php endforeach; ?>
						<?php endforeach; ?>
                        </div>
                    </div>

                    <div class="d-flex flex-row-reverse bg-dark text-white p-4">
                        <div class="py-3 px-5 text-right">
                            <div class="mb-2">Total</div>
                            <div class="h2 font-weight-light"><?php echo $full_total; ?> &euro;</div>
                        </div>

<!--
                        <div class="py-3 px-5 text-right">
                            <div class="mb-2">Discount</div>
                            <div class="h2 font-weight-light">10%</div>
                        </div>
                        <div class="py-3 px-5 text-right">
                            <div class="mb-2">Sub - Total amount</div>
                            <div class="h2 font-weight-light">$32,432</div>
                        </div>
-->
                    </div>
			</div>

				
		</div>
	</div>
	</div>
</div>
	</body>

</html>