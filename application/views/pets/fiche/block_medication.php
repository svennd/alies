<?php if(!empty($pet['medication'])): ?>
<div class="card border-left-warning shadow py-1 mb-3">
	<div class="card-body">
		<div class="row no-gutters align-items-center">
			<div class="col mr-2">
				<div class="text-xs font-weight-bold text-uppercase mb-1"><?php echo $this->lang->line('medicine'); ?></div>
				<div class="text-gray-800">
					<?php echo nl2br($pet['medication']); ?>
				</div>
			</div>
			<div class="col-auto"><i class="fa-solid fa-pills fa-2x text-gray-300"></i></div>
		</div>
	</div>
</div>
<?php endif; ?>
