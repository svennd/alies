<!-- manual adding -->
<style>
.autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto; }
.autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
.autocomplete-selected { background: #F0F0F0; }
.autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
.autocomplete-group { padding: 2px 5px; }
.autocomplete-group strong { display: block; border-bottom: 1px solid #000; }
</style>


<tr>
	<td>
		<input type="text" name="product" class="form-control form-control-sm" style="width:250px;" tabindex="0" id="autocomplete" placeholder="search" autocomplete="off" autofocus>
		<input type="hidden" id="new_pid" name="pid" value="" />
		<input type="hidden" id="product_or_proc" name="prod" value="" />
	</td>
	<td>
		<div class="input-group input-group-sm" style="width:125px;">
			<input type="text" name="volume" value="" class="form-control" id="amount" required>
			<div class="input-group-append">
				<span class="input-group-text" id="unit_sell">st</span>
			</div>
		</div>
	</td>
	<td><select class="form-control form-control-sm" style="width:175px;" name="barcode" id="stock_select" disabled></select></td>
	<td>		
		<a href="#" id="show_booking_select"></a>
		<div id="booking_select" style="display:none;">
			<select class="form-control form-control-sm" name="booking_code" id="hidden_booking">
			<?php foreach($booking_codes as $booking): ?>
				<option value="<?php echo $booking['id']; ?>"><?php echo $booking['category'] . " - " . $booking['code'] . " - " . $booking['btw'] . "%"; ?></option>
			<?php endforeach; ?>
			</select>
		</div>
		<input type="hidden" name="btw" value="" id="btw_sell">
		<input type="hidden" name="vaccin" value="" id="vaccin_or_no">
		<input type="hidden" name="vaccin_freq" value="" id="vaccin_freq">
	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>
		<button type="submit" class="btn btn-outline-success btn-sm" name="add_line" id="add_line"><i class="fas fa-plus"></i></button>
	</td>
</tr>	

<script>
function add_line()
{
	$.ajax({
		method: 'POST',
		url: '<?php echo base_url('events/add_line/'. $event_id . '/'); ?>' + $("#product_or_proc").val(),
		data: {
			line: $("#new_pid").val(),
			title: $("#autocomplete").val(),
			volume: $("#amount").val(),
			booking: $("#hidden_booking").val(),
			stock: $("#stock_select").val(),
			btw: $("#btw_sell").val(),
			vaccin: $("#vaccin_or_no").val(),
			vaccin_freq: $("#vaccin_freq").val(),
		},
		success: function(response) {
			add_table_line(JSON.parse(response));
			reset_input();
		},
		error: function(xhr, status, error) {
			let errorhtml = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Houston, we have a problem!</strong> Something didn't work. Please try refreshing.<br/>
				Technical error: ${error}
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				</div>`;
			$("#nav-home").before(errorhtml);
		}
	});
}

function add_table_line(info)
{
	var vatRate = parseFloat(info.vat.btw);
	var netPrice = parseFloat(info.price);
	var brutPrice = netPrice * (1 + vatRate / 100);
        
	var newRowHtml = `
		<tr>
			<td>${info.title}</td>
			<td>${info.volume}</td>
			<td>&nbsp;</td>
			<td>${vatRate} % ${info.vat.category}</td>
			<td>${brutPrice.toFixed(2)}</td>
			<td>${netPrice}</td>
		</tr>
	`;
	
	// Insert the new row after the first row
	$("#invoice_table tbody tr:first").after(newRowHtml);
}

function reset_input()
{
	$("#autocomplete").val("");
	$("#new_pid").val("");
	$("#product_or_proc").val("");
	$("#amount").val("");
	$("#hidden_booking").val("");
	$("#stock_select").val("");
	$("#vaccin_or_no").val("");	
	$("#vaccin_freq").val("");
}

document.addEventListener("DOMContentLoaded", function(){

    $("#add_line").click(function() {
		add_line();
    });

	// if on enter we want to push the line
    $("#amount").on("keydown", function(event) {
        if (event.which === 13) {
			add_line();
        }
    });

});

</script>
