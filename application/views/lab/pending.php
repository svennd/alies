<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url('lab'); ?>"><?php echo $this->lang->line('Lab'); ?></a> / <?php echo $this->lang->line('lab_pending_title'); ?></div>
			</div>
			<div class="card-body">
				<?php if (!empty($pending_message)): ?>
					<div class="alert alert-<?php echo html_escape($pending_message_type ?: 'info'); ?>" role="alert">
						<?php echo html_escape($pending_message); ?>
					</div>
				<?php endif; ?>

				<?php if ($pending_results): ?>
					<div class="table-responsive">
						<table class="table table-sm" id="pendingLabTable">
							<thead>
								<tr>
									<th><?php echo $this->lang->line('lab_last_received'); ?></th>
									<th><?php echo $this->lang->line('source'); ?></th>
									<th><?php echo $this->lang->line('lab_pending_identifiers'); ?></th>
									<th><?php echo $this->lang->line('reason'); ?></th>
									<th><?php echo $this->lang->line('lab_pending_action'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($pending_results as $pending): ?>
									<tr>
										<!-- <td><?php echo (int) $pending['id']; ?></td> -->
										<?php $received_at = $pending['last_received_at'] ?? $pending['created_at']; ?>
										<td data-sort="<?php echo strtotime($received_at); ?>"><?php echo html_escape($received_at); ?></td>
										<td>
											<?php echo html_escape($pending['device'] ?: $pending['source'] ?: '-'); ?>
											<?php if (!empty($pending['source_id'])): ?><br><small>#<?php echo html_escape($pending['source_id']); ?></small><?php endif; ?>
										</td>
										<td>
											<?php if ($pending['identifiers']): ?>
												<ul class="list-unstyled mb-0">
													<?php foreach ($pending['identifiers'] as $key => $value): ?>
														<li><small><strong><?php echo html_escape(str_replace('_', ' ', $key)); ?>:</strong> <?php echo html_escape((string) $value); ?></small></li>
													<?php endforeach; ?>
												</ul>
											<?php else: ?>-<?php endif; ?>
										</td>
										<td><?php echo html_escape($pending['reason']); ?></td>
										<td>
											<a href="<?php echo base_url('lab/pending_detail/' . (int) $pending['id']); ?>" class="btn btn-sm btn-outline-primary">
												<i class="fa-solid fa-magnifying-glass"></i> <?php echo $this->lang->line('lab_pending_inspect'); ?>
											</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="text-center text-muted py-5">
						<i class="fa-solid fa-flask-circle-check fa-2x mb-3"></i>
						<p class="mb-0"><?php echo $this->lang->line('lab_pending_empty'); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#labo").addClass('active');
	$("#pendingLabTable").DataTable({responsive: true, order: [[0, "desc"]]});
});
</script>
