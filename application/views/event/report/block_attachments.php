<div class="mt-3" id="event-attachments">
	<div class="d-flex align-items-center justify-content-between mb-2">
		<strong><?php echo $this->lang->line('attachments'); ?></strong>
		<?php if ($event_uploads): ?><span class="badge badge-info"><?php echo count($event_uploads); ?></span><?php endif; ?>
	</div>

	<?php if ($event_uploads): ?>
		<div class="d-flex flex-wrap">
			<?php foreach ($event_uploads as $upload): ?>
				<?php $previewable = in_array($upload['mime'], array('image/jpeg', 'image/png', 'image/gif'), true); ?>
				<div id="upload_<?php echo (int) $upload['id']; ?>" class="border rounded p-2 mr-2 mb-2" style="max-width: 180px;">
					<?php if ($previewable): ?>
						<a href="<?php echo base_url('files/preview/' . (int) $upload['id']); ?>" target="_blank" rel="noopener" class="d-block mb-1">
							<img loading="lazy" class="event-attachment-preview img-thumbnail" src="<?php echo base_url('files/preview/' . (int) $upload['id']); ?>" alt="<?php echo html_escape($upload['filename']); ?>" />
						</a>
					<?php else: ?>
						<div class="event-attachment-preview d-flex align-items-center justify-content-center bg-light border rounded mb-1" aria-hidden="true"><i class="far fa-file fa-3x"></i></div>
					<?php endif; ?>
					<a class="small d-block text-truncate" href="<?php echo base_url('files/get_file/' . (int) $upload['id']); ?>" title="<?php echo html_escape($upload['filename']); ?>"><?php echo html_escape($upload['filename']); ?></a>
					<button type="button" class="file_line btn btn-sm btn-outline-danger mt-1" id="del_<?php echo (int) $upload['id']; ?>" aria-label="Delete <?php echo html_escape($upload['filename']); ?>"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
