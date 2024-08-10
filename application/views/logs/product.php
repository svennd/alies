<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url('accounting/dashboard/'); ?>"><?php echo $this->lang->line('admin'); ?></a> / 
				<a href="<?php echo base_url('products/profile/' . $logs['0']['product']['id']); ?>"><?php echo $logs['0']['product']['name']; ?></a> / 
				detail transaction log
			</div>
            <div class="card-body">
			<?php if ($logs): ?>
			
				<table class="table table-sm" id="dataTable">
				<thead>
				<tr>
					<th>Event</th>
					<th><?php echo $this->lang->line('volume'); ?></th>
					<th><?php echo $this->lang->line('vet'); ?></th>
					<th><?php echo $this->lang->line('location'); ?></th>
					<th><?php echo $this->lang->line('date'); ?></th>
				</tr>
				</thead>
					<tbody>
					<?php foreach ($logs as $log): ?>
					<tr>
						<td><?php echo $log['event']; ?></td>
						<td><?php echo ($log['volume'] >= 0) ? 
							'<span style="color:green">'  . $log['volume'] .  ' ' . $log['product']['unit_sell'] . '</span>' 
							: 
							'<span style="color:red">' . $log['volume'] .  ' ' . $log['product']['unit_sell'] . '</span>'; ?></td>
						<td><?php echo $log['vet']['first_name']; ?></td>
						<td><?php echo (isset($log['locations'])) ? $log['locations']['name'] : 'invalid'; ?></td>
						<td data-sort="<?php echo strtotime($log['created_at']); ?>"><?php echo $log['created_at']; ?></td>
					</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else: ?>
				No products in this view.
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
	$("#dataTable").DataTable({"order": [[ 4, "desc" ]]});
});
</script>