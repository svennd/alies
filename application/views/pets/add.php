<?php 
$edit_mode = false; 
?>
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
				<?php 
					$pet_type = array(
								"0" => array("dog", "#f2a10d", "dog"),
								"1" => array("cat", "#005248", "cat"),
								"2" => array("horse", "#402E32", "horse"),
								"3" => array("bird", "#FFB087", "dove"),
								"5" => array("rabbit", "#AD4CF4", "paw"),
								"4" => array("other", "#DFE0DF", "ghost"),
					);
				?>
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
					<div class="col">
						<?php 
							$gender_type = array(
										"0" => array("Male", "#4c6ef5", "mars"),
										"2" => array("Male neutered", "#000", "mars"),
										"1" => array("Female", "#f783ac", "venus"),
										"3" => array("Female neutered", "#000", "venus"),
										"4" => array("Other", "#6cce23", "genderless"),
							);
						?>
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
					<div class="col">
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
								<label for="second_breed">x <?php echo $this->lang->line('breed'); ?></label>
								<select name="breed2" class="form-control" id="second_breed" data-placeholder='<?php echo ($edit_mode && isset($pet['breeds2'])) ? $pet['breeds2']['name']: ''; ?>'></select>
								<input type="hidden" id="current_breed2" name="current_breed2" value="<?php echo ($edit_mode && isset($pet['breed2'])) ? $pet['breed2']: -1; ?>">
							</div>
						</div>
					</div>
				</div>

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
					<div class="form-group">
						<label for="color"><?php echo $this->lang->line('haircolor'); ?></label>
						<input type="text" name="color" class="form-control" id="color" value="">
					</div>
					</div>
					<div class="col">
					<div class="form-group">
						<label for="weight"><?php echo $this->lang->line('weight'); ?></label>
						<div class="input-group mb-3">
							<input type="text" class="form-control" name="weight" id="weight" aria-describedby="basic-addon2"  value="">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2">kg</span>
							</div>
						</div>
					</div>
				</div>
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

<script src="<?php echo base_url('assets/js/add_pet.js'); ?>"></script>

<script type="text/javascript">

document.addEventListener("DOMContentLoaded", function(){


const url = '<?php echo base_url('breeds/search_breed/'); ?>';
	$("#breeds").select2(createBreedSelect2(url));
	$("#second_breed").select2(createBreedSelect2(url));

	$("#birth").change(function() {
		make_date(this.value);
	});
	$("#chip").change(function() {
		get_chip_info(this.value);
	});

	$("dead").change(function(event) {
		var checkbox = event.target;
		if (checkbox.checked) {
		} else {

		}
	});

	make_date($("#birth").val());
	get_chip_info($("#chip").val());

      $('input[name="type"]').change(function() {
        $('label.lbl-radio').removeClass('opacy-disabled');
		$('#pre-type-select').removeClass('opacy-disabled');
        $('input[name="type"]:not(:checked)').each(function() {
          $('label[for="' + this.id + '"]').addClass('opacy-disabled');
        });
      });

      $('input[name="gender"]').change(function() {
        $('label.gender').addClass('gender-select');
        $('input[name="gender"]:not(:checked)').each(function() {
          $('label[for="' + this.id + '"]').removeClass('gender-select');
        });
      });
});

</script>