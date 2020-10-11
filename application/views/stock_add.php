<style>
.autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto; }
.autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
.autocomplete-selected { background: #F0F0F0; }
.autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
.autocomplete-group { padding: 2px 5px; }
.autocomplete-group strong { display: block; border-bottom: 1px solid #000; }
</style>

<div class="row">
	<div class="col-lg-4 mb-4">
		<div class="card shadow mb-4">		
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / Add
			</div>
			<div class="card-body">
				<form action="<?php echo base_url(); ?>stock/add_stock" method="post" autocomplete="off">
				  <div class="form-group">
					<label for="lotnr">product</label>
					<input type="text" name="product" class="form-control" id="autocomplete">
					<input type="hidden" name="pid" id="pid" value="">
				  </div>
				  
					<div class="form-row mb-3">
					  <div class="col">
						<label for="lotnr">lot nr</label>
						<input type="text" name="lotnr" class="form-control" id="lotnr">
					  </div>
					  <div class="col">
						<label for="date">End of Life</label>
						<input type="date" name="eol" class="form-control" id="date">
					  </div>
				  </div>
				  
					<div class="form-row mb-3">
						<div class="col">
							<label for="exampleFormControlInput3">New Volume (sellable)</label>
							<div class="input-group mb-3">
							  <input type="text" class="form-control" name="new_volume" id="sell" value="">
							  <div class="input-group-append">
								<span class="input-group-text" id="unit_sell">fl</span>
							  </div>
							</div>
							<small id="tip">&nbsp;</small>
						</div>
					</div>
					
					<div class="form-row mb-3">
						<div class="col">
							<label for="exampleFormControlInput3">Current Buy Price per buyable volume</label>
							<div class="input-group mb-3">
							  <input type="text" class="form-control" name="in_price" id="buy" value="">
							  <div class="input-group-append">
								<span class="input-group-text">&euro;</span>
							  </div>
							</div>
							<small id="tip">This does not impact selling price!</small>
						</div>
						<div class="col">
							<label for="exampleFormControlInput3">Catalog Price</label>
							<div class="input-group mb-3">
							  <input type="text" class="form-control" name="catalog_price" disabled id="catalog_price" value="">
							  <div class="input-group-append">
								<span class="input-group-text" id="catalog_unit">&euro;</span>
							  </div>
							</div>
						</div>
					</div>
					
				  <button type="submit" name="submit" value="1" class="btn btn-primary">Submit</button>
				</form>
			</div>
		</div>
	</div>
	<div class="col-lg-8 mb-4">
		<div class="card shadow mb-4">		
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / check
			</div>
			<div class="card-body">
			<?php if (isset($error) && $error): ?>
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Holy guacamole!</strong><br/> 
				<?php echo $error; ?>
				  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				  </button>
			</div>
			<?php endif; ?>
			<?php if($products): ?>
			<table class="table">
				<tr>
					<td>Name</td>
					<td>LotNr</td>
					<td>EOL</td>
					<td>In Price</td>
					<td>Volume</td>
					<td>Barcode</td>
					<td>Option</td>
				</tr>
			<?php foreach($products as $prod): ?>
			<?php
				$change = round((($prod['in_price']-$prod['products']['buy_price'])/$prod['products']['buy_price'])*100);
			?>
				<tr>
					<td><?php echo $prod['products']['name']; ?></td>
					<td><?php echo $prod['lotnr']; ?></td>
					<td><?php echo $prod['eol']; ?></td>
					<td><?php echo $prod['in_price']; ?> &euro; (<?php echo ($change > 0) ? '<span style="color:red;">+' . $change : '<span style="color:green;">' . $change; ?>%</span>)</td>
					<td><?php echo $prod['volume'] . ' ' . $prod['products']['unit_sell']; ?></td>
					<td><img src="<?php echo base_url(); ?>assets/barcode/<?php echo $prod['barcode']; ?>.png" alt="<?php echo $prod['barcode']; ?>"/></td>
					<td><a href="<?php echo base_url(); ?>stock/delete_stock/<?php echo $prod['id']; ?>" class="btn btn-danger"><i class="fas fa-trash-alt"></i></a></td>
				</tr>
			<?php endforeach; ?>
			</table>
			<a href="<?php echo base_url(); ?>stock/verify_stock" class="btn btn-success">Verify New Stock</a>
			<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#products").addClass('active');
	$("#stock").addClass('active');
	
		$('#autocomplete').autocomplete({
		
		serviceUrl: '<?php echo base_url(); ?>products/get_product',
		
		onSelect: function (suggestion) {
			console.log(suggestion.data);
			
			var res = suggestion.data;
			$("#pid").val(res.id);
			$("#catalog_price").val(res.buy_price);
			
			
			$("#unit_buy").html(res.unit_buy);
			$("#unit_sell").html(res.unit_sell);
			$("#tip").html("Min buy volume, " + res.buy_volume + " " + res.unit_buy + " => sell volume, " + res.sell_volume + " " + res.unit_sell);
			$("#catalog_unit").html("&euro; / " + res.buy_volume + " " + res.unit_sell);
		},
		groupBy: 'type',
		minChars: '2'
	});
});
</script>
  