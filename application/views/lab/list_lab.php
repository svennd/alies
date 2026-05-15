<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div>
					<a href="<?php echo base_url('pets/fiche/' . $pet_id); ?>">
						<?php echo html_escape($pet_info['name']); ?>
					</a>
					/ lab
				</div>
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url('pets/fiche/' . $pet_id); ?>" class="btn btn-outline-primary btn-sm">
						<i class="fas fa-arrow-left"></i> pet
					</a>
				</div>
			</div>
			<div class="card-body">
				<?php if ($lab_results): ?>
					<table class="table table-sm" id="dataTable">
						<thead>
							<tr>
								<th><?php echo $this->lang->line('lab_id'); ?></th>
								<th><?php echo $this->lang->line('lab_received'); ?></th>
								<th><?php echo $this->lang->line('source'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($lab_results as $lab): ?>
								<tr>
									<td>
										<a href="<?php echo base_url('lab/detail/' . $lab['id']); ?>" class="btn btn-sm btn-outline-primary">
											<?php echo $lab['id']; ?>
										</a>
									</td>
									<td data-sort="<?php echo strtotime($lab['sample_date']); ?>">
										<?php echo user_format_date($lab['sample_date'], $user->user_date); ?>
										<br/>
										<small><?php echo time_ago($lab['sample_date']); ?></small>
									</td>
									<td><?php echo html_escape($lab['device']); ?></td>
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
	if ($("#dataTable").length) {
		$("#dataTable").DataTable({responsive: true, "order": [[0, "desc" ]]});
	}
	$("#labo").addClass('active');
});
</script>
