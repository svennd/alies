<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>logs">Logs</a> / Global
			</div>
            <div class="card-body">
			<?php if ($logs): ?>
			
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Event</th>
					<th>Message</th>
					<th>Level</th>
					<th>Vet</th>
					<th>Date</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($logs as $log): ?>
				<tr>
					<td><?php echo $log['event']; ?></td>
					<td><?php echo $log['msg']; ?></td>
					<td><?php echo $log['level']; ?></td>
					<td><?php echo (isset($log['vet']['first_name'])) ? $log['vet']['first_name'] : ''; ?></td>
					<td><?php echo $log['created_at']; ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No log messages
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
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
});
</script>