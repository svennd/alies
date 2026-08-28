<div class="card shadow-sm mb-3">
	<div class="card-body py-2">
		<div class="d-flex flex-nowrap align-items-center overflow-auto text-nowrap justify-content-between" aria-label="Event summary">
			<div>
			<a class="font-weight-bold" href="<?php echo base_url('owners/detail/' . (int) $owner['id']); ?>">
				<i class="fas fa-user fa-fw" aria-hidden="true"></i>
				<?php echo html_escape(trim($owner['last_name'] . ' ' . $owner['first_name'])); ?>
			</a>
			<span class="mx-3 text-gray-400" aria-hidden="true">&bull;</span>
			<a class="font-weight-bold" href="<?php echo base_url('pets/fiche/' . (int) $pet['id']); ?>">
				<i class="fas fa-paw fa-fw" aria-hidden="true"></i>
				<?php echo html_escape($pet['name']); ?>
			</a> 
			<?php if ($pet['breeds']): ?>(<?php echo html_escape($pet['breeds']['name']); ?><?php if ($pet['breeds2']): ?>x <?php echo html_escape($pet['breeds2']['name']); ?><?php endif; ?><?php if ($pet['last_weight']): ?>, <?php echo html_escape($pet['last_weight']); ?> kg<?php endif; ?>)<?php endif; ?>
			<span class="mx-3 text-gray-400" aria-hidden="true">&bull;</span>
			<span><i class="far fa-calendar fa-fw" aria-hidden="true"></i> <?php echo user_format_date($event_info['created_at'], $user->user_date); ?></span>
			</div>
			<?php include __DIR__ . '/../event/block_header_types.php'; ?>
		</div>
	</div>
</div>