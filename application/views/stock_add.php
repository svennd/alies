<style>
.autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto; }
.autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
.autocomplete-selected { background: #F0F0F0; }
.autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
.autocomplete-group { padding: 2px 5px; }
.autocomplete-group strong { display: block; border-bottom: 1px solid #000; }
</style>

<div class="row">
	<div class="col-lg-5 mb-4">
		<div class="card shadow mb-4">		
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / Add
			</div>
			<div class="card-body">
				<form action="<?php echo base_url(); ?>stock/add_stock" method="post" autocomplete="off">
				  <div class="form-group">
					<label for="gs1_datamatrix">GS1 DataMatrix</label>
					<input type="text" name="gs1_datamatrix" class="form-control" id="gs1_datamatrix" autofocus>
				  </div>
				  <div class="form-group">
					<label for="product">product</label>
					<input type="text" name="product" class="form-control" id="autocomplete">
					<input type="hidden" name="pid" id="pid" value="">
					<input type="hidden" name="new_barcode_input" id="new_barcode_input" value="0">
					<input type="hidden" name="barcode_gs1" id="barcode_gs1" value="">
					<small id="product_tip">&nbsp;</small>
				  </div>
				  
					<div class="form-row mb-3">
					  <div class="col">
						<label for="lotnr">lot nr</label>
						<input type="text" name="lotnr" class="form-control" id="lotnr" value="">
					  </div>
					  <div class="col">
						<label for="date">End of Life</label>
						<input type="date" name="eol" class="form-control" id="date" value="">
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
							<label for="exampleFormControlInput3">Current Price</label>
							<div class="input-group mb-3">
							  <input type="text" class="form-control" name="in_price" id="buy" value="">
							  <div class="input-group-append">
								<span class="input-group-text">&euro;</span>
							  </div>
							</div>
							<small id="tip">Does not impact selling price!</small>
						</div>
						<div class="col">
							<label for="exampleFormControlInput3">Catalog Price</label>
							<input type="text" class="form-control" name="catalog_price" disabled id="catalog_price" value="">
						</div>
					</div>
					
				  <button type="submit" name="submit" value="1" class="btn btn-primary">Submit</button>
				</form>
			</div>
		</div>
	</div>
	<div class="col-lg-7 mb-4">
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
				$change = (isset($prod['in_price'])) ? round((($prod['in_price']-$prod['products']['buy_price'])/$prod['products']['buy_price'])*100) : '';
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
function process_datamatrix(barcode) {
	// console.log(barcode);
	// console.log(barcode.length);
	
	// GS1 data matrix 
	// 01 05420036903635 17 210400 10 111219
	// length : ~30 
	// 01 EAN/GTIN  (14 length)
	// 17 YY MM DD date (6 length)
	// 10 barcode (variable length)
	// 6 + 14 + 6 + x
	/**
0: "0105060249176305109947450-2 172209302124100317851583 "
1: "05060249176305"
2: "109947450-2 172209302124100317851583 "
3: "9947450-2 "
4: "220930"
5: "24100317851583 "
6: undefined
7: undefined

0: "01040072210261671722050010KP0EDBR"
1: "04007221026167"
2: "1722050010KP0EDBR"
3: undefined
4: undefined
5: undefined
6: "220500"
7: "KP0EDBR"
	*/
	if (barcode.length > 26)
	{
		// result = barcode.match(/01([0-9]{14})17([0-9]{6})10(.*)/);
		result = barcode.match(/01([0-9]{14})(10(.*?)17([0-9]{6})21(.*)|17([0-9]{6})10(.*))/);
		if(result)
		{
			// if first match (non vet) or second match (vet)
			if (typeof(result[3]) === 'undefined') {
				// vet
				var date = result[6];
				var day = (date.substr(4,2) == "00") ? "01" : date.substr(4,2);
				$("#lotnr").val(result[7]);
				$("#date").val("20" + date.substr(0, 2) + "-" + date.substr(2,2) + "-" + day);
			} else {
				var date = result[4];
				var day = (date.substr(4,2) == "00") ? "01" : date.substr(4,2);
				$("#lotnr").val(result[3]);
				$("#date").val("20" + date.substr(0, 2) + "-" + date.substr(2,2) + "-" + day);
			}
			
			$.getJSON("<?php echo base_url(); ?>products/gs1_to_product?gs1=" + result[1] , function(data, status){
				if (data.state)
				{
					$("#pid").val(data[0].id);
					$("#autocomplete").val(data[0].name);
					$("#sell").val(1);
					$("#buy").focus();

					$("#unit_buy").html(data[0].unit_buy);
					$("#unit_sell").html(data[0].unit_sell);
					$("#tip").html("Min buy volume, " + data[0].buy_volume + " " + data[0].unit_buy + " => sell volume, " + data[0].sell_volume + " " + data[0].unit_sell);
			
					$("#catalog_price").val(data[0].buy_price + " € / " + data[0].buy_volume + " " + data[0].unit_sell);

				}
				else 
				{ 
					$("#new_barcode_input").val(1);
					$("#barcode_gs1").val(result[1]);
					$("#product_tip").html("unknown gs1, please select product!"); 
					$("#autocomplete").focus();
				}
			});
		}
	}
	else
	{
		$("#product_tip").html("code to short not recognized"); 
	}	
}

document.addEventListener("DOMContentLoaded", function(){
	var _changeInterval = null;
	var barcode = null;
	
	$("#prd").show();
	$("#products").addClass('active');
	$("#stock").addClass('active');
	
	$("#gs1_datamatrix").keyup(function(){
		barcode = this.value;
		clearInterval(_changeInterval)
		_changeInterval = setInterval(function() {
		clearInterval(_changeInterval)
			// console.log(barcode);
			process_datamatrix(barcode);
		
		}, 500);
	});
		$('#autocomplete').autocomplete({
		
		serviceUrl: '<?php echo base_url(); ?>products/get_product',
		
		onSelect: function (suggestion) {
			
			var res = suggestion.data;
			$("#pid").val(res.id);
			$("#catalog_price").val(res.buy_price + " € / " + res.buy_volume + " " + res.unit_sell);

			$("#unit_buy").html(res.unit_buy);
			$("#unit_sell").html(res.unit_sell);
			$("#tip").html("Min buy volume, " + res.buy_volume + " " + res.unit_buy + " => sell volume, " + res.sell_volume + " " + res.unit_sell);
		},
		groupBy: 'type',
		minChars: '2'
	});
});
</script>
  