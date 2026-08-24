<?php
$event_cost = isset($billing_info['total_brut'])
	? '&euro;&nbsp;' . number_format((float) $billing_info['total_brut'], 2, ',', '.')
	: html_escape($this->lang->line('cost_unavailable'));
?>
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
			<span class="mx-3 text-gray-400" aria-hidden="true">&bull;</span>
			<span><i class="far fa-calendar fa-fw" aria-hidden="true"></i> <?php echo user_format_date($event_info['created_at'], $user->user_date); ?></span>
			<span class="mx-3 text-gray-400" aria-hidden="true">&bull;</span>
			<span><?php echo $event_cost; ?></span>
			</div>
			<?php include __DIR__ . '/../event/block_header_types.php'; ?>
		</div>
	</div>
</div>