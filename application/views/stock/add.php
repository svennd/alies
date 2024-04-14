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
				<form action="<?php echo base_url('stock/add_stock'); ?>" method="post" autocomplete="off">
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
					<!-- GTIN only -->
					<input type="hidden" name="barcode_gs1" id="barcode_gs1" value="">
					<small id="product_tip">&nbsp;</small>
				  </div>
				  
				<div class="form-row mb-3">
					  <div class="col">
						<label for="lotnr"><?php echo $this->lang->line('lotnr'); ?></label>
						<input type="text" name="lotnr" class="form-control" id="lotnr" value="" required>
					  </div>
					  <div class="col">
						<label for="date"><?php echo $this->lang->line('eol'); ?></label>
						<input type="date" name="eol" class="form-control" id="date" value="" required>
					  </div>
				  </div>
				  
					<div class="form-row mb-3">
						<div class="col">
							<label for="sell"><?php echo $this->lang->line('sellable_volume'); ?></label>
							<div class="input-group">
							  <input type="text" class="form-control" name="new_volume" id="sell" value="" required>
							  <div class="input-group-append">
								<span class="input-group-text" id="unit_sell"><?php echo ($preselected) ? $preselected['unit_buy']: 'fl' ?></span>
							  </div>
							</div>
							<small id="tip">&nbsp;</small>
						</div>
					</div>
				  
					<div class="form-row mb-3">
						<div class="col">
							<label for="current_buy_price"><?php echo $this->lang->line('price_dayprice'); ?></label>
							<div class="input-group">
							  <input type="text" class="form-control" name="in_price" id="current_buy_price" value="">
							  <div class="input-group-append">
								<span class="input-group-text">&euro;</span>
							  </div>
							</div>
						</div>
					</div>

					<div class="form-row mb-3">
						<div class="col">
							<label for="supplier"><?php echo ucfirst($this->lang->line('supplier')); ?></label>
							<div class="input-group">
							  <input type="text" class="form-control" name="supplier" id="supplier" placeholder="" value="">
							</div>
						</div>
					</div>
					
				  <button type="submit" name="submit" value="1" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-cart-plus"></i> <?php echo $this->lang->line('add'); ?></button>
				</form>
			</div>
		</div>
	</div>
	<div class="col-lg-7 mb-4">
		<div class="card shadow mb-4">		
			<div class="card-header">
				<a href="<?php echo base_url('stock'); ?>">Stock</a> / check
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
			<table class="table table-sm">
				<tr>
					<td><?php echo $this->lang->line('name'); ?></td>
					<td><?php echo $this->lang->line('lotnr'); ?></td>
					<td><?php echo $this->lang->line('eol'); ?></td>
					<td><?php echo $this->lang->line('price_dayprice'); ?></td>
					<td><?php echo $this->lang->line('volume'); ?></td>
					<td><?php echo $this->lang->line('option'); ?></td>
				</tr>
			<?php foreach($products as $prod): ?>
				<tr>
					<td><?php echo $prod['products']['name']; ?></td>
					<td><?php echo $prod['lotnr']; ?></td>
					<td><?php echo user_format_date($prod['eol'], $user->user_date); ?></td>
					<td><?php echo $prod['in_price']; ?> &euro;</td>
					<td><?php echo $prod['volume'] . ' ' . $prod['products']['unit_sell']; ?></td>
					<td><a href="<?php echo base_url('stock/delete_stock/' . $prod['id']); ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></a></td>
				</tr>
			<?php endforeach; ?>
			</table>
			<div class="text-right">
				<a href="<?php echo base_url('stock/verify_stock'); ?>" class="btn btn-sm btn-outline-primary mr-3"><i class="fas fa-shipping-fast"></i> <?php echo $this->lang->line('verify_stock'); ?></a>
			</div>
			<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

const PRODUCT_LOOKUP = '<?php echo base_url('products/get_product'); ?>';
function getLastDayOfMonth(year, month) {
  // Month in JavaScript is 0-indexed (0 for January, 1 for February, etc.)
  // So, subtract 1 from the provided month to get the correct month in the Date object.
  // 0 = last day of previous month
  var lastDay = new Date(year, month, 0);

  // getDate() returns the day of the month, which is the last day in this case.
  return lastDay.getDate();
}

function new_gs1(gs1)
{
	$("#gs1_datamatrix").val($("#autocomplete").val());
	$("#new_barcode_input").val(1);
	$("#barcode_gs1").val(gs1.GTIN);
	$("#autocomplete").val("");
	$("#lotnr").val(gs1.LOTNR);
	$("#date").val(gs1.EXP_DATE);
	$("#product_tip").html("New GS1 barcode detected, please fill in the details");
	$("#matrix").show();
}

function read_gs1(gs1) {
    $("#lotnr").val(gs1.LOTNR);
    console.log(gs1);
    if (gs1.EXP_DATE || gs1.DUE_DATE || gs1.BEST_BEFORE_DATE || gs1.SELL_BY_DATE) {
        $("#date").val(gs1.EXP_DATE || gs1.DUE_DATE || gs1.BEST_BEFORE_DATE || gs1.SELL_BY_DATE);
        $("#sell").focus();
    } else {
        $("#date").focus();
    }
}

function read_product(res)
{
	$("#pid").val(res.id);

	$("#unit_buy").html(res.unit_buy);
	$("#unit_sell").html(res.unit_sell);
	$("#supplier").attr("placeholder", res.supplier);
	$("#tip").html("Min buy volume, " + res.buy_volume + " " + res.unit_buy + " => sell volume, " + res.sell_volume + " " + res.unit_sell);
	$("#lotnr").focus();
}

// 010084016450685321200429323663[GS]1726093010A104212
document.addEventListener("DOMContentLoaded", function(){
	var _changeInterval = null;
	var barcode = null;
	
	// if html autofocus fails
	$("#autocomplete").focus();

	$("#add_stock").addClass('active');
	
	$('#autocomplete').autocomplete({
		
		serviceUrl: PRODUCT_LOOKUP,
		onSelect: function (suggestion) {
			var res = suggestion.data;
			read_product(res);
			if (res.gs1) {
				read_gs1(res.gs1);
			}
			$("#reset_button").show();
		},
		transformResult: function(response, query) {
			// Process the raw JSON results here
			resp = JSON.parse(response);

			// in case its a new product
			if (resp.suggestions.length == 0 && resp.gs1 !== undefined) { new_gs1(resp.gs1); }
			
			// return default mapping
			return {
				suggestions: $.map(resp.suggestions, function(dataItem) {
					return { value: dataItem.value, data: dataItem.data };
				})
        	};
    	},
		onSearchComplete: function (query, suggestion) {
			// in case its a barcode
			// autoselect
			if(suggestion.length == 1 && suggestion[0].data.gs1 !== undefined)
			{
				$(this).autocomplete().onSelect(0);
			}
		},
		autoSelectFirst: true,
		minChars: '2',
		deferRequestBy: 10
	});

	$("#reset_search").on("click", function() {
		$("#autocomplete").val("").focus().prop("readonly", false);
		$("#pid").val("").prop("readonly", false);
		$("#lotnr").val("").prop("readonly", false);
		$("#date").val("").prop("readonly", false);
		$("#sell").val("");
		$("#buy").val("");
		$("#supplier").attr("placeholder","");
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
  