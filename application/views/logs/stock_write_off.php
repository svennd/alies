<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>logs">Logs</a> / writs off logs
			</div>
            <div class="card-body">
			<?php if ($logs): ?>
			
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Product</th>
					<th>Volume</th>
					<th>Reason</th>
					<th>Vet</th>
					<th>Location</th>
					<th>Date</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($logs as $log): ?>
				<tr>
					<td><?php echo $log['products']['name']; ?></td>
					<td><?php echo $log['volume']; ?> <?php echo $log['products']['unit_sell']; ?></td>
					<td><?php echo $log['reason']; ?></td>
					<td><?php echo $log['vet']['first_name']; ?></td>
					<td><?php echo $log['location']['name']; ?></td>
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
	$("#dataTable").DataTable({
		dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
		buttons: [
            { extend:'excel', text:'<i class="fas fa-file-export"></i> Excel', className:'btn btn-outline-success btn-sm'},
            { extend:'pdf', text:'<i class="far fa-file-pdf"></i> PDF', className:'btn btn-outline-success btn-sm'}
        ],
		"order": [[ 5, "desc" ]]
	});
});
</script>