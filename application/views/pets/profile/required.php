<style>

.lbl-radio {
	display: block;
	border: 1px solid rgba(0, 0, 0, 0.1);
	border-radius: 10px;
	padding: 15px;
	position: relative;
	cursor: pointer;
}

.lbl-radio .content .title {
	margin-bottom: 7px; 
}

input[type='radio']:checked + .lbl-radio {
	border-color: #97cf9b;
	background-color: #eeffef;
}

.noradio input[type='radio'] {
	display: none;
}

input[type='radio']:checked {
	border-color: red;
}

.opacy-disabled {
	opacity: 0.3;
    cursor: not-allowed;
}

.gender-select {
    border-color: #5194cf;
	background-color: aliceblue;
}
</style>

<h5><?php echo $this->lang->line('required'); ?></h5>
<hr>
<div class="form-row">
	<div class="form-group col-md-12">
	<label><b><a data-toggle="collapse" href="#Type" role="button" aria-expanded="false" aria-controls="Type">Type</a></b></label>
	<div class="collapse" id="Type">
				<?php 
					$pet_type = array(
								"0" => array("dog", "#f2a10d", "dog", DOG),
								"1" => array("cat", "#005248", "cat", CAT),
								"2" => array("horse", "#402E32", "horse", HORSE),
								"3" => array("bird", "#FFB087", "dove", BIRD),
								"5" => array("rabbit", "#AD4CF4", "paw", RABBIT),
								"4" => array("other", "#DFE0DF", "ghost", OTHER),
					);
				?>
				<div class="row">
					<?php foreach($pet_type as $pid => $p): ?>
						<div class="col text-center noradio">
							<input type="radio" name="type" value="<?php echo $pid; ?>" id="<?php echo $pid; ?>" <?php echo ($pet['type'] == $p['3']) ? 'checked' : ''; ?>  required />
							<label for="<?php echo $pid; ?>" class="lbl-radio">
							<div class="content bounceit" >
								<div class="title"><span style="color:<?php echo $p[1]; ?>"><i class="fas fa-<?php echo $p[2]; ?> fa-fw fa-2x"></i></span></div>
								<?php echo $this->lang->line($p[0]); ?>
							</div>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
		</div>
	</div>
</div>

<div class="form-row">
	<div class="form-group col-md-4">
		<label for="name"><b><?php echo $this->lang->line('gender'); ?></b>*</label>
		<div class="col">
		<?php 
			$gender_type = array(
						"0" => array("Male", "#4c6ef5", "mars", MALE),
						"2" => array("Male neutered", "#000", "mars", MALE_NEUTERED),
						"1" => array("Female", "#f783ac", "venus", FEMALE),
						"3" => array("Female neutered", "#000", "venus", FEMALE_NEUTERED),
						"4" => array("Other", "#6cce23", "genderless", OTHER),
			);
		?>
		<?php foreach($gender_type as $gid => $g): ?>
			<label for="g<?php echo $gid; ?>" class="lbl-radio gender <?php echo ($pet['gender'] == $g['3']) ? 'gender-select' : ''; ?>">
			<div class="content">
				<input type="radio" name="gender" value="<?php echo $gid; ?>" id="g<?php echo $gid; ?>" <?php echo ($pet['gender'] == $g['3']) ? 'checked' : ''; ?>  required/>
				<span style="color:<?php echo $g['1']; ?>"><i class="fas fa-<?php echo $g['2']; ?> fa-fw"></i></span> <?php echo $g['0']; ?>
			</div>
			</label>
		<?php endforeach; ?>

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