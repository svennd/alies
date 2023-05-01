
<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><?php echo $this->lang->line('sticky'); ?></div>
				<div class="dropdown no-arrow"></div>
			</div>
			<div class="card-body">
				<?php if($data): ?>
				<table class="table" id="dataTable">
					<thead>
						<tr>
							<th><?php echo $this->lang->line('sticky'); ?></th>
							<th><?php echo $this->lang->line('vet'); ?></th>
							<th><?php echo $this->lang->line('date'); ?></th>
							<th><?php echo $this->lang->line('options'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($data as $d): ?>
						<tr>
							<td><?php echo $d["note"]; ?></td>
							<td>
                                <?php echo $d["vet"]["first_name"]; ?><br/>
                                <small><?php echo $d["location"]["name"]; ?></small>
                            </td>
							<td>
                                <?php echo user_format_date($d['created_at'], $user->user_date); ?><br/>
								<small><?php echo time_ago($d["created_at"]);?></small>
							</td>
                            <td>
                                <?php if($d['user_id'] == $user->id): ?>
                                    <a href="<?php echo base_url('sticky/delete/' . $d['id']); ?>" class="file_line btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></a>
                                <?php endif; ?>
                            </td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php else: ?>
					no stickys!
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({responsive: true, "order": [[ 0, "desc" ]]});
	$("#sticky").addClass('active');
});
</script>