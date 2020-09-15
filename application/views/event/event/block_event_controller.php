<div class="card border-left-success shadow py-1 mb-3">
	<div class="card-body">
		<div class="row no-gutters align-items-center">
			<div class="col mr-2">
				<div class="text-xs font-weight-bold text-uppercase mb-1">Control</div>
				<div class="text-gray-800">
					<?php if($event_info['status'] == STATUS_OPEN): ?>
						<?php if (!$consumables && !$procedures_d && empty($event_info['anamnese']) && empty($event_info['title']) && !$event_uploads): ?>
							<a href="<?php echo base_url(); ?>events/del/<?php echo $event_id; ?>/<?php echo $owner['id']; ?>" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Remove event</a>
						<?php else : ?>
							<a href="<?php echo base_url(); ?>events/lock/<?php echo $event_id; ?>" class="btn btn-warning btn-sm"><i class="fas fa-lock"></i> Lock event</a>
							<br/>
							<a href="<?php echo base_url(); ?>events/edit_price/<?php echo $event_id; ?>" class="btn btn-danger btn-sm mt-3"><i class="fas fa-skull-crossbones"></i> Edit Price</a>
						<?php endif; ?>
					<?php elseif($event_info['status'] == STATUS_CLOSED): ?>
					<i class="fas fa-lock"></i> Locked
					<?php elseif($event_info['status'] == STATUS_HISTORY): ?>
					<i class="fas fa-landmark"></i> History
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
