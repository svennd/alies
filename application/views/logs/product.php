<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>logs">Logs</a> / detail transaction product log
			</div>
            <div class="card-body">
			<?php if ($logs): ?>
			
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Event</th>
					<th>product</th>
					<th>Volume</th>
					<th>Vet</th>
					<th>Location</th>
					<th>Date</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($logs as $log): ?>
				<tr>
					<td><?php echo $log['event']; ?></td>
					<td><?php echo $log['product']['name']; ?></td>
					<td><?php echo $log['volume']; ?> <?php echo $log['product']['unit_sell']; ?></td>
					<td><?php echo $log['vet']['first_name']; ?></td>
					<td><?php echo $log['locations']['name']; ?></td>
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
	$("#dataTable").DataTable({"order": [[ 5, "desc" ]]});
});
</script>