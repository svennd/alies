<style>
.bs-wizard {margin-bottom: 40px;}

/*Form Wizard*/
.bs-wizard {border-bottom: solid 1px #e0e0e0; padding: 0 0 10px 0;}
.bs-wizard > .bs-wizard-step {padding: 0; position: relative;}
.bs-wizard > .bs-wizard-step + .bs-wizard-step {}
.bs-wizard > .bs-wizard-step .bs-wizard-stepnum {color: #595959; font-size: 16px; margin-bottom: 5px;}
.bs-wizard > .bs-wizard-step .bs-wizard-info {color: #999; font-size: 14px;}
.bs-wizard > .bs-wizard-step > .bs-wizard-dot {position: absolute; width: 30px; height: 30px; display: block; background: #fbe8aa; top: 45px; left: 50%; margin-top: -15px; margin-left: -15px; border-radius: 50%;}
.bs-wizard > .bs-wizard-step > .bs-wizard-dot:after {content: ' '; width: 14px; height: 14px; background: #fbbd19; border-radius: 50px; position: absolute; top: 8px; left: 8px; }
.bs-wizard > .bs-wizard-step > .progress {position: relative; border-radius: 0px; height: 8px; box-shadow: none; margin: 20px 0;}
.bs-wizard > .bs-wizard-step > .progress > .progress-bar {width:0px; box-shadow: none; background: #fbe8aa;}
.bs-wizard > .bs-wizard-step.complete > .progress > .progress-bar {width:100%;}
.bs-wizard > .bs-wizard-step.active > .progress > .progress-bar {width:50%;}
.bs-wizard > .bs-wizard-step:first-child.active > .progress > .progress-bar {width:0%;}
.bs-wizard > .bs-wizard-step:last-child.active > .progress > .progress-bar {width: 100%;}
.bs-wizard > .bs-wizard-step.disabled > .bs-wizard-dot {background-color: #f5f5f5;}
.bs-wizard > .bs-wizard-step.disabled > .bs-wizard-dot:after {opacity: 0;}
.bs-wizard > .bs-wizard-step:first-child  > .progress {left: 50%; width: 50%;}
.bs-wizard > .bs-wizard-step:last-child  > .progress {width: 50%;}
.bs-wizard > .bs-wizard-step.disabled a.bs-wizard-dot{ pointer-events: none; }
/*END Form Wizard*/

</style>
<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>products">Products</a> / New Product
	</div>

	<div class="card-body">
		<div class="row bs-wizard" style="border-bottom:0;">

			<div class="col-4 bs-wizard-step <?php if($step == 1): ?>active<?php else: ?>complete<?php endif; ?>">
			  <div class="text-center bs-wizard-stepnum">Step 1</div>
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="bs-wizard-info text-center">Product Info</div>
			</div>

			<div class="col-4 bs-wizard-step <?php if($step == 1): ?>disabled<?php elseif($step == 3): ?>complete<?php else: ?>active<?php endif; ?>">
			  <div class="text-center bs-wizard-stepnum">Step 2</div>
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="bs-wizard-info text-center">Pricing</div>
			</div>

			<div class="col-3 bs-wizard-step <?php if($step == 3): ?>complete<?php else: ?>disabled<?php endif; ?>">
			  <div class="text-center bs-wizard-stepnum">Step 3</div>
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="bs-wizard-info text-center">Add stock</div>
			</div>
		</div>

		<?php if ($step == "1"): ?>
		<form action="<?php echo base_url(); ?>products/new" method="post" autocomplete="off">
			<h5>Product info</h5>
			<hr />
			<div class="form-row">
				<div class="col mb-3">
				<label for="exampleFormControlInput3">Name*</label>
				<input type="text" name="name" class="form-control" id="exampleFormControlInput3" value="" required>
				</div>
				<div class="col mb-3">
				<label for="exampleFormControlInput3">Abbreviation</label>
				<input type="text" name="short_name" class="form-control" id="exampleFormControlInput3" value="">
				</div>
			</div>

			<div class="form-row">
				<div class="col mb-3">
				<label for="exampleFormControlInput3">Producer</label>
				<input type="text" name="producer" class="form-control" id="exampleFormControlInput3" value="">
				</div>
				<div class="col mb-3">
				<label for="exampleFormControlInput3">Supplier</label>
				<input type="text" name="supplier" class="form-control" id="exampleFormControlInput3" value="">
				</div>
			</div>

			<div class="form-row">
				<div class="col mb-3">

					<label for="type">Type</label>
					<select name="type" class="form-control" id="type">
						<?php foreach($type as $t):?>
							<option value="<?php echo $t['id']; ?>">
							<?php echo $t['name']; ?></option>
						<?php endforeach; ?>
						<option value="0">Other</option>
					</select>

				</div>
				<div class="col mb-3">&nbsp;
				</div>
			</div>

			<br/>
			<h5>Vaccin</h5>
			<hr />
			<div class="form-row">
				<div class="col mb-3">
					<div class="custom-control custom-switch">
						<input type="checkbox" class="custom-control-input" name="vaccin" value="1" id="vaccine_switch">
						<label class="custom-control-label" for="vaccine_switch">Vaccine</label>
					</div>
				</div>
				<div class="col mb-3">
					<div class="form-group" style="display:none;" id="is_a_vaccine">
						<label for="exampleFormControlTextarea1">Expire *</label>
						<input type="text" name="vaccin_freq" class="form-control" id="exampleFormControlInput3" value="0">
						<small id="offsetHelp" class="form-text text-muted">time untill renewal is required, 0 for no expire date. (in days)</small>
					</div>
				</div>
			</div>

			<br/>
			<h5>Transaction info</h5>
			<hr />
			<div class="form-row">
				<div class="col mb-3">
				<div class="custom-control custom-switch">
				  <input type="checkbox" class="custom-control-input" id="customSwitch1" name="sellable" value="1" checked>
				  <label class="custom-control-label" for="customSwitch1">sellable</label>
				</div>
				</div>
				<div class="col mb-3">&nbsp;</div>
			</div>

			<div class="form-row">
				<div class="col">
				<label for="exampleFormControlInput3">Buy volume*</label>
				<input type="text" name="buy_volume" class="form-control" id="exampleFormControlInput3" value="1" >
				</div>
				<div class="col-md-2">
				<label for="exampleFormControlInput3">Buy unit*</label>
				<input type="text" name="unit_buy" class="form-control" id="exampleFormControlInput3" value="" required>
				<small id="unitHelp" class="form-text text-muted">kg, fles, doos</small>
				</div>
				<div class="col-md-1"><label for="exampleFormControlInput3">==></label></div>
				<div class="col">
				<label for="exampleFormControlInput3">Sell volume*</label>
				<input type="text" name="sell_volume" class="form-control" id="exampleFormControlInput3" value="1" >
				<small class="form-text text-muted">eg. 2 box, containing 4 strips : "8" "strips"</small>
				</div>
				<div class="col-md-2">
				<label for="exampleFormControlInput3">Sell Unit*</label>
				<input type="text" name="unit_sell" class="form-control" id="exampleFormControlInput3" value="" required>
				</div>
			</div>
			<br/>

			<div class="form-row">
				<div class="col">
				<label for="exampleFormControlInput3">BTW buy</label>
				<div class="input-group mb-3">
				  <input type="text" class="form-control" name="btw_buy" value="">
				  <div class="input-group-append">
					<span class="input-group-text" id="basic-addon2">%</span>
				  </div>
				</div>
				</div>
				<div class="col">
				<label for="exampleFormControlInput3">BTW sell</label>
				<div class="input-group mb-3">
					<select name="booking_code" class="form-control" id="type">
						<?php foreach($booking as $t): ?>
							<option value="<?php echo $t['id']; ?>"><?php echo $t['code'] . ' ' . $t['category'] . ' ' . $t['btw']  . '%'; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				</div>
			</div>

			<br/>
			<h5>Advanced</h5>
			<hr />

			<div class="form-row">
				<div class="col mb-3">
				<label for="exampleFormControlInput3">Dead volume</label>
				<input type="text" name="dead_volume" class="form-control" id="exampleFormControlInput3" value="">
				<small id="dead_volumeHelp" class="form-text text-muted">Volume that is removed from stock but not injected.</small>
				</div>
				<div class="col mb-3">
				<label for="exampleFormControlInput3">Min. requirement stock</label>
				<input type="text" name="limit_stock" class="form-control" id="limits" value="">
				<small id="limitHelp" class="form-text text-muted">Minimum sellable volumes that should be available; (global)</small>
				</div>
			</div>

			<div class="form-row">
				<div class="col mb-3">
				<label for="gs1_datamatrix">Scan barcode</label>
				<input type="text" name="gs1_datamatrix" class="form-control" id="gs1_datamatrix" value="">
				<small class="form-text text-danger">Will overwrite input_barcode! (scan with a reader)</small>
				</div>
				<div class="col mb-3">
				<label for="exampleFormControlInput3">input_barcode</label>
				<input type="text" name="input_barcode" class="form-control" id="input_barcode" value="">
				<small class="form-text text-muted" id="extra_info">Set the barcode manually</small>
				</div>
			</div>
		<br/>
		<button type="submit" name="submit" value="add" class="btn btn-outline-success">Next</button>
		</form>

		<?php elseif ($step == "2"): ?>
		<?php if ($product): ?>

				<form method="post" action="<?php echo base_url(); ?>products/new/2/<?php echo $product['id']; ?>">
				<h5>Catalog Price : <?php echo $product['name'] ?></h5>
				<hr />
				<div class="form-row">
					<div class="col">
						<label for="exampleFormControlInput3">Catalog Price</label>
						<div class="input-group mb-3">
						  <input type="text" class="form-control" name="buy_price" value="<?php echo $product['buy_price'] ?>">
						  <div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
						  </div>
						</div>
						<small id="unitHelp" class="form-text text-muted">Normal buy-in price, not taking into account reductions or promotions</small>
					</div>
					<div class="col">
						<label for="exampleFormControlInput3">&nbsp;</label>
						<div class="input-group">
							<button type="submit" name="submit" value="store_buy_price" class="btn btn-primary">Store</button>
						</div>
					</div>
				</div>
				</form>

				<br/>
				<?php if (!is_null($product['prices'])): ?>
				<h5>Current Sell Price</h5>
				<hr />
					<div class="form-row">
						<div class="col-2">
							<label for="exampleFormControlInput3">Volume</label>
						</div>
						<div class="col-3">
							<label for="exampleFormControlInput3">Price*</label>
						</div>
						<div class="col">&nbsp;</div>
					</div>
				<?php foreach($product['prices'] as $price):
					$unit_price = ($product['buy_price']/$product['buy_volume']);
					$change = round((($unit_price-$price['price'])/$unit_price)*100*-1);
				?>

					<form method="post" action="<?php echo base_url(); ?>products/new/2/<?php echo $product['id']; ?>">
					<div class="form-row">

						<div class="col-2">
							<div class="input-group mb-2 mr-sm-2">
								<input type="text" class="form-control" id="volume" name="volume" value="<?php echo $price['volume']; ?>">
								<div class="input-group-append">
									<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
								</div>
							</div>
							<small class="form-text text-danger"></small>
						</div>

						<div class="col-3">
							<div class="input-group">
								<input type="text" class="form-control" id="price" name="price" placeholder="" value="<?php echo $price['price']; ?>">
								<div class="input-group-append">
									<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
								</div>
							</div>

						</div>
						<div class="col">
							<div class="input-group">
								<input type="hidden" name="price_id" value="<?php echo $price['id']; ?>" />
								<button type="submit" name="submit" value="edit" class="btn btn-primary">Update</button>
								<a href="<?php echo base_url(); ?>products/remove_product_price/<?php echo $price['id']; ?>/new" class="btn btn-danger mx-1">Remove</a>
								<small class="form-text p-2"><?php echo $change; ?>%</small>
							</div>
						</div>
					</div>
					</form>
				<?php endforeach; ?>
					<br/>
					<form method="post" action="<?php echo base_url(); ?>products/new/2/<?php echo $product['id']; ?>">
					<h6><u>Add price</u></h6>
					<div class="form-row">

						<div class="col-2 mb-3">
							<label for="exampleFormControlInput3">Volume</label>
							<div class="input-group mb-2 mr-sm-2">
								<input type="text" class="form-control" id="volume" name="volume" value="" required>
								<div class="input-group-append">
									<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
								</div>
							</div>
							<small class="form-text text-danger"></small>
						</div>

						<div class="col-3 mb-3">
							<label for="exampleFormControlInput3">Price*</label>

							<div class="input-group">
								<input type="text" class="form-control" id="price" name="price" placeholder="" value="" required>
								<div class="input-group-append">
									<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
								</div>
							</div>
							<small class="form-text">reduced price : per unit</small>
						</div>
						<div class="col mb-3">
						<label for="exampleFormControlInput3">&nbsp;</label>
							<div class="input-group">
								<button type="submit" name="submit" value="store" class="btn btn-primary">Add</button>
							</div>
						</div>
					</div>
					</form>

				<?php else: ?>

					<br/>

					<form method="post" action="<?php echo base_url(); ?>products/new/2/<?php echo $product['id']; ?>">

					<h5>Set sell price</h5>
					<hr />
					<div class="form-row">
						<div class="col mb-3">
						<label for="exampleFormControlInput3">Price*</label>
							<div class="input-group">
								<input type="text" class="form-control" id="price" name="price" placeholder="" required>
								<div class="input-group-append">
									<span class="input-group-text" id="basic-addon2">&euro; / <?php echo $product['unit_sell']; ?></span>
								</div>
							</div>

							<small class="form-text text-danger">Sales price for a single unit</small>
							<input type="hidden" name="volume" value="1" />
						</div>
						<div class="col mb-3">
						<label for="exampleFormControlInput3">&nbsp;</label>
							<div class="input-group">
								<button type="submit" name="submit" value="store" class="btn btn-primary mb-2">Store</button>
							</div>
						</div>
					</div>
					</form>
				<?php endif; ?>
				<?php else : ?>
				<div class="alert alert-danger" role="alert">Product is not sellable, or can't have a price;</div>
			<?php endif; ?>

			<?php if(!is_null($product['prices'])): ?>
				<a href="<?php echo base_url(); ?>products/new/3/<?php echo $product['id']; ?>" class="btn btn-outline-success">next</a>
			<?php endif; ?>
		<?php elseif ($step == "3"): ?>

		<div class="alert alert-success" role="alert">
		  <h4 class="alert-heading">Product added!</h4>
		  <p>Product <i><?php echo $product['name']; ?></i> is now added.</p>
		  <hr>
		  <p class="mb-0">
		  <ul>
			<li><a href="<?php echo base_url(); ?>stock/add_stock/<?php echo $product['id']; ?>">add stock ?</a></li>
			<li><a href="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>">finetune sell price</a></li>
			<li><a href="<?php echo base_url(); ?>products/new">add another product</a></li>
		  </ul>
		  </p>
		</div>
		<?php endif;?>
    </div>
</div>




<script type="text/javascript">

function process_datamatrix(barcode) {

	// GS1 data matrix
	// 01 05420036903635 17 210400 10 111219
	// length : ~30
	// 01 EAN/GTIN  (14 length)
	// 17 YY MM DD date (6 length)
	// 10 barcode (variable length)
	// 6 + 14 + 6 + x

	if (barcode.length > 26)
	{
		result = barcode.match(/01([0-9]{14})17([0-9]{6})10(.*)/);
		if(result)
		{
			var input_barcode = result[1];
			var date = result[2];
			var day = (date.substr(4,2) == "00") ? "01" : date.substr(4,2);

			$("#input_barcode").val(result[1]);
			$("#extra_info").html("Scanned LotNR : " + "20" + date.substr(0, 2) + "-" + date.substr(2,2) + "-" + day + " lotnr :" + result[3]);
		}
	}
	else
	{
		console.log("code to short not recognized");
	}
}

document.addEventListener("DOMContentLoaded", function(){
	var _changeInterval = null;
	var barcode = null;
	$("#prd").show();
	$("#products").addClass('active');
	$("#product_list").addClass('active');

	$("#vaccine_switch").change(function() {
		$("#is_a_vaccine").toggle();
	});

	$("#gs1_datamatrix").keyup(function(){
		barcode = this.value;
		clearInterval(_changeInterval)
		_changeInterval = setInterval(function() {
		clearInterval(_changeInterval)
			process_datamatrix(barcode);

		}, 500);
	});

});
</script>
