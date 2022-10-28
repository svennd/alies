<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>logs">Logs</a> / deliveries
			</div>
            <div class="card-body">
			<?php if ($logs): ?>

				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Id</th>
					<th>Note</th>
					<th>regdate</th>
					<th>Vet</th>
					<th>Date</th>
					<th>Products</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($logs as $log): ?>
				<tr>
					<td><a href="<?php echo base_url('logs/delivery/' . $log['id']); ?>"><?php echo $log['id']; ?></a></td>
					<td><?php echo $log['note']; ?></td>
					<td><?php echo user_format_date($log['regdate'], $user->user_date); ?></td>
					<td><?php echo (isset($log['vet']['first_name'])) ? $log['vet']['first_name'] : '?'; ?></td>
					<td><?php echo user_format_date($log['created_at'], $user->user_date); ?></td>
					<td><?php foreach ($log['products'] as $prod): echo $prod['name'] . ", "; endforeach; ?></td>
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
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]],
	    "order": [[ 0, "desc" ]]});
});
</script>
