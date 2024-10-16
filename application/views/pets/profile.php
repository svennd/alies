<div class="row">
	<div class="col-lg-7 col-xl-10">
		<div class="card shadow mb-4">
			<div class="card-header">
					<a href="<?php echo base_url('owners/detail/' . $owner['id']); ?>"><?php echo $owner['last_name'] ?></a> / <a href="<?php echo base_url('pets/fiche/' . $pet['id']); ?>"><?php echo (isset($pet['name'])) ? $pet['name']: '' ?></a> / <?php echo $this->lang->line('edit_pet'); ?>
			</div>
			<div class="card-body">
				<form action="<?php echo base_url('pets/edit/' . $pet['id']); ?>" method="post" autocomplete="off" name="edit_pet">

				<h5><?php echo $this->lang->line('required'); ?></h5>
				<hr>
				<!-- type, hidden by default -->
				<label><b><a data-toggle="collapse" href="#Type" role="button" aria-expanded="false" aria-controls="Type">Type</a></b></label>
				<div class="collapse" id="Type">
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


				<div class="row py-3">
					<!-- gender list -->
					<div class="col">
						<label for="name"><b><?php echo $this->lang->line('gender'); ?></b>*</label>
						<?php foreach($gender_type as $gid => $g): ?>
							<label for="g<?php echo $gid; ?>" class="lbl-radio gender <?php echo ($pet['gender'] == $g['3']) ? 'gender-select' : ''; ?>">
							<div class="content">
								<input type="radio" name="gender" value="<?php echo $gid; ?>" id="g<?php echo $gid; ?>" <?php echo ($pet['gender'] == $g['3']) ? 'checked' : ''; ?>  required/>
								<span style="color:<?php echo $g['1']; ?>"><i class="fas fa-<?php echo $g['2']; ?> fa-fw"></i></span> <?php echo $g['0']; ?>
							</div>
							</label>
						<?php endforeach; ?>
					</div>

					<!-- required info -->
					<div class="col">
						<div class="form-group col-md-8">
							<label for="name"><b><?php echo $this->lang->line('pet_name'); ?></b>*</label>
							<input type="text" name="name" class="form-control" id="name" value="<?php echo $pet['name']; ?>" required>
						</div>
						<div class="form-group col-md-8">
							<label for="birth"><b><?php echo $this->lang->line('birth'); ?></b>*</label>
							<input type="date" name="birth" class="form-control" id="birth" value="<?php echo (isset($pet['birth'])) ? $pet['birth']: '' ?>" required>
							<?php if(!$pet['death']): ?><i><small id="birth_info" class="form-text text-muted ml-2">&nbsp;</small></i><?php endif; ?>
						</div>
		
						<div class="row form-group mx-1">
							<div class="col-md-6">
								<label for="breeds"><b><?php echo $this->lang->line('breed'); ?></b>*</label>
								<select name="breed" class="form-control" id="breeds" data-placeholder='<?php echo (isset($pet['breeds'])) ? $pet['breeds']['name']: ''; ?>'></select>
								<input type="hidden" id="current_breed" name="current_breed" value="<?php echo (isset($pet['breed'])) ? $pet['breed']: 1; ?>">
							</div>
							<div class="col-md-6">
								<label for="second_breed">x <?php echo $this->lang->line('breed'); ?></label>
								<select name="breed2" class="form-control" id="second_breed" data-placeholder='<?php echo (isset($pet['breeds2'])) ? $pet['breeds2']['name']: ''; ?>'></select>
								<input type="hidden" id="current_breed2" name="current_breed2" value="<?php echo (isset($pet['breed2'])) ? $pet['breed2']: -1; ?>">
							</div>
						</div>
					</div>
				</div>

				<h5><?php echo $this->lang->line('identification') ?></h5>
				<hr>
				<div class="form-row">
					<div class="col">
						<div class="form-group">
							<label for="chip"><?php echo $this->lang->line('chip'); ?></label>
							<input type="text" name="chip" class="form-control" id="chip" value="<?php echo (isset($pet['chip'])) ? $pet['chip']: '' ?>">
							<i><small id="chip_info" class="form-text text-muted ml-2">&nbsp;</small></i>
						</div>
					</div>
					<div class="col">
						<div class="form-group">
							<label for="vacbook"><?php echo $this->lang->line('vacc_nr'); ?></label>
							<input type="text" name="vacbook" class="form-control" id="vacbook" value="<?php echo (isset($pet['nr_vac_book'])) ? $pet['nr_vac_book']: '' ?>">
						</div>
					</div>
				</div>

				<h5>Trivia</h5>
				<hr>
				<div class="row">
					<div class="col">
							<label for="color"><?php echo $this->lang->line('haircolor'); ?></label>
							<input type="text" name="color" class="form-control" id="color" value="<?php echo (isset($pet['color'])) ? $pet['color']: '' ?>">
					</div>
					<div class="col">
						<label for="weight"><?php echo $this->lang->line('weight'); ?></label>
						<div class="input-group mb-3">
							<input type="text" class="form-control" name="weight" id="weight" aria-describedby="basic-addon2"  value="<?php echo (isset($pet['last_weight'])) ? $pet['last_weight']: '' ?>">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2">kg</span>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col">
						<div class="form-group">
							<label for="hairtype"><?php echo $this->lang->line('hairtype'); ?></label>
							<input type="text" name="hairtype" class="form-control" id="hairtype" value="<?php echo (isset($pet['hairtype'])) ? $pet['hairtype']: '' ?>">
						</div>
					</div>
					<div class="col">&nbsp;</div>
				</div>

				<!-- text fields -->
				<div class="form-group">
					<label for="notes"><?php echo $this->lang->line('notes'); ?></label>
					<textarea class="form-control" name="msg" id="notes" rows="3"><?php echo (isset($pet['note'])) ? $pet['note']: '' ?></textarea>
				</div>

				<div class="form-group">
					<label for="nutrion"><?php echo $this->lang->line('nutrition'); ?></label>
					<textarea class="form-control" name="nutritional_advice" id="nutrion" rows="3"><?php echo (isset($pet['nutritional_advice'])) ? $pet['nutritional_advice']: '' ?></textarea>
				</div>

				<div class="form-group">
					<label for="medication"><?php echo $this->lang->line('medicine'); ?></label>
					<textarea class="form-control" name="medication" id="medication" rows="3"><?php echo (isset($pet['medication'])) ? $pet['medication']: '' ?></textarea>
				</div>

				<h5><?php echo $this->lang->line('state'); ?></h5>
				<hr>
					<div class="form-group">
						<div class="form-check">
							<input class="form-check-input" name="dead" type="checkbox" value="1" id="dead" <?php echo (isset($pet['death']) && $pet['death'] == 1) ? 'checked': ''; ?>>
							<label class="form-check-label" for="dead"><?php echo $this->lang->line('passed_away'); ?></label>
						</div>
						<div class="form-check">
							<input class="form-check-input" name="lost" type="checkbox" value="1" id="lost" <?php echo (isset($pet['lost']) && $pet['lost'] == 1) ? 'checked': ''; ?>>
							<label class="form-check-label" for="lost"><?php echo $this->lang->line('gone_or_lost'); ?></label>
						</div>
					</div>

				<input type="hidden" name="owner" value="<?php echo $owner['id']; ?>">
				<button type="submit" name="submit" value="1" class="btn btn-outline-success"><i class="fa-solid fa-pen"></i> <?php echo $this->lang->line('edit'); ?></button>


				<?php if ($this->ion_auth->in_group("admin")): ?>
					<a href="<?php echo base_url('pets/delete/' . $pet['id']); ?>" class="btn btn-outline-danger float-right"><i class="fa-solid fa-triangle-exclamation fa-beat"></i> Delete Pet</a>
				<?php endif; ?>

			</div>
		</div>
	</div>
	<div class="col-lg-5 col-xl-2">
		<?php include "application/views/blocks/block_full_client.php"; ?>
	</div>
</div>

<script src="<?php echo base_url('assets/js/pet_profile.js'); ?>"></script>

<script type="text/javascript">

const SEARCH_BREED = '<?php echo base_url('breeds/search_breed/'); ?>';

document.addEventListener("DOMContentLoaded", function(){
	// $("#color").select2({
	// 	// need to map since they don't have an id
	// 	data: $.map(simple_colors, function (obj) { obj.id = obj.id || obj.text; return obj;}),
	// 	tags: true
	// });

	$("#breeds").select2(createBreedSelect2(SEARCH_BREED));
	$("#second_breed").select2(createBreedSelect2(SEARCH_BREED));

	/*
		make chip readable
	*/
	$("#chip").inputmask("***-***-***-***-***");

	/*
		calculate the age
	*/
	make_date($("#birth").val());
	$("#birth").change(function() {
		make_date(this.value);
	});

	/*
		check the chip, return the info
	*/
	get_chip_info($("#chip").val());
	$("#chip").change(function() {
		get_chip_info(this.value);
	});

	/*
		gender selection animation
	*/
	$('input[name="gender"]').change(function() {
        $('label.gender').addClass('gender-select');
        $('input[name="gender"]:not(:checked)').each(function() {
          $('label[for="' + this.id + '"]').removeClass('gender-select');
        });
      });
});
</script>