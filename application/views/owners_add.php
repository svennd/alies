<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header py-3">
				<a href="<?php echo base_url(); ?>owners/search">Client</a> / Add
			</div>
			<div class="card-body">

				<form action="<?php echo base_url(); ?>owners/add" method="post" autocomplete="off">
				<div class="row">
				<div class="col-md-6">
					<h5>Personal info</h5>
					<hr>
					  <div class="form-row">
						<div class="form-group col-md-6">
						  <label for="last_name">Last Name</label>
						  <input type="text" class="form-control" id="last_name" name="last_name"  autocomplete="dezzd">
						</div>
						<div class="form-group col-md-6">
						  <label for="first_name">First Name</label>
						  <input type="text" class="form-control" id="first_name" name="first_name" autocomplete="dezzd">
						</div>
					  </div>
					  <div class="form-row">
							<div class="form-group col">
								<label for="street">Street</label>
								<input type="text" name="street" class="form-control" id="street" autocomplete="dezzd">
							</div>
							<div class="form-group col-md-2">
								<label for="nr">nr</label>
								<input type="text" name="nr" class="form-control" id="nr" autocomplete="dezzd">
							</div> 
						</div>
					   <div class="form-row">
						<div class="form-group col-md-2">
						  <label for="inputZip">Zip</label>
						  <input type="text" name="zip" class="form-control" id="inputZip" autocomplete="dezzd">
						</div>
						<div class="form-group col">
						  <label for="city">City</label>
						  <input type="text" name="city" class="form-control" id="city" autocomplete="dezzd">
						</div>
					  </div>
					  <div class="form-group">
						<label for="maincity">Main City</label>
						<input type="text" name="main_city" class="form-control" id="maincity" autocomplete="dezzd">
					  </div>
					  <div class="form-group">
						<label for="province">Province</label>
						<input type="text" name="province" class="form-control" id="province" autocomplete="dezzd">
					  </div>
				</div>	  
				<div class="col-md-6">
					<h5>Contact info</h5>
					<hr>
					  <div class="form-group">
						<label for="exampleFormControlInput1">Email address</label>
						<input type="email" name="mail" class="form-control" id="exampleFormControlInput1" autocomplete="dezzd">
					  </div>
					  <div class="form-group">
						<label for="exampleFormControlInput2">Phone</label>
						<input type="text" name="phone" class="form-control" id="exampleFormControlInput2" autocomplete="dezzd">
					  </div>
					  <div class="form-group">
						<label for="exampleFormControlInput3">Mobile</label>
						<input type="text" name="mobile" class="form-control" id="exampleFormControlInput3" autocomplete="dezzd">
					  </div>
				</div>	  
				<div class="col-md-6">  
					<h5>Payment info</h5>
					<hr>
					  <div class="form-group">
						<label for="btw_nr">BTW nr</label>
						<input type="text" name="btw_nr" class="form-control" id="btw_nr">
					  </div>
					  <div class="form-group">
						<label for="invoice_addr">Invoice address</label>
						<textarea class="form-control" name="invoice_addr" id="invoice_addr" rows="3" autocomplete="dezzd"></textarea>
					  </div>
					  <div class="form-group">
						<label for="invoice_contact">Invoice contact</label>
						<input type="text" name="invoice_contact" class="form-control" id="invoice_contact" autocomplete="dezzd">
					  </div>
					  <div class="form-group">
						<label for="invoice_tel">Invoice telephone</label>
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
								Debts
								</label>
							</div>
						</div>
						<div class="form-group">
							<div class="form-check">
							<input class="form-check-input" name="low_budget" type="checkbox" value="1" id="low_budget">
								<label class="form-check-label" for="low_budget">
								Low Budget
								</label>
							</div>
						</div>
						<div class="form-group">
							<div class="form-check">
							<input class="form-check-input" name="contact" type="checkbox" value="1" id="contact" checked="checked">
								<label class="form-check-label" for="contact">
								Contact
								</label>
							</div>
						</div> 
					  <div class="form-group">
						<label for="exampleFormControlTextarea1">Comment</label>
						<textarea class="form-control" name="msg" id="exampleFormControlTextarea1" rows="3"></textarea>
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