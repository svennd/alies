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
		<tr>
		  <td><a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $bill['id']; ?>"><?php echo get_bill_id($bill['id'], $bill['created_at']); ?></a></td>
		  <td><?php echo user_format_date($bill['created_at'], $user->user_date); ?></td>
		  <td><?php echo get_bill_status($bill['status']); ?></td>
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