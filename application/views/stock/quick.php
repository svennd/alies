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
				Stock / <?php echo $this->lang->line('add'); ?> / quick
			</div>
			<div class="card-body">
			Found <?php echo $lines; ?> lines to process. <br/>
			<?php if($delivery): ?>
			<table class="table">
				<tr>
					<td>Order</td>
					<td>Delivery</td>
					<td>my_ref</td>
					<td><?php echo $this->lang->line('product_name'); ?> / <?php echo $this->lang->line('product_wholesale'); ?></td>
					<td><?php echo $this->lang->line('price_wholesale'); ?></td>
					<td><?php echo $this->lang->line('price_dayprice'); ?></td>
					<td><?php echo $this->lang->line('volume'); ?></td>
					<td><?php echo $this->lang->line('lotnr'); ?></td>
					<td><?php echo $this->lang->line('eol'); ?></td>
					<td>imported</td>
				</tr>
			<?php foreach($delivery as $d): ?>
				<tr>
					<td><?php echo $d['order_date']; ?><br><small><?php echo $d['order_nr']; ?></small></td>
					<td><?php echo $d['delivery_date']; ?><br><small><?php echo $d['delivery_nr']; ?></small></td>
					<td><?php echo $d['my_ref']; ?></td>
					<td>
						<small><?php echo ($d['wholesale_id'] != 0) ? $d['wholesale']['description'] : $d['wholesale_artnr']; ?></small><br/>
						<?php if ($d['product_id'] != 0): ?>
							<a href="<?php echo base_url('products/profile/' . $d['product_id']); ?>"><?php echo $d['product']['name']; ?></a>
						<?php else: ?>
							<input type="text" name="product" class="form-control" id="autocomplete" value="" autofocus>
							<input type="hidden" name="pid" id="pid" value="" />
						<?php endif; ?>
					</td>
					<td><?php echo ($updated[$d['id']]) ? "<a href='". base_url('wholesale/get_history/' . $d['wholesale_id']). "' target='_blank'>". $d['bruto_price']. "(!)" : "<a href='". base_url('wholesale/get_history/' . $d['wholesale_id']). "' target='_blank'>". $d['bruto_price']; ?></a></td>
					<td>
						<div class="input-group">
							  <input type="text" class="form-control" name="in_price" id="current_buy_price" value="<?php echo $d['netto_price']; ?>">
							  <div class="input-group-append">
								<span class="input-group-text">&euro;</span>
							  </div>
							</div>	
					</td>
					<td><input type="text" class="form-control" name="in_price" id="amount" value="<?php echo $d['amount']; ?>"></td>
					<td><input type="text" class="form-control" name="in_price" id="" value="<?php echo $d['lotnr']; ?>"></td>
					<td><input type="text" class="form-control" name="in_price" id="" value="<?php echo $d['due_date']; ?>"></td>
					<td>
						<?php if($d['imported']): ?>
							imported
						<?php else: ?>
							<?php if(($d['product_id'] != 0)): ?>
								<button type="submit" name="submit" value="1" class="btn btn-success btn-sm">Add</button>
							<?php else: ?>
								<button type="submit" name="submit" value="1" class="btn btn-secondary btn-sm">Add</button>
							<?php endif; ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</table>

			<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
function process_datamatrix(barcode) {
	if (barcode.length > 26)
	{
		result = barcode.match(/01([0-9]{14})(10(.*?)17([0-9]{6})21(.*)|17([0-9]{6})10(.*))/);
		if(result)
		{
			var gsbarcode = result[1];
			var date = (typeof(result[3]) === 'undefined') ? result[6] : result[4];
			var lotnr = (typeof(result[3]) === 'undefined') ?  result[7] : result[3];
			var day = (date.substr(4,2) == "00") ? "01" : date.substr(4,2);
			
			// enter lotnr + date and disable them
			$("#lotnr").val(lotnr).prop("readonly", true);
			$("#date").val("20" + date.substr(0, 2) + "-" + date.substr(2,2) + "-" + day).prop("readonly", true);
			
			$.getJSON("<?php echo base_url(); ?>products/gs1_to_product?gs1=" + gsbarcode , function(data, status){
				if (data.state)
				{
					$("#pid").val(data[0].id);
					$("#autocomplete").val(data[0].name).prop("readonly", true);
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

	// this fails as the ID is NOT UNIQUE!!!
	$('#autocomplete').autocomplete({
		
		serviceUrl: '<?php echo base_url(); ?>products/get_product',
		
		onSelect: function (suggestion) {
			var res = suggestion.data;
			$("#pid").val(res.id);
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
				console.log("only one left fire!");
				$(this).autocomplete().onSelect(0);
				$("#amount").focus();
			}
		},
		autoSelectFirst: true,
		minChars: '2'
	});
});
</script>