  <h5>Trivia</h5>
  <hr>
<div class="row">
    <div class="col">
	  <div class="form-group">
		<label for="color"><?php echo $this->lang->line('haircolor'); ?></label>
		<input type="text" name="color" class="form-control" id="color" value="<?php echo ($edit_mode && isset($pet['color'])) ? $pet['color']: '' ?>">
	  </div>
	</div>
    <div class="col">
	  <div class="form-group">
		<label for="weight"><?php echo $this->lang->line('weight'); ?></label>
		<div class="input-group mb-3">
			<input type="text" class="form-control" name="weight" id="weight" aria-describedby="basic-addon2"  value="<?php echo ($edit_mode && isset($pet['last_weight'])) ? $pet['last_weight']: '' ?>">
			<div class="input-group-append">
				<span class="input-group-text" id="basic-addon2">kg</span>
			</div>
		</div>
	  </div>
  </div>
</div>
<div class="row">
    <div class="col">
	  <div class="form-group">
		<label for="hairtype"><?php echo $this->lang->line('hairtype'); ?></label>
		<input type="text" name="hairtype" class="form-control" id="hairtype" value="<?php echo ($edit_mode && isset($pet['hairtype'])) ? $pet['hairtype']: '' ?>">
	  </div>
	</div>
    <div class="col">&nbsp;</div>
</div>

  <div class="form-group">
    <label for="exampleFormControlTextarea1"><?php echo $this->lang->line('notes'); ?></label>
    <textarea class="form-control" name="msg" id="exampleFormControlTextarea1" rows="3"><?php echo ($edit_mode && isset($pet['note'])) ? $pet['note']: '' ?></textarea>
  </div>
  <?php if($edit_mode): ?>
  <div class="form-group">
    <label for="nutrion"><?php echo $this->lang->line('nutrition'); ?></label>
    <textarea class="form-control" name="nutritional_advice" id="nutrion" rows="3"><?php echo ($edit_mode && isset($pet['nutritional_advice'])) ? $pet['nutritional_advice']: '' ?></textarea>
  </div>

  <div class="form-group">
    <label for="medication"><?php echo $this->lang->line('medicine'); ?></label>
    <textarea class="form-control" name="medication" id="medication" rows="3"><?php echo ($edit_mode && isset($pet['medication'])) ? $pet['medication']: '' ?></textarea>
  </div>

  <h5>Status</h5>
  <hr>
	<div class="form-group">
		<div class="form-check">
		<input class="form-check-input" name="dead" type="checkbox" value="1" id="dead" <?php echo ($edit_mode && isset($pet['death']) && $pet['death'] == 1) ? 'checked': ''; ?>>
			<label class="form-check-label" for="dead">
			<?php echo $this->lang->line('passed_away'); ?>
			</label>
		</div>
		<div class="form-check">
		<input class="form-check-input" name="lost" type="checkbox" value="1" id="lost" <?php echo ($edit_mode && isset($pet['lost']) && $pet['lost'] == 1) ? 'checked': ''; ?>>
		  <label class="form-check-label" for="lost">
		  	<?php echo $this->lang->line('gone_or_lost'); ?>
		  </label>
		</div>
	</div>
<?php endif; ?>