<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>products">Products</a> /
		<?php if ($product) : ?>
		<a href="<?php echo base_url(); ?>products/product_list">List</a> /
		Edit Product / <?php echo (isset($product['name'])) ? $product['name']: '' ?>
		<?php else : ?>
		New Product
		<?php endif; ?>
	</div>
	
	<div class="card-body">
		<?php if (isset($update) && $update) : ?>
			<div class="alert alert-success alert-dismissible fade show" role="alert">
			  Product updated!
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			</div>
		<?php endif; ?>
		
		<?php if ($product) : ?>
			<form action="<?php echo base_url(); ?>products/product/<?php echo $product['id']; ?>" method="post" autocomplete="off">
		<?php else : ?>
			<form action="<?php echo base_url(); ?>products/product" method="post" autocomplete="off">
		<?php endif; ?>
		
		<div class="row">
			<div class="col-2">
				<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
					<a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">Product Info</a>
					<a class="nav-link" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">Stock</a>
					<a class="nav-link" id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">Transaction Info</a>
					<a class="nav-link" id="v-pills-vaccine-tab" data-toggle="pill" href="#v-pills-vaccine" role="tab" aria-controls="v-pills-settings" aria-selected="false">Vaccine</a>
				<?php if ($product) : ?>
					<?php if ($this->ion_auth->in_group("admin")): ?>
						<a class="nav-link" id="v-pills-price-tab" data-toggle="pill" href="#v-pills-price" role="tab" aria-controls="v-pills-varia" aria-selected="false">Price</a>
					<?php endif; ?>
				<?php endif; ?>
					<a class="nav-link" id="v-pills-varia-tab" data-toggle="pill" href="#v-pills-varia" role="tab" aria-controls="v-pills-varia" aria-selected="false">Varia</a>
				<?php if ($product) : ?>
					<?php if ($this->ion_auth->in_group("admin")): ?>
					<a class="nav-link" id="v-pills-danger-tab" data-toggle="pill" href="#v-pills-danger" role="tab" aria-controls="v-pills-danger" aria-selected="false">Danger</a>
					<?php endif; ?>
				<?php endif; ?>
				</div>
			</div>
			<div class="col-10">
    <div class="tab-content" id="v-pills-tabContent">
      <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
	  
	  	<h5>Product info</h5>
	<hr />
		<div class="form-row">
			<div class="col mb-3">
			<label for="exampleFormControlInput3">Name</label>
			<input type="text" name="name" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['name'])) ? $product['name']: '' ?>">
			</div>
			<div class="col mb-3">
			<label for="exampleFormControlInput3">Abbreviation</label>
			<input type="text" name="short_name" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['short_name'])) ? $product['short_name']: '' ?>">
			</div>
		</div>
	  
		<div class="form-row">
			<div class="col mb-3">
			<label for="exampleFormControlInput3">Producer</label>
			<input type="text" name="producer" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['producer'])) ? $product['producer']: '' ?>">
			</div>
			<div class="col mb-3">
			<label for="exampleFormControlInput3">Supplier</label>
			<input type="text" name="supplier" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['supplier'])) ? $product['supplier']: '' ?>">
			</div>
		</div>
		<div class="form-group">
			<label for="type">Type</label>
			<select name="type" class="form-control" id="type">
				<?php foreach($type as $t): ?>
					<option value="<?php echo $t['id']; ?>" <?php echo ($t['id'] == $product['type']) ? "selected='selected'":"";?>><?php echo $t['name']; ?></option>
				<?php endforeach; ?>
				<option value="0">Other</option>
			</select>
		</div>
		
		<h5>Advanced</h5>
		<hr />		
		<div class="form-group">
			<label for="exampleFormControlTextarea1">Dead volume</label>
			<input type="text" name="offset" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['offset'])) ? $product['offset']: '0' ?>">
			<small id="offsetHelp" class="form-text text-muted">Volume that is removed from stock but not injected.</small>
		</div> 		
	  </div>
      <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
	  
	<h5>Stock Requirements</h5>

	<?php if ($product) : ?>
	<?php $total_1m = 0.0; foreach ($history_1m as $h) { $total_1m += $h['volume']; } ?>
	<?php $total_6m = 0.0; foreach ($history_6m as $h) { $total_6m += $h['volume']; } ?>
	<?php $total_1y = 0.0; foreach ($history_1y as $h) { $total_1y += $h['volume']; } ?>
	<?php endif; ?>
	<hr />		
	<div class="form-group">
		<label for="limits">Min. requirement</label>
		<input type="text" name="limit_stock" class="form-control" id="limits" value="<?php echo (isset($product['limit_stock'])) ? $product['limit_stock']: '' ?>">
		<small id="limitHelp" class="form-text text-muted">Minimum sellable volumes that should be available; (global)</small>
		
		<?php if ($product) : ?>
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
		<?php endif; ?>
	</div> 
	  </div>
      <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
	  	<h5>Transaction info</h5>
	<hr />				  
	  <div class="form-group form-check">
		<input type="checkbox" class="form-check-input" name="sellable" value="1" id="exampleCheck1" <?php echo ($product['sellable']) ? "checked" : ""; ?>>
		<label class="form-check-label" for="exampleCheck1">verkoopbaar</label>
	  </div>	  
	  
		<div class="form-row">
			<div class="col">
			<label for="exampleFormControlInput3">Buy volume</label>
			<input type="text" name="buy_volume" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['buy_volume'])) ? $product['buy_volume']: '' ?>">
			</div>
			<div class="col-md-2">
			<label for="exampleFormControlInput3">Buy unit</label>
			<input type="text" name="unit_buy" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['unit_buy'])) ? $product['unit_buy']: '' ?>">
			<small id="unitHelp" class="form-text text-muted">kg, fles, doos</small>
			</div>
			<div class="col-md-1"><label for="exampleFormControlInput3">==></label></div>
			<div class="col">
			<label for="exampleFormControlInput3">Sell volume</label>
			<input type="text" name="sell_volume" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['sell_volume'])) ? $product['sell_volume']: '' ?>">
			<small class="form-text text-muted">min. 2 box, containing 4 strips : "8" "strips"</small>
			</div>
			<div class="col-md-2">
			<label for="exampleFormControlInput3">Sell Unit</label>
			<input type="text" name="unit_sell" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['unit_sell'])) ? $product['unit_sell']: '' ?>">
			</div>
		</div>
		
		<div class="form-row">
		<p>
			&nbsp;
		</p>
		</div>
		
		<div class="form-row">
			<div class="col">
			<label for="exampleFormControlInput3">BTW buy</label>
			<div class="input-group mb-3">
			  <input type="text" class="form-control" name="btw_buy" value="<?php echo (isset($product['btw_buy'])) ? $product['btw_buy']: '' ?>">
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
						<option value="<?php echo $t['id']; ?>" <?php echo ($t['id'] == $product['booking_code']) ? "selected='selected'":"";?>><?php echo $t['code'] . ' ' . $t['category'] . ' ' . $t['btw']  . '%'; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			</div>

		</div>
		
		  <div class="form-group">
			<label for="exampleFormControlInput3">buy price</label>
			<input type="text" name="buy_price" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['buy_price'])) ? $product['buy_price']: '' ?>">
		  </div>
		  <div class="form-group">
			<label for="exampleFormControlInput3">input_barcode</label>
			<input type="text" name="input_barcode" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['input_barcode'])) ? $product['input_barcode']: '' ?>">
		  </div>
	  </div>
	  
      <div class="tab-pane fade" id="v-pills-vaccine" role="tabpanel" aria-labelledby="v-pills-vaccine-tab">
		  <div class="form-group form-check">
			<input type="checkbox" class="form-check-input" name="vaccin" value="1" id="exampleCheck1" <?php echo ($product['vaccin']) ? "checked" : ""; ?>>
			<label class="form-check-label" for="exampleCheck1">Vaccin</label>
		  </div>	
			<div class="form-group">
				<label for="exampleFormControlTextarea1">Expire : </label>
				<input type="text" name="vaccin_freq" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['vaccin_freq'])) ? $product['vaccin_freq']: '0' ?>">
				<small id="offsetHelp" class="form-text text-muted">0 for no expire date</small>
			</div>
	  </div>	
	  
	  
      <div class="tab-pane fade" id="v-pills-price" role="tabpanel" aria-labelledby="v-pills-price-tab">
		<?php 
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
		?><br/>
		<a href="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>" target="_blank" class="btn btn-success">Edit Price</a>
	  </div>
	  
	  
	  
      <div class="tab-pane fade" id="v-pills-varia" role="tabpanel" aria-labelledby="v-pills-varia-tab">
		   <div class="form-group">
			<label for="exampleFormControlTextarea1">Posologie</label>
			<textarea class="form-control" name="posologie" id="exampleFormControlTextarea1" rows="3"><?php echo (isset($product['posologie'])) ? $product['posologie']: '' ?></textarea>
		  </div>
		  
		<div class="form-row">
			<div class="col">
			<label for="exampleFormControlInput3">Toedieningsweg</label>
			<input type="text" name="toedieningsweg" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['toedieningsweg'])) ? $product['toedieningsweg']: '' ?>">
			</div>
			<div class="col">
			<label for="exampleFormControlInput3">delay</label>
			<input type="text" name="delay" class="form-control" id="exampleFormControlInput3" value="<?php echo (isset($product['delay'])) ? $product['delay']: '' ?>">
			</div>
		</div>		
		<div class="form-group">
			<label for="exampleFormControlTextarea1">comment</label>
			<textarea class="form-control" name="comment" id="exampleFormControlTextarea1" rows="3"><?php echo (isset($product['comment'])) ? $product['comment']: '' ?></textarea>
		  </div>
	  </div>
	  
      <div class="tab-pane fade" id="v-pills-danger" role="tabpanel" aria-labelledby="v-pills-danger-tab">
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
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#products").addClass('active');
	$("#product_list").addClass('active');
});
</script>
  