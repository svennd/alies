<style>
.lab-result-bar { width: 150px; height: 6px; position: relative; display: inline-flex; gap: 3px; }
.lab-result-bar .segment { height: 6px; flex-grow: 1; border-radius: 3px; }
.lab-result-bar .low, .lab-result-bar .high { background: #f8d3d3; }
.lab-result-bar .mid { background: #e2f5dc; }
.lab-result-bar .position { position: absolute; top: -2px; width: 2px; height: 10px; background: #333; }
.pending-raw-json { max-height: 32rem; overflow: auto; white-space: pre-wrap; word-break: break-word; }
</style>

<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex align-items-center justify-content-between">
				<div><a href="<?php echo base_url('lab/pending'); ?>"><?php echo $this->lang->line('lab_pending_title'); ?></a> / #<?php echo (int) $pending['id']; ?></div>
				<a href="<?php echo base_url('lab/pending'); ?>" class="btn btn-sm btn-outline-primary"><?php echo $this->lang->line('lab_pending_back'); ?></a>
			</div>
			<div class="card-body">
				<?php if (!empty($pending_message)): ?>
					<div class="alert alert-<?php echo html_escape($pending_message_type ?: 'info'); ?>"><?php echo html_escape($pending_message); ?></div>
				<?php endif; ?>
				<?php if (!empty($preview_warning)): ?>
					<div class="alert alert-warning"><?php echo html_escape($preview_warning); ?></div>
				<?php endif; ?>

				<table class="table table-sm">
					<tr><th><?php echo $this->lang->line('lab_received'); ?></th><td><?php echo html_escape($pending['created_at']); ?></td></tr>
					<tr><th><?php echo $this->lang->line('source'); ?></th><td><?php echo html_escape($preview['device'] ?: $preview['source'] ?: '-'); ?><?php if (!empty($preview['source_id'])): ?> (#<?php echo html_escape($preview['source_id']); ?>)<?php endif; ?></td></tr>
					<tr><th><?php echo $this->lang->line('reason'); ?></th><td><?php echo html_escape($pending['reason']); ?></td></tr>
					<tr><th><?php echo $this->lang->line('last_update'); ?></th><td><?php echo html_escape($preview['sample_date'] ?: '-'); ?></td></tr>
					<?php if (!empty($preview['software_version'])): ?><tr><th>software_version</th><td><?php echo html_escape($preview['software_version']); ?></td></tr><?php endif; ?>
				</table>

				<?php if ($identifiers): ?>
					<h6><?php echo $this->lang->line('lab_pending_identifiers'); ?></h6>
					<table class="table table-sm">
						<?php foreach ($identifiers as $key => $value): ?>
							<tr><th><?php echo html_escape(str_replace('_', ' ', $key)); ?></th><td><?php echo html_escape(is_scalar($value) ? (string) $value : json_encode($value)); ?></td></tr>
						<?php endforeach; ?>
					</table>
				<?php endif; ?>

				<h6><?php echo $this->lang->line('lab_pending_results'); ?></h6>
				<?php if ($preview['results']): ?>
					<div class="table-responsive"><table class="table table-sm">
						<thead><tr><th><?php echo $this->lang->line('lab_code'); ?></th><th>chart</th><th><?php echo $this->lang->line('value'); ?></th><th><?php echo $this->lang->line('limit'); ?></th><th><?php echo $this->lang->line('unit'); ?></th></tr></thead>
						<tbody><?php foreach ($preview['results'] as $result): ?><tr>
							<td><?php echo html_escape($result['code']); ?></td>
							<td><?php if ($result['draw_plot']): ?><div class="lab-result-bar"><span class="segment low"></span><span class="segment mid"></span><span class="segment high"></span><span class="position" style="left:<?php echo (float) $result['pct']; ?>%"></span></div><?php endif; ?></td>
							<td class="<?php echo $result['is_out'] ? 'text-danger font-weight-bold' : ''; ?>"><?php echo html_escape($result['value']); ?></td>
							<td><?php echo html_escape($result['limit']); ?></td>
							<td><?php echo html_escape($result['unit']); ?></td>
						</tr><?php endforeach; ?></tbody>
					</table></div>
				<?php else: ?><p class="text-muted"><?php echo $this->lang->line('lab_pending_no_results'); ?></p><?php endif; ?>

				<?php if ($preview['metadata']): ?>
					<h6><?php echo $this->lang->line('lab_pending_metadata'); ?></h6>
					<table class="table table-sm"><?php foreach ($preview['metadata'] as $key => $value): ?><tr><th><?php echo html_escape($key); ?></th><td><?php echo html_escape(is_scalar($value) || $value === null ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></td></tr><?php endforeach; ?></table>
				<?php endif; ?>

				<?php if ($preview['plots']): ?>
					<h6><?php echo $this->lang->line('lab_pending_plots'); ?></h6>
					<table class="table table-sm"><?php foreach ($preview['plots'] as $type => $values): ?><tr><th><?php echo html_escape($type); ?></th><td><?php echo html_escape(json_encode($values)); ?></td></tr><?php endforeach; ?></table>
				<?php endif; ?>

				<hr>
				<h6><?php echo $this->lang->line('lab_pending_assign'); ?></h6>
				<form action="<?php echo base_url('lab/recover_pending/' . (int) $pending['id']); ?>" method="post" autocomplete="off" id="pendingRecoveryForm">
					<div class="form-row">
						<div class="form-group col-md-6"><label for="pending_owner_id"><?php echo $this->lang->line('client'); ?></label><select id="pending_owner_id" name="owner_id" class="form-control owner-select" required></select></div>
						<div class="form-group col-md-6"><label for="pending_pet_id"><?php echo $this->lang->line('pet_info'); ?></label><select id="pending_pet_id" name="pet_id" class="form-control pet-select" disabled required></select></div>
					</div>
					<button type="submit" class="btn btn-success"><i class="fa-solid fa-link"></i> <?php echo $this->lang->line('lab_pending_recover'); ?></button>
				</form>
				<form action="<?php echo base_url('lab/delete_pending/' . (int) $pending['id']); ?>" method="post" class="mt-3" onsubmit="return confirm(<?php echo htmlspecialchars(json_encode($this->lang->line('lab_pending_delete_confirm')), ENT_QUOTES, 'UTF-8'); ?>);">
					<button type="submit" class="btn btn-outline-danger"><i class="fa-solid fa-trash"></i> <?php echo $this->lang->line('lab_pending_delete'); ?></button>
				</form>

				<details class="mt-4">
					<summary><?php echo $this->lang->line('lab_pending_raw_json'); ?></summary>
					<pre class="pending-raw-json bg-light border rounded p-3 mt-2"><code><?php echo html_escape($raw_json); ?></code></pre>
				</details>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#labo").addClass('active');
	const owner = $("#pending_owner_id");
	const pet = $("#pending_pet_id");
	owner.select2({theme: 'bootstrap4', placeholder: <?php echo json_encode($this->lang->line('lab_pending_select_owner')); ?>, minimumInputLength: 1, ajax: {url: <?php echo json_encode(base_url('lab/search_owners')); ?>, dataType: 'json'}});
	pet.select2({theme: 'bootstrap4', placeholder: <?php echo json_encode($this->lang->line('lab_pending_select_pet')); ?>, ajax: {url: <?php echo json_encode(base_url('lab/search_pets')); ?>, dataType: 'json', data: function(params){ return {term: params.term || '', owner_id: owner.val()}; }}});
	owner.on('change', function(){ pet.val(null).trigger('change'); pet.prop('disabled', !owner.val()); });
});
</script>
