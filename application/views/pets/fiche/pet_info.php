<style>
.pet-profile-card .pet-profile-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	flex: 0 0 4rem;
	width: 4rem;
	height: 4rem;
	border-radius: 50%;
	background-color: #f0f3ff;
}

.pet-profile-card .pet-profile-icon i {
	width: auto;
	font-size: 1.75rem;
}

.pet-profile-card .pet-profile-identity,
.pet-profile-card .pet-profile-value {
	min-width: 0;
	overflow-wrap: anywhere;
}

.pet-profile-card .pet-profile-label {
	display: block;
	margin-bottom: .25rem;
	color: #858796;
	font-size: .7rem;
	font-weight: 700;
	text-transform: uppercase;
}

.pet-profile-card .pet-profile-value {
	color: #3a3b45;
}

@media (max-width: 575.98px) {
	.pet-profile-card .pet-profile-header-actions {
		width: 100%;
		margin-top: 1rem;
		margin-left: 0 !important;
		padding-left: 0 !important;
	}
}
</style>

<div class="pet-profile-card card shadow mb-4 w-100">
	<div class="card-body">
		<div class="d-flex align-items-start justify-content-between flex-wrap">
			<div class="pet-profile-identity d-flex align-items-center pr-2">
				<div class="pet-profile-icon mr-3" aria-hidden="true">
					<?php echo get_symbol($pet['type']); ?>
				</div>
				<div class="pet-profile-identity">
					<h2 class="h5 mb-1 font-weight-bold text-gray-900"><?php echo html_escape($pet['name']); ?></h2>
					<div class="small text-muted">
						<span>#<?php echo (int) $pet['id']; ?></span>
						<span class="mx-1" aria-hidden="true">&middot;</span>
						<a href="<?php echo base_url('owners/detail/' . (int) $owner['id']); ?>"><?php echo html_escape($owner['last_name']); ?></a>
					</div>
				</div>
			</div>

			<div class="pet-profile-header-actions d-flex align-items-center ml-auto pl-2">
				<a href="<?php echo base_url('pets/edit/' . (int) $pet['id']); ?>" class="btn btn-outline-primary btn-sm mr-2">
					<i class="fas fa-fw fa-pen" aria-hidden="true"></i>
					<?php echo $this->lang->line('edit_pet'); ?>
				</a>

				<a href="<?php echo base_url('tooth/fiche/' . (int) $pet['id']); ?>" class="btn btn-outline-primary btn-sm mr-2">
					<i class="fas fa-fw fa-tooth" aria-hidden="true"></i>
					<?php echo $this->lang->line('tooth'); ?>
				</a>
				<?php if (isset($pet_has_rx) && $pet_has_rx === true): ?>
					<a href="<?php echo base_url('rx/list/' . (int) $pet['id']); ?>" class="btn btn-outline-primary btn-sm mr-2">
						<i class="fas fa-fw fa-radiation" aria-hidden="true"></i>
						<?php echo $this->lang->line('rx'); ?>
					</a>
				<?php endif; ?>
				<?php if (isset($pet_has_lab) && $pet_has_lab === true): ?>
					<a href="<?php echo base_url('lab/list_lab/' . (int) $pet['id']); ?>" class="btn btn-outline-primary btn-sm mr-2">
						<i class="fas fa-fw fa-flask" aria-hidden="true"></i>
						Lab
					</a>
				<?php endif; ?>

				<div class="dropdown no-arrow">
					<button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="petProfileActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="More actions" title="More actions">
						<i class="fas fa-ellipsis-v" aria-hidden="true"></i>
					</button>
					<div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="petProfileActions">
						<a href="<?php echo base_url('pets/export/' . (int) $pet['id']); ?>" class="dropdown-item">
							<i class="fas fa-fw fa-file-export text-info mr-2" aria-hidden="true"></i>
							<?php echo $this->lang->line('export'); ?>
						</a>
						<a href="<?php echo base_url('pets/change_owner/' . (int) $pet['id']); ?>" class="dropdown-item text-danger">
							<i class="fas fa-fw fa-exchange-alt mr-2" aria-hidden="true"></i>
							<?php echo $this->lang->line('change_owner'); ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="row border-top mt-4 pt-4">
			<div class="col-6 col-md-4 mb-4">
				<span class="pet-profile-label"><?php echo $this->lang->line('breed'); ?></span>
				<div class="pet-profile-value">
					<?php echo isset($pet['breeds']['name']) ? html_escape($pet['breeds']['name']) : '?'; ?>
					<?php echo isset($pet['breeds2']['name']) ? ' x ' . html_escape($pet['breeds2']['name']) : ''; ?>
				</div>
			</div>

			<div class="col-6 col-md-4 mb-4">
				<span class="pet-profile-label">Type</span>
				<div class="pet-profile-value"><?php echo get_name($pet['type']); ?></div>
			</div>

			<div class="col-6 col-md-4 mb-4">
				<span class="pet-profile-label"><?php echo $this->lang->line('gender'); ?></span>
				<div class="pet-profile-value"><?php echo get_gender($pet['gender']); ?></div>
			</div>

			<div class="col-6 col-md-4 mb-4">
				<span class="pet-profile-label"><?php echo $this->lang->line('weight'); ?></span>
				<div class="pet-profile-value font-weight-bold">
					<a href="<?php echo base_url('pets/history_weight/' . (int) $pet['id']); ?>">
						<?php echo ($pet['last_weight'] == 0) ? '---' : html_escape($pet['last_weight']) . ' kg'; ?>
					</a>
				</div>
			</div>

			<div class="col-6 col-md-4 mb-4">
				<span class="pet-profile-label"><?php echo $this->lang->line('birth'); ?></span>
				<div class="pet-profile-value">
					<?php echo html_escape(user_format_date($pet['birth'], $user->user_date)); ?>
					<?php if (!$pet['death']): ?>
						<small class="d-block text-muted"><?php echo html_escape(timespan(strtotime($pet['birth']), time(), 1)); ?></small>
					<?php endif; ?>
				</div>
			</div>

			<div class="col-6 col-md-4 mb-4">
				<span class="pet-profile-label"><?php echo $this->lang->line('chip'); ?></span>
				<div class="pet-profile-value">
					<?php echo (empty($pet['chip']) || !ctype_digit($pet['chip'])) ? '---' : preg_replace('/(\d{3})(?=\d)/', '$1-', $pet['chip']); ?>
				</div>
			</div>

			<?php if (!empty($pet['color'])): ?>
				<div class="col-6 col-md-4 mb-4">
					<span class="pet-profile-label"><?php echo $this->lang->line('haircolor'); ?></span>
					<div class="pet-profile-value"><?php echo html_escape($pet['color']); ?></div>
				</div>
			<?php endif; ?>

			<?php if (!empty($pet['hairtype'])): ?>
				<div class="col-6 col-md-4 mb-4">
					<span class="pet-profile-label"><?php echo $this->lang->line('hairtype'); ?></span>
					<div class="pet-profile-value"><?php echo html_escape($pet['hairtype']); ?></div>
				</div>
			<?php endif; ?>

			<?php if (!empty($pet['nr_vac_book'])): ?>
				<div class="col-6 col-md-4 mb-4">
					<span class="pet-profile-label"><?php echo $this->lang->line('vacc_nr'); ?></span>
					<div class="pet-profile-value"><?php echo html_escape($pet['nr_vac_book']); ?></div>
				</div>
			<?php endif; ?>
		</div>

		<?php if (!empty($pet['note'])): ?>
			<div class="alert alert-warning mb-4" role="note">
				<i class="fas fa-fw fa-exclamation-triangle mr-1" aria-hidden="true"></i>
				<?php echo nl2br(html_escape($pet['note'])); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
