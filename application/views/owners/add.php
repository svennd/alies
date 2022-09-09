<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<a href="<?php echo base_url(); ?>owners/search"><?php echo $this->lang->line('client'); ?></a> / <?php echo $this->lang->line('add'); ?>
			</div>
			<div class="card-body">

				<form action="<?php echo base_url(); ?>owners/add" method="post" autocomplete="off">
				<div class="row">
				<div class="col-md-6">
					<h5><?php echo $this->lang->line('personal_info'); ?></h5>
					<hr>
					  <div class="form-row">
						<div class="form-group col-md-6">
						  <label for="last_name"><b><?php echo $this->lang->line('last_name'); ?>*</b></label>
						  <input type="text" class="form-control" id="last_name" name="last_name"  autocomplete="dezzd" required>
						</div>
						<div class="form-group col-md-6">
						  <label for="first_name"><?php echo $this->lang->line('first_name'); ?></label>
						  <input type="text" class="form-control" id="first_name" name="first_name" autocomplete="dezzd">
						</div>
					  </div>
					  <div class="form-row">
							<div class="form-group col">
								<label for="street"><?php echo $this->lang->line('street'); ?></label>
								<input type="text" name="street" class="form-control" id="street" autocomplete="dezzd">
							</div>
							<div class="form-group col-md-2">
								<label for="nr">nr</label>
								<input type="text" name="nr" class="form-control" id="nr" autocomplete="dezzd">
							</div> 
						</div>
					   <div class="form-row">
						<div class="form-group col-md-2">
						  <label for="inputZip"><?php echo $this->lang->line('zip'); ?></label>
						  <input type="text" name="zip" class="form-control" id="inputZip" autocomplete="dezzd">
						</div>
						<div class="form-group col">
						  <label for="city"><?php echo $this->lang->line('city'); ?></label>
						  <input type="text" name="city" class="form-control" id="city" autocomplete="dezzd">
						</div>
					  </div>
					  <div class="form-group">
						<label for="maincity"><?php echo $this->lang->line('main_city'); ?></label>
						<input type="text" name="main_city" class="form-control" id="maincity" autocomplete="dezzd">
					  </div>
					  <div class="form-group">
						<label for="province"><?php echo $this->lang->line('province'); ?></label>
						<input type="text" name="province" class="form-control" id="province" autocomplete="dezzd">
					  </div>
				</div>	  
				<div class="col-md-6">
					<h5><?php echo $this->lang->line('contact_info'); ?></h5>
					<hr>
					  <div class="form-group">
						<label for="exampleFormControlInput1"><?php echo $this->lang->line('email'); ?></label>
						<input type="email" name="mail" class="form-control" id="exampleFormControlInput1" autocomplete="dezzd">
					  </div>
					  <div class="form-group">
						<label for="exampleFormControlInput2"><?php echo $this->lang->line('phone'); ?></label>
						<input type="text" name="phone" class="form-control" id="exampleFormControlInput2" autocomplete="dezzd">
					  </div>
					  <div class="form-group">
						<label for="exampleFormControlInput3"><?php echo $this->lang->line('mobile'); ?></label>
						<input type="text" name="mobile" class="form-control" id="exampleFormControlInput3" autocomplete="dezzd">
					  </div>
				</div>	  
				<div class="col-md-6">  
					<h5><?php echo $this->lang->line('payment_info'); ?></h5>
					<hr>
					  <div class="form-group">
						<label for="btw_nr"><?php echo $this->lang->line('btw_nr'); ?></label>
						<input type="text" name="btw_nr" class="form-control" id="btw_nr">
					  </div>
					  <div class="form-group">
						<label for="invoice_addr"><?php echo $this->lang->line('invoice_addr'); ?></label>
						<textarea class="form-control" name="invoice_addr" id="invoice_addr" rows="3" autocomplete="dezzd"></textarea>
					  </div>
					  <div class="form-group">
						<label for="invoice_contact"><?php echo $this->lang->line('invoice_contact'); ?></label>
						<input type="text" name="invoice_contact" class="form-control" id="invoice_contact" autocomplete="dezzd">
					  </div>
					  <div class="form-group">
						<label for="invoice_tel"><?php echo $this->lang->line('invoice_phone'); ?></label>
						<input type="text" name="invoice_tel" class="form-control" id="invoice_tel" autocomplete="dezzd">
					  </div>
				  
				</div>	  
				<div class="col-md-6">
					<h5>Varia</h5>
					<hr>
						<div class="form-group">
							<div class="form-check">
							<input class="form-check-input" name="debts" type="checkbox" value="1" id="gridCheck">
								<label class="form-check-label" for="gridCheck">
								<?php echo $this->lang->line('debts'); ?>
								</label>
							</div>
						</div>
						<div class="form-group">
							<div class="form-check">
							<input class="form-check-input" name="low_budget" type="checkbox" value="1" id="low_budget">
								<label class="form-check-label" for="low_budget">
								<?php echo $this->lang->line('low_budget'); ?>
								</label>
							</div>
						</div>
						<div class="form-group">
							<div class="form-check">
							<input class="form-check-input" name="contact" type="checkbox" value="1" id="contact" checked="checked">
								<label class="form-check-label" for="contact">
								<?php echo $this->lang->line('contact'); ?>
								</label>
							</div>
						</div> 
					  <div class="form-group">
						<label for="exampleFormControlTextarea1"><?php echo $this->lang->line('comment'); ?></label>
						<textarea class="form-control" name="msg" id="exampleFormControlTextarea1" rows="3"></textarea>
					  </div>

				</div>	  
				</div>	  
				  <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $this->lang->line('add'); ?></button>
				</form>


		</div>
	</div>

</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#owners").show();
	$("#clients").addClass('active');
	$("#new_client").addClass('active');
	
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