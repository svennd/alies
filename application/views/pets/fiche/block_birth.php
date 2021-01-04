<div class="card border-left-success shadow py-1 mb-3">
	<div class="card-body">
		<div class="row no-gutters align-items-center">
			<div class="col mr-2">
				<div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php if(!$pet['death']): ?>Age : <?php echo timespan(strtotime($pet['birth']), time(), 1); ?><?php endif; ?></div>
				<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pet['birth'] ?></div>
			</div>
			<div class="col-auto"><i class="fas fa-birthday-cake fa-2x text-gray-300"></i></div>
		</div>
	</div>
</div>