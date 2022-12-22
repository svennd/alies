
<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url('lab'); ?>"><?php echo $this->lang->line('Lab'); ?></a> / Lab results</div>
				<div class="dropdown no-arrow">
					<a href="https://online.medilab.be/dokter/staal/<?php echo $lab_info['lab_id']; ?>" class="btn btn-outline-success btn-sm" target="blank"><i class="fas fa-external-link-alt"></i> <?php echo $lab_info['source'] . ' ('. $lab_info['lab_id'] . ')';  ?></a>
				</div>
			</div>
			<div class="card-body">
				<table class="table">
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
						<td><?php echo $this->lang->line('comment'); ?></td>
						<td>
							<form action="<?php echo base_url('lab/detail/' . $lab_info['id']); ?>" method="post" autocomplete="off">
								<div class="form-group">
									<textarea class="form-control" name="message" id="message" rows="3"><?php echo (isset($lab_info['comment'])) ? $lab_info['comment']: '' ?></textarea>
								</div>
								<button type="submit" name="submit" value="update" class="btn <?php echo ($comment_update) ? "btn-success" : "btn-primary" ?>"><?php echo (!$comment_update) ? $this->lang->line('store') : $this->lang->line('updated') . '!'; ?></button>
							</form>
						</td>
					</tr>
				</table>

				<table class="table" id="dataTable">
					<thead>
						<tr>
							<th><?php echo $this->lang->line('lab_code'); ?></th>
							<th><?php echo $this->lang->line('value'); ?></th>
							<th><?php echo $this->lang->line('limit'); ?></th>
							<th><?php echo $this->lang->line('comment'); ?></th>
							<th><?php echo $this->lang->line('lab_update'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($lab_details as $d): ?>
						<tr class="<?php echo ($d["report"]) ? "" : "table-secondary"; ?>">
							<td data-sort="<?php echo $d['lab_code']; ?>"><?php echo $d["lab_code_text"]; ?> (<?php echo $d["lab_code"]; ?>)</td>
							<td><?php echo ($d["value"] != 0 && strlen($d["string_value"]) <= 1) ? $d["string_value"] . $d["value"] : $d["string_value"]; ?> <?php echo $d["unit"]; ?></td>
							<td><?php echo (strlen($d["string_value"]) <= 1) ? $d["lower_limit"] . ' - ' . $d['upper_limit'] : ''; ?></td>
							<td><?php echo ($d["comment"] == $d["string_value"]) ? '' : $d["comment"]; ?></td>
							<td><?php echo $d["lab_updated_at"]; ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({responsive: true});
	$("#labo").addClass('active');
});
</script>