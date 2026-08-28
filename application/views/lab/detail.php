<style>
.bar {
    width: 150px;
    height: 6px;
    position: relative;
    display: inline-flex;
    gap: 3px;
    margin-left: 8px;
    margin-bottom: 3px;
}

.seg {
    height: 6px;
    flex-grow: 1;
    border-radius: 3px;
}

.seg.low {
    background: #f8d3d3;
}

.seg.mid {
    background: #e2f5dc;
}

.seg.high {
    background: #f8d3d3;
}

.pos {
    position: absolute;
    top: -2px;
    width: 2px;
    height: 10px;
    background: #333;
    border-radius: 2px;
}
</style>

<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url('lab'); ?>"><?php echo $this->lang->line('Lab'); ?></a> / Lab results</div>
				
				<div class="dropdown no-arrow">
					<?php if ($lab_info['device'] == "medilab"): ?>
						<a href="https://online.medilab.be/dokter/staal/<?php echo $lab_info['source_id']; ?>" class="btn btn-outline-primary btn-sm" target="blank"><i class="fas fa-external-link-alt"></i> <?php echo $lab_info['source'] . ' ('. $lab_info['source_id'] . ')';  ?></a>
					<?php else: ?>
						<a href="<?php echo base_url('lab/print/' . $lab_id); ?>" class="btn btn-outline-success btn-sm" target="blank"><i class="fa-solid fa-print"></i> print</a>
					<?php endif; ?>
					<?php if($this->ion_auth->is_admin()): ?>
						<a href="<?php echo base_url('lab/delete/'. $lab_id); ?>" class="btn btn-outline-danger btn-sm ml-4"><i class="fa-solid fa-trash"></i></a>
					<?php endif; ?>
				</div>
			</div>
			<div class="card-body">
				<?php if (!empty($lab_message)): ?>
					<div class="alert alert-<?php echo html_escape($lab_message_type ?: 'info'); ?>"><?php echo html_escape($lab_message); ?></div>
				<?php endif; ?>
				<table class="table table-sm">
					<tr>
						<td><?php echo $this->lang->line('pet_info'); ?></td>
						<td>
							<?php if ($lab_info['pet']): ?>
								<a href="<?php echo base_url('pets/fiche/' . $lab_info['pet']['id']); ?>"><?php echo $lab_info['pet']['name']; ?></a>
							<?php else: ?>
								-
							<?php endif; ?>
							<?php if (!isset($can_manage_lab_assignment) || $can_manage_lab_assignment): ?>
								<a class="btn btn-outline-warning btn-sm py-0 px-1 ml-2" data-toggle="collapse" href="#reassignCard" role="button" aria-expanded="false" aria-controls="reassignCard" title="<?php echo $this->lang->line('lab_reassign_title'); ?>"><i class="fas fa-exchange-alt"></i></a>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('client'); ?></td>
						<td>
							<?php if($owner): ?>
								<a href="<?php echo base_url('owners/detail/' . $owner['id']); ?>"><?php echo $owner['last_name']; ?></a>
							<?php else: ?>
								-
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('last_update'); ?></td>
						<td><?php echo $lab_info['sample_date']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('source'); ?></td>
						<td><?php echo $lab_info['source_id']; ?></td>
					</tr>
				</table>

				<?php if (!isset($can_manage_lab_assignment) || $can_manage_lab_assignment): ?>
				<div class="collapse mb-4" id="reassignCard">
					<div class="card border-warning">
						<div class="card-header py-2"><?php echo $this->lang->line('lab_reassign_title'); ?></div>
						<div class="card-body">
							<p class="small text-muted"><?php echo $this->lang->line('lab_reassign_warning'); ?></p>
							<form action="<?php echo base_url('lab/reassign/' . (int) $lab_id); ?>" method="post" id="labReassignForm" onsubmit="return confirm(<?php echo htmlspecialchars(json_encode($this->lang->line('lab_reassign_confirm')), ENT_QUOTES, 'UTF-8'); ?>);">
								<div class="form-row">
									<div class="form-group col-md-6">
										<label for="lab_reassign_owner_id"><?php echo $this->lang->line('client'); ?></label>
										<select id="lab_reassign_owner_id" name="owner_id" class="form-control" required style="width: 100%;">
											<?php if ($owner): ?><option value="<?php echo (int) $owner['id']; ?>" selected><?php echo html_escape(trim(($owner['first_name'] ?? '') . ' ' . ($owner['last_name'] ?? '')) . ' (#' . (int) $owner['id'] . ')'); ?></option><?php endif; ?>
										</select>
									</div>
									<div class="form-group col-md-6">
										<label for="lab_reassign_pet_id"><?php echo $this->lang->line('pet_info'); ?></label>
										<select id="lab_reassign_pet_id" name="pet_id" class="form-control" <?php echo $owner ? '' : 'disabled'; ?> required style="width: 100%;">
											<?php if ($pet_info): ?><option value="<?php echo (int) $pet_info['id']; ?>" selected><?php echo html_escape($pet_info['name'] . ' (#' . (int) $pet_info['id'] . ')'); ?></option><?php endif; ?>
										</select>
									</div>
								</div>
								<button type="submit" class="btn btn-warning btn-sm"><?php echo $this->lang->line('lab_reassign_submit'); ?></button>
							</form>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<table class="table table-sm">
					<thead>
						<tr>
							<th><?= $this->lang->line('lab_code'); ?></th>
							<th>chart</th>
							<th><?= $this->lang->line('value'); ?></th>
							<th class="text-center"><?= $this->lang->line('limit'); ?></th>
							<th><?= $this->lang->line('unit'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($lab_details as $d): ?>
						<tr>
							<td><?= htmlspecialchars($d['code']); ?></td>

							<td width="30%">
								<?php if ($d['draw_plot']): ?>
									<div class="bar">
										<div class="seg low"></div><div class="seg mid"></div><div class="seg high"></div>
										<div class="pos" style="left: <?= $d['pct']; ?>%;"></div>
									</div>
								<?php endif; ?>
							</td>

							<?php if ($d['is_text']): ?>
								<td colspan="3"><?= htmlspecialchars($d['value']); ?></td>
							<?php else: ?>
								<td class="<?= $d['is_out'] ? 'text-danger font-weight-bold' : ''; ?>">
									<?= htmlspecialchars($d['value']); ?>
								</td>
								<td class="text-center"><?= htmlspecialchars($d['limit']); ?></td>
								<td><?= htmlspecialchars($d['unit']); ?></td>
							<?php endif; ?>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php if ($lab_info['device'] == "ms4s2"): ?>
				<?php include "plots/ms4s2.php" ?>
				<?php endif; ?>
				<br/>
				<a href="#" id="showMore" class="btn btn-sm btn-link p-0">Show details</a>

				<div id="moreContent" class="d-none mt-2">
					<table class="table table-sm">
						<tr>
							<td>software_version</td>
							<td><?php echo $lab_info['software_version']; ?></td>
						</tr>
						<?php
						$meta = json_decode($lab_info['metadata'], true);

						if (is_array($meta)) {
							foreach ($meta as $key => $value) {

								echo '<tr>';
								echo '<td>' . htmlspecialchars($key) . '</td>';
								echo '<td>';

								if (is_array($value)) {
									echo htmlspecialchars(implode(', ', $value));
								} else {
									echo htmlspecialchars($value);
								}

								echo '</td>';
								echo '</tr>';
							}
						}
						?>
						<tr>
							<td>source_id</td>
							<td><?php echo $lab_info['source_id']; ?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#labo").addClass('active');

	$('#showMore').on('click', function(e){
		e.preventDefault();
		$('#moreContent').toggleClass('d-none');
		$(this).text(
			$('#moreContent').hasClass('d-none') ? 'Show details' : 'Show less details'
		);
	});

	const reassignOwner = $('#lab_reassign_owner_id');
	const reassignPet = $('#lab_reassign_pet_id');
	if (reassignOwner.length) {
		reassignOwner.select2({theme: 'bootstrap4', width: '100%', placeholder: <?php echo json_encode($this->lang->line('lab_pending_select_owner')); ?>, minimumInputLength: 1, ajax: {url: <?php echo json_encode(base_url('lab/search_owners')); ?>, dataType: 'json'}});
		reassignPet.select2({theme: 'bootstrap4', width: '100%', placeholder: <?php echo json_encode($this->lang->line('lab_pending_select_pet')); ?>, ajax: {url: <?php echo json_encode(base_url('lab/search_pets')); ?>, dataType: 'json', data: function(params){ return {term: params.term || '', owner_id: reassignOwner.val()}; }}});
		reassignOwner.on('change', function(){ reassignPet.val(null).trigger('change'); reassignPet.prop('disabled', !reassignOwner.val()); });
	}
});
</script>
