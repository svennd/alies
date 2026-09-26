<?php $label = fn($key) => html_escape($this->lang->line('doc_' . $key)); ?>
<div class="card shadow mb-4">
    <div class="card-header"><h1 class="h4 mb-0"><?= $label('center') ?></h1></div>
    <div class="card-body">
        <nav class="nav nav-pills mb-4" aria-label="<?= $label('center') ?>">
            <?php foreach (['branding', 'invoice', 'reminder', 'overview'] as $tab): ?>
                <a class="nav-link <?= $scope === $tab ? 'active' : '' ?>" href="<?= base_url('document_center/index/' . $tab) ?>"><?= $label($tab) ?></a>
            <?php endforeach; ?>
        </nav>
        <?php if ($document_error): ?><div class="alert alert-danger" role="alert"><?= html_escape($document_error) ?></div><?php endif; ?>
        <?php if ($document_success): ?><div class="alert alert-success" role="status"><?= $label('saved') ?></div><?php endif; ?>
        <p class="text-muted"><?= $label('font_hint') ?></p>
        <form method="post" enctype="multipart/form-data" action="<?= base_url('document_center/index/' . $scope) ?>">
            <input type="hidden" name="document_token" value="<?= html_escape($document_token) ?>">
            <div class="row">
            <?php foreach ($document_settings[$scope] as $key => $value): if ($key === 'logo') continue; ?>
                <div class="form-group col-md-<?= is_bool($value) || is_int($value) ? '4' : '12' ?>">
                    <?php if (is_bool($value)): ?>
                        <div class="custom-control custom-checkbox mt-3">
                            <input class="custom-control-input" type="checkbox" id="doc-<?= $key ?>" name="fields[<?= $key ?>]" value="1" <?= $value ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="doc-<?= $key ?>"><?= $label($key) ?></label>
                        </div>
                    <?php else: ?>
                        <label for="doc-<?= $key ?>"><?= $label($key) ?></label>
                        <?php if (is_int($value)): $bounds = ['font_size' => [8,14], 'margin' => [12,30], 'spacing' => [1,10]][$key]; ?>
                            <input class="form-control" type="number" id="doc-<?= $key ?>" name="fields[<?= $key ?>]" value="<?= $value ?>" min="<?= $bounds[0] ?>" max="<?= $bounds[1] ?>" required>
                        <?php elseif ($key === 'accent'): ?>
                            <input class="form-control" type="color" id="doc-accent" name="fields[accent]" value="<?= html_escape($value) ?>">
                        <?php else: ?>
                            <textarea class="form-control" id="doc-<?= $key ?>" name="fields[<?= $key ?>]" rows="<?= $key === 'body' ? '5' : '2' ?>" maxlength="<?= $key === 'body' ? '4000' : '600' ?>"><?= html_escape($value) ?></textarea>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
            <?php if ($scope === 'branding'): ?>
                <div class="form-group"><label for="document-logo"><?= $label('logo') ?></label><input id="document-logo" class="form-control-file" type="file" name="logo" accept="image/png,image/jpeg"></div>
                <?php if ($document_settings['branding']['logo']): ?>
                    <img src="<?= base_url('document_center/logo') ?>" alt="<?= $label('logo') ?>" style="max-width:180px;max-height:100px" class="mb-3">
                    <label><input type="checkbox" name="remove_logo" value="1"> <?= $label('remove_logo') ?></label>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($scope === 'reminder'): ?><p><?= $label('placeholders') ?>: <code>{recipient} {pet} {vaccine} {disease} {due_date}</code></p><?php endif; ?>
            <div class="border-top pt-3 mt-3 row">
                <div class="form-group col-md-6"><label for="record-id"><?= $label('record_id') ?></label><input id="record-id" class="form-control" type="number" min="1" name="record_id"></div>
                <?php if ($scope === 'reminder'): ?><div class="form-group col-md-6"><label for="preview-month"><?= $label('month') ?></label><input id="preview-month" class="form-control" type="number" name="month" value="0" min="-1200" max="1200"></div><?php endif; ?>
            </div>
            <button class="btn btn-primary" name="action" value="save"><?= $label('save') ?></button>
            <button class="btn btn-outline-primary" name="action" value="preview" formtarget="_blank"><?= $label('preview') ?></button>
            <button class="btn btn-outline-secondary float-md-right" name="action" value="restore" formnovalidate><?= $label('restore') ?></button>
        </form>
    </div>
</div>
