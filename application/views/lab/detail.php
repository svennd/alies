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
					<?php if(!empty($lab_info['lab_comment'])): ?>
					<tr>
						<td><?php echo $this->lang->line('lab_comment'); ?></td>
						<td><?php echo $lab_info['lab_comment']; ?></td>
					</tr>
					<?php endif; ?>
				</table>
				</form>

				<table class="table table-sm">
					<thead>
						<tr>
							<th><?php echo $this->lang->line('lab_code'); ?></th>
							<th>chart</th>
							<th><?php echo $this->lang->line('value'); ?></th>
							<th><?php echo $this->lang->line('limit'); ?></th>
							<th><?php echo $this->lang->line('unit'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($lab_details as $d): ?>
                            <?php 
                                $draw_plot = ($d["value_num"] != null && $d["ref_min"] != null && $d["ref_max"] != null);
                                $value = ($d["value_num"] != null && strlen($d["value_text"]) <= 1) ? $d["value_num"] : $d["value_text"];
                                $is_text = ($d["value_num"] == null);
                            ?>
						<tr>
							<td><?php echo $d["code"]; ?></td>
							<?php if($d["value_text"] == null): ?>
							<td width="30%">
                                <?php if($draw_plot): ?>
                                <div class="bar"><div class="seg low"></div><div class="seg mid"></div><div class="seg high"></div><div class="pos" style="left: <?php echo ((0.5+(rand(-2, 2)/10)) * 100); ?>%;"></div></div></div>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                            <td <?php if($is_text): ?>colspan="4"<?php endif; ?>><?php echo $value; ?></td>
                            
                            <?php if(!$is_text): ?>
                            <td><?php echo (strlen($d["value_text"]) <= 1  && $d['ref_max']) ? $d["ref_min"] . ' - ' . $d['ref_max'] : ''; ?></td>
							<td><?php echo $d["unit"]; ?></td>
                            <?php endif; ?>
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