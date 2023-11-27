<h5><?php echo $this->lang->line('required'); ?></h5>
<hr>
<div class="form-row">
	<div class="form-group col-md-4">
		<label for="name"><b><?php echo $this->lang->line('gender'); ?></b>*</label>
		<div class="col">
			<div class="form-check">
			  <input class="form-check-input" type="radio" name="gender" id="gender1" value="0" <?php echo ($edit_mode && $pet['gender'] == MALE) ? 'checked' : ''; ?> required>
			  <label class="form-check-label" for="gender1">
				<span style="color:#4c6ef5;"><i class="fas fa-mars fa-fw"></i></span> Male
			  </label>
			</div>
			<div class="form-check">
			  <input class="form-check-input" type="radio" name="gender" id="gender1n" value="2" <?php echo ($edit_mode && $pet['gender'] == MALE_NEUTERED) ? 'checked' : ''; ?> required>
			  <label class="form-check-label" for="gender1n">
				<span style="color:#000;"><i class="fas fa-mars fa-fw"></i></span> Male neutered
			  </label>
			</div>
			
			<div class="form-check">
			  <input class="form-check-input" type="radio" name="gender" id="gender2" value="1" <?php echo ($edit_mode && $pet['gender'] == FEMALE) ? 'checked' : ''; ?> required>
			  <label class="form-check-label" for="gender2">
				<span style="color:#f783ac;"><i class="fas fa-venus fa-fw"></i></span> Female
			  </label>
			</div>
			
			<div class="form-check">
			  <input class="form-check-input" type="radio" name="gender" id="gender2n" value="3" <?php echo ($edit_mode && $pet['gender'] == FEMALE_NEUTERED) ? 'checked' : ''; ?> required>
			  <label class="form-check-label" for="gender2n">
				<span style="color:#000;"><i class="fas fa-venus fa-fw"></i></span> Female neutered
			  </label>
			</div>
			
			<div class="form-check">
			  <input class="form-check-input" type="radio" name="gender" id="gender7n" value="4" <?php echo ($edit_mode && $pet['gender'] == OTHER) ? 'checked' : ''; ?> required>
			  <label class="form-check-label" for="gender7n">
				<span style="color:#6cce23;"><i class="fas fa-genderless fa-fw"></i></span> Other
			  </label>
			</div>
		</div>
	</div>
	<div class="form-group col-md-6">
		<div class="form-group col-md-8">
			<label for="name"><b><?php echo $this->lang->line('pet_name'); ?></b>*</label>
			<input type="text" name="name" class="form-control" id="name" value="<?php echo ($edit_mode && isset($pet['name'])) ? $pet['name']: '' ?>" required>
		</div>
		<div class="form-group col-md-8">
			<label for="birth"><b><?php echo $this->lang->line('birth'); ?></b>*</label>
			<input type="date" name="birth" class="form-control" id="birth" value="<?php echo ($edit_mode && isset($pet['birth'])) ? $pet['birth']: '' ?>" required>
			<?php if($edit_mode && !$pet['death']): ?><i><small id="birth_info" class="form-text text-muted ml-2">&nbsp;</small></i><?php endif; ?>
		</div>
		
		<div class="row form-group mx-1">
			<div class="col-md-6">
				<label for="breeds"><b><?php echo $this->lang->line('breed'); ?></b>*</label>
				<select name="breed" class="form-control" id="breeds" data-placeholder='<?php echo ($edit_mode && isset($pet['breeds'])) ? $pet['breeds']['name']: ''; ?>' <?php if(!$edit_mode): ?>required<?php endif; ?>></select>
				<input type="hidden" id="current_breed" name="current_breed" value="<?php echo ($edit_mode && isset($pet['breed'])) ? $pet['breed']: 1; ?>">
			</div>
			<div class="col-md-6">
				<label for="breeds2">x <?php echo $this->lang->line('breed'); ?></label>
				<select name="breed2" class="form-control" id="second_breed" data-placeholder='<?php echo ($edit_mode && isset($pet['breeds2'])) ? $pet['breeds2']['name']: ''; ?>'></select>
				<input type="hidden" id="current_breed2" name="current_breed2" value="<?php echo ($edit_mode && isset($pet['breed2'])) ? $pet['breed2']: -1; ?>">
			</div>
		</div>
	</div>
</div>
  
<h5>Identification</h5>
  <hr>
  <div class="form-row">
		<div class="col">
			<div class="form-group">
				<label for="chip"><?php echo $this->lang->line('chip'); ?></label>
				<input type="text" name="chip" class="form-control" id="chip" value="<?php echo ($edit_mode && isset($pet['chip'])) ? $pet['chip']: '' ?>">
				<i><small id="chip_info" class="form-text text-muted ml-2">&nbsp;</small></i>
			</div>
		</div>
		<div class="col">
			<div class="form-group">
				<label for="vacbook"><?php echo $this->lang->line('vacc_nr'); ?></label>
				<input type="text" name="vacbook" class="form-control" id="vacbook" value="<?php echo ($edit_mode && isset($pet['nr_vac_book'])) ? $pet['nr_vac_book']: '' ?>">
			</div>
		</div>
	</div>