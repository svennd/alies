<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>owners/search">Client</a> / <a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / Invoices
		<small>(#<?php echo $owner['id']; ?>)</small>
	</div>
	<div class="card-body">
	<?php if($bills): ?>
	<table class="table">
	  <thead>
		<tr>
		  <th>Factuurnr</th>
		  <th>Date</th>
		  <th>Status</th>
		  <th>Amount</th>
		  <th>Card</th>
		  <th>Cash</th>
		  <th><i class="fas fa-user-md"></i> Vet</th>
		  <th>Location</th>
		</tr>
	  </thead>
	  <tbody>
		<?php foreach($bills as $bill): ?>
		<?php 
			$date = date_create_from_format ('Y-m-d H:i:s', $bill['created_at']); 
			switch ($bill['status'])
			{
				case PAYMENT_PAID:
					$state = "Paid";
				break;
				case PAYMENT_PARTIALLY:
					$state = "Paid Partially";
				break;
				case PAYMENT_UNPAID:
					$state = "Unpaid";
				break;
				case PAYMENT_OPEN:
					$state = "Open";
				break;
				case PAYMENT_NON_COLLECTABLE:
					$state = "PAYMENT_NON_COLLECTABLE";
				break;
			}
		 ?>
		<tr>
		  <td><a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $bill['id']; ?>">
				<?php echo date_format($date, 'Y') . str_pad($bill['id'], 5, '0', STR_PAD_LEFT); ?>
				</a>
			</td>
		  <td><?php echo date_format($date, 'd M \'y'); ?></td>
		  <td><?php echo $state; ?></td>
		  <td><?php echo $bill['amount']; ?></td>
		  <td><?php echo $bill['card']; ?></td>
		  <td><?php echo $bill['cash']; ?></td>
		  <td><?php echo $bill['vet']['first_name']; ?></td>
		  <td><?php echo $bill['location']['name']; ?></td>
		</tr>
		<?php endforeach; ?>
	  </tbody>
	</table>
	<?php else: ?>
		No invoices
	<?php endif; ?>
	</div>
</div>