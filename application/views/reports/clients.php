<div class="row">
	<div class="col-lg-10 mb-4">


		<div class="card shadow mb-4">
			<div class="card-header">
				Report / Export clients (<?php echo $total_clients; ?>)
			</div>

			<div class="card-body">
				<a href="<?php echo base_url(); ?>export/clients/30" class="btn btn-success" download><i class="fas fa-file-export"></i> 30 days</a>
				<a href="<?php echo base_url(); ?>export/clients/90" class="btn btn-success" download><i class="fas fa-file-export"></i> 90 days</a>
				<a href="<?php echo base_url(); ?>export/clients" class="btn btn-warning ml-3" download><i class="fas fa-file-export"></i> All Clients</a>
				<br>
				<small>Exports only created & updated</small>
			</div>
		</div>
	</div>
</div>
