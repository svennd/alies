<div class="row">
	<div class="col-lg-7 col-xl-10">
		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url('owners/detail/'. $owner['id']); ?>"><?php echo $owner['last_name'] ?></a> / <?php echo $this->lang->line('add_pet'); ?>
			</div>
			<div class="card-body">
				<form action="<?php echo base_url('pets/add/' . $owner['id']) ?>" method="post" autocomplete="off" name="new_pet">

				<h5><?php echo $this->lang->line('properties'); ?></h5>
				<hr>
				<!-- type -->
				<label><b>Type</b>*</label>
				<div class="row">
					<?php foreach($pet_type as $pid => $p): ?>
						<div class="col text-center noradio">
							<input type="radio" name="type" value="<?php echo $pid; ?>" id="<?php echo $pid; ?>" required />
							<label for="<?php echo $pid; ?>" class="lbl-radio">
							<div class="content bounceit" >
								<div class="title"><span style="color:<?php echo $p[1]; ?>"><i class="fas fa-<?php echo $p[2]; ?> fa-fw fa-2x"></i></span></div>
								<?php echo $this->lang->line($p[0]); ?>
							</div>
							</label>
						</div>
					<?php endforeach; ?>
				</div>

			<div id="pre-type-select" class="opacy-disabled">
				<div class="row py-3">

					<!-- gender list -->
					<div class="col">
						<label><b><?php echo $this->lang->line('gender'); ?></b>*</label>
						<?php foreach($gender_type as $gid => $g): ?>
							<label for="g<?php echo $gid; ?>" class="lbl-radio gender">
							<div class="content">
								<input type="radio" name="gender" value="<?php echo $gid; ?>" id="g<?php echo $gid; ?>" required/>
								<span style="color:<?php echo $g['1']; ?>"><i class="fas fa-<?php echo $g['2']; ?> fa-fw"></i></span> <?php echo $g['0']; ?>
							</div>
							</label>
						<?php endforeach; ?>
					</div>

					<!-- required info -->
					<div class="col">
						<div class="form-group col-md-8">
							<label for="name"><b><?php echo $this->lang->line('pet_name'); ?></b>*</label>
							<input type="text" name="name" class="form-control" id="name" value="" required>
						</div>
						<div class="form-group col-md-8">
							<label for="birth"><b><?php echo $this->lang->line('birth'); ?></b>*</label>
							<input type="date" name="birth" class="form-control" id="birth" value="" min="<?php echo date("Y-m-d", strtotime("-30 year")); ?>" max="<?php echo date("Y-m-d", strtotime("+1 day")); ?>" required>
							<i><small id="birth_info" class="form-text text-muted ml-2">&nbsp;</small></i>
						</div>
						
						<div class="row form-group mx-1">
							<div class="col-md-6">
								<label for="breeds"><b><?php echo $this->lang->line('breed'); ?></b>*</label>
								<select name="breed" class="form-control" id="breeds" required></select>
							</div>
							<div class="col-md-6">
								<label for="breed2">x <?php echo $this->lang->line('breed'); ?></label>
								<select name="breed2" class="form-control" id="breed2"></select>
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
								<input type="text" name="chip" class="form-control" id="chip" value="">
								<i><small id="chip_info" class="form-text text-muted ml-2">&nbsp;</small></i>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label for="vacbook"><?php echo $this->lang->line('vacc_nr'); ?></label>
								<input type="text" name="vacbook" class="form-control" id="vacbook" value="">
							</div>
						</div>
					</div>

				<h5>Trivia</h5>
				<hr>
				<div class="row">
					<div class="col">
						<label for="color"><?php echo $this->lang->line('haircolor'); ?></label>
						<select class="form-control" name="color" id="color"></select>
					</div>
					<div class="col">
						<label for="weight"><?php echo $this->lang->line('weight'); ?></label>
						<div class="input-group mb-3">
							<input type="text" class="form-control" name="weight" id="weight" aria-describedby="basic-addon2"  value="">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2">kg</span>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<label for="hairtype"><?php echo $this->lang->line('hairtype'); ?></label>
						<input type="text" name="hairtype" class="form-control" id="hairtype" value="">
					</div>
					<div class="col">&nbsp;</div>
				</div>

				<div class="form-group">
					<label for="exampleFormControlTextarea1"><?php echo $this->lang->line('notes'); ?></label>
					<textarea class="form-control" name="msg" id="exampleFormControlTextarea1" rows="3"></textarea>
				</div>
				<input type="hidden" name="owner" value="<?php echo $owner['id']; ?>">
				<button type="submit" name="submit" value="1" class="btn btn-success"><i class="fas fa-plus fa-fw"></i> <?php echo $this->lang->line('add'); ?></button>
			<!-- opacy-disable -->
			</div>

			</form>
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

	$("#color").select2({
		// need to map since they don't have an id
		data: $.map(simple_colors, function (obj) { obj.id = obj.id || obj.text; return obj;}),
		tags: true
	});

	/*
		make chip readable
	*/
	$("#chip").inputmask("***-***-***-***-***");

	$("#breeds").select2(createBreedSelect2(SEARCH_BREED));
	$("#second_breed").select2(createBreedSelect2(SEARCH_BREED));


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
		type selection animation
	*/
	$('input[name="type"]').change(function() {
		$('label.lbl-radio').removeClass('opacy-disabled');
		$('#pre-type-select').removeClass('opacy-disabled');
		$('input[name="type"]:not(:checked)').each(function() {
			$('label[for="' + this.id + '"]').addClass('opacy-disabled');
		});
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