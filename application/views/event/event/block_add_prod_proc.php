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
		<form id="prod_or_proc_form" name="new_product_form" action="<?php echo base_url('events/add_proc_prod/' . $event_id); ?>" method="post" autocomplete="off">
			<input type="text" name="product" class="form-control form-control-sm" style="width:250px;" tabindex="0" id="autocomplete" placeholder="search" autocomplete="off" autofocus>
			<input type="hidden" id="new_pid" name="pid" value="" />
			<input type="hidden" id="product_or_proc" name="prod" value="" />
		</form>
	</td>
	<td>
		<div class="input-group input-group-sm" style="width:125px;">
			<input type="text" name="volume" form="prod_or_proc_form" value="" class="form-control" id="amount" required>
			<div class="input-group-append">
				<span class="input-group-text" id="unit_sell">st</span>
			</div>
		</div>
	</td>
	<td><select class="form-control form-control-sm" style="width:175px;" form="prod_or_proc_form" name="barcode" id="stock_select" disabled></select></td>
	<td>		
		<a href="#" id="show_booking_select"></a>
		<div id="booking_select" style="display:none;">
			<select class="form-control" form="prod_or_proc_form" name="booking_code" id="hidden_booking">
			<?php foreach($booking_codes as $booking): ?>
				<option value="<?php echo $booking['id']; ?>"><?php echo $booking['category'] . " - " . $booking['code'] . " - " . $booking['btw'] . "%"; ?></option>
			<?php endforeach; ?>
			</select>
		</div>
		<input type="hidden" name="btw" value="" form="prod_or_proc_form" id="btw_sell">
		<input type="hidden" name="booking" value="" form="prod_or_proc_form" id="booking_default">
		<input type="hidden" name="vaccin" value="" form="prod_or_proc_form" id="vaccin_or_no">
		<input type="hidden" name="vaccin_freq" value="" form="prod_or_proc_form" id="vaccin_freq">
	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>
		<button type="submit" form="prod_or_proc_form" class="btn btn-outline-success btn-sm"><i class="fas fa-plus"></i></button>
	</td>
</tr>	