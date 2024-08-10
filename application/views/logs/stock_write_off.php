<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url('accounting/dashboard/'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('write_off_log'); ?>
			</div>
            <div class="card-body">
			<?php if ($logs): ?>
			
				<table class="table table-sm" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('product'); ?></th>
					<th><?php echo $this->lang->line('eol'); ?></th>
					<th><?php echo $this->lang->line('lotnr'); ?></th>
					<th><?php echo $this->lang->line('volume'); ?></th>
					<th><?php echo $this->lang->line('reason'); ?></th>
					<th><?php echo $this->lang->line('vet'); ?></th>
					<th><?php echo $this->lang->line('location'); ?></th>
					<th>Write off date</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($logs as $log): ?>
				<tr>
					<td><?php echo (isset($log['products']['name'])) ? $log['products']['name'] : $log['product_name']; ?></td>
					<td><?php echo user_format_date($log['eol'], $user->user_date); ?></td>
					<td><?php echo $log['lotnr']; ?></td>
					<td><?php echo $log['volume']; ?> <?php echo (isset($log['products']['unit_sell'])) ? $log['products']['unit_sell'] : ''; ?></td>
					<td><?php echo $log['reason']; ?></td>
					<td><?php echo (!isset($log['vet'])) ? "" : $log['vet']['first_name']; ?></td>
					<td><?php echo $log['location']['name']; ?></td>
					<td data-sort="<?php echo strtotime($log['created_at']); ?>"><?php echo user_format_date($log['created_at'], $user->user_date); ?></td>
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
	$("#dataTable").DataTable({
		dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
		buttons: [
            { extend:'excel', text:'<i class="fas fa-file-export"></i> Excel', className:'btn btn-outline-success btn-sm'},
            { extend:'pdf', text:'<i class="far fa-file-pdf"></i> PDF', className:'btn btn-outline-success btn-sm'}
        ],
		"order": [[ 7, "desc" ]]
	});
});
</script>