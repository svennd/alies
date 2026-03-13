<p>Advanced</p>

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
						<input type="text" name="ab_unit_volume" class="form-control form-control-sm" style="max-width:150px;" id="ab_unit_volume" value="<?php echo (isset($product['ab_unit_volume'])) ? $product['ab_unit_volume']: '' ?>">
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
				<strong class="mb-0"><?php echo ucfirst($this->lang->line('comment')); ?></strong>
				<p class="text-muted mb-0"><?php echo $this->lang->line('admin_comment'); ?></p>
			</div>
			<div class="col">
				<textarea class="form-control" name="comment_admin" id="comment_admin" rows="3"><?php echo (isset($product['comment_admin'])) ? $product['comment_admin']: '' ?></textarea>
			</div>
		</div>
	</div>

</div>