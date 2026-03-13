<?php if ($buff['status'] == 'SENT'): ?>
	<span class="badge badge-pill badge-success">sent</span>
<?php elseif ($buff['status'] == 'ERROR'): ?>
	    <span class="badge badge-pill badge-danger"
          data-toggle="tooltip"
          title="<?= htmlspecialchars($buff['api_error'] ?? 'Unknown error') ?>">
        error
    </span>
<?php elseif ($buff['status'] == 'DRAFT'): ?>
	<span class="badge badge-pill badge-warning">draft</span>
<?php elseif ($buff['status'] == 'INVALID'): ?>
	<span class="badge badge-pill badge-secondary">invalid</span>
<?php endif; ?>