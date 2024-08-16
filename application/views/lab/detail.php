
<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url('lab'); ?>"><?php echo $this->lang->line('Lab'); ?></a> / Lab results</div>
				
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url('lab/print/' . $lab_info['id']); ?>" class="btn btn-outline-success btn-sm" target="blank"><i class="fa-solid fa-print"></i> print</a>
					<?php if ($lab_info['source'] == "medilab"): ?>
						<a href="https://online.medilab.be/dokter/staal/<?php echo $lab_info['lab_id']; ?>" class="btn btn-outline-primary btn-sm" target="blank"><i class="fas fa-external-link-alt"></i> <?php echo $lab_info['source'] . ' ('. $lab_info['lab_id'] . ')';  ?></a>
					<?php endif; ?>
				</div>
			</div>
			<div class="card-body">
				<form action="<?php echo base_url('lab/detail/' . $lab_info['id']); ?>" method="post" autocomplete="off">
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
						<td><?php echo $this->lang->line('lab_received'); ?></td>
						<td><?php echo $lab_info['lab_date']; ?></td>
					</tr>
					<?php if(!empty($lab_info['lab_comment'])): ?>
					<tr>
						<td><?php echo $this->lang->line('lab_comment'); ?></td>
						<td><?php echo $lab_info['lab_comment']; ?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<td><?php echo ucfirst($this->lang->line('comment')); ?></td>
						<td>
							<div class="form-group">
								<textarea class="form-control" name="message" id="message" rows="3"><?php echo (isset($lab_info['comment'])) ? $lab_info['comment']: '' ?></textarea>
							</div>
							<button type="submit" name="submit" value="update" class="btn <?php echo ($comment_update) ? "btn-success" : "btn-outline-primary" ?>"><i class="fa-solid fa-floppy-disk"></i> <?php echo (!$comment_update) ? $this->lang->line('store') : $this->lang->line('updated') . '!'; ?></button>
						</td>
					</tr>
				</table>
				</form>
				<?php if($lab_info['source'] == "mslink - HEMATO"): ?>
					<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
					<?php
					foreach($lab_details as $d):
						if ($d["lab_code"] == "1")
						{
							$WBC = substr($d["comment"], 4);
						}
						if ($d["lab_code"] == "2")
						{
							$RBC = substr($d["comment"], 4);
						}
						if ($d["lab_code"] == "3")
						{
							$THR = substr($d["comment"], 4);
						}
					endforeach;
					?>
					<div class="row">
						<div class="col-4" style="height:250px;">
							<canvas id="my-wbc"></canvas>
						</div>
						<div class="col-4" style="height:250px;">
							<canvas id="my-rbc"></canvas>
						</div>
						<div class="col-4" style="height:250px;">
							<canvas id="my-plt"></canvas>
						</div>
					</div>
					<script>
						// Your data
						const wbc_data = [<?php echo $WBC; ?>];
						const rbc_data = [<?php echo $RBC; ?>];
						const plt_data = [<?php echo $THR; ?>];
					</script>

					<script src="<?php echo base_url('assets/js/lab.charts.js'); ?>"></script>
				<?php endif; ?>

				<table class="table table-sm" id="dataTable">
					<thead>
						<tr>
							<th><?php echo $this->lang->line('lab_code'); ?></th>
							<th><?php echo $this->lang->line('value'); ?></th>
							<th><?php echo $this->lang->line('limit'); ?></th>
							
							<?php if($lab_info['source'] == "medilab"): ?>
								<th><?php echo $this->lang->line('comment'); ?></th>
								<th><?php echo $this->lang->line('lab_update'); ?></th>
							<?php endif;?>
						</tr>
					</thead>
					<tbody>
						<?php 
						foreach($lab_details as $d):
							if ($d["lab_code"] == "1") continue; // WBC
							if ($d["lab_code"] == "2") continue; // RBC
							if ($d["lab_code"] == "3") continue; // THR
						?>
						<tr class="<?php echo ($d["report"] || $lab_info["source"] != "medilab") ? "" : "table-secondary"; ?>">
							<td data-sort="<?php echo $d['lab_code']; ?>"><?php echo $d["lab_code_text"]; ?> (<?php echo $d["lab_code"]; ?>)</td>
							<td><?php echo ($d["value"] != 0 && strlen($d["string_value"]) <= 1) ? $d["string_value"] . $d["value"] : $d["string_value"]; ?> <?php echo $d["unit"]; ?></td>
							<td><?php echo (strlen($d["string_value"]) <= 1) ? $d["lower_limit"] . ' - ' . $d['upper_limit'] : ''; ?></td>

							<?php if($lab_info['source'] == "medilab"): ?>
								<td><?php echo ($d["comment"] == $d["string_value"]) ? '' : $d["comment"]; ?></td>
								<td><?php echo $d["lab_updated_at"]; ?></td>
							<?php endif;?>

						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
const URL_SELECT = "<?php echo base_url('pets/get_pet_name'); ?>";

document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({responsive: true});
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

});
</script>