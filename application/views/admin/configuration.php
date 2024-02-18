<style>
.list-group-item {
    margin-bottom: 0;
}
</style>

<div class="row">
	<div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('settings_screen'); ?></div>
				<div class="dropdown no-arrow">
				</div>
			</div>
            <div class="card-body">
<form method="post" action="<?php echo base_url('admin/settings'); ?>" autocomplete="integrations">
<strong>Invoicing</strong>
<p>These settings are dealing with invoicing, billing and invoice numbering.</p>

<div class="list-group mb-5 shadow">
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Maximum invoices</strong>
                <p class="text-muted mb-0">Maximum amount of invoices visible at once.</p>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control" id="RestrictBills" name="conf_RestrictBills" placeholder="250" value="<?php echo (isset($config['RestrictBills'])) ? base64_decode($config['RestrictBills']) : ''; ?>">
            </div>
        </div>
    </div>
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Due date</strong>
                <p class="text-muted mb-0">From the invoice date, howmany days are allowed to complete payment.</p>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control" id="due_date" name="conf_due_date" placeholder="30" value="<?php echo (isset($config['due_date'])) ? base64_decode($config['due_date']) : ''; ?>">
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Invoice id</strong>
                <p class="text-muted mb-0">Invoice id has YYYY prefix.</p>
            </div>
            <div class="col-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="invoice_prefix" name="check_invoice_prefix" <?php echo (isset($config['invoice_prefix']) && base64_decode($config['invoice_prefix'])) ? 'checked' : ''; ?>>
                    <label class="custom-control-label" for="invoice_prefix"></label>
                </div>
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Structured messages</strong>
                <p class="text-muted mb-0">Structured messages</p>
            </div>
            <div class="col-auto">
                <select class="custom-select" size="3">
                    <option value="<?php echo CLIENT; ?>" <?php echo (isset($config['struct_config']) && base64_decode($config['struct_config']) == CLIENT) ? 'selected' : ''; ?>>Client ID</option>
                    <option value="<?php echo CLIENT_BILL; ?>" <?php echo (isset($config['struct_config']) && base64_decode($config['struct_config']) == CLIENT_BILL) ? 'selected' : ''; ?>>Client ID + bill ID</option>
                    <option value="<?php echo CLIENT_3DIGIT_BILL; ?>" <?php echo (isset($config['struct_config']) && base64_decode($config['struct_config']) == CLIENT_3DIGIT_BILL) ? 'selected' : ''; ?>>Client ID + 3 last digits bill</option>
                </select>
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Email</strong>
                <p class="text-muted mb-0">Title of mail</p>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control" id="emailtitle" name="conf_emailtitle" value="<?php echo (isset($config['emailtitle'])) ? base64_decode($config['emailtitle']) : ''; ?>" autocomplete="emailtitle">
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Mail content</strong>
                <p class="text-muted mb-0"></p>
            </div>
            <div class="col">
                <textarea type="text" class="form-control" id="emailcontent" name="conf_emailcontent" autocomplete="emailcontent"><?php echo (isset($config['emailcontent'])) ? base64_decode($config['emailcontent']) : ''; ?></textarea>
            </div>
        </div>
    </div>
</div>

<strong>Transfers</strong>
<p>These settings control the transfer settings.</p>

<div class="list-group mb-5 shadow">
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Name</strong>
                <p class="text-muted mb-0">Owner of the account.</p>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control" id="nameiban" name="conf_nameiban" value="<?php echo (isset($config['nameiban'])) ? base64_decode($config['nameiban']) : ''; ?>" autocomplete="nameiban">
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">IBAN</strong>
                <p class="text-muted mb-0">IBAN of the account.</p>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control" id="iban" name="conf_iban" value="<?php echo (isset($config['iban'])) ? base64_decode($config['iban']) : ''; ?>" autocomplete="iban">
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">BIC</strong>
                <p class="text-muted mb-0">BIC of the account.</p>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control" id="bic" name="conf_bic" value="<?php echo (isset($config['bic'])) ? base64_decode($config['bic']) : ''; ?>" autocomplete="bic">
            </div>
        </div>
    </div>

</div>

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

<strong>Pets</strong>
<p>Configuration of pets table</p>

<div class="list-group mb-5 shadow">
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Auto Death</strong>
                <p class="text-muted mb-0">Allow the system to mark pets as death.<br/>Based on the age defined below and<br/> atleast <strong>no event</strong> for 2 years. (including hidden events)<br/>
                    <i>The value "0 years" disables auto death for this type.</i>
                </p>
            </div>
            <div class="col-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="autdeath" name="check_autdeath" <?php echo (isset($config['autdeath']) && base64_decode($config['autdeath'])) ? 'checked' : ''; ?>>
                    <label class="custom-control-label" for="autdeath"></label>
                </div>
            </div>
        </div>
    </div>
    <?php foreach(array(DOG, CAT, HORSE, BIRD, RABBIT, OTHER) as $type): ?>
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0"><?php echo get_symbol($type, true); ?></strong>
                <p class="text-muted mb-0"></p>
            </div>
            <div class="col-auto">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="auto_dead_<?php echo $type; ?>" name="conf_auto_dead_<?php echo $type; ?>" value="<?php echo (isset($config['auto_dead_' . $type])) ? base64_decode($config['auto_dead_' . $type]) : ''; ?>" autocomplete="auto_dead_<?php echo $type ?>" placeholder="90">
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">years</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<strong>Integrations</strong>
<p>External data sources</p>

<div class="list-group mb-5 shadow">
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">MediLab</strong>
                <p class="text-muted mb-0">Pulls <a href="https://www.medilab.be/" target="_blank">Medilab</a> results.</p>
            </div>
            <div class="col-auto">
                <div class="form-inline">
                    <div class="form-group">
                        <label for="staticEmail2" class="sr-only">User</label>
                        <input type="text" class="form-control" id="medilab_user" name="conf_medilab_user" value="<?php echo (isset($config['medilab_user'])) ? base64_decode($config['medilab_user']) : ''; ?>" autocomplete="medilabuser">
                    </div>
                    <div class="form-group mx-sm-3">
                        <label for="inputPassword2" class="sr-only">Password</label>
                        <input type="password" class="form-control" id="medilab_pasw" value="<?php echo (isset($config['medilab_pasw'])) ? base64_decode($config['medilab_pasw']) : ''; ?>" name="conf_medilab_pasw" autocomplete="medilabpasw">
                    </div>
                </div>
                <button type="button" name="test_covetrus" value="test" id="test_covetrus" class="mt-1 btn btn-sm btn-outline-primary"><i class="fa-solid fa-satellite-dish"></i> Test connection</button>
            </div>
        </div>
    </div>

</div>

<button type="submit" name="submit" value="edit" class="btn btn-outline-primary float-right"><i class="fa-solid fa-wrench"></i> Update</button>

</form>
</div>
</div>
</div>
</div>

<script>
const URL_LOGIN_TEST = '<?php echo base_url('tests/test_connection_covetrus/'); ?>';
document.addEventListener("DOMContentLoaded", function(){
	$("#iban").inputmask("**** **** **** ****");
    $("#admin").addClass('active');
	$("#test_covetrus").on( "click", function(e) {
		$(this).html("<i class='fas fa-sync fa-spin'></i>");
		$.ajax({
			method: 'GET',
			url: URL_LOGIN_TEST + $("#medilab_user").val() + "/" + $("#medilab_pasw").val(),
			success: function(data) {
				$("#test_covetrus").html(data);
			}
    	});
	});
});
</script>