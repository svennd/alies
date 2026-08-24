<style>
.dropbox {
	border: 2px dashed #d3d8e8;
	min-height: 110px;
}
.dropbox:hover,
.dropbox.drag-over {
	border-color: #6984da;
	color: #6984da;
}
.event-attachment-preview {
	width: 96px;
	height: 96px;
	object-fit: cover;
}
.event-lab-results {
	max-height: 72vh;
	overflow-y: auto;
}
.event-lab-results .table {
	font-size: .82rem;
}
.event-lab-result-out {
	color: #e74a3b;
	font-weight: 700;
}
</style>

<div class="col-lg-12">
	<?php include 'report/block_report_header.php'; ?>

	<?php if (!empty($event_lab_message)): ?>
		<div class="alert alert-<?php echo html_escape(in_array($event_lab_message_type, array('success', 'danger'), true) ? $event_lab_message_type : 'info'); ?> alert-dismissible fade show" role="alert">
			<?php echo html_escape($event_lab_message); ?>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		</div>
	<?php endif; ?>

	<div class="card shadow mb-4">
		<form action="<?php echo base_url('events_report/update_report/' . $event_id); ?>" method="post" autocomplete="off">
			<div class="card-body">
				<?php include 'report/block_report.php'; ?>
				<hr />
				<div class="row">
					<div class="col-lg-8">
						<input type="hidden" name="pet_id" value="<?php echo (int) $pet['id']; ?>" />
						<button type="submit" name="submit" value="report" class="btn btn-outline-success" id="save_report_submit"><i class="fas fa-save"></i> <?php echo $this->lang->line('save_report'); ?></button>

						<?php if ((int) $event_info['report'] === REPORT_DONE): ?>
							<input type="hidden" name="finished" value="1" />
						<?php else: ?>
							<button type="submit" name="submit" value="finished_report" id="finish_report" class="btn btn-outline-primary float-right"><i class="fas fa-clipboard-check"></i> <?php echo $this->lang->line('finish'); ?></button>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</form>
	</div>

	<form id="event-lab-link-form" action="<?php echo base_url('events/link_lab/' . $event_id); ?>" method="post"></form>
	<?php foreach ($linked_labs as $linked_lab): ?>
		<form id="event-lab-unlink-<?php echo (int) $linked_lab['id']; ?>" action="<?php echo base_url('events/unlink_lab/' . $event_id . '/' . (int) $linked_lab['id']); ?>" method="post"></form>
	<?php endforeach; ?>
</div>
