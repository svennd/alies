  <h5>Details</h5>
  <hr>
  <div class="form-group">
    <label for="breed">Breed</label>
	<div class="form-row">
		<div class="col">
		<select name="breed" class="form-control" id="breeds">
			<?php foreach($breeds as $breed): ?>
				<option value="<?php echo $breed['id']; ?>" <?php echo ($edit_mode && $breed['id'] == $pet['breed']) ? 'selected': ''; ?>><?php echo $breed['name']; ?></option>
			<?php endforeach; ?>
		</select>
		</div>
		<div class="col">
		<div class="input-group input-group mb-2 mr-sm-2">
			<div class="input-group-prepend">
				<div class="input-group-text">or add</div>
			</div>
			<input name="breed_custom" type="text" class="form-control form-control" <?php echo ($edit_mode)? 'disabled' : ''; ?> placeholder="custom breed">
		</div>
		</div>
	 </div>
  </div>
<div class="row">
    <div class="col">
	  <div class="form-group">
		<label for="color">Color</label>
		<input type="text" name="color" class="form-control" id="color" value="<?php echo ($edit_mode && isset($pet['color'])) ? $pet['color']: '' ?>">
	  </div>
	</div>
    <div class="col">
	  <div class="form-group">
		<label for="weight">Weight</label>
		<div class="input-group mb-3">
			<input type="text" class="form-control" name="weight" id="weight" aria-describedby="basic-addon2"  value="<?php echo ($edit_mode && isset($pet['last_weight'])) ? $pet['last_weight']: '' ?>">
			<div class="input-group-append">
				<span class="input-group-text" id="basic-addon2">kg</span>
			</div>
		</div>
	  </div>
  </div>
</div>

  <h5>Identification</h5>
  <hr>
   <div class="form-group">
    <label for="vacbook">nr vac book</label>
    <input type="text" name="vacbook" class="form-control" id="vacbook" value="<?php echo ($edit_mode && isset($pet['nr_vac_book'])) ? $pet['nr_vac_book']: '' ?>">
  </div>
  <div class="form-group">
    <label for="chip">Chip</label>
    <input type="text" name="chip" class="form-control" id="chip" value="<?php echo ($edit_mode && isset($pet['chip'])) ? $pet['chip']: '' ?>">
	<i><small id="chip_info" class="form-text text-muted ml-2">&nbsp;</small></i>
  </div>

  <h5>Trivia</h5>
  <hr>
  <div class="form-group">
    <label for="exampleFormControlTextarea1">Notes</label>
    <textarea class="form-control" name="msg" id="exampleFormControlTextarea1" rows="3"><?php echo ($edit_mode && isset($pet['note'])) ? $pet['note']: '' ?></textarea>
  </div>
  <?php if($edit_mode): ?>
  <div class="form-group">
    <label for="nutrion">Nutrion Advice</label>
    <textarea class="form-control" name="nutritional_advice" id="nutrion" rows="3"><?php echo ($edit_mode && isset($pet['nutritional_advice'])) ? $pet['nutritional_advice']: '' ?></textarea>
  </div>

  <h5>Status</h5>
  <hr>
	<div class="form-group">
		<div class="form-check">
		<input class="form-check-input" name="dead" type="checkbox" value="1" id="dead" <?php echo ($edit_mode && isset($pet['death']) && $pet['death'] == 1) ? 'checked': ''; ?>>
			<label class="form-check-label" for="dead">
			Dead
			</label>
		</div>
		<div class="form-check">
		<input class="form-check-input" name="lost" type="checkbox" value="1" id="lost" <?php echo ($edit_mode && isset($pet['lost']) && $pet['lost'] == 1) ? 'checked': ''; ?>>
		  <label class="form-check-label" for="lost">
			Lost
		  </label>
		</div>
	</div>
<?php endif; ?>
  <h5>Update</h5>
  <hr>

  <input type="hidden" name="owner" value="<?php echo $owner['id']; ?>">

  <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo ($edit_mode) ? "Update Fiche" : "Add Pet"; ?></button>
