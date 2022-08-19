  <div class="card shadow mb-4">
	<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
	  <h6 class="m-0 font-weight-bold text-primary">Tables</h6>
	</div>
	<!-- Card Body -->
	<div class="card-body">
	Full database backup options : <br/><br/>
<a class="btn btn-outline-success" href="<?php echo base_url(); ?>backup/sql"><i class="fas fa-database"></i> FULL SQL</a>&nbsp;
<a class="btn btn-outline-success" href="<?php echo base_url(); ?>backup/sql/all/true"><i class="fas fa-file-archive"></i> FULL ZIP</a>&nbsp; 

<br/>
<br/>
<br/>
	Known database tables : <br/>
	
	<table class="table">
		<tr>
			<th>Name</th>
			<th>Estimated size</th>
			<th>Export</th>
		</tr>
		<?php foreach($tables as $k => $v): ?>
			<tr>
				<td><?php echo $v['table_name']; ?></td>
				<td><?php echo $v['size']; ?> MB</td>
				<td>
					<a class="btn btn-outline-success" href="<?php echo base_url(); ?>backup/sql/<?php echo $v['table_name']; ?>"><i class="fas fa-database"></i> SQL</a>
					<a class="btn btn-outline-success" href="<?php echo base_url(); ?>backup/sql/<?php echo $v['table_name']; ?>/true"><i class="fas fa-file-archive"></i> ZIP</a>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	</div>
  </div>
<script>
document.addEventListener("DOMContentLoaded", function(){
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#backup").addClass('active');
});
</script>