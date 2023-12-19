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
		<input type="text" name="product" class="form-control form-control-sm" tabindex="0" id="autocomplete" placeholder="search" autocomplete="off" autofocus>
		<input type="hidden" id="new_pid" name="pid" value="" />
		<input type="hidden" id="product_or_proc" name="prod" value="" />
	</td>
	<td>
		<div class="form-inline">
			<div class="input-group input-group-sm">
				<input type="text" name="volume" value="" class="form-control" placeholder="volume" id="volume" required>
				<div class="input-group-append">
					<span class="input-group-text" id="unit_sell">st</span>
				</div>
			</div>
		</div>
	</td>
	<td class="d-none d-sm-table-cell">
		<div class="form-inline">
			<select class="form-control form-control-sm" placeholder="lotnr" name="barcode" id="stock_select" disabled><option>&nbsp;&nbsp;&nbsp;&nbsp;---&nbsp;&nbsp;&nbsp;&nbsp;</option></select>
		</div>
	</td>
	<td class="d-none d-sm-table-cell">		
		<a href="#" id="show_booking_select"></a>
		<div id="booking_select" style="display:none;">
			<select class="form-control form-control-sm" name="booking_code" id="hidden_booking">
			<?php foreach($booking_codes as $booking): ?>
				<option value="<?php echo $booking['id']; ?>"><?php echo $booking['category'] . " - " . $booking['code'] . " - " . $booking['btw'] . "%"; ?></option>
			<?php endforeach; ?>
			</select>
		</div>
		<input type="hidden" name="btw" value="" id="btw_sell">
		<input type="hidden" name="booking" value="" id="booking_default">
		<input type="hidden" name="vaccin" value="" id="vaccin_or_no">
		<input type="hidden" name="vaccin_freq" value="" id="vaccin_freq">
	</td>
	<td class="d-none d-sm-table-cell">&nbsp;</td>
	<td class="d-none d-sm-table-cell">&nbsp;</td>
	<td class="d-none d-sm-table-cell">
		<button type="submit" class="btn btn-outline-success btn-sm" name="add_line" id="add_line"><i class="fas fa-plus"></i></button>
	</td>
</tr>	
