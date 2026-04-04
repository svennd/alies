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
                <select name="conf_struct_config" class="custom-select" size="3">
                    <option value="<?php echo CLIENT; ?>" <?php echo (isset($config['struct_config']) && base64_decode($config['struct_config']) == CLIENT) ? 'selected' : ''; ?>>Client ID</option>
                    <option value="<?php echo CLIENT_BILL; ?>" <?php echo (isset($config['struct_config']) && base64_decode($config['struct_config']) == CLIENT_BILL) ? 'selected' : ''; ?>>Client ID + bill ID</option>
                    <option value="<?php echo CLIENT_3DIGIT_BILL; ?>" <?php echo (isset($config['struct_config']) && base64_decode($config['struct_config']) == CLIENT_3DIGIT_BILL) ? 'selected' : ''; ?>>Client ID + 3 last digits bill</option>
                </select>
            </div>
        </div>
    </div>
</div>
<?php include 'settings/transfer.php'; ?>
<?php include 'settings/events.php'; ?>
<?php include 'settings/pruning.php'; ?>
<?php include 'settings/integration.php'; ?>

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