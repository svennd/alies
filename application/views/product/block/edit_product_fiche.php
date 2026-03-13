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
				<strong class="mb-0"><?php echo $this->lang->line('product_wholesale'); ?></strong> <div class="btn btn-sm btn-outline-success" id="wholesale_button"><i class="fa-solid fa-link"></i></div>
			</div>
			<div class="col">
				<input type="text" class="form-control" id="input_wh_name" name="input_wh_name" placeholder="" value="<?php echo (isset($product['wholesale_name'])) ? ($product['wholesale_name']) : ''; ?>">
				<input type="hidden" id="wholesale_id" name="wholesale" value="<?php echo (isset($product['wholesale'])) ? ($product['wholesale']) : ''; ?>">
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

	<div class="list-group-item list-group-item-action">
		<div class="row align-items-center">
			<div class="col">
				<strong class="mb-0"><?php echo $this->lang->line('product_labels'); ?></strong>
			</div>
			<div class="col">
				<?php $selected_labels = array_column($product_labels, 'id'); ?>
				<select name="labels[]" class="form-control" id="product_labels" multiple="multiple" style="width: 100%;">
					<?php foreach($labels as $label): ?>
						<option value="<?php echo $label['id']; ?>" <?php echo in_array($label['id'], $selected_labels) ? "selected='selected'" : ""; ?>>
							<?php echo ucfirst($label['name']); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>

	<div class="list-group-item list-group-item-action">
		<div class="row align-items-center">
			<div class="col">
				<strong class="mb-0"><?php echo $this->lang->line('vhbcode'); ?></strong>
			</div>
			<div class="col-auto">
				<input type="text" class="form-control" id="vhbcode" name="vhbcode" placeholder="" value="<?php echo (isset($product['vhbcode'])) ? ($product['vhbcode']) : ''; ?>">
			</div>
		</div>
	</div>
	<div class="list-group-item list-group-item-action">
		<div class="row align-items-center">
			<div class="col">
				<strong class="mb-0">CNK</strong> <div class="btn btn-sm btn-outline-success" id="cnk_button"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
			</div>
			<div class="col-auto">
				<input type="text" class="form-control" id="cnk" name="cnk" placeholder="" value="<?php echo (isset($product['cnk'])) ? ($product['cnk']) : ''; ?>">
			</div>
		</div>
	</div>
	<div class="list-group-item list-group-item-action">
		<div class="row align-items-center">
			<div class="col">
				<strong class="mb-0">CTI-e</strong>
			</div>
			<div class="col-auto">
				<input type="text" class="form-control" id="cti-e" name="cti_e" placeholder="" value="<?php echo (isset($product['cti_e'])) ? ($product['cti_e']) : ''; ?>">
			</div>
		</div>
	</div>

	<div class="list-group-item list-group-item-action">
			<div class="row align-items-center">
				<div class="col">
					<strong class="mb-0"><i class="fas fa-barcode"></i> <?php echo $this->lang->line('gs1_barcode'); ?></strong>
					<p class="text-muted mb-0"><?php echo $this->lang->line('gs1_scan_explain'); ?></p>
				</div>
				<div class="col-md-4">
					<div class="d-flex">
						<input type="text" name="gs1_datamatrix" class="form-control" id="gs1_datamatrix" placeholder="scan code here">
						<input type="text" name="input_barcode" class="form-control ml-2" id="input_barcode" value="<?php echo $product['input_barcode'] ?? ''; ?>">
					</div>
				</div>
			</div>
		</div>
		

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
				<strong class="mb-0"><?php echo $this->lang->line('producer'); ?></strong>
			</div>
			<div class="col-auto">
				<input type="text" class="form-control" id="input_producer" name="producer" placeholder="" value="<?php echo (isset($product['producer'])) ? ($product['producer']) : ''; ?>">
			</div>
		</div>
	</div>
</div>
