
<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><?php echo $this->lang->line('Lab'); ?></div>
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url('cron/medilab/sync'); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-sync"></i> Sync</a>
				</div>
			</div>
			<div class="card-body">
				<?php if($data): ?>
				<table class="table" id="dataTable">
					<thead>
						<tr>
							<th><?php echo $this->lang->line('lab_id'); ?></th>
							<th><?php echo $this->lang->line('lab_comment'); ?></th>
							<th><?php echo $this->lang->line('lab_received'); ?></th>
							<th><?php echo $this->lang->line('lab_update'); ?></th>
							<th><?php echo $this->lang->line('source'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($data as $d): ?>
						<tr>
							<td><a href="<?php echo base_url('lab/detail/'. $d["id"]); ?>"><?php echo $d["lab_id"]; ?></a></td>
							<td><?php echo $d["lab_comment"]; ?></td>
							<td><?php echo $d["lab_date"]; ?></td>
							<td>
								<?php echo $d["lab_updated_at"]; ?><br/>
								<small><?php echo time_ago($d["lab_updated_at"]);?></small>
							</td>
							<td><?php echo $d["source"]; ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php else: ?>
					no lab results found!
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({responsive: true, "order": [[ 0, "desc" ]]});
	$("#labo").addClass('active');
});
</script>