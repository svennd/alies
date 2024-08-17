
<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><?php echo $this->lang->line('Lab'); ?></div>
				<div class="dropdown no-arrow">
				</div>
			</div>
			<div class="card-body">
				<?php if($data): ?>
				<table class="table table-sm" id="dataTable">
					<thead>
						<tr>
							<th><?php echo $this->lang->line('lab_id'); ?></th>
							<th><?php echo $this->lang->line('client'); ?></th>
							<th><?php echo $this->lang->line('lab_received'); ?></th>
							<th><?php echo $this->lang->line('lab_update'); ?></th>
							<th><?php echo $this->lang->line('source'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($data as $d): ?>
						<tr>
							<td>
								<a href="<?php echo base_url('lab/detail/'. $d["id"]); ?>" <?php if(!isset($d['pet_id'])): ?>class="btn btn-sm btn-outline-danger"<?php else: ?>class="btn btn-sm btn-outline-primary"<?php endif; ?>>
									<?php echo $d['id']; ?>
								</a>
								
							</td>
							<td>
								<?php if(isset($d['pet_id'])): ?>
									<a href="<?php echo base_url('owners/detail/' . $d['owners_id']); ?>"><?php echo $d['last_name']; ?></a> (<small><?php echo get_symbol($d['pet_type']) ; ?> <?php echo $d['pet_name']; ?></small>)
								<?php else: ?>
									---
								<?php endif; ?>
							</td>
							<td><?php echo user_format_date($d['lab_date'], $user->user_date); ?></td>
							<td data-sort="<?php echo strtotime($d['updated_at']) ?>">
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
	$("#dataTable").DataTable({responsive: true, "order": [[0, "desc" ]]});
	$("#labo").addClass('active');
});
</script>