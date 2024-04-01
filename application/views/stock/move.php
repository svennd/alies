<style>
.autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto; }
.autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
.autocomplete-selected { background: #F0F0F0; }
.autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
.autocomplete-group { padding: 2px 5px; }
.autocomplete-group strong { display: block; border-bottom: 1px solid #000; }
</style>
<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">		
			<div class="card-header">
				<a href="<?php echo base_url('products'); ?>">Stock</a> / <?php echo $this->lang->line('move'); ?>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col">				
						<select name="from" class="form-control" id="type">
								<option value="">---</option>
							<?php foreach($stocks as $stock): if($stock['id'] == $user_location) { continue; } ?>
								<option value="<?php echo $stock['id']; ?>"><?php echo $stock['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col">		
						<select name="notupe" disabled class="form-control">
							<?php foreach($stocks as $stock): if($stock['id'] != $user_location) { continue; } ?>
								<option value="<?php echo $stock['id']; ?>"><?php echo $stock['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<br/>
				<div class="row">
					<div class="col">
						<input type="text" name="product" class="form-control" style="width:250px;" tabindex="0" id="autocomplete" placeholder="search" autocomplete="off" autofocus>
					</div>
					<div class="col"><div id="product-details"></div></div>

					<form action="" method="post" autocomplete="off">
									<div class="form-row">
										<div class="form-group col-md-4">
											<label for="product1un9pm">Product</label>
											<input type="text" readonly="" class="form-control-plaintext" id="product1un9pm" value="acular 10 ml">
										</div>
										<div class="form-group col-md-4">
											<label for="move_volume1un9pm">Volume</label>
											<div class="input-group">
											  <input type="text" class="form-control" id="move_volume1un9pm" name="move_volume[1un9pm]" required="">
											  <div class="input-group-append">
												<span class="input-group-text">fl</span>
												<span class="input-group-text">/ 2.00</span>
											  </div>
											</div>
										</div>
									</div>
										<input type="hidden" name="from_location" value="1">
										<button type="submit" name="submit" value="quantities" class="btn btn-success">Verplaatsen</button>
										<a href="http://localhost/alies/products" class="btn btn-danger ml-3">Annuleer</a>
									</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
const URL_PRODUCTS_API = "<?php echo base_url('stock/get_product_stock'); ?>";

document.addEventListener("DOMContentLoaded", function(){


function renderProductDetails(details) {
	const container = $("#product-details");

	details.id.forEach((id, index) => {
		const productRow = $("<div>").addClass("product-row");
		const productInfo = $("<div>").addClass("product-info");

	// for (const key in details) {
	// 	const label = $("<span>").addClass("label").text(key + ": ");
	// 	const value = Array.isArray(details[key]) ? details[key][index] : details[key];

	// 	productInfo.append(label, value + " ");
	// }
	var newRowHtml = `

		`;
	productRow.append(productInfo);
	container.append(productRow);
	});
}


$('#autocomplete').autocomplete({
		serviceUrl: function (el){
			return URL_PRODUCTS_API + '/' + $("#type").find(":selected").val() + '/';
		},
		onSelect: function (suggestion) {
            var data = suggestion.data;
			console.log(data);
			renderProductDetails(data);
		},
		// onSearchComplete: function (query, suggestion) { 
		// 	// fire onselect if only 1 results
		// 	if(suggestion.length == 1 && query.length > 10)
		// 	{
		// 		$(this).autocomplete().onSelect(0);
		// 		// $("#volume").focus();
		// 	}
		// },
		autoSelectFirst: true,
		showNoSuggestionNotice: true,
		minChars: '2'
	});
});
</script>