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
	border: 0;
	color: inherit;
	cursor: pointer;
	overflow: hidden;
	padding: 0;
}

.pet-profile-card .pet-profile-icon i {
	width: auto;
	font-size: 1.75rem;
}

.pet-profile-card .pet-profile-icon:hover,
.pet-profile-card .pet-profile-icon:focus {
	box-shadow: 0 0 0 .2rem rgba(78, 115, 223, .25);
	outline: 0;
}

.pet-profile-card .pet-profile-avatar {
	display: block;
	width: 100%;
	height: 100%;
	object-fit: cover;
}

#petAvatarCropper {
	min-height: 320px;
}

.pet-avatar-rotate {
	white-space: normal;
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

	#petAvatarCropper {
		min-height: 280px;
	}
}
</style>

<div class="pet-profile-card card shadow mb-4 w-100">
	<div class="card-body">
		<?php if (!empty($pet_avatar_message)): ?>
			<div class="alert alert-<?php echo html_escape(in_array($pet_avatar_message_type, array('success', 'danger'), true) ? $pet_avatar_message_type : 'info'); ?> alert-dismissible fade show" role="alert">
				<?php echo html_escape($pet_avatar_message); ?>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
		<?php endif; ?>
		<div class="d-flex align-items-start justify-content-between flex-wrap">
			<div class="pet-profile-identity d-flex align-items-center pr-2">
				<?php $has_pet_avatar = isset($pet_avatar_available) ? $pet_avatar_available : !empty($pet['avatar']); ?>
				<button
					type="button"
					class="pet-profile-icon mr-3"
					data-toggle="modal"
					data-target="#petAvatarModal"
					aria-label="<?php echo html_escape($this->lang->line($has_pet_avatar ? 'pet_avatar_change' : 'pet_avatar_add')); ?>"
					title="<?php echo html_escape($this->lang->line($has_pet_avatar ? 'pet_avatar_change' : 'pet_avatar_add')); ?>"
				>
					<?php if ($has_pet_avatar): ?>
						<img
							class="pet-profile-avatar"
							src="<?php echo base_url('pets/avatar_file/' . (int) $pet['id']) . '?v=' . rawurlencode(substr(sha1($pet['avatar']), 0, 12)); ?>"
							alt=""
						>
					<?php else: ?>
						<span aria-hidden="true"><?php echo get_symbol($pet['type']); ?></span>
					<?php endif; ?>
				</button>
				<div class="pet-profile-identity">
					<h2 class="h5 mb-1 font-weight-bold text-gray-900"><?php echo html_escape($pet['name']); ?></h2>
					<div class="small text-muted">
						<span>#<?php echo (int) $pet['id']; ?></span>
						<span class="mx-1" aria-hidden="true">&middot;</span>
						<a href="<?php echo base_url('owners/detail/' . (int) $owner['id']); ?>"><?php echo html_escape($owner['last_name']); ?></a>
					</div>
				</div>
			</div>
			
			<div class="pet-profile-header-actions d-none d-sm-flex align-items-center ml-auto pl-2">
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

<div
	class="modal fade"
	id="petAvatarModal"
	tabindex="-1"
	role="dialog"
	aria-labelledby="petAvatarModalTitle"
	aria-hidden="true"
	data-invalid-type="<?php echo html_escape($this->lang->line('pet_avatar_invalid_type')); ?>"
	data-too-large="<?php echo html_escape($this->lang->line('pet_avatar_too_large')); ?>"
	data-invalid-image="<?php echo html_escape($this->lang->line('pet_avatar_invalid_image')); ?>"
>
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="petAvatarModalTitle"><?php echo $this->lang->line('pet_avatar_title'); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div id="petAvatarClientMessage" class="alert alert-danger d-none" role="alert" aria-live="polite"></div>
				<form action="<?php echo base_url('pets/save_avatar/' . (int) $pet['id']); ?>" method="post" enctype="multipart/form-data" id="petAvatarUploadForm">
					<div class="form-group">
						<label for="petAvatarSource"><?php echo $this->lang->line('pet_avatar_choose'); ?></label>
						<input type="hidden" name="MAX_FILE_SIZE" value="8388608">
						<input type="file" class="form-control-file" id="petAvatarSource" name="pet_avatar_source" accept="image/jpeg,image/png" required>
					</div>
					<div id="petAvatarEditor" class="d-none">
						<div id="petAvatarCropper"></div>
						<div class="btn-group d-flex mt-2" role="group" aria-label="<?php echo html_escape($this->lang->line('pet_avatar_title')); ?>">
							<button type="button" class="btn btn-outline-secondary pet-avatar-rotate" data-deg="-90">
								<i class="fas fa-undo" aria-hidden="true"></i> <?php echo $this->lang->line('pet_avatar_rotate_left'); ?>
							</button>
							<button type="button" class="btn btn-outline-secondary pet-avatar-rotate" data-deg="90">
								<i class="fas fa-redo" aria-hidden="true"></i> <?php echo $this->lang->line('pet_avatar_rotate_right'); ?>
							</button>
						</div>
					</div>
					<input type="hidden" id="petAvatarCrop" name="pet_avatar_crop">
				</form>
			</div>
			<div class="modal-footer d-flex flex-wrap">
				<?php if ($has_pet_avatar): ?>
					<form action="<?php echo base_url('pets/remove_avatar/' . (int) $pet['id']); ?>" method="post" class="mr-auto" onsubmit="return confirm('<?php echo html_escape($this->lang->line('pet_avatar_remove_confirm')); ?>');">
						<button type="submit" class="btn btn-outline-danger"><?php echo $this->lang->line('pet_avatar_remove'); ?></button>
					</form>
				<?php else: ?>
					<span class="mr-auto"></span>
				<?php endif; ?>
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $this->lang->line('pet_avatar_cancel'); ?></button>
				<button type="button" class="btn btn-primary" id="petAvatarSave" form="petAvatarUploadForm" disabled><?php echo $this->lang->line('pet_avatar_save'); ?></button>
			</div>
		</div>
	</div>
</div>
