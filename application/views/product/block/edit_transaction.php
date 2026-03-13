<p>Transactie info</p>

<div class="list-group mb-3 shadow">
	<div class="list-group-item list-group-item-action">
		<div class="row align-items-center">
			<div class="col">
				<strong class="mb-0"><?php echo $this->lang->line('buy'); ?></strong>
			</div>
			<div class="col-auto">
				<div class="form-inline">
					<div class="form-group">
						<input type="text" name="buy_volume" class="form-control form-control-sm" style="max-width:150px;" id="buy_volume" value="<?php echo (isset($product['buy_volume'])) ? $product['buy_volume']: '' ?>" required>
					</div>
					<div class="form-group mx-sm-1">
						<input type="text" name="unit_buy" class="form-control form-control-sm" style="max-width:100px;" id="unit_buy" value="<?php echo (isset($product['unit_buy'])) ? $product['unit_buy']: '' ?>" required>
					</div>
				</div>
				<p class="text-muted mb-0 small">volume, eenheid</p>
			</div>
		</div>
	</div>
	<div class="list-group-item list-group-item-action">
		<div class="row align-items-center">
			<div class="col">
				<strong class="mb-0"><?php echo $this->lang->line('sell'); ?></strong>
			</div>
			<div class="col-auto">
				<div class="form-inline">
					<div class="form-group">
						<input type="text" name="sell_volume" class="form-control form-control-sm" style="max-width:150px;" id="sell_volume" value="<?php echo (isset($product['sell_volume'])) ? $product['sell_volume']: '' ?>" required>
					</div>
					<div class="form-group mx-sm-1">
						<input type="text" name="unit_sell" class="form-control form-control-sm" style="max-width:100px;" id="unit_sell" value="<?php echo (isset($product['unit_sell'])) ? $product['unit_sell']: '' ?>" required>
					</div>
				</div>
				<p class="text-muted mb-0 small">volume, eenheid</p>
			</div>
		</div>
	</div>
	<div class="list-group-item list-group-item-action">
		<div class="row align-items-center">
			<div class="col">
				<strong class="mb-0"><?php echo $this->lang->line('buy_btw'); ?></strong>
			</div>
			<div class="col-auto">
				<div class="input-group input-group-sm">
					<input type="text" name="btw_buy" class="form-control form-control-sm" id="btw_buy" value="<?php echo (isset($product['btw_buy'])) ? $product['btw_buy']: '' ?>">
					<div class="input-group-append">
						<span class="input-group-text" id="basic-addon2">%</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="list-group-item list-group-item-action">
		<div class="row align-items-center">
			<div class="col">
				<strong class="mb-0"><?php echo $this->lang->line('sell_btw'); ?></strong>
			</div>
			<div class="col-auto">
				<select name="booking_code" class="form-control form-control-sm" id="booking_code">
					<?php foreach($booking as $t): ?>
						<option value="<?php echo $t['id']; ?>" <?php echo ($product && $t['id'] == $product['booking_code']) ? "selected='selected'":"";?>><?php echo $t['code'] . ' ' . $t['category'] . ' ' . $t['btw']  . '%'; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>
</div>