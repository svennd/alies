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
				Stock / <?php echo $this->lang->line('add'); ?>
			</div>
			<div class="card-body">
				<form action="<?php echo base_url(); ?>stock/add_stock" method="post" autocomplete="off">
				  <div class="form-group" id="matrix" style="display:none;">
					<label for="gs1_datamatrix">GS1 DataMatrix</label>
					<input type="text" name="gs1_datamatrix" class="form-control" id="gs1_datamatrix">
				  </div>
				  <div class="form-group">
					<label for="autocomplete"><?php echo $this->lang->line('product') . ' ' . $this->lang->line('or') . ' ' . $this->lang->line('gs1_barcode'); ?></label>
					<div class="input-group">
						<input type="text" name="product" class="form-control" id="autocomplete" value="<?php echo ($preselected) ? $preselected['name']: '' ?>" autofocus>
						<div class="input-group-append" style="display:none" id="reset_button">
							<button class="btn btn-outline-danger" id="reset_search" type="button"><i class="fa-solid fa-ban"></i></button>
						</div>
					</div>
					<input type="hidden" name="pid" id="pid" value="<?php echo ($preselected) ? $preselected['id']: '' ?>">
					<input type="hidden" name="new_barcode_input" id="new_barcode_input" value="0">
					<input type="hidden" name="barcode_gs1" id="barcode_gs1" value="">
					<small id="product_tip">&nbsp;</small>
				  </div>
				  
				<div class="form-row mb-3">
					  <div class="col">
						<label for="lotnr"><?php echo $this->lang->line('lotnr'); ?></label>
						<input type="text" name="lotnr" class="form-control" id="lotnr" value="">
					  </div>
					  <div class="col">
						<label for="date"><?php echo $this->lang->line('eol'); ?></label>
						<input type="date" name="eol" class="form-control" id="date" value="">
					  </div>
				  </div>
				  
					<div class="form-row mb-3">
						<div class="col">
							<label for="sell"><?php echo $this->lang->line('sellable_volume'); ?></label>
							<div class="input-group mb-3">
							  <input type="text" class="form-control" name="new_volume" id="sell" value="">
							  <div class="input-group-append">
								<span class="input-group-text" id="unit_sell"><?php echo ($preselected) ? $preselected['unit_buy']: 'fl' ?></span>
							  </div>
							</div>
							<small id="tip">&nbsp;</small>
						</div>
					</div>
				  
					<div class="form-row mb-3">
						<div class="col">
							<label for="sell"><?php echo $this->lang->line('supplier'); ?></label>
							<div class="input-group mb-3">
							  <input type="text" class="form-control" name="supplier" id="supplier" placeholder="" value="">
							</div>
						</div>
					</div>
					
					<div class="form-row mb-3">
						<div class="col">
							<label for="current_buy_price"><?php echo $this->lang->line('price_dayprice'); ?></label>
							<div class="input-group mb-3">
							  <input type="text" class="form-control" name="in_price" id="current_buy_price" value="">
							  <div class="input-group-append">
								<span class="input-group-text">&euro;</span>
							  </div>
							</div>
							<small id="tip">Does not impact selling price!</small>
						</div>
						<div class="col">
							<label for="catalog_price"><?php echo $this->lang->line('price_alies'); ?></label>
							<input type="text" class="form-control" name="catalog_price" disabled id="catalog_price" value="">
						</div>
					</div>
					
				  <button type="submit" name="submit" value="1" class="btn btn-success"><?php echo $this->lang->line('add'); ?></button>
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
				<!-- i want to see the world burn -->
				<script>
				o=(A=new AudioContext()).createOscillator();o.connect(A.destination);o.start(0);setTimeout('o.stop(0)',500)
				</script>
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
					<td><?php echo $this->lang->line('name'); ?></td>
					<td><?php echo $this->lang->line('lotnr'); ?></td>
					<td><?php echo $this->lang->line('eol'); ?></td>
					<td><?php echo $this->lang->line('price_dayprice'); ?></td>
					<td><?php echo $this->lang->line('volume'); ?></td>
					<td><?php echo $this->lang->line('option'); ?></td>
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
					<td><a href="<?php echo base_url('stock/delete_stock/' . $prod['id']); ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></a></td>
				</tr>
			<?php endforeach; ?>
			</table>
			
			<form action="<?php echo base_url('stock/verify_stock'); ?>" method="post" autocomplete="off">
				<hr>
				<div class="form-group">
					<label for="delivery_slip"><?php echo $this->lang->line('delivery_date'); ?></label>
					<input type="date" name="regdate" class="form-control" id="date" value="<?php echo date('Y-m-d') ?>">
				</div>
				<div class="form-group">
					<label for="delivery_slip"><?php echo $this->lang->line('comment'); ?></label>
					<textarea class="form-control" name="delivery_slip" id="delivery_slip" rows="3"></textarea>
				</div>
				<button type="submit" name="submit" value="1" class="btn btn-sm btn-primary"><i class="fas fa-shipping-fast"></i> <?php echo $this->lang->line('verify_stock'); ?></button>
			</form>
			<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

const PRODUCT_GS1_LOOKUP = '<?php echo base_url('products/gs1_to_product?gs1='); ?>';
const PRODUCT_LOOKUP = '<?php echo base_url('products/get_product'); ?>';
function getLastDayOfMonth(year, month) {
  // Month in JavaScript is 0-indexed (0 for January, 1 for February, etc.)
  // So, subtract 1 from the provided month to get the correct month in the Date object.
  // 0 = last day of previous month
  var lastDay = new Date(year, month, 0);

  // getDate() returns the day of the month, which is the last day in this case.
  return lastDay.getDate();
}

function process_datamatrix(barcode) {
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
		result = barcode.match(/01([0-9]{14})(10(.*?)17([0-9]{6})21(.*)|17([0-9]{6})10(.*))/);
		if(result)
		{
			var gsbarcode = result[1];
			var date = (typeof(result[3]) === 'undefined') ? result[6] : result[4];
			var lotnr = (typeof(result[3]) === 'undefined') ?  result[7] : result[3];
			var year = "20" + date.substr(0, 2);
			var month = date.substr(2,2);
			var day = (date.substr(4,2) == "00") ? getLastDayOfMonth(year, month) : date.substr(4,2);
			
			
			// enter lotnr + date and disable them
			$("#lotnr").val(lotnr).prop("readonly", true);
			$("#date").val( year + "-" + month + "-" + day).prop("readonly", true);
			
			$.getJSON(PRODUCT_GS1_LOOKUP + gsbarcode , function(data, status){
				if (data.state)
				{
					$("#pid").val(data[0].id);
					$("#autocomplete").val(data[0].name).prop("readonly", true);
					$("#sell").val(1);
					$("#buy").focus();

					$("#supplier").attr("placeholder", data[0].supplier);
					$("#unit_buy").html(data[0].unit_buy);
					$("#unit_sell").html(data[0].unit_sell);
					$("#tip").html("Min buy volume, " + data[0].buy_volume + " " + data[0].unit_buy + " => sell volume, " + data[0].sell_volume + " " + data[0].unit_sell);
			
					$("#catalog_price").val(data[0].buy_price + " € / " + data[0].buy_volume + " " + data[0].unit_sell);
					$("#current_buy_price").val(data[0].buy_price);
					$("#sell").focus();

					$('#autocomplete').autocomplete().disable();
				}
				else 
				{
					// need to re-enable everything.
					$("#new_barcode_input").val(1);
					$("#barcode_gs1").val(gsbarcode);
					$("#product_tip").html("unknown GS1, please select product!"); 
				
					$("#autocomplete").val("").focus();
					$("#gs1_datamatrix").val(barcode);
					$("#matrix").show();
				}
			});
			
			// getJSON is out of sync
			return true;
		}
		else 
		{
			$("#product_tip").html("invalid code; not recognized"); 
		}
	}
	return false;
}

document.addEventListener("DOMContentLoaded", function(){
	var _changeInterval = null;
	var barcode = null;
	
	// if html autofocus fails
	$("#autocomplete").focus();

	$("#product_list").addClass('active');
	
	$("#gs1_datamatrix").keyup(function(){
		barcode = this.value;
		clearInterval(_changeInterval)
		_changeInterval = setInterval(function() {
		clearInterval(_changeInterval)
			process_datamatrix(barcode);
		
		}, 500);
	});
	$('#autocomplete').autocomplete({
		
		serviceUrl: PRODUCT_LOOKUP,
		
		onSelect: function (suggestion) {
			var res = suggestion.data;
			$("#pid").val(res.id);
			$("#catalog_price").val(res.buy_price + " € / " + res.buy_volume + " " + res.unit_buy);
			$("#current_buy_price").val(res.buy_price);

			$("#unit_buy").html(res.unit_buy);
			$("#unit_sell").html(res.unit_sell);
			$("#supplier").attr("placeholder", res.supplier);
			$("#tip").html("Min buy volume, " + res.buy_volume + " " + res.unit_buy + " => sell volume, " + res.sell_volume + " " + res.unit_sell);
			$("#lotnr").focus();
			$("#reset_button").show();
		},
		onSearchComplete: function (query, suggestion) { 
			if(query.length > 26)
			{
				clearInterval(_changeInterval)
				_changeInterval = setInterval(function() {
					clearInterval(_changeInterval)
					process_datamatrix(query);
				}, 500);

			}
			else if(suggestion.length == 1)
			{
				$(this).autocomplete().onSelect(0);
			}
		},
		autoSelectFirst: true,
		minChars: '2'
	});

	$("#reset_search").on("click", function() {
		$("#autocomplete").val("").focus().prop("readonly", false);
		$("#pid").val("").prop("readonly", false);
		$("#lotnr").val("").prop("readonly", false);
		$("#date").val("").prop("readonly", false);
		$("#sell").val("");
		$("#buy").val("");
		$("#supplier").attr("placeholder","");
		$("#current_buy_price").val("");
		$("#catalog_price").val("");
		$("#unit_buy").html("");
		$("#unit_sell").html("");
		$("#tip").html("");
		$("#reset_button").hide();
		$("#product_tip").html("");
		$("#new_barcode_input").val(0);
		$("#barcode_gs1").val("");
		$("#matrix").hide();
		$('#autocomplete').autocomplete().enable();
	});
});
</script>
  