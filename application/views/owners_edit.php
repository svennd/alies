<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>owners/search">Client</a> / <a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / Edit Client
			</div>
			<div class="card-body">

				<form action="<?php echo base_url(); ?>owners/edit/<?php echo $owner['id']; ?>" method="post" autocomplete="off">
				<div class="row">
				<div class="col-md-6">
					<h5>Personal info</h5>
					<hr>
					  <div class="form-row">
						<div class="form-group col-md-6">
						  <label for="last_name">Last Name</label>
						  <input type="text" class="form-control" id="last_name" name="last_name"  autocomplete="dezzd" value="<?php echo (isset($owner['last_name'])) ? $owner['last_name']: '' ?>">
						</div>
						<div class="form-group col-md-6">
						  <label for="first_name">First Name</label>
						  <input type="text" class="form-control" id="first_name" name="first_name" autocomplete="dezzd" value="<?php echo (isset($owner['first_name'])) ? $owner['first_name']: '' ?>">
						</div>
					  </div>
					  <div class="form-row">
							<div class="form-group col">
								<label for="street">Street</label>
								<input type="text" name="street" class="form-control" id="street" autocomplete="dezzd" value="<?php echo (isset($owner['street'])) ? $owner['street']: '' ?>">
							</div>
							<div class="form-group col-md-2">
								<label for="nr">nr</label>
								<input type="text" name="nr" class="form-control" id="nr" autocomplete="dezzd" value="<?php echo (isset($owner['nr'])) ? $owner['nr']: '' ?>">
							</div> 
						</div>
					   <div class="form-row">
						<div class="form-group col-md-2">
						  <label for="inputZip">Zip</label>
						  <input type="text" name="zip"  class="form-control" id="inputZip" autocomplete="dezzd" value="<?php echo (isset($owner['zip'])) ? $owner['zip']: '' ?>">
						</div>
						<div class="form-group col">
						  <label for="inputCity">City</label>
						  <input type="text" name="city" class="form-control" id="city" autocomplete="dezzd" value="<?php echo (isset($owner['city'])) ? $owner['city']: '' ?>">
						</div>
					  </div>
					  <div class="form-group">
						<label for="maincity">Main City</label>
						<input type="text" name="main_city" class="form-control" id="maincity" autocomplete="dezzd" value="<?php echo (isset($owner['main_city'])) ? $owner['main_city']: '' ?>">
					  </div>
					  <div class="form-group">
						<label for="province">Province</label>
						<input type="text" name="province" class="form-control" id="province" autocomplete="dezzd" value="<?php echo (isset($owner['province'])) ? $owner['province']: '' ?>">
					  </div>
				</div>	  
				<div class="col-md-6">
					<h5>Contact info</h5>
					<hr>
					  <div class="form-group">
						<label for="exampleFormControlInput1">Email address</label>
						<input type="email" name="mail" class="form-control" id="exampleFormControlInput1" autocomplete="dezzd" value="<?php echo (isset($owner['mail'])) ? $owner['mail']: '' ?>">
					  </div>
					  <div class="form-group">
						<label for="exampleFormControlInput2">Phone</label>
						<input type="text" name="phone" class="form-control" id="exampleFormControlInput2" autocomplete="dezzd" value="<?php echo (isset($owner['telephone'])) ? $owner['telephone']: '' ?>">
					  </div>
					  <div class="form-group">
						<label for="exampleFormControlInput3">Mobile</label>
						<input type="text" name="mobile" class="form-control" id="exampleFormControlInput3" autocomplete="dezzd" value="<?php echo (isset($owner['mobile'])) ? $owner['mobile']: '' ?>">
					  </div>
				</div>	  
				<div class="col-md-6">  
					<h5>Payment info</h5>
					<hr>
					  <div class="form-group">
						<label for="btw_nr">BTW nr</label>
						<input type="text" name="btw_nr" class="form-control" id="btw_nr" value="<?php echo (isset($owner['btw_nr'])) ? $owner['btw_nr']: '' ?>">
					  </div>
					  <div class="form-group">
						<label for="invoice_addr">Invoice address</label>
						<textarea class="form-control" name="invoice_addr" id="invoice_addr" rows="3" autocomplete="dezzd"><?php echo (isset($owner['invoice_addr'])) ? $owner['invoice_addr']: '' ?></textarea>
					  </div>
					  <div class="form-group">
						<label for="invoice_contact">Invoice contact</label>
						<input type="text" name="invoice_contact" class="form-control" id="invoice_contact" autocomplete="dezzd" value="<?php echo (isset($owner['invoice_contact'])) ? $owner['invoice_contact']: '' ?>">
					  </div>
					  <div class="form-group">
						<label for="invoice_tel">Invoice telephone</label>
						<input type="text" name="invoice_tel" class="form-control" id="invoice_tel" autocomplete="dezzd" value="<?php echo (isset($owner['invoice_tel'])) ? $owner['invoice_tel']: '' ?>">
					  </div>
				  
				</div>	  
				<div class="col-md-6">
					<h5>Varia</h5>
					<hr>
						<div class="form-group">
							<div class="form-check">
							<input class="form-check-input" name="debts" type="checkbox" value="1" id="gridCheck" <?php echo (isset($owner['debts']) && $owner['debts'] == 1) ? 'checked': ''; ?>>
								<label class="form-check-label" for="gridCheck">
								Debts
								</label>
							</div>
						</div> 
						<div class="form-group">
							<div class="form-check">
							<input class="form-check-input" name="low_budget" type="checkbox" value="1" id="low_budget" <?php echo (isset($owner['low_budget']) && $owner['low_budget'] == 1) ? 'checked': ''; ?>>
								<label class="form-check-label" for="low_budget">
								Low Budget
								</label>
							</div>
						</div> 
						<div class="form-group">
							<div class="form-check">
							<input class="form-check-input" name="contact" type="checkbox" value="1" id="contact" <?php echo (isset($owner['contact']) && $owner['contact'] == 1) ? 'checked': ''; ?>>
								<label class="form-check-label" for="contact">
								Contact
								</label>
							</div>
						</div> 
					  <div class="form-group">
						<label for="exampleFormControlTextarea1">Comment</label>
						<textarea class="form-control" name="msg" id="exampleFormControlTextarea1" rows="3"><?php echo (isset($owner['msg'])) ? $owner['msg']: '' ?></textarea>
					  </div>

				</div>	  
				</div>	  
				  <button type="submit" name="submit" value="1" class="btn btn-primary">Submit</button>
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