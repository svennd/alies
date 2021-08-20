<!-- barcode -->
<tr>
	<td>
		<form id="barcode_prod_or_proc_form" name="new_product_form" action="<?php echo base_url(); ?>events/add_proc_prod/<?php echo $event_id; ?>" method="post" autocomplete="off">
		<input type="text" name="product" class="form-control mb-2 mr-sm-2" id="name_by_barcode" autocomplete="off" disabled>
		<input type="hidden" id="barcode_new_pid" name="pid" value="" />
		<input type="hidden" id="barcode_barcode" name="barcode" value="" />
		<input type="hidden" name="prod" value="1" />
		</form>
	</td>
	<td><input type="text" name="barcode" class="form-control mb-2 mr-sm-2" id="barcode_field" placeholder="barcode" autocomplete="off"></td>
	<td id="price_ajax_request">&nbsp;</td>
	<td>
		<div class="input-group" style="width:175px;">
			<input type="text" name="volume" form="barcode_prod_or_proc_form" value="" class="form-control" id="barcode_amount">
			<div class="input-group-append">
				<span class="input-group-text" id="barcode_unit_sell">st</span>
			</div>
		</div>
	</td>
	<td>		
		<a href="#" id="barcode_show_booking_select"></a>
		<div id="barcode_booking_select" style="display:none;">
			<select class="form-control mb-2 mr-sm-2" form="barcode_prod_or_proc_form" name="booking_code" id="barcode_hidden_booking">
			<?php foreach($booking_codes as $booking): ?>
				<option value="<?php echo $booking['id']; ?>"><?php echo $booking['code'] . " - " . $booking['btw'] . "%"; ?></option>
			<?php endforeach; ?>
			</select>
		</div>
	</td>
	<td></td>
	<td><button type="submit" form="barcode_prod_or_proc_form" class="btn btn-outline-success btn-sm"><i class="fas fa-plus"></i></button>
	</td>
</tr>
