<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url('accounting/dashboard/'); ?>"><?php echo $this->lang->line('admin'); ?></a> / 
				<a href="<?php echo base_url('logs/delivery'); ?>"><?php echo $this->lang->line('delivery_log'); ?></a> / details
			</div>
            <div class="card-body">
				<table class="table table-sm">
					<tr>
						<td><?php echo $this->lang->line('vet'); ?></td>
						<td><?php echo $delivery['vet']['first_name']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('message'); ?></td>
						<td><?php echo $delivery['note']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('delivery_date'); ?></td>
						<td><?php echo user_format_date($delivery['regdate'], $user->user_date); ?></td>
					</tr>
					<tr>
						<td>entry date</td>
						<td><?php echo user_format_date($delivery['created_at'], $user->user_date); ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('location'); ?></td>
						<td><?php echo $delivery['location']['name']; ?></td>
					</tr>
				</table>
			<?php if ($products): ?>

				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('product'); ?></th>
					<th><?php echo $this->lang->line('volume'); ?></th>
					<th><?php echo $this->lang->line('eol'); ?></th>
					<th><?php echo $this->lang->line('price_dayprice'); ?></th>
					<th><?php echo $this->lang->line('lotnr'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($products as $product): ?>
				<tr>
					<td><a href="<?php echo base_url('products/profile/' . $product['product']['id']); ?>"><?php echo $product['product']['name']; ?></a></td>
					<td><?php echo $product['volume']; ?></td>
					<td><?php echo user_format_date($product['eol'], $user->user_date); ?></td>
					<td><?php echo $product['in_price']; ?></td>
					<td><?php echo $product['lotnr']; ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No products found :s
			<?php endif; ?>
			</div>
		</div>

	</div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#logs").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]],
	    "order": [[ 0, "desc" ]]});
});
</script>
