<?php $collapse_id = 'event-lab-results-bill'; ?>



<section class="border rounded bg-light event-lab-results" id="event-lab-results" aria-labelledby="event-lab-heading">

	<div class="px-2 pb-2 border-bottom bg-white d-flex align-items-center" data-toggle="collapse" data-target="#<?php echo $collapse_id; ?>" aria-controls="<?php echo $collapse_id; ?>">
		<div class="flex-grow-1"><i class="fa-solid fa-euro-sign"></i> &middot; <?php echo $this->lang->line('bill'); ?></div>
		<button type="button" class="btn btn-sm btn-link" aria-expanded="true"><i class="fas fa-chevron-down" aria-hidden="true"></i></button>
	</div>

	<div id="event-lab-accordion">
		<!-- always show bill -->
		<article class="border-bottom bg-white">
			<div id="<?php echo $collapse_id; ?>" class="collapse show p-2" data-parent="#event-lab-accordion">
				<?php if($consumables): ?>
					<?php foreach($consumables as $prod): ?>
						<p class="list-group-item list-group-item-action list-group-hack"><?php echo $prod['volume'] . ' ' . $prod['product_unit_sell']  . ' ' . $prod['product_name']; ?></p>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if($procedures_d): ?>
						<?php foreach($procedures_d as $proc): ?>
							<p class="list-group-item list-group-item-action list-group-hack"><?php echo $proc['procedures']['name']; ?></p>
						<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</article>
		<?php foreach ($linked_labs as $index => $lab): ?>
			<?php $collapse_id = 'event-lab-results-' . (int) $lab['id']; ?>
			<article class="border-bottom bg-white">
				<div class="d-flex align-items-center p-2" data-toggle="collapse" data-target="#<?php echo $collapse_id; ?>" aria-controls="<?php echo $collapse_id; ?>">
					<div class="flex-grow-1"><i class="fas fa-flask fa-fw" aria-hidden="true"></i> &middot; <?php echo html_escape(user_format_date($lab['sample_date'] ?: $lab['created_at'], $user->user_date)); ?></div>
					<small class="text-muted d-block"><?php echo html_escape($lab['device'] ?: $lab['source']); ?></small>
					<button type="button" class="btn btn-sm btn-link"  aria-expanded="false" aria-label="<?php echo $this->lang->line('event_labs'); ?> #<?php echo (int) $lab['id']; ?>"><i class="fas fa-chevron-down" aria-hidden="true"></i></button>
				</div>

				<div id="<?php echo $collapse_id; ?>" class="collapse" data-parent="#event-lab-accordion">
					<div class="px-2 pb-2">
						<?php if ($lab['results']): ?>
							<div class="table-responsive">
								<table class="table table-sm mb-2">
									<thead><tr><th><?php echo $this->lang->line('lab_code'); ?></th><th><?php echo $this->lang->line('value'); ?></th><th><?php echo $this->lang->line('limit'); ?></th><th><?php echo $this->lang->line('unit'); ?></th></tr></thead>
									<tbody>
									<?php foreach ($lab['results'] as $result): ?>
										<tr>
											<td><?php echo html_escape($result['code']); ?></td>
											<td class="<?php echo $result['is_out'] ? 'event-lab-result-out' : ''; ?>"><?php echo html_escape($result['value']); ?></td>
											<td><?php echo html_escape($result['limit']); ?></td>
											<td><?php echo html_escape($result['unit']); ?></td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
						<div class="d-flex justify-content-between align-items-center">
							<a class="btn btn-sm btn-outline-primary" href="<?php echo base_url('lab/detail/' . (int) $lab['id']); ?>"><i class="fas fa-external-link-alt" aria-hidden="true"></i> <?php echo $this->lang->line('event_lab_open'); ?></a>
							<button class="btn btn-sm btn-outline-danger" type="submit" form="event-lab-unlink-<?php echo (int) $lab['id']; ?>" onclick="return confirm(<?php echo htmlspecialchars(json_encode($this->lang->line('event_lab_unlink_confirm')), ENT_QUOTES, 'UTF-8'); ?>);" aria-label="<?php echo html_escape($this->lang->line('event_lab_unlink')); ?> #<?php echo (int) $lab['id']; ?>"><i class="fas fa-unlink" aria-hidden="true"></i></button>
						</div>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
		<?php $collapse_id = 'event-lab-results-add-link'; ?>
		<article class="border-bottom bg-white">
			<div class="d-flex align-items-center p-2" data-toggle="collapse" data-target="#<?php echo $collapse_id; ?>" aria-controls="<?php echo $collapse_id; ?>">
				<div class="font-weight-bold flex-grow-1"><i class="fa-solid fa-vial-circle-check text-success"></i>  &middot;  <?php echo $this->lang->line('event_lab_link'); ?></div>
				<button type="button" class="btn btn-sm btn-link" aria-expanded="false"><i class="fas fa-chevron-down" aria-hidden="true"></i></button>
			</div>

			<div id="<?php echo $collapse_id; ?>" class="collapse" data-parent="#event-lab-accordion">
				<div class="form-group px-3">
					<label class="small" for="event_lab_id"><?php echo $this->lang->line('event_lab_choose'); ?></label>
					<select class="form-control form-control-sm" id="event_lab_id" name="lab_id" form="event-lab-link-form" required>
						<option value=""><?php echo $this->lang->line('event_lab_choose'); ?></option>
						<?php foreach ($linkable_labs as $lab): ?>
							<option value="<?php echo (int) $lab['id']; ?>">
								#<?php echo (int) $lab['id']; ?> - <?php echo html_escape(user_format_date($lab['sample_date'] ?: $lab['created_at'], $user->user_date)); ?> - <?php echo html_escape($lab['device'] ?: $lab['source']); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group px-3"><button class="btn btn-sm btn-outline-success" type="submit" form="event-lab-link-form"><i class="fas fa-link" aria-hidden="true"></i> <?php echo $this->lang->line('event_lab_link'); ?></button></div>
				</form>

			</div>
		</article>
	</div>
</section>
