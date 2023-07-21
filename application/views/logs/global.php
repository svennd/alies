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
					<th>Id</th>
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
					<td><?php echo $log['id']; ?></td>
					<td><?php echo $log['event']; ?></td>
					<td><?php echo $log['msg']; ?></td>
					<td><?php echo get_error_level($log['level']); ?></td>
					<td><?php echo (isset($log['vet']['first_name'])) ? $log['vet']['first_name'] : ''; ?></td>
					<td data-sort="<?php echo strtotime($log['created_at']); ?>"><?php echo user_format_date($log['created_at'], $user->user_date); ?></td>
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
	$("#dataTable").DataTable({"order": [[ 5, "desc" ]]});
});
</script>
