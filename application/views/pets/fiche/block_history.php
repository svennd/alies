
<?php
$history_veterinarians = array();

foreach ((array) $pet_history as $history_item) {
	foreach ((isset($history_item['veterinarians']) ? $history_item['veterinarians'] : array()) as $veterinarian) {
		$filter_token = isset($veterinarian['filter_token']) ? $veterinarian['filter_token'] : '';
		$veterinarian_name = isset($veterinarian['name']) ? trim($veterinarian['name']) : '';

		if ($filter_token !== '' && $veterinarian_name !== '') {
			$history_veterinarians[$filter_token] = $veterinarian_name;
		}
	}
}

uasort($history_veterinarians, function ($first_name, $second_name) {
	return strcasecmp($first_name, $second_name);
});
?>

<!-- phone only links -->
<a href="<?php echo base_url(); ?>events/new_event/<?php echo $pet['id']; ?>" class="btn btn-success mb-3 d-block d-sm-none d-md-none"><i class="fas fa-user-md"></i> <?php echo $this->lang->line('consult'); ?></a>
<a href="<?php echo base_url('pets/edit/'. $pet['id']); ?>" class="btn btn-info mb-3 d-block d-sm-none d-md-none"><i class="fas fa-paw"></i> <?php echo $this->lang->line('edit_pet'); ?></a>

<style>
.pet-history {
	--history-border: #e3e6f0;
	--history-detail-background: #f8f9fc;
}

.pet-history__header {
	gap: .75rem;
}

.pet-history__filters {
	display: flex;
	flex-wrap: wrap;
	gap: .5rem;
}

.pet-history__filter {
	min-width: 10rem;
	width: auto;
}

.pet-history__entry {
	border: 1px solid var(--history-border);
	border-radius: .5rem;
	box-shadow: 0 .125rem .35rem rgba(58, 59, 69, .05);
	overflow: hidden;
}

.pet-history__entry + .pet-history__entry {
	margin-top: .75rem;
}

.pet-history__entry.is-expanded {
	box-shadow: 0 .2rem .55rem rgba(58, 59, 69, .1);
}

.pet-history__summary {
	display: grid;
	grid-template-columns: minmax(0, 1fr) auto;
	align-items: center;
	gap: 0;
	background: #fff;
}

.pet-history__summary-toggle {
	display: grid;
	grid-template-columns: minmax(7rem, .75fr) minmax(12rem, 1.8fr) minmax(9rem, 1.1fr) minmax(8rem, 1fr);
	align-items: center;
	gap: 1rem;
	width: 100%;
	padding: 1rem;
	border: 0;
	background: #fff;
	color: inherit;
	cursor: pointer;
	text-align: left;
}

.pet-history__summary-toggle:hover {
	background: #fdfdfe;
}

.pet-history__summary-toggle:focus {
	position: relative;
	z-index: 1;
	outline: 2px solid #4e73df;
	outline-offset: -2px;
}

.pet-history__title {
	min-width: 0;
	font-weight: 600;
	color: #3a3b45;
}

.pet-history__title-text {
	overflow-wrap: anywhere;
}

.pet-history__vets {
	min-width: 0;
	color: #6e707e;
	overflow-wrap: anywhere;
}

.pet-history__location {
	min-width: 0;
	color: #6e707e;
	overflow-wrap: anywhere;
}

.pet-history__actions {
	display: flex;
	align-items: center;
	justify-content: flex-end;
	gap: .35rem;
	padding: .75rem 1rem .75rem .35rem;
	background: #fff;
}

.pet-history__details {
	padding: 1.25rem;
	border-top: 1px solid var(--history-border);
	background: var(--history-detail-background);
}

.pet-history__detail-group + .pet-history__detail-group {
	margin-top: 1.25rem;
}

.pet-history__detail-heading {
	margin-bottom: .5rem;
	color: #858796;
	font-size: .75rem;
	font-weight: 700;
	letter-spacing: .04em;
	text-transform: uppercase;
}

.pet-history__clinical-report > :last-child,
.pet-history__details ul:last-child {
	margin-bottom: 0;
}

.pet-history__empty-filter {
	padding: 2rem 1rem;
	text-align: center;
	color: #6e707e;
}

.pet-history [hidden] {
	display: none !important;
}

.pet-history-procedure {
	border-left: 2px solid #1cc88a;
}

@media (max-width: 767.98px) {
	.pet-history__header {
		align-items: stretch !important;
		flex-direction: column;
	}

	.pet-history__filters,
	.pet-history__edit,
	.pet-history__eye-toggle {
		display: none !important;
	}

	.pet-history__summary {
		grid-template-columns: 1fr auto;
	}

	.pet-history__summary-toggle {
		grid-template-columns: 1fr;
		gap: .5rem;
	}

	.pet-history__date,
	.pet-history__title,
	.pet-history__vets,
	.pet-history__location {
		grid-column: 1;
	}

	.pet-history__actions {
		grid-column: 2;
		grid-row: 1;
		align-self: center;
		padding-left: 0;
	}

	.pet-history__actions--no-mobile {
		display: none;
	}
}
</style>

<!-- full screen -->
<section class="pet-history card mb-4" id="pet-medical-history">
	<div class="pet-history__header card-header d-flex align-items-center justify-content-between">
		<h3 class="h5 mb-0 text-gray-800"><?php echo $this->lang->line('medical_history'); ?></h3>

		<?php if ($pet_history): ?>
			<div class="pet-history__filters">
				<label class="sr-only" for="pet-history-type-filter"><?php echo $this->lang->line('type'); ?></label>
				<select class="pet-history__filter form-control form-control-sm" id="pet-history-type-filter">
					<option value="all"><?php echo $this->lang->line('history_all_types'); ?></option>
					<option value="<?php echo DISEASE; ?>"><?php echo ucfirst($this->lang->line('disease')); ?></option>
					<option value="<?php echo OPERATION; ?>"><?php echo $this->lang->line('history_operations'); ?></option>
				</select>

				<label class="sr-only" for="pet-history-vet-filter"><?php echo $this->lang->line('vet'); ?></label>
				<select class="pet-history__filter form-control form-control-sm" id="pet-history-vet-filter">
					<option value="all"><?php echo $this->lang->line('history_all_vets'); ?></option>
					<?php foreach ($history_veterinarians as $veterinarian_id => $veterinarian_name): ?>
						<option value="<?php echo html_escape($veterinarian_id); ?>"><?php echo html_escape($veterinarian_name); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		<?php endif; ?>
	</div>

	<div class="card-body">
		<?php if ($pet_history): ?>
			<div class="pet-history__entries">
				<?php foreach ($pet_history as $history_index => $history): ?>
					<?php
					$products = isset($history['products']) ? $history['products'] : array();
					$procedures = isset($history['procedures']) ? $history['procedures'] : array();
					$report_text = isset($history['anamnese']) ? trim($history['anamnese']) : '';
					$location_name = isset($history['location_name']) ? trim($history['location_name']) : '';
					$upload_count = isset($history['upload_count']) ? (int) $history['upload_count'] : 0;
					$lab_count = isset($history['lab_count']) ? (int) $history['lab_count'] : 0;
					$entry_veterinarian_tokens = array();
					$entry_veterinarian_names = array();

					foreach ((isset($history['veterinarians']) ? $history['veterinarians'] : array()) as $veterinarian) {
						$filter_token = isset($veterinarian['filter_token']) ? $veterinarian['filter_token'] : '';
						$veterinarian_name = isset($veterinarian['name']) ? trim($veterinarian['name']) : '';

						if ($filter_token !== '' && !in_array($filter_token, $entry_veterinarian_tokens, true)) {
							$entry_veterinarian_tokens[] = $filter_token;
						}

						if ($veterinarian_name !== '' && !in_array($veterinarian_name, $entry_veterinarian_names, true)) {
							$entry_veterinarian_names[] = $veterinarian_name;
						}
					}

					$details_id = 'pet-history-details-' . (int) $history['id'];
					$is_initially_visible = ($history_index < 10);
					?>
					<article
						class="pet-history__entry <?php echo !$history['type'] ? 'pet-history-disease' : 'pet-history-procedure'; ?>"
						data-history-type="<?php echo (int) $history['type']; ?>"
						data-veterinarians="<?php echo html_escape(implode(',', $entry_veterinarian_tokens)); ?>"
						<?php echo $is_initially_visible ? '' : 'hidden'; ?>
					>
						<div class="pet-history__summary">
							<button
								type="button"
								class="pet-history__summary-toggle pet-history__toggle"
								aria-expanded="false"
								aria-controls="<?php echo $details_id; ?>"
							>
								<span class="pet-history__date">
									<?php echo html_escape(user_format_date($history['created_at'], $user->user_date)); ?>
								</span>
								<span class="pet-history__title d-flex align-items-center">
									<span class="mr-2" aria-hidden="true"><?php echo get_event_type($history['type']); ?></span>
									<span class="pet-history__title-text"><?php echo html_escape($history['title']); ?> 
								<?php if ($lab_count > 0): ?>
									<a href="<?php echo base_url('events/event/' . (int) $history['id'] . '#event-lab-results'); ?>" class="btn btn-sm btn-outline-primary">
										<i class="fas fa-flask" aria-hidden="true"></i>
										<span class="sr-only"><?php echo $this->lang->line('event_labs'); ?>:</span>
										<?php echo $lab_count; ?>
									</a>
								<?php endif; ?>
								<?php if ($upload_count > 0): ?>
									<a href="<?php echo base_url('events/event/' . (int) $history['id'] . '#files'); ?>" class="btn btn-sm btn-outline-success">
										<i class="fa-solid fa-download" aria-hidden="true"></i>
										<span class="sr-only"><?php echo $this->lang->line('attachments'); ?>:</span>
										<?php echo $upload_count; ?>
									</a>
								<?php endif; ?></span>
									<?php if ((int) $history['report'] !== REPORT_DONE): ?>
										<i class="fas fa-unlock ml-2 text-warning" data-toggle="tooltip" data-placement="top" title="<?php echo html_escape($this->lang->line('not_finished')); ?>">
											<span class="sr-only"><?php echo $this->lang->line('not_finished'); ?></span>
										</i>
									<?php endif; ?>
								</span>
								<span class="pet-history__vets">
									<i class="fa-solid fa-user-doctor mr-1" aria-hidden="true"></i>
									<?php echo $entry_veterinarian_names ? html_escape(implode(', ', $entry_veterinarian_names)) : 'unknown'; ?>
								</span>
								<span class="pet-history__location">
									<?php if ($location_name !== ''): ?>
										<i class="fa-solid fa-location-dot mr-1" aria-hidden="true"></i><?php echo html_escape($location_name); ?>
									<?php endif; ?>
								</span>
							</button>
							<div class="pet-history__actions<?php echo ($upload_count > 0 || $lab_count > 0) ? '' : ' pet-history__actions--no-mobile'; ?>">
								<button
									type="button"
									class="pet-history__eye-toggle pet-history__toggle btn btn-sm btn-outline-primary"
									aria-expanded="false"
									aria-controls="<?php echo $details_id; ?>"
								>
									<i class="fa-solid fa-eye" aria-hidden="true"></i>
									<span class="sr-only"><?php echo $this->lang->line('anamnese'); ?></span>
								</button>
								<a href="<?php echo base_url('events/event/' . (int) $history['id']); ?>" class="pet-history__edit btn btn-sm <?php echo ((int) $history['report'] === REPORT_DONE) ? 'btn-outline-secondary not-allowed' : 'btn-outline-success'; ?>">
									<i class="fa-solid fa-pen" aria-hidden="true"></i>
									<span class="sr-only"><?php echo $this->lang->line('edit'); ?></span>
								</a>
							</div>
						</div>

						<div class="pet-history__details" id="<?php echo $details_id; ?>" hidden>
							<?php if ($report_text !== ''): ?>
								<div class="pet-history__detail-group">
									<h4 class="pet-history__detail-heading"><?php echo $this->lang->line('report'); ?></h4>
									<div class="pet-history__clinical-report"><?php echo nl2br($history['anamnese']); ?></div>
								</div>
							<?php endif; ?>

							<?php if ($products || $procedures): ?>
								<div class="pet-history__detail-group">
									<h4 class="pet-history__detail-heading"><?php echo $this->lang->line('history_treatments'); ?></h4>
									<ul class="pl-3">
										<?php foreach ($products as $product): ?>
											<li><?php echo html_escape(trim((isset($product['volume']) ? $product['volume'] : '') . ' ' . (isset($product['unit_sell']) ? $product['unit_sell'] : '') . ' ' . (isset($product['name']) ? $product['name'] : ''))); ?></li>
										<?php endforeach; ?>
										<?php foreach ($procedures as $procedure): ?>
											<li><?php echo html_escape(trim((isset($procedure['volume']) ? $procedure['volume'] : '') . ' ' . (isset($procedure['name']) ? $procedure['name'] : ''))); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="pet-history__empty-filter" hidden>
				<p><?php echo $this->lang->line('history_no_matches'); ?></p>
				<button type="button" class="pet-history__reset btn btn-sm btn-outline-primary">
					<i class="fa-solid fa-undo-alt mr-1" aria-hidden="true"></i><?php echo $this->lang->line('history_reset_filters'); ?>
				</button>
			</div>

			<div class="text-center mt-3">
				<button type="button" class="pet-history__show-more btn btn-sm btn-outline-primary" hidden>
					<?php echo $this->lang->line('history_show_more'); ?>
					<i class="fa-solid fa-chevron-down ml-1" aria-hidden="true"></i>
				</button>
			</div>
		<?php else: ?>
			<?php echo $this->lang->line('no_history_txt'); ?>
		<?php endif; ?>
	</div>
</section>

<?php if ($pet_history): ?>
	<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function () {
		var $history = $('#pet-medical-history');
		var $entries = $history.find('.pet-history__entry');
		var $typeFilter = $('#pet-history-type-filter');
		var $veterinarianFilter = $('#pet-history-vet-filter');
		var $emptyState = $history.find('.pet-history__empty-filter');
		var $showMore = $history.find('.pet-history__show-more');
		var visibleLimit = 10;

		function closeAllEntries() {
			$entries.removeClass('is-expanded');
			$entries.find('.pet-history__details').prop('hidden', true);
			$entries.find('.pet-history__toggle')
				.attr('aria-expanded', 'false');
			$entries.find('.pet-history__eye-toggle i')
				.removeClass('fa-eye-slash')
				.addClass('fa-eye');
		}

		function openEntry($entry) {
			closeAllEntries();
			$entry.addClass('is-expanded');
			$entry.find('.pet-history__details').prop('hidden', false);
			$entry.find('.pet-history__toggle')
				.attr('aria-expanded', 'true');
			$entry.find('.pet-history__eye-toggle i')
				.removeClass('fa-eye')
				.addClass('fa-eye-slash');
		}

		function matchingEntries() {
			var selectedType = $typeFilter.val();
			var selectedVeterinarian = $veterinarianFilter.val();

			return $entries.filter(function () {
				var $entry = $(this);
				var typeMatches = selectedType === 'all' || $entry.attr('data-history-type') === selectedType;
				var veterinarianIds = ($entry.attr('data-veterinarians') || '').split(',');
				var veterinarianMatches = selectedVeterinarian === 'all' || veterinarianIds.indexOf(selectedVeterinarian) !== -1;

				return typeMatches && veterinarianMatches;
			});
		}

		function updateHistory(closeExpanded) {
			var $matches = matchingEntries();
			var $visibleMatches = $matches.slice(0, visibleLimit);

			$entries.prop('hidden', true);
			$visibleMatches.prop('hidden', false);
			$emptyState.prop('hidden', $matches.length > 0);
			$showMore.prop('hidden', $matches.length <= visibleLimit);

			if (closeExpanded) {
				closeAllEntries();
			}
		}

		$history.on('click', '.pet-history__toggle', function () {
			var $entry = $(this).closest('.pet-history__entry');
			var isExpanded = $(this).attr('aria-expanded') === 'true';

			if (isExpanded) {
				closeAllEntries();
			} else {
				openEntry($entry);
			}
		});

		$typeFilter.add($veterinarianFilter).on('change', function () {
			visibleLimit = 10;
			updateHistory(true);
		});

		$history.on('click', '.pet-history__reset', function () {
			$typeFilter.val('all');
			$veterinarianFilter.val('all');
			visibleLimit = 10;
			updateHistory(true);
			$typeFilter.trigger('focus');
		});

		$showMore.on('click', function () {
			visibleLimit += 10;
			updateHistory(false);
		});

		updateHistory(true);
		$history.find('[data-toggle="tooltip"]').tooltip();
	});
	</script>
<?php endif; ?>
