<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>logs">Logs</a> / Global
			</div>
            <div class="card-body">
				<form action="<?php echo base_url('logs/nlog') ?>" method="post" autocomplete="off" class="form-inline">

				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">search_from</label>
					<input type="date" name="search_from" class="form-control" value="<?php echo $search_from; ?>" id="search_from">
				</div>
				  <div class="form-group mb-2">
					<span class="fa-stack" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-arrow-right fa-stack-1x"></i>
					</span>
				  </div>
				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">search_to</label>
					<input type="date" name="search_to" class="form-control" value="<?php echo $search_to; ?>" max="<?php echo date_format(new DateTime(), 'Y-m-d'); ?>" id="search_to">
				  </div>
				  <button type="submit" class="btn btn-success mb-2"><?php echo $this->lang->line('search_range'); ?></button>
				</form>
			<?php if ($logs): ?>

				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Date</th>
					<th>Event</th>
					<th>Message</th>
					<th>Level</th>
					<th>Vet</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($logs as $log): ?>
				<tr>
					<td data-sort="<?php echo strtotime($log['created_at']); ?>"><?php echo user_format_date($log['created_at'], $user->user_date); ?></td>
					<td><?php echo $log['event']; ?></td>
					<td><?php echo $log['msg']; ?></td>
					<td><?php echo get_error_level($log['level']); ?></td>
					<td><?php echo (isset($log['vet']['first_name'])) ? $log['vet']['first_name'] : ''; ?></td>
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
	$("#dataTable").DataTable({"order": [[ 0, "desc" ]]});
});
</script>
