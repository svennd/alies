<div class="card border-left-success shadow py-1 mb-3">
	<div class="card-body">
		<div class="row no-gutters align-items-center">
			<div class="col mr-2">
				<div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php echo $pet['name'] ?></div>
				<div class="h5 mb-0 font-weight-bold text-gray-800">
				<a href="<?php echo base_url(); ?>pets/history_weight/<?php echo $pet['id']; ?>"><?php echo ($pet['last_weight'] == 0) ? "Add weight" : $pet['last_weight'] . 'kg'; ?></a>
				</div>
			</div>
			<div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300"></i></div>
		</div>
	</div>
</div>