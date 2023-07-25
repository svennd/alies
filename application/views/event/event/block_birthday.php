<div class="card border-left-success shadow py-1 mb-3 d-none d-xl-block">
	<div class="card-body">
		<div class="row no-gutters align-items-center">
			<div class="col mr-2">
				<div class="h5 mb-0 font-weight-bold text-gray-800">
				<a href="<?php echo base_url(); ?>pets/history_weight/<?php echo $pet['id']; ?>" target="_blank"><?php echo ($pet['last_weight'] == 0) ? "Add weight" : $pet['last_weight'] . 'kg'; ?></a>
				</div>
			</div>
			<div class="col-auto"><i class="fas fa-weight fa-2x text-gray-300"></i></div>
		</div>
	</div>
</div>