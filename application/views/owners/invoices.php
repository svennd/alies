<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>owners/search"><?php echo $this->lang->line('client'); ?></a> / <a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / <?php echo $this->lang->line('invoices'); ?>
		<small>(#<?php echo $owner['id']; ?>)</small>
	</div>
	<div class="card-body">
	<?php if($bills): ?>
	<table class="table" id="dataTable">
	  <thead>
		<tr>
		  <th><?php echo $this->lang->line('Invoice'); ?></th>
		  <th><?php echo $this->lang->line('date'); ?></th>
		  <th><?php echo $this->lang->line('state'); ?></th>
		  <th><?php echo $this->lang->line('amount'); ?></th>
		  <th><?php echo $this->lang->line('card'); ?></th>
		  <th><?php echo $this->lang->line('cash'); ?></th>
		  <th><i class="fas fa-user-md"></i> <?php echo $this->lang->line('vet'); ?></th>
		  <th><?php echo $this->lang->line('location'); ?></th>
		</tr>
	  </thead>
	  <tbody>
		<?php foreach($bills as $bill): ?>
		<tr>
		  <td><a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $bill['id']; ?>">#<?php echo get_bill_id($bill['id'], $bill['created_at']); ?></a></td>
		  <td data-sort="<?php echo strtotime($bill['created_at']) ?>"><?php echo user_format_date($bill['created_at'], $user->user_date); ?></td>
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
		<?php echo $this->lang->line('no_invoices'); ?>
	<?php endif; ?>
	</div>
</div>



<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable();
	$("#clients").addClass('active');
});
</script>