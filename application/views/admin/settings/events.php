<strong>Events</strong>
<p>Events are linked to bills and are essential for tracing patients.</p>

<div class="list-group mb-5 shadow">
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Auto close reports</strong>
                <p class="text-muted mb-0">Reports that are never finished get auto closed after x days. (0 : disabled)</p>
            </div>
            <div class="col-auto">
            <input type="text" class="form-control" id="autoclose" name="conf_autoclose" value="<?php echo (isset($config['autoclose'])) ? base64_decode($config['autoclose']) : ''; ?>" autocomplete="autoclose" placeholder="14">
            </div>
        </div>
    </div>
    
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Auto disable report</strong>
                <p class="text-muted mb-0">If a report doesn't contain any valid data, set it to disabled on autoclose.</p>
            </div>
            <div class="col-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="autodisable" name="check_autodisable" <?php echo (isset($config['autodisable']) && base64_decode($config['autodisable'])) ? 'checked' : ''; ?>>
                    <label class="custom-control-label" for="autodisable"></label>
                </div>
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Report template</strong>
                <p class="text-muted mb-0">Must be in html format</p>
            </div>
            <div class="col">
            <textarea type="text" class="form-control" id="autotemplate" name="conf_autotemplate" autocomplete="autotemplate"><?php echo (isset($config['autotemplate'])) ? base64_decode($config['autotemplate']) : ''; ?></textarea>
            </div>
        </div>
    </div>
</div>