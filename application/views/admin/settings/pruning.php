
<strong>Pruning</strong>
<p>This will, when enabled, automatically delete records from the database. <strong>0 means disabled</strong>.</p>

<div class="list-group mb-5 shadow">
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Prune</strong>
                <p class="text-muted mb-0">Enable pruning</p>
            </div>
            <div class="col-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="pruning" name="check_pruning" <?php echo (isset($config['pruning']) && base64_decode($config['pruning'])) ? 'checked' : ''; ?>>
                    <label class="custom-control-label" for="pruning"></label>
                </div>
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Global logs</strong>
                <p class="text-muted mb-0">Remove <a href="<?php echo base_url('logs/nlog'); ?>">global logs</a> after x days.</p>
            </div>
            <div class="col-auto">
                <div class="input-group">
                    <input type="text" class="form-control" id="prune_global_log" name="conf_prune_global_log" value="<?php echo (isset($config['prune_global_log'])) ? base64_decode($config['prune_global_log']) : ''; ?>" autocomplete="prune_global_log" placeholder="90">
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">days</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Stock</strong>
                <p class="text-muted mb-0">History stock lines.</p>
            </div>
            <div class="col-auto">
                <div class="input-group">
                    <input type="text" class="form-control" id="prune_stock" name="conf_prune_stock" value="<?php echo (isset($config['prune_stock'])) ? base64_decode($config['prune_stock']) : ''; ?>" autocomplete="prune_stock" placeholder="5">
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">years</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Stock logs</strong>
                <p class="text-muted mb-0">Detailed transaction logs.</p>
            </div>
            <div class="col-auto">
                <div class="input-group">
                    <input type="text" class="form-control" id="prune_stock_log" name="conf_prune_stock_log" value="<?php echo (isset($config['prune_stock_log'])) ? base64_decode($config['prune_stock_log']) : ''; ?>" autocomplete="prune_stock_log" placeholder="365">
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">days</span>
                    </div>
                </div>
           </div>
        </div>
    </div>
</div>