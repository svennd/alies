<style>
.list-group-item {
    margin-bottom: 0;
}

.toggle-hide {
	background-color: #e7f5e7;
	display: none;
}

.toggle-hide:hover {
	background-color: #e7f5e7;
}
</style>

<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url('products'); ?>">Products</a> /
		<?php echo $this->lang->line('edit'); ?> / <a href="<?php echo base_url('products/profile/' . $product['id']); ?>"><?php echo (isset($product['name'])) ? $product['name']: '' ?></a>
	</div>
	
	<div class="card-body">
		<!-- modify success -->
		<?php if (isset($update) && $update) : ?>
			<div class="alert alert-success alert-dismissible fade show" role="alert">
			  Product updated! Want to :
				<ul>
					<li><a href="<?php echo base_url('stock/add_stock/' . $product['id']); ?>">add stock</a></li>
					<li><a href="<?php echo base_url('products/profile/' . $product['id']); ?>">see stock</a></li>
				</ul>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
		<?php endif; ?>
		
		<form action="<?php echo base_url('products/product/' . $product['id']); ?>" method="post" autocomplete="off">

		<strong><?php echo $this->lang->line('product_sheet'); ?></strong>
		<p><?php echo $this->lang->line('product_name_info'); ?></p>

		<div class="list-group mb-3 shadow">
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('product_name'); ?>*</strong>
						<p class="text-muted mb-0"><?php echo $this->lang->line('product_name_base_name'); ?></p>
					</div>
					<div class="col">
						<input type="text" class="form-control" id="product_name" name="name" placeholder="" value="<?php echo (isset($product['name'])) ? ($product['name']) : ''; ?>" required>
					</div>
				</div>
			</div>
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('type'); ?></strong>
						<p class="text-muted mb-0"></p>
					</div>
					<div class="col-auto">
						<select name="type" class="form-control" style="width: 225px;" id="type">
							<?php foreach($type as $t):?>
								<option value="<?php echo $t['id']; ?>" <?php echo ($product && $t['id'] == $product['type']) ? "selected='selected'" : ""; ?>>
								<?php echo ucfirst($t['name']); ?></option>
							<?php endforeach; ?>
							<option value="0">Other</option>
						</select>
					</div>
				</div>
			</div>
			<!-- <div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('abbreviation'); ?></strong>
						<p class="text-muted mb-0"><?php echo $this->lang->line(''); ?></p>
					</div>
					<div class="col-auto">
						<input type="text" class="form-control" id="abbr" name="short_name" placeholder="" value="<?php echo (isset($product['short_name'])) ? ($product['short_name']) : ''; ?>" required>
					</div>
				</div>
			</div> -->
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo ucfirst($this->lang->line('supplier')); ?></strong>
						<p class="text-muted mb-0"><?php echo $this->lang->line(''); ?></p>
					</div>
					<div class="col-auto">
						<input type="text" class="form-control" id="supplier" name="supplier" placeholder="" value="<?php echo (isset($product['supplier'])) ? ($product['supplier']) : ''; ?>" required>
					</div>
				</div>
			</div>
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo ucfirst($this->lang->line('comment')); ?></strong>
						<p class="text-muted mb-0"><?php echo $this->lang->line('admin_comment'); ?></p>
					</div>
					<div class="col">
						<textarea class="form-control" name="comment_admin" id="comment_admin" rows="3"><?php echo (isset($product['comment_admin'])) ? $product['comment_admin']: '' ?></textarea>
					</div>
				</div>
			</div>

<!-- 
<div class="list-group-item list-group-item-action">
	<div class="row align-items-center">
		<div class="col">
			<strong class="mb-0">State Icon</strong>
			<p class="text-muted mb-0">Choose one icon to indicate product state</p>
		</div>
		<div class="col">
			<div class="d-flex flex-wrap">
				<label class="d-flex align-items-center mr-4">
					<input type="radio" name="state_icon" value="" checked>
					<span class="ml-2">None</span>
				</label>
				<label class="d-flex align-items-center mr-4">
					<input type="radio" name="state_icon" value="fa-syringe">
					<i style="color:#17A2B8" class="fas fa-syringe ml-2"></i>
				</label>
				<label class="d-flex align-items-center mr-4">
					<input type="radio" name="state_icon" value="fa-star">
					<i style="color:#FFC107" class="fas fa-star ml-2"></i>
				</label>
				<label class="d-flex align-items-center mr-4">
					<input type="radio" name="state_icon" value="fa-box-open">
					<i style="color:orange"class="fas fa-box-open ml-2"></i>
				</label>
				<label class="d-flex align-items-center mr-4">
					<input type="radio" name="state_icon" value="fa-tag">
					<i style="color:#28A745" class="fas fa-tag ml-2"></i>
				</label>
				<label class="d-flex align-items-center mr-4">
					<input type="radio" name="state_icon" value="fa-ban">
					<i style="color:#6C757D" class="fas fa-ban ml-2"></i>
				</label>
			</div>
		</div>
	</div>
</div> -->






		</div>

		<strong><?php echo $this->lang->line('wholesale'); ?></strong>
		<p><?php echo $this->lang->line('wholesale_info'); ?></p>

		<div class="list-group mb-3 shadow">
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('wholesale_link'); ?></strong>
						<p class="text-muted mb-0"><?php echo $this->lang->line('wholesale_link_info'); ?></p>
					</div>
					<div class="col-auto">
						<select name="wholesale" class="form-control <?php echo ($product['wholesale']) ? 'is-valid' : 'is-invalid'; ?>" style="width: 225px;" id="wholesale" data-allow-clear="1">
							<?php if($product['wholesale']): ?>
							<option value="<?php echo $product['wholesale']; ?>" selected>selected : id <?php echo $product['wholesale']; ?></option>
							<?php endif; ?>
						</select>
					</div>
				</div>
			</div>
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('product_wholesale'); ?></strong>
					</div>
					<div class="col-auto">
						<input type="text" class="form-control form-control-sm" id="input_wh_name" name="input_wh_name" placeholder="" value="<?php echo (isset($product['wholesale_name'])) ? ($product['wholesale_name']) : ''; ?>">
					</div>
				</div>
			</div>
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('producer'); ?></strong>
					</div>
					<div class="col-auto">
						<input type="text" class="form-control form-control-sm" id="input_producer" name="producer" placeholder="" value="<?php echo (isset($product['producer'])) ? ($product['producer']) : ''; ?>">
					</div>
				</div>
			</div>
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('vhbcode'); ?> (VHB)</strong>
					</div>
					<div class="col-auto">
						<input type="text" class="form-control form-control-sm" id="vhbcode" name="vhbcode" placeholder="" value="<?php echo (isset($product['vhbcode'])) ? ($product['vhbcode']) : ''; ?>">
					</div>
				</div>
			</div>
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('vhbcode'); ?> (CNK)</strong>
					</div>
					<div class="col-auto">
						<input type="text" class="form-control form-control-sm" id="cnk" name="cnk" placeholder="" value="<?php echo (isset($product['cnk'])) ? ($product['cnk']) : ''; ?>">
					</div>
				</div>
			</div>
			<p id="get_info">AJAX</a>
		</div>

		<strong><?php echo $this->lang->line('limit'); ?></strong>
		<p><?php echo $this->lang->line('c'); ?></p>

		<div class="list-group mb-3 shadow">
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('global_limit'); ?></strong>
						<p class="text-muted mb-0"><?php echo $this->lang->line('global_limit_explain'); ?></p>
					</div>
					<div class="col-auto">
						<div class="input-group">
							<input type="text" name="limit_stock" class="form-control" id="limit_stock" value="<?php echo (isset($product['limit_stock'])) ? $product['limit_stock']: '' ?>">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php foreach ($stock_locations as $stock): 
			$local_limit_id = -1;
			$local_limit_value = 0;
			$local_color = $stock['color'];
			# super dirty but whateves
			if ($llimit)
			{
				foreach($llimit as $limit)
				{
					if ($stock['id'] == $limit['stock'])
					{
						$local_limit_id = $limit['id'];
						$local_limit_value = $limit['volume'];
						break;
					}
				}
			}
			?>
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0">
						<i class="fa-solid fa-fw fa-location-dot" style="color:<?php echo $local_color; ?>"></i><?php echo $stock['name']; ?></strong>
					</div>
					<div class="col-auto">
						<div class="input-group input-group-sm">
							<input type="text" name="limit[<?php echo $stock['id']; ?>][<?php echo $local_limit_id; ?>]" class="form-control" id="limits<?php echo $stock['id'] . $local_limit_id; ?>" value="<?php echo $local_limit_value; ?>">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php endforeach; ?>


		</div>


		<strong>Transactie info</strong>
		<p><?php echo $this->lang->line('c'); ?></p>

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

		<strong>Advanced</strong>
		<p></p>

		<div class="list-group mb-5 shadow">
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0">is_antibiotic</strong>
					</div>
					<div class="col-auto">
						<div class="custom-control custom-switch">
							<input type="checkbox" class="custom-control-input" id="is_antibiotic" name="is_antibiotic" <?php echo (isset($product['is_antibiotic']) && $product['is_antibiotic']) ? 'checked' : ''; ?>>
							<label class="custom-control-label" for="is_antibiotic"></label>
						</div>
					</div>
				</div>
			</div>
			<div class="list-group-item list-group-item-action toggle-hide no_antibiotic_hide">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0">AB default</strong>
					</div>
					<div class="col-auto">
						<select name="default_indication" class="form-control form-control-sm" id="default_indication">
							<option value="DIGEST" <?php if($product['default_indication'] == 'DIGEST') echo 'selected'; ?>>Spijsverteringsstoornissen</option>
							<option value="EYE" <?php if($product['default_indication'] == 'EYE') echo 'selected'; ?>>Oogproblemen</option>
							<option value="LOCO" <?php if($product['default_indication'] == 'LOCO') echo 'selected'; ?>>Locomotorische aandoeningen</option>
							<option value="MAST" <?php if($product['default_indication'] == 'MAST') echo 'selected'; ?>>Mastitis</option>
							<option value="NERVE" <?php if($product['default_indication'] == 'NERVE') echo 'selected'; ?>>Zenuwstoornissen</option>
							<option value="PERI_OP" <?php if($product['default_indication'] == 'PERI_OP') echo 'selected'; ?>>Peri-operatieve antibacteriële behandeling</option>
							<option value="RESP" <?php if($product['default_indication'] == 'RESP') echo 'selected'; ?>>Ademhalingsaandoeningen</option>
							<option value="DERMA" <?php if($product['default_indication'] == 'DERMA') echo 'selected'; ?>>Huidaandoeningen</option>
							<option value="SYST" <?php if($product['default_indication'] == 'SYST') echo 'selected'; ?>>Systemische aandoeningen</option>
							<option value="URO_GEN" <?php if($product['default_indication'] == 'URO_GEN') echo 'selected'; ?>>Urogenitale aandoeningen</option>
							<option value="null" <?php if(is_null($product['default_indication'])) echo 'selected'; ?>>-</option>
						</select>
					</div>
				</div>
			</div>
			<div class="list-group-item list-group-item-action toggle-hide no_antibiotic_hide">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0">AB UNIT</strong>
					</div>
					<div class="col-auto">
						
						<div class="form-inline">
							<div class="form-group">
								<input type="text" name="ab_unit_volume" class="form-control form-control-sm" style="max-width:150px;" id="ab_unit_volume" value="<?php echo (isset($product['ab_unit_volume'])) ? $product['ab_unit_volume']: '' ?>" required>
							</div>
							<div class="form-group mx-sm-1">
								<select name="ab_unit" class="form-control form-control-sm" id="ab_unit">
									<option value="PACKS" <?php if($product['ab_unit'] == 'PACKS') echo 'selected'; ?>>Pack</option>
									<option value="PIECE" <?php if($product['ab_unit'] == 'PIECE') echo 'selected'; ?>>Piece</option>
									<option value="PRESTATION" <?php if($product['ab_unit'] == 'PRESTATION') echo 'selected'; ?>>prestation</option>
									<option value="TUBE" <?php if($product['ab_unit'] == 'TUBE') echo 'selected'; ?>>tube</option>
									<option value="G" <?php if($product['ab_unit'] == 'G') echo 'selected'; ?>>g</option>
									<option value="ML" <?php if($product['ab_unit'] == 'ML') echo 'selected'; ?>>ml</option>
								</select>
							</div>
						</div>
						
					</div>
				</div>
			</div>

			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('vaccin'); ?></strong>
					</div>
					<div class="col-auto">
						<div class="custom-control custom-switch">
							<input type="checkbox" class="custom-control-input" id="vaccin" name="vaccin" <?php echo (isset($product['vaccin']) && $product['vaccin']) ? 'checked' : ''; ?>>
							<label class="custom-control-label" for="vaccin"></label>
						</div>
					</div>
				</div>
			</div>

			<div class="list-group-item list-group-item-action toggle-hide no_vaccin_hide">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('vaccin_freq'); ?></strong>
						<p class="text-muted mb-0"><?php echo $this->lang->line('vaccin_explain'); ?></p>
					</div>
					<div class="col-auto">
						<div class="input-group">
							<input type="text" class="form-control" id="vaccin_freq" style="width:150px;" name="vaccin_freq" value="<?php echo (isset($product['vaccin_freq'])) ? $product['vaccin_freq'] : 0; ?>" autocomplete="vaccin_freq" placeholder="">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2"><?php echo $this->lang->line('date_days'); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="list-group-item list-group-item-action toggle-hide no_vaccin_hide">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('vaccin_layterm'); ?></strong>
						<p class="text-muted mb-0"><?php echo $this->lang->line('vaccin_layterm_explain'); ?></p>
					</div>
					<div class="col-auto">
						<input type="text" class="form-control" id="vaccin_disease" name="vaccin_disease" value="<?php echo (isset($product['vaccin_disease'])) ? ($product['vaccin_disease']) : ''; ?>">
					</div>
				</div>
			</div>

			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo $this->lang->line('dead_volume'); ?></strong>
						<p class="text-muted mb-0"><?php echo $this->lang->line('dead_volume_explain'); ?></p>
					</div>
					<div class="col-auto">
						<div class="input-group input-group-sm">
							<input type="text" name="dead_volume" class="form-control form-control-sm" style="width:175px;" id="dead_volume" value="<?php echo (isset($product['dead_volume'])) ? $product['dead_volume']: '0' ?>">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo ucfirst($this->lang->line('discontinued')); ?></strong>
					</div>
					<div class="col-auto">
						<div class="custom-control custom-switch">
							<input type="checkbox" class="custom-control-input" id="discontinued" name="discontinued" <?php echo (isset($product['discontinued']) && $product['discontinued']) ? 'checked' : ''; ?>>
							<label class="custom-control-label" for="discontinued"></label>
						</div>
					</div>
				</div>
			</div>
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><?php echo ucfirst($this->lang->line('saleable')); ?></strong>
					</div>
					<div class="col-auto">
						<div class="custom-control custom-switch">
							<input type="checkbox" class="custom-control-input" id="sellable" name="sellable" <?php echo (isset($product['sellable']) && $product['sellable']) ? 'checked' : ''; ?>>
							<label class="custom-control-label" for="sellable"></label>
						</div>
					</div>
				</div>
			</div>
			<div class="list-group-item list-group-item-action">
				<div class="row align-items-center">
					<div class="col">
						<strong class="mb-0"><i class="fas fa-barcode"></i> <?php echo $this->lang->line('gs1_barcode'); ?></strong>
						<p class="text-muted mb-0"><?php echo $this->lang->line('gs1_scan_explain'); ?></p>
					</div>
					<div class="col-auto">
						<div class="form-inline">
							<div class="form-group">
								<input type="text" name="gs1_datamatrix" class="form-control" id="gs1_datamatrix" value="" placeholder="scan code here">
							</div>
							<div class="form-group mx-sm-3">
								<input type="text" name="input_barcode" class="form-control" id="input_barcode" value="<?php echo (isset($product['input_barcode'])) ? $product['input_barcode']: '' ?>">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


			<div class="float-right">
				<button type="submit" name="submit" value="edit" class="btn btn-outline-primary float-right"><i class="fa-solid fa-wrench"></i> <?php echo $this->lang->line('edit'); ?></button>
			</div>
		</form>
	  </div>
</div>
		


<script type="text/javascript">

function process_datamatrix(barcode) {
	
	// GS1 data matrix 
	// 01 05420036903635 17 210400 10 111219
	// length : ~30 
	// 01 EAN/GTIN  (14 length)
	// 17 YY MM DD date (6 length)
	// 10 barcode (variable length)
	// 6 + 14 + 6 + x
	
	if (barcode.length > 26)
	{
		result = barcode.match(/01([0-9]{14})17([0-9]{6})10(.*)/);
		if(result)
		{
			// console.log(result);
			var input_barcode = result[1];
			var date = result[2];
			var day = (date.substr(4,2) == "00") ? "01" : date.substr(4,2);
			
			$("#input_barcode").val(result[1]);
			$("#extra_info").html("Scanned LotNR : " + "20" + date.substr(0, 2) + "-" + date.substr(2,2) + "-" + day + " lotnr :" + result[3]);
		}
	}
	else
	{
		console.log("code to short not recognized");
	}	
}

function toggleByCheckbox(checkbox, target) {
    if ($(checkbox).is(':checked')) {
        $(target).show();
    } else {
        $(target).hide();
    }

    $(checkbox).on('change', function () {
        $(this).is(':checked')
            ? $(target).slideDown()
            : $(target).slideUp();
    });
}


document.addEventListener("DOMContentLoaded", function(){
	var _changeInterval = null;
	var barcode = null;
	$("#prd").show();
	$("#products").addClass('active');
	$("#product_list").addClass('active');
	
	$("#gs1_datamatrix").keyup(function(){
		barcode = this.value;
		clearInterval(_changeInterval)
		_changeInterval = setInterval(function() {
		clearInterval(_changeInterval)
			process_datamatrix(barcode);
		}, 500);
	});
	toggleByCheckbox('#vaccin', '.no_vaccin_hide');
	toggleByCheckbox('#is_antibiotic', '.no_antibiotic_hide');

	$('#wholesale').select2({
    theme: 'bootstrap4',
    placeholder: 'Select Article',
    ajax: {
        url: '<?php echo base_url('wholesale/ajax_get_articles'); ?>',
        dataType: 'json'
    },
	}).on("select2:selecting", function(e) { 
		$(this).addClass('is-valid');

		// Handle vhb and wh_name without issue
		$("#vhbcode").val(e.params.args.data.vhb).addClass('is-valid');
		$("#cnk").val(e.params.args.data.cnk).addClass('is-valid');
		$("#input_wh_name").val(e.params.args.data.text).addClass('is-valid');

		// Check if #input_producer is already filled in
		var currentProducerValue = $("#input_producer").val();
		if (currentProducerValue) {
			// If there is already a value, ask the user if they want to overwrite
			var confirmOverwrite = confirm("The producer is already set to '" + currentProducerValue + "'. Do you want to overwrite it with '" + e.params.args.data.distr + "'?");
			if (confirmOverwrite) {
				// Overwrite the producer value if the user agrees
				$("#input_producer").val(e.params.args.data.distr).addClass('is-valid');
			} else {
				// Keep the existing value (optional: give some visual feedback)
				$("#input_producer").addClass('is-valid');
			}
		} else {
			// If there's no existing value, just set the new one
			$("#input_producer").val(e.params.args.data.distr).addClass('is-valid');
		}
	});

	// ajax call to populate get_info
	$.ajax({
		url: '/fagg/api/fagg/by-cnk/<?php echo $product['cnk']; ?>',
		type: 'GET',
		headers: {
			'X-API-Key': '2a0a05b5c83d3b15d537174eabe0be09ba6fb52097d41ed318663c78cff5ac98'
		},
		success: function(response) {
		const item = response && response.data ? response.data : response; // supports both shapes

		var infoHtml = '<strong>FAGG Info:</strong><br>';
		infoHtml += 'Naam: ' + (item.benaming || '-') + '<br>';
		infoHtml += 'Werkzaam bestanddeel: ' + (item.werkzaam_bestandeel || '-') + '<br>';
		infoHtml += 'ATC: ' + (item.atc_code || '-') + '<br>';
		infoHtml += 'Gebruik type: ' + (item.gebruik_type || '-') + '<br>';
		infoHtml += 'CTI: ' + (item.cti_extended || '-') + '<br>';
		infoHtml += 'Bijsluiter: ' + (item.url_bijsluiter_nl ? '<a target="_blank" href="' + item.url_bijsluiter_nl + '">open</a>' : '-') + '<br>';

		$('#get_info').html(infoHtml);
		},
		error: function(xhr, status, error) {
			$('#get_info').html(
				'<strong>AJAX Error:</strong><pre style="white-space: pre-wrap; word-break: break-word; margin-top: 8px;">' +
				JSON.stringify({ status: status, error: error, responseText: xhr.responseText }, null, 2) +
				'</pre>'
			);
		}
	});

});
</script>
  
