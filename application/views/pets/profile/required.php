<h5>Required</h5>
<hr>
<?php if(isset($invalid) && $invalid): ?><div class="alert alert-danger" role="alert">Required fields where not filled in!</div><?php endif; ?>
	
<div class="form-row">
	<div class="form-group col-md-6">
		<label for="type"><b>Type</b>*</label>
		<div class="col">
			<div class="form-check">
			  <input class="form-check-input" type="radio" name="type" id="exampleRadios1" value="0" <?php echo ($edit_mode && $pet['type'] == DOG) ? 'checked' : ''; ?> required>
			  <label class="form-check-label" for="exampleRadios1">
				<span style="color:#628395"><i class="fas fa-dog fa-fw"></i></span> Dog
			  </label>
			</div>
			
			<div class="form-check">
			  <input class="form-check-input" type="radio" name="type" id="exampleRadios2" value="1" <?php echo ($edit_mode && $pet['type'] == CAT) ? 'checked' : ''; ?> required>
			  <label class="form-check-label" for="exampleRadios2">
				<span style="color:#96897B"><i class="fas fa-cat fa-fw"></i></span> Cat
			  </label>
			</div>
			
			<div class="form-check">
			  <input class="form-check-input" type="radio" name="type" id="exampleRadios3" value="2" <?php echo ($edit_mode && $pet['type'] == HORSE) ? 'checked' : ''; ?> required>
			  <label class="form-check-label" for="exampleRadios3">
				<span style="color:#CF995F"><i class="fas fa-horse fa-fw"></i></span> Horse
			  </label>
			</div>
			
			<div class="form-check">
			  <input class="form-check-input" type="radio" name="type" id="exampleRadios4" value="3" <?php echo ($edit_mode && $pet['type'] == BIRD) ? 'checked' : ''; ?> required>
			  <label class="form-check-label" for="exampleRadios4">
				<span style="color:#DBAD6A"><i class="fas fa-dove fa-fw"></i></span> Bird
			  </label>
			</div>
			
			<div class="form-check">
			  <input class="form-check-input" type="radio" name="type" id="exampleRadios6" value="5" <?php echo ($edit_mode && $pet['type'] == RABBIT) ? 'checked' : ''; ?> required>
			  <label class="form-check-label" for="exampleRadios6">
				<span style="color:#dec5a1"><i class="fas fa-paw fa-fw"></i></span> Rabbit
			  </label>
			</div>
			
			<div class="form-check">
			  <input class="form-check-input" type="radio" name="type" id="exampleRadios5" value="4" <?php echo ($edit_mode && $pet['type'] == OTHER) ? 'checked' : ''; ?> required>
			  <label class="form-check-label" for="exampleRadios5">
				<span style="color:#DFD5A5"><i class="fas fa-ghost fa-fw"></i></span> Other
			  </label>
			</div>
		</div>
	</div>
	<div class="form-group col-md-6">
		<label for="name"><b>Gender</b>*</label>
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
</div>

<div class="row">
    <div class="col"> 
	  <div class="form-group">
		<label for="name"><b>Name</b>*</label>
		<input type="text" name="name" class="form-control" id="name" value="<?php echo ($edit_mode && isset($pet['name'])) ? $pet['name']: '' ?>" required>
	  </div>
	</div>
    <div class="col"> 
	  <div class="form-group">
		<label for="birth"><b>Birth</b>*</label>
		<input type="date" name="birth" class="form-control" id="birth" value="<?php echo ($edit_mode && isset($pet['birth'])) ? $pet['birth']: '' ?>" required>
		<?php if($edit_mode && !$pet['death']): ?><i><small id="birth_info" class="form-text text-muted ml-2">&nbsp;</small></i><?php endif; ?>
	  </div>
  </div>
</div>