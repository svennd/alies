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
					<?php if ($lab_info['source'] == "medilab"): ?>
						<a href="https://online.medilab.be/dokter/staal/<?php echo $lab_info['lab_id']; ?>" class="btn btn-outline-primary btn-sm" target="blank"><i class="fas fa-external-link-alt"></i> <?php echo $lab_info['source'] . ' ('. $lab_info['lab_id'] . ')';  ?></a>
					<?php else: ?>
						<a href="<?php echo base_url('lab/print/' . $lab_info['id']); ?>" class="btn btn-outline-success btn-sm" target="blank"><i class="fa-solid fa-print"></i> print</a>
					<?php endif; ?>
					<?php if($this->ion_auth->is_admin()): ?>
						<a href="<?php echo base_url('lab/delete/'. $lab_info['id']); ?>" class="btn btn-outline-danger btn-sm ml-4"><i class="fa-solid fa-trash"></i></a>
					<?php endif; ?>
				</div>
			</div>
			<div class="card-body">
				<table class="table table-sm">
					<tr>
						<td><?php echo $this->lang->line('pet_info'); ?></td>
						<td>
							<?php if ($lab_info['pet']): ?>
								<a href="<?php echo base_url('pets/fiche/' . $lab_info['pet']['id']); ?>"><?php echo $lab_info['pet']['name']; ?></a>
								<a href="<?php echo base_url('lab/reset_lab_link/' . $lab_info['id']); ?>" class="btn btn-sm btn-outline-danger spinit ml-4"><i class="fa-solid fa-rotate-right"></i></a>
								<input type="hidden" name="pet_id" value="<?php echo $lab_info['pet']['id']; ?>" />
								<input type="hidden" name="no_event" value="1" />
							<?php else: ?>
								<select name="pet_id" style="width:100%" id="pet_id" data-allow-clear="1">
									<?php if($lab_info['pet']): ?>
									<option value="<?php echo $lab_info['pet']['id']; ?>" selected></option>
									<?php endif; ?>
								</select>
								<input type="hidden" name="no_event" value="0" />
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
const URL_SELECT = "<?php echo base_url('pets/get_pet_name'); ?>";

document.addEventListener("DOMContentLoaded", function(){
	$("#labo").addClass('active');

	/* get pet names */
	$('#pet_id').select2({
		theme: 'bootstrap4',
		placeholder: 'Select Pet',
		ajax: {
			url: URL_SELECT,
			dataType: 'json'
		},
	});

	$('#showMore').on('click', function(e){
		e.preventDefault();
		$('#moreContent').toggleClass('d-none');
		$(this).text(
			$('#moreContent').hasClass('d-none') ? 'Show details' : 'Show less details'
		);
	});
});
</script>