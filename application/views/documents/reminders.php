<?php $label = fn($key) => html_escape($this->lang->line('doc_' . $key));
$selected = $selected ?? array_map('intval', array_column(array_slice($rows, 0, Document_data_model::REMINDER_BATCH_LIMIT), 'id')); ?>
<div class="card shadow mb-4">
    <div class="card-header"><h1 class="h4 mb-0"><?= $label('reminder') ?> — <?= Document_data_model::month($month)->format('m/Y') ?></h1></div>
    <div class="card-body">
        <?php if ($document_error): ?><div class="alert alert-danger" role="alert"><?= html_escape($document_error) ?></div><?php endif; ?>
        <p><?= $label('batch_hint') ?></p>
        <form method="post" action="<?= base_url('documents/reminders/' . $month) ?>">
            <input type="hidden" name="document_token" value="<?= html_escape($document_token) ?>">
            <div class="form-group"><label for="excluded-products"><?= $label('exclude') ?></label>
                <select id="excluded-products" class="form-control" name="excluded[]" multiple size="4">
                    <?php foreach ($products as $id => $name): ?><option value="<?= (int) $id ?>" <?= in_array((int) $id, $excluded, true) ? 'selected' : '' ?>><?= html_escape($name) ?></option><?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-outline-secondary mb-3" name="action" value="filter"><?= $label('filter') ?></button>
            <p><?= $label('eligible') ?>: <?= count($rows) ?> · <?= $label('selected') ?>: <span id="document-selected"><?= count($selected) ?></span> / <?= Document_data_model::REMINDER_BATCH_LIMIT ?></p>
            <?php if (!$rows): ?><div class="alert alert-info"><?= $label('empty') ?></div><?php else: ?>
            <div class="table-responsive"><table class="table table-sm">
                <thead><tr><th><?= $label('select') ?></th><th><?= $label('recipient') ?></th><th><?= $label('pet') ?></th><th><?= $label('vaccine') ?></th><th><?= $label('due_date') ?></th></tr></thead>
                <tbody><?php foreach ($rows as $row): ?><tr>
                    <td><input type="checkbox" name="rows[]" class="document-row" value="<?= (int) $row['id'] ?>" aria-label="<?= $label('select') ?> <?= (int) $row['id'] ?>" <?= in_array((int) $row['id'], $selected, true) ? 'checked' : '' ?>></td>
                    <td><?= html_escape($row['recipient']) ?></td><td><?= html_escape($row['pet']) ?></td><td><?= html_escape($row['vaccine']) ?></td><td><?= html_escape($row['due_date']) ?></td>
                </tr><?php endforeach; ?></tbody>
            </table></div>
            <button id="document-generate" class="btn btn-primary" name="action" value="generate"><?= $label('generate') ?></button>
            <?php endif; ?>
            <a class="btn btn-outline-secondary" href="<?= base_url('vaccine/index/' . $month) ?>"><?= $label('back') ?></a>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('.document-row');
    function update() {
        const count = document.querySelectorAll('.document-row:checked').length;
        document.getElementById('document-selected').textContent = count;
        const button = document.getElementById('document-generate');
        if (button) button.disabled = count === 0 || count > <?= Document_data_model::REMINDER_BATCH_LIMIT ?>;
    }
    rows.forEach(row => row.addEventListener('change', update));
    update();
});
</script>
