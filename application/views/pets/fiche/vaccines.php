<div class="card shadow mb-4" style="width:100%;">
	<div class="card-header">
		<div>
			<a href="<?php echo base_url('vaccine/fiche/' . $pet['id']); ?>">
				<i class="fa-solid fa-syringe fa-fw" aria-hidden="true"></i>
				<?php echo $this->lang->line('title_vaccines'); ?>
			</a>
		</div>
	</div>
	<div class="card-body">
		<?php if ($vaccines): ?>
			<?php
			$today = new DateTime('today');
			$warning_boundary = (clone $today)->modify('+3 months');
			?>
			<div class="vaccine-status-list">
				<?php foreach ($vaccines as $vac): ?>
					<?php
					$rappel_date = new DateTime($vac['max_rappel']);
					$rappel_date->setTime(0, 0, 0);

					if ($rappel_date < $today) {
						$status = 'danger';
					} elseif ($rappel_date <= $warning_boundary) {
						$status = 'warning';
					} else {
						$status = 'success';
					}
					?>
					<div class="alert alert-<?php echo $status; ?> mb-2 px-3 py-2">
						<div class="font-weight-bold text-break">
							<?php echo html_escape($vac['name']); ?>
						</div>
						<div class="small d-flex flex-wrap align-items-center mt-1">
							<span class="text-nowrap"><?php echo html_escape(user_format_date($vac['max_injection'], $user->user_date)); ?></span>
							<i class="fas fa-arrow-right mx-2" aria-hidden="true"></i>
							<span class="text-nowrap"><?php echo html_escape(user_format_date($vac['max_rappel'], $user->user_date)); ?></span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else: ?>
			<?php echo $this->lang->line('no_vaccines'); ?>
		<?php endif; ?>
	</div>
</div>
