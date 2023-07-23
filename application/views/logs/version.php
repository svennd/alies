<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>logs">Logs</a> / software information
			</div>
            <div class="card-body">
				<h4>Database Version</h4>
				<?php echo($database_version['0']['version']); ?>
				<br/>
				<br/>
				<br/>
				<h4>Changelog</h4>
			<?php echo $changelog; ?>
			</div>
		</div>

	</div>
      
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#logs").addClass('active');
	$("#dataTable").DataTable();
});
</script>