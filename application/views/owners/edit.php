<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>owners/search"><?php echo $this->lang->line('client'); ?></a> / <a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / Edit Client
			</div>
			<div class="card-body">

				<form action="<?php echo base_url(); ?>owners/edit/<?php echo $owner['id']; ?>" method="post" autocomplete="off">
				<div class="row">
					<div class="col-md-6">
						<h5><?php echo $this->lang->line('personal_info'); ?></h5>
						<hr>
						<div class="form-row">
							<div class="form-group col-md-6">
							<label for="last_name"><?php echo $this->lang->line('last_name'); ?></label>
							<input type="text" class="form-control" id="last_name" name="last_name"  autocomplete="dezzd" value="<?php echo (isset($owner['last_name'])) ? $owner['last_name']: '' ?>">
							</div>
							<div class="form-group col-md-6">
							<label for="first_name"><?php echo $this->lang->line('first_name'); ?></label>
							<input type="text" class="form-control" id="first_name" name="first_name" autocomplete="dezzd" value="<?php echo (isset($owner['first_name'])) ? $owner['first_name']: '' ?>">
							</div>
						</div>
						<div class="form-row">
								<div class="form-group col">
									<label for="street"><?php echo $this->lang->line('street'); ?></label>
									<input type="text" name="street" class="form-control" id="street" autocomplete="dezzd" value="<?php echo (isset($owner['street'])) ? $owner['street']: '' ?>">
								</div>
								<div class="form-group col-md-2">
									<label for="nr">nr</label>
									<input type="text" name="nr" class="form-control" id="nr" autocomplete="dezzd" value="<?php echo (isset($owner['nr'])) ? $owner['nr']: '' ?>">
								</div> 
							</div>
						<div class="form-row">
							<div class="form-group col-md-2">
							<label for="inputZip"><?php echo $this->lang->line('zip'); ?></label>
							<input type="text" name="zip"  class="form-control" id="inputZip" autocomplete="dezzd" value="<?php echo (isset($owner['zip'])) ? $owner['zip']: '' ?>">
							</div>
							<div class="form-group col">
							<label for="inputCity"><?php echo $this->lang->line('city'); ?></label>
							<input type="text" name="city" class="form-control" id="city" autocomplete="dezzd" value="<?php echo (isset($owner['city'])) ? $owner['city']: '' ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="maincity"><?php echo $this->lang->line('main_city'); ?></label>
							<input type="text" name="main_city" class="form-control" id="maincity" autocomplete="dezzd" value="<?php echo (isset($owner['main_city'])) ? $owner['main_city']: '' ?>">
						</div>
						<div class="form-group">
							<label for="province"><?php echo $this->lang->line('province'); ?></label>
							<input type="text" name="province" class="form-control" id="province" autocomplete="dezzd" value="<?php echo (isset($owner['province'])) ? $owner['province']: '' ?>">
						</div>
					</div>	  
					<div class="col-md-6">
						<h5><?php echo $this->lang->line('contact_info'); ?></h5>
						<hr>
						<div class="form-group">
							<label for="exampleFormControlInput1"><?php echo $this->lang->line('email'); ?></label>
							<input type="email" name="mail" class="form-control" id="exampleFormControlInput1" autocomplete="dezzd" value="<?php echo (isset($owner['mail'])) ? $owner['mail']: '' ?>">
						</div>
						<div class="form-group">
							<label for="exampleFormControlInput2"><?php echo $this->lang->line('phone'); ?></label>
							<input type="text" name="phone" class="form-control" id="exampleFormControlInput2" autocomplete="dezzd" value="<?php echo (isset($owner['telephone'])) ? $owner['telephone']: '' ?>">
						</div>
						<div class="form-group">
							<label for="exampleFormControlInput3"><?php echo $this->lang->line('mobile'); ?></label>
							<input type="text" name="mobile" class="form-control" id="exampleFormControlInput3" autocomplete="dezzd" value="<?php echo (isset($owner['mobile'])) ? $owner['mobile']: '' ?>">
						</div>
						<div class="form-group">
							<label for="exampleFormControlTextarea1"><?php echo $this->lang->line('comment'); ?></label>
							<textarea class="form-control" name="msg" id="exampleFormControlTextarea1" rows="3"><?php echo (isset($owner['msg'])) ? $owner['msg']: '' ?></textarea>
						</div>
					</div>	  
					<div class="col-md-6">  
						<h5><a data-toggle="collapse" href="#payment_info" role="button" aria-expanded="false" aria-controls="payment_info"><?php echo $this->lang->line('payment_info'); ?></a></h5>
						<hr>
							<div class="collapse <?php echo (isset($owner['btw_nr']) && !empty($owner['btw_nr'])) ? 'show' : ''; ?>" id="payment_info">
								<div class="form-group">
									<label for="btw_nr"><?php echo $this->lang->line('btw_nr'); ?></label>
									<input type="text" name="btw_nr" class="form-control" id="btw_nr" value="<?php echo (isset($owner['btw_nr'])) ? $owner['btw_nr']: '' ?>">
								</div>
								<div class="form-group">
									<label for="invoice_addr"><?php echo $this->lang->line('invoice_addr'); ?></label>
									<textarea class="form-control" name="invoice_addr" id="invoice_addr" rows="3" autocomplete="dezzd"><?php echo (isset($owner['invoice_addr'])) ? $owner['invoice_addr']: '' ?></textarea>
								</div>
								<div class="form-group">
									<label for="invoice_contact"><?php echo $this->lang->line('invoice_contact'); ?></label>
									<input type="text" name="invoice_contact" class="form-control" id="invoice_contact" autocomplete="dezzd" value="<?php echo (isset($owner['invoice_contact'])) ? $owner['invoice_contact']: '' ?>">
								</div>
								<div class="form-group">
									<label for="invoice_tel"><?php echo $this->lang->line('invoice_phone'); ?></label>
									<input type="text" name="invoice_tel" class="form-control" id="invoice_tel" autocomplete="dezzd" value="<?php echo (isset($owner['invoice_tel'])) ? $owner['invoice_tel']: '' ?>">
								</div>
						</div>
					
					</div>	  
					<div class="col-md-6">
						<h5><a data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">Varia</a></h5>
						<hr>
							<div class="collapse" id="collapseExample">
								<div class="form-group">
									<div class="form-check">
									<input class="form-check-input" name="debts" type="checkbox" value="1" id="gridCheck" <?php echo (isset($owner['debts']) && $owner['debts'] == 1) ? 'checked': ''; ?>>
										<label class="form-check-label" for="gridCheck">
										<?php echo $this->lang->line('debts'); ?>
										</label>
									</div>
								</div> 
								<div class="form-group">
									<div class="form-check">
									<input class="form-check-input" name="low_budget" type="checkbox" value="1" id="low_budget" <?php echo (isset($owner['low_budget']) && $owner['low_budget'] == 1) ? 'checked': ''; ?>>
										<label class="form-check-label" for="low_budget">
										<?php echo $this->lang->line('low_budget'); ?>
										</label>
									</div>
								</div> 
								<div class="form-group">
									<div class="form-check">
									<input class="form-check-input" name="contact" type="checkbox" value="1" id="contact" <?php echo (isset($owner['contact']) && $owner['contact'] == 1) ? 'checked': ''; ?>>
										<label class="form-check-label" for="contact">
										<?php echo $this->lang->line('contact'); ?>
										</label>
									</div>
								</div> 
							</div> 
					</div>
					<div class="col-md-12">
						<button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $this->lang->line('edit'); ?></button>
					</div>
				</div>
				</form>
	</div>

</div>
</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#owners").show();
	$("#clients").addClass('active');
	$("#client_search").addClass('active');
	
	$('#inputZip').on('input', function() {
		var zip = $("#inputZip").val();
		if (zip.length == 4)
		{
			$.getJSON("<?php echo base_url(); ?>owners/get_zip/" + zip , function(data, status)
			{
				$("#city").val(data.city);
				$("#maincity").val(data.main_city);
				$("#province").val(data.province);
			});
		}
	});
});
</script>