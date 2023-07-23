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
				<h4>Bills</h4>
				
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="due_date">Due date</label>
						<input type="text" class="form-control" id="due_date" name="conf_due_date" value="<?php echo (isset($config['due_date'])) ? base64_decode($config['due_date']) : ''; ?>" autocomplete="due_date">
						<small id="due_datehelp" class="form-text text-muted">Bills are due, this many days. (default : 30)</small>
					</div>
				</div>
				
				<fieldset class="form-group row">
					<legend class="col-form-label col-sm-2 float-sm-left pt-0">Invoice ID Prefix</legend>
					<div class="col-sm-10">
					<div class="form-check">
						<input class="form-check-input" type="radio" name="conf_invoice_prefix" id="gridRadios1" value="YYYY" <?php echo (isset($config['invoice_prefix']) && base64_decode($config['invoice_prefix']) == "YYYY") ? 'checked' : ''; ?>>
						<label class="form-check-label" for="gridRadios1">
						Full Year (YYYY)
						</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="radio" name="conf_invoice_prefix" id="gridRadios2" value="none" <?php echo (isset($config['invoice_prefix']) && base64_decode($config['invoice_prefix']) == "none") ? 'checked' : ''; ?>>
						<label class="form-check-label" for="gridRadios2">
						None
						</label>
					</div>
					</div>
				</fieldset>
				<h4>Events</h4>
				
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="autoclose">Autoclose reports</label>
						<input type="text" class="form-control" id="autoclose" name="conf_autoclose" value="<?php echo (isset($config['autoclose'])) ? base64_decode($config['autoclose']) : ''; ?>" autocomplete="autoclose">
						<small id="autoclosehelp" class="form-text text-muted">Reports that are never finished get auto closed after x days. (default : 14)</small>
					</div>
				</div>
				
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="autotemplate">Force template</label>
						<input type="text" class="form-control" id="autotemplate" name="conf_autotemplate" value="<?php echo (isset($config['autotemplate'])) ? base64_decode($config['autotemplate']) : ''; ?>" autocomplete="autotemplate">
						<small id="autotemplatehelp" class="form-text text-muted">Must be in html format.</small>
					</div>
				</div>

				<h4>Integrations</h4>
				<!--
				<h5>Covetrus, Nerum (NetOrder)</h5>
				<hr />
				<div class="form-row">
					<div class="form-group col-md-5">
						<label for="last_name">Covetrus login</label>
						<input type="text" class="form-control" id="last_name" name="conf_covetrus_login" value="<?php echo ($config['covetrus_login']) ? base64_decode($config['covetrus_login']) : ''; ?>" autocomplete="covetrus">
						<small id="emailHelp" class="form-text text-muted">Currently not used</small>
					</div>
					<div class="form-group col-md-5">
						<label for="first_name">Covetrus pasword</label>
						<input type="password" class="form-control" name="conf_covetrus_password" value="" autocomplete="covetruspasw">
					</div>
				</div>
				-->
				<h5>MediLab</h5>
				<hr />
				<div class="form-row">
					<div class="form-group col-md-5">
						<label for="medilab_user">Medilab login</label>
						<input type="text" class="form-control" id="medilab_user" name="conf_medilab_user" value="<?php echo (isset($config['medilab_user'])) ? base64_decode($config['medilab_user']) : ''; ?>" autocomplete="medilabuser">
						<small id="medilab_userhelp" class="form-text text-muted">Used to synchronize lab results</small>
					</div>
					<div class="form-group col-md-5">
						<label for="medilab_pasw">Medilab pasword</label>
						<input type="password" class="form-control" id="medilab_pasw" value="<?php echo (isset($config['medilab_pasw'])) ? base64_decode($config['medilab_pasw']) : ''; ?>" name="conf_medilab_pasw" autocomplete="medilabpasw">
					</div>
					<div class="form-group col-md-1">
						<label for="test_covetrus">Connection</label><br/>
						<button type="button" name="test_covetrus" value="test" id="test_covetrus" class="btn btn-outline-primary">Test</button>
					</div>
				</div>
				<button type="submit" name="submit" value="edit" class="btn btn-primary">Update</button>
			</form>
			</div>
		</div>

	</div>
      
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');

	$("#test_covetrus").on( "click", function(e) {
		$(this).html("<i class='fas fa-sync fa-spin'></i>");
		$.ajax({
			method: 'GET',
			url: '<?php echo base_url(); ?>tests/test_connection_covetrus/' + $("#medilab_user").val() + "/" + $("#medilab_pasw").val(),
			success: function(data) {
				$("#test_covetrus").html(data);
			}
    	});
	});
});
</script>
  