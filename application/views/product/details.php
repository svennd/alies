<style>
	.light-grey-hover:hover {
  background-color: #f3f6ff;
}
</style>

<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>products">Products</a> /
		<a href="<?php echo base_url(); ?>products/product_list">List</a> /
		Edit Product / <?php echo (isset($product['name'])) ? $product['name']: '' ?>
	</div>
	
	<div class="card-body">
		<?php if (isset($update) && $update) : ?>
			<div class="alert alert-success alert-dismissible fade show" role="alert">
			  Product updated! Want to <a href="<?php echo base_url(); ?>stock/add_stock/<?php echo $product['id']; ?>">add stock</a> ?
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			</div>
		<?php endif; ?>
		
		<form action="<?php echo base_url(); ?>products/product/<?php echo $product['id']; ?>" method="post" autocomplete="off">
		
		<div class="row">
			<div class="col-2">
				<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
					<a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">Product Info</a>
					<a class="nav-link light-grey-hover" id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">Transaction Info</a>
					<?php if ($this->ion_auth->in_group("admin")): ?>
						<a class="nav-link light-grey-hover" id="v-pills-price-tab" data-toggle="pill" href="#v-pills-price" role="tab" aria-controls="v-pills-varia" aria-selected="false">Price</a>
					<?php endif; ?>
					<a class="nav-link light-grey-hover" id="v-pills-varia-tab" data-toggle="pill" href="#v-pills-varia" role="tab" aria-controls="v-pills-varia" aria-selected="false">Varia</a>
				</div>
			</div>
			<div class="col-10">
    <div class="tab-content" id="v-pills-tabContent">
      <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
	  
	  	<h5>Product info</h5>
		<hr />
		<div class="form-row">
			<div class="col mb-3">
				<label for="product_name"><?php echo $this->lang->line('product_name') ?>*</label>
				<input type="text" name="name" class="form-control" id="product_name" value="<?php echo (isset($product['name'])) ? $product['name']: '' ?>" required>
			</div>
			<div class="col mb-3">
				<div class="form-check">
					<input type="checkbox" class="form-check-input" name="sellable" value="1" id="sellable" <?php echo ($product) ? (($product['sellable']) ? "checked" : "" ) : "checked"; ?>>
					<label class="form-check-label" for="sellable"><?php echo $this->lang->line('saleable'); ?></label>
				</div>
					
				<div class="form-check">
					<input type="checkbox" class="form-check-input" name="discontinued" value="1" id="discontinued" <?php echo ($product) ? (($product['discontinued']) ? "checked" : "" ) : "checked"; ?>>
					<label class="form-check-label" for="discontinued"><?php echo $this->lang->line('discontinued'); ?></label>
				</div>
			</div>
		</div>

		<div class="form-row">
			<div class="col mb-3">
				<label for="abbr"><?php echo $this->lang->line('abbreviation'); ?></label>
				<input type="text" name="short_name" class="form-control" id="abbr" value="<?php echo (isset($product['short_name'])) ? $product['short_name']: '' ?>">
			</div>
			<div class="col mb-3">
				<label for="supplier">Supplier</label>
				<input type="text" name="supplier" class="form-control" id="supplier" value="<?php echo (isset($product['supplier'])) ? $product['supplier']: '' ?>">
			</div>
		</div>

		<div class="form-row">
			<div class="col mb-3">
				<div class="form-row">
					<div class="col">
						<label for="wholesale"><?php echo $this->lang->line('wholesale_link'); ?></label>
						<select name="wholesale" class="<?php echo ($product['wholesale']) ? 'is-valid' : 'is-invalid'; ?>" style="width:100%" id="wholesale" data-allow-clear="1">
							<?php if($product['wholesale']): ?>
								<option value="<?php echo $product['wholesale']; ?>" selected>selected : id <?php echo $product['wholesale']; ?></option>
							<?php endif; ?>
						</select>
					</div>
					<div class="col">
						<label for="input_wh_name"><?php echo $this->lang->line('product_wholesale'); ?></label>
						<input type="text" name="input_wh_name" class="form-control" id="input_wh_name" value="<?php echo (isset($product['wholesale_name'])) ? $product['wholesale_name']: '' ?>">
					</div>
				</div>
			</div>
			<div class="col mb-3">
				<label for="input_producer">Producer</label>
				<input type="text" name="producer" class="form-control" id="input_producer" value="<?php echo (isset($product['producer'])) ? $product['producer']: '' ?>">
			</div>
		</div>

		<div class="form-row">
			<div class="col mb-3">
				<label for="type">Type</label>
				<select name="type" class="form-control" id="type">
					<?php foreach($type as $t):?>
						<option value="<?php echo $t['id']; ?>" <?php echo ($product && $t['id'] == $product['type']) ? "selected='selected'" : ""; ?>>
						<?php echo $t['name']; ?></option>
					<?php endforeach; ?>
					<option value="0">Other</option>
				</select>
			</div>
			<div class="col mb-3">
				<label for="vhb_input">VHB code</label>
				<input type="text" name="vhbcode" class="form-control" id="vhb_input" value="<?php echo (isset($product['vhbcode'])) ? $product['vhbcode']: '' ?>">
			</div>
		</div>
		<h5 class="pt-5">Advanced</h5>
		<hr />
		<div class="form-row">
			<div class="col">
				<div class=" input-group mb-3">
					<label for="vaccin">Vaccin (Rappel)</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">
								<input type="checkbox" id="vaccin" name="vaccin" aria-label="Checkbox for following text input" <?php echo ($product && $product['vaccin']) ? "checked" : ""; ?>>
							</div>
						</div>
						<input type="text" class="form-control" name="vaccin_freq" aria-label="Text input with checkbox" value="<?php echo (isset($product['vaccin_freq'])) ? $product['vaccin_freq']: '0' ?>">
					</div>
					<small id="offsetHelp" class="form-text text-muted">0 for no autogenerated rappel date (in days)</small>
				</div>
			</div>

			<div class="col mb-3">
				<div class="form-group">
					<label for="offset"><?php echo $this->lang->line('dead_volume'); ?></label>
					<input type="text" name="offset" class="form-control" id="offset" value="<?php echo (isset($product['offset'])) ? $product['offset']: '0' ?>">
					<small id="offsetHelp" class="form-text text-muted">Volume that is removed from stock but not injected.</small>
				</div> 	
			</div> 	
		</div> 	

		<div class="form-row">
			<div class="col">
				<div class="form-row">
					<div class="col">
						<label for="gs1_datamatrix"><i class="fas fa-barcode"></i> Scan barcode</label>
						<input type="text" name="gs1_datamatrix" class="form-control" id="gs1_datamatrix" value="">
						<small class="form-text text-danger">Scan a full GS1 code. Will overwrite Product code.</small>
					</div>
					<div class="col">
						<label for="input_barcode">Product code</label>
						<input type="text" name="input_barcode" class="form-control" id="input_barcode" value="<?php echo (isset($product['input_barcode'])) ? $product['input_barcode']: '' ?>">
						<small class="form-text text-muted" id="extra_info">Used to identify products that use GS1.</small>
					</div>
				</div>
			</div> 	
			<div class="col">
			</div> 	
		</div>
		
		<h5 class="pt-5">Stock Requirements</h5>

	<?php if ($product):  ?>
	<?php $total_1m = 0.0; if ($history_1m) : foreach ($history_1m as $h) { $total_1m += $h['volume']; } endif; ?>
	<?php $total_6m = 0.0; if ($history_6m) : foreach ($history_6m as $h) { $total_6m += $h['volume']; } endif; ?>
	<?php $total_1y = 0.0; if ($history_1y) : foreach ($history_1y as $h) { $total_1y += $h['volume']; } endif; ?>
	<?php endif; ?>
	<hr />		

	<div class="form-row">
			<div class="form-group col">
				<label for="limits">Limit (global)</label>

				<div class="input-group mb-3">
					<input type="text" name="limit_stock" class="form-control" id="limits" value="<?php echo (isset($product['limit_stock'])) ? $product['limit_stock']: '' ?>">
					<div class="input-group-append">
						<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
					</div>
				</div>
			<small id="limitHelp" class="form-text text-muted">Minimum sellable volumes that should be available; (global)</small>
			</div>
			<div class="form-group col">
				<table class="table table-sm mt-4">
					<tr>
						<td>Use Last Month</td>
						<td><?php echo $total_1m; ?> <?php echo $product['unit_sell']; ?></td>
					</tr>
					<tr>
						<td>Use Last 6 Month</td>
						<td><?php echo $total_6m; ?> <?php echo $product['unit_sell']; ?></td>
					</tr>
					<tr>
						<td>Use Last Year</td>
						<td><?php echo $total_1y; ?> <?php echo $product['unit_sell']; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<h5 class="pt-5">Local limits</h5>
		<hr />
		<p>The mimimal volume that should be in each location.</p>

		<div class="form-row">
		<?php foreach ($stock_locations as $stock): 
			$local_limit_id = -1;
			$local_limit_value = 0;

			# super dirty but whateves
			if ($llimit)
			{
				foreach($llimit as $limit)
				{
					if ($stock['id'] == $limit['stock'])
					{
						$local_limit_id = $limit['id'];
						$local_limit_value = $limit['volume'];
						break;
					}
				}
			}
			?>
		<div class="form-group col-md-6">
			<div class="form-group row">
				<label for="limits<?php echo $local_limit_id; ?>" class="col-sm-4 col-form-label"><?php echo $stock['name']; ?></label>
				<div class="col-sm-4">
					<div class="input-group mb-3">
						<input type="text" name="limit[<?php echo $stock['id']; ?>][<?php echo $local_limit_id; ?>]" class="form-control" id="limits<?php echo $local_limit_id; ?>" value="<?php echo $local_limit_value; ?>">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
				
		<?php endforeach; ?>
		</div>


	  </div>
      <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
	  
	

	  </div>
      <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
	  	<h5>Transaction info</h5>
	<hr />				  

		<div class="form-row">
			<div class="col">
			<label for="buy_volume">Buy volume</label>
			<input type="text" name="buy_volume" class="form-control" id="buy_volume" value="<?php echo (isset($product['buy_volume'])) ? $product['buy_volume']: '' ?>" required>
			</div>
			<div class="col-md-2">
			<label for="unit_buy">Buy unit</label>
			<input type="text" name="unit_buy" class="form-control" id="unit_buy" value="<?php echo (isset($product['unit_buy'])) ? $product['unit_buy']: '' ?>" required>
			<small id="unitHelp" class="form-text text-muted">kg, fles, doos</small>
			</div>
			<div class="col-md-1">==></div>
			<div class="col">
			<label for="sell_volume">Sell volume</label>
			<input type="text" name="sell_volume" class="form-control" id="sell_volume" value="<?php echo (isset($product['sell_volume'])) ? $product['sell_volume']: '' ?>" required>
			<small class="form-text text-muted">min. 2 box, containing 4 strips : "8" "strips"</small>
			</div>
			<div class="col-md-2">
			<label for="unit_sell">Sell Unit</label>
			<input type="text" name="unit_sell" class="form-control" id="unit_sell" value="<?php echo (isset($product['unit_sell'])) ? $product['unit_sell']: '' ?>" required>
			</div>
		</div>

		<div class="form-row">
		<p>
			&nbsp;
		</p>
		</div>
		
		<div class="form-row">
			<div class="col">
			<label for="btw_buy">BTW buy</label>
			<div class="input-group mb-3">
			  <input type="text" class="form-control" name="btw_buy" id="btw_buy" value="<?php echo (isset($product['btw_buy'])) ? $product['btw_buy']: '' ?>">
			  <div class="input-group-append">
				<span class="input-group-text" id="basic-addon2">%</span>
			  </div>
			</div>
			</div>
			<div class="col">
			<label for="booking_code">BTW sell</label>
			<div class="input-group mb-3">
				<select name="booking_code" class="form-control" id="booking_code">
					<?php foreach($booking as $t): ?>
						<option value="<?php echo $t['id']; ?>" <?php echo ($product && $t['id'] == $product['booking_code']) ? "selected='selected'":"";?>><?php echo $t['code'] . ' ' . $t['category'] . ' ' . $t['btw']  . '%'; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			</div>

		</div>
			

	  </div>
	  
		<div class="tab-pane fade" id="v-pills-price" role="tabpanel" aria-labelledby="v-pills-price-tab">

		<div class="form-row">
			<div class="form-group col">
				<label for="buy_price">Catalog Price</label>
				<input type="text" name="buy_price" class="form-control" id="buy_price" value="<?php echo (isset($product['buy_price'])) ? $product['buy_price']: '' ?>">
				<small id="offsetHelp" class="form-text text-muted">Manually tracking pricing</small>
			</div>
			<div class="form-group col">
				<label for="buy_price_date">Catalog Update</label>
				<input type="date" name="buy_price_date" class="form-control" id="buy_price_date" value="<?php echo (isset($product['buy_price_date'])) ? $product['buy_price_date']: '' ?>">
			</div>
		</div>
		<hr />
		<?php if($product['sellable']): ?>
		<h5>Current price</h5>
		<?php 
		if (!isset($product['prices']))
		{
			echo "<span style='color:red;'><b>no price set!</b></span>";
		} 
		else 
		{
			if (count($product['prices']) > 1)
			{
				echo '<a data-toggle="collapse" href="#collapse' . $product['id'] . '" role="button" aria-expanded="false" aria-controls="collapse' . $product['id'] . '">' . $product['prices'][0]['price'] . '~' . $product['prices'][sizeof($product['prices']) - 1]['price']. '&euro;</a> / ' . $product['prices']['0']['volume'] . ' '. $product['unit_sell'];
				echo "<div class='collapse' id='collapse" . $product['id'] . "'><table class='small'>";
				foreach ($product['prices'] as $price)
				{
					echo "<tr><td>". $price['volume'] ." ". $product['unit_sell']."</td><td>". $price['price'] ."&euro;</td><tr>";
				}
				echo "</table></div>";
			}
			else
			{
				echo $product['prices']['0']['price'] . "&euro; / " . $product['prices']['0']['volume'] . " ". $product['unit_sell'];
			}
		}
		?><br/><br/>
		<a href="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>" target="_blank" class="btn btn-outline-success"><i class="fas fa-external-link-alt"></i> Edit Price </a>
		<small id="offsetHelp" class="form-text text-muted">This will open in a new window, changes currently made are <b>not</b> saved untill edit is complete.</small>

		<?php else: ?>
			<span style='color:red;'><b>Product is set as non-can't be sold.</b></span>
		<?php endif; ?>
		</div>
	  
	  
	  
      <div class="tab-pane fade" id="v-pills-varia" role="tabpanel" aria-labelledby="v-pills-varia-tab">
		   <div class="form-group">
			<label for="posologie">Posologie</label>
			<textarea class="form-control" name="posologie" id="posologie" rows="3"><?php echo (isset($product['posologie'])) ? $product['posologie']: '' ?></textarea>
		  </div>
		  
		<div class="form-row">
			<div class="col">
			<label for="toedieningsweg">Toedieningsweg</label>
			<input type="text" name="toedieningsweg" class="form-control" id="toedieningsweg" value="<?php echo (isset($product['toedieningsweg'])) ? $product['toedieningsweg']: '' ?>">
			</div>
			<div class="col">
			<label for="delay">delay</label>
			<input type="text" name="delay" class="form-control" id="delay" value="<?php echo (isset($product['delay'])) ? $product['delay']: '' ?>">
			</div>
		</div>		
		<div class="form-group">
			<label for="comment">comment</label>
			<textarea class="form-control" name="comment" id="comment" rows="3"><?php echo (isset($product['comment'])) ? $product['comment']: '' ?></textarea>
		</div>
		<h5 class="pt-5">Danger</h5>
		<hr />
	  		<a href="<?php echo base_url(); ?>products/delete_product/<?php echo $product['id']; ?>" class="btn btn-outline-danger"><i class="fas fa-trash-alt"></i> Remove Product</a><br/>
			<small>Note: product won't be removed from database, for accountancy reasons; But won't be available any longer.</small>
	  </div>		
    </div>
  </div>
</div>
	
	



		<hr>
			<div class="float-right">
				<?php if ($product) : ?>
				  <button type="submit" name="submit" value="edit" class="btn btn-primary">Edit</button>
				<?php else : ?>
				  <button type="submit" name="submit" value="add" class="btn btn-primary">Add</button>
				<?php endif; ?>
			</div>
		</form>
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
			// console.log(result);
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
	
	$("#gs1_datamatrix").keyup(function(){
		barcode = this.value;
		clearInterval(_changeInterval)
		_changeInterval = setInterval(function() {
		clearInterval(_changeInterval)
			process_datamatrix(barcode);
		
		}, 500);
	});

	$('#wholesale').select2({
		theme: 'bootstrap4',
		placeholder: 'Select Article',
		ajax: {
			url: '<?php echo base_url(); ?>wholesale/ajax_get_articles',
			dataType: 'json'
		},
	}).on("select2:selecting", function(e) { 
		$(this).addClass('is-valid');
		// also have bruto, but not sure if we always want to overwrite ...
		$("#vhb_input").val(e.params.args.data.vhb).addClass('is-valid');
		$("#input_producer").val(e.params.args.data.distr).addClass('is-valid');
		$("#input_wh_name").val(e.params.args.data.text).addClass('is-valid');
	});
});
</script>
  