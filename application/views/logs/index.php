<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>logs">Logs</a>
			</div>
            <div class="card-body">
				<ul>
					<li><a href="<?php echo base_url(); ?>logs/write_off">Write Off logs</a> : expired / lost products from the stock.</li>
					<li><a href="<?php echo base_url(); ?>logs/nlog">Global logs</a> : system logs for alies</li>
					<li><a href="<?php echo base_url(); ?>logs/software_version">Version logs</a> : software information</li>
				</ul>
			</div>
		</div>

	</div>
      
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#logs").addClass('active');
});
</script>