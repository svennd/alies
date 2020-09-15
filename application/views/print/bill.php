<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Alies - Dashboard</title>

    <link href="<?php echo base_url(); ?>assets/css/sb-admin-2.min.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>assets/css/dataTables.bootstrap4.min.css" rel="stylesheet"> <!-- datatables -->
	<link href="<?php echo base_url(); ?>assets/css/all.min.css" rel="stylesheet"> <!-- font awesome -->
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
	
	<?php echo (isset($extra_header)) ? $extra_header : ""; ?>
</head>
<body id="page-top">
<div class="card-body p-0">
	<div class="row p-5">
		<div class="col-md-6">
			<img src="http://via.placeholder.com/400x90?text=logo">
		</div>

		<div class="col-md-6 text-right">
			<p class="font-weight-bold mb-1">Factuur #550</p>
			<p class="text-muted">Due to: 4 Dec, 2019</p>
		</div>
	</div>

	<hr class="my-5">
	<div class="row pb-5 p-5">
		<div class="col-md-6">
			<address>
			<strong><?php echo $owner['last_name'] . " &nbsp;" . $owner['first_name']; ?></strong><br>
				<?php echo $owner['street'] . ' ' . $owner['nr']; ?><br>
				<?php echo $owner['city'] . ' ' . $owner['zip']; ?><br><br>
				<?php if ($owner['telephone']) : ?><abbr title="Phone">P:</abbr> <?php echo $owner['telephone']; ?><br/><?php endif; ?>
				<?php if ($owner['mobile']) : ?><abbr title="Mobile">M:</abbr> <?php echo $owner['mobile']; ?><br/><?php endif; ?>
				<?php if ($owner['mail']) : ?><a href="mailto:<?php echo $owner['mail']; ?>"><?php echo $owner['mail']; ?></a><br/><?php endif; ?>
				<?php if ($owner['btw_nr']) : ?><br/><?php echo $owner['btw_nr']; ?><br/><?php endif; ?>
				<?php if ($owner['invoice_addr']) : ?>
					<hr>
					<?php if ($owner['invoice_contact']) : ?><b><?php echo $owner['invoice_contact']; ?></b><br/><?php endif; ?>
					<?php if ($owner['invoice_addr']) : ?><?php echo $owner['invoice_addr']; ?><br/><?php endif; ?>
					<?php if ($owner['invoice_tel']) : ?><abbr title="Phone">P:</abbr> <?php echo $owner['invoice_tel']; ?><br/><?php endif; ?>
				<?php endif; ?>
			</address>
		</div>

		<div class="col-md-6 text-right">
			<!--
			<p class="font-weight-bold mb-4">Payment Details</p>
			<p class="mb-1"><span class="text-muted">VAT: </span> 1425782</p>
			<p class="mb-1"><span class="text-muted">VAT ID: </span> 10253642</p>
			<p class="mb-1"><span class="text-muted">Payment Type: </span> Root</p>
			<p class="mb-1"><span class="text-muted">Name: </span> John Doe</p>
			-->
		</div>
	</div>

	<div class="row p-5">
		<div class="col-md-12">
			<?php 
			$total = 0.0;
			foreach($bill_lines as $pet_id => $bill): 
				// var_dump($pet_id);
				// var_dump($pets);
				list($prod, $proc) = $bill;
				$pet_info = $pets[$pet_id];
			?>
			<div class="row">
				<div class="col-md-6">
					<h4><?php echo ucfirst(strtolower($pet_info['name'])); ?></h4>
					<small><i><?php echo $pet_info['chip']; ?></i></small>
				</div>

				<div class="col-md-6 text-right">
				<small><i><?php //echo $bill['created_at']; ?></i></small>
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
							<td><?php echo $product['price']; $total += $product['price']; ?> &euro;</td>
						</tr>
					<?php endforeach; ?>
					<?php foreach ($proc as $procedure): ?>
						<tr>
							<td><?php echo $procedure['name']; ?></td>
							<td><?php echo $procedure['amount']; ?></td>
							<td><?php echo $procedure['btw']; ?> %</td>
							<td><?php echo $procedure['price']; $total += $product['price']; ?> &euro;</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				
		<?php endforeach; ?>
		</div>
	</div>

	<div class="d-flex flex-row-reverse bg-dark text-white p-4">
		<div class="py-3 px-5 text-right">
			<div class="mb-2">Total</div>
			<div class="h2 font-weight-light"><?php echo $total; ?> &euro;</div>
		</div>

	</div>
</div>



	<!-- Bootstrap core JavaScript-->
	<script src="<?php echo base_url(); ?>assets/js/jQuery.3.4.1.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/bootstrap.bundle.min.js"></script>

	<!-- Custom scripts for all pages-->
	<script src="<?php echo base_url(); ?>assets/js/sb-admin-2.js"></script>
	
	<!-- datatables -->
	<script src="<?php echo base_url(); ?>assets/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/dataTables.bootstrap4.min.js"></script>
	
	<!-- bootstrap suggest -->
	<script src="<?php echo base_url(); ?>assets/js/bootstrap-suggest.js"></script>
	
	<?php echo (isset($extra_footer)) ? $extra_footer : ""; ?>
</body>

</html>
