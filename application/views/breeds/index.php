<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><?php echo $this->lang->line('breeds'); ?></div>
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url(); ?>breeds/rebuild_frequenty" class="btn btn-outline-warning btn-sm"><i class="fas fa-fw fa-history"></i> rebuild freq</a>
					<a href="<?php echo base_url(); ?>breeds/add" class="btn btn-outline-success btn-sm"><i class="fas fa-plus fa-fw"></i> <?php echo $this->lang->line('add'); ?></a>
				</div>
			</div>
            <div class="card-body">
			<?php if ($breeds): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('type'); ?></th>
					<th><?php echo $this->lang->line('breed'); ?></th>
					<th><?php echo $this->lang->line('frequency'); ?></th>
					<th><?php echo $this->lang->line('weight_male'); ?></th>
					<th><?php echo $this->lang->line('weight_female'); ?></th>
					<th><?php echo $this->lang->line('last_update'); ?></th>
					<th><?php echo $this->lang->line('options'); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php foreach($breeds as $breed): ?>
						<tr>
							<td><?php echo get_symbol($breed['type'], true); ?></td>
							<td><?php echo $breed['name']; ?></td>
							<td><a href="<?php echo base_url('breeds/index/' . $breed['id']); ?>"><?php echo isset($breed['pets'][0]['counted_rows']) ? $breed['pets'][0]['counted_rows'] : 0; ?></a></td>
							<td><?php echo $breed['male_min_weight']; ?> - <?php echo $breed['male_max_weight']; ?></td>
							<td><?php echo $breed['female_min_weight']; ?> - <?php echo $breed['female_max_weight']; ?></td>
							<td><?php echo user_format_date($breed['updated_at'], $user->user_date); ?></td>
							<td>
								<a href="<?php echo base_url('breeds/edit/' . $breed['id']); ?>" class="btn btn-sm btn-success"><?php echo $this->lang->line('edit'); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				</table>
			<?php endif; ?>
                </div>
		</div>

	</div>

</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable();
	$("#admin").addClass('active');
});
</script>
