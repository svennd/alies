  <div class="card">
  <div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><?php echo $this->lang->line('backup'); ?></div>
				<div class="dropdown no-arrow">
					<a class="btn btn-outline-success btn-sm" href="<?php echo base_url(); ?>backup/sql"><i class="fas fa-database"></i> FULL SQL</a>
					<a class="btn btn-outline-primary btn-sm" href="<?php echo base_url(); ?>backup/sql/all/true"><i class="fas fa-file-archive"></i> FULL ZIP</a>
				</div>
			</div>
	<!-- Card Body -->
	<div class="card-body">
	<?php echo $this->lang->line('known_tables'); ?> : <br/>
	
	<table class="table">
		<tr>
			<th><?php echo $this->lang->line('table_name'); ?></th>
			<th><?php echo $this->lang->line('est_size'); ?></th>
			<th><?php echo $this->lang->line('export'); ?></th>
		</tr>
		<?php foreach($tables as $k => $v): ?>
			<tr>
				<td><?php echo $v['table_name']; ?></td>
				<td><?php echo $v['size']; ?> MB</td>
				<td>
					<a class="btn btn-outline-success btn-sm" href="<?php echo base_url(); ?>backup/sql/<?php echo $v['table_name']; ?>"><i class="fas fa-database"></i> SQL</a>
					<a class="btn btn-outline-primary btn-sm" href="<?php echo base_url(); ?>backup/sql/<?php echo $v['table_name']; ?>/true"><i class="fas fa-file-archive"></i> ZIP</a>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	</div>
  </div>
<script>
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
});
</script>