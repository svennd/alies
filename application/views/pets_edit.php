<div class="row">
	<div class="col-lg-7 col-xl-10">
		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / 
				<?php if($pet['death'] == 1 || $pet['lost'] == 1): ?>
				<?php echo (isset($pet['name'])) ? $pet['name']: '' ?>
				<?php else : ?>
				<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id']; ?>"><?php echo (isset($pet['name'])) ? $pet['name']: '' ?></a> 
				<?php endif; ?>
				/ Edit pet
			</div>
			<div class="card-body">


<form action="<?php echo base_url(); ?>pets/edit/<?php echo $pet['id']; ?>" method="post" autocomplete="off">
  <div class="form-group">
<div class="form-check form-check-inline">
  <input class="form-check-input" type="radio" name="type" id="exampleRadios1" value="0" <?php echo ($pet['type'] == 0) ? 'checked' : ''; ?>>
  <label class="form-check-label" for="exampleRadios1">
    <i class="fas fa-dog"></i> Dog
  </label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="radio" name="type" id="exampleRadios2" value="1" <?php echo ($pet['type'] == 1) ? 'checked' : ''; ?>>
  <label class="form-check-label" for="exampleRadios2">
	<i class="fas fa-cat"></i> Cat
  </label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="radio" name="type" id="exampleRadios3" value="2" <?php echo ($pet['type'] == 2) ? 'checked' : ''; ?>>
  <label class="form-check-label" for="exampleRadios3">
	<i class="fas fa-horse"></i> Horse
  </label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="radio" name="type" id="exampleRadios4" value="3" <?php echo ($pet['type'] == 3) ? 'checked' : ''; ?>>
  <label class="form-check-label" for="exampleRadios4">
	<i class="fas fa-dove"></i> Bird
  </label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="radio" name="type" id="exampleRadios5" value="4" <?php echo ($pet['type'] == 4) ? 'checked' : ''; ?>>
  <label class="form-check-label" for="exampleRadios5">
    <i class="fas fa-ghost"></i> Other
  </label>
</div>

  </div>

<div class="row">
    <div class="col"> 
	  <div class="form-group">
		<label for="name">name</label>
		<input type="text" name="name" class="form-control" id="name" value="<?php echo (isset($pet['name'])) ? $pet['name']: '' ?>">
	  </div>
	</div>
    <div class="col"> 
	  <div class="form-group">
		<label for="birth">birth</label>
		<input type="date" name="birth" class="form-control" id="birth" value="<?php echo (isset($pet['birth'])) ? $pet['birth']: '' ?>">
	  </div>
  </div>
</div>
  <div class="form-group">
    <label for="name">Gender</label>
		<div class="form-row">
    <div class="col">
		<div class="form-check">
		  <input class="form-check-input" type="radio" name="gender" id="gender1" value="0" <?php echo ($pet['gender'] == 0) ? 'checked' : ''; ?>>
		  <label class="form-check-label" for="gender1">
			male
		  </label>
		</div>
		<div class="form-check">
		  <input class="form-check-input" type="radio" name="gender" id="gender2" value="1" <?php echo ($pet['gender'] == 1) ? 'checked' : ''; ?>>
		  <label class="form-check-label" for="gender2">
			female
		  </label>
		</div>
	</div>
    <div class="col">
		<div class="form-check">
		  <input class="form-check-input" type="radio" name="gender" id="gender1n" value="2" <?php echo ($pet['gender'] == 2) ? 'checked' : ''; ?>>
		  <label class="form-check-label" for="gender1n">
			male neutered
		  </label>
		</div>
		<div class="form-check">
		  <input class="form-check-input" type="radio" name="gender" id="gender2n" value="3" <?php echo ($pet['gender'] == 3) ? 'checked' : ''; ?>>
		  <label class="form-check-label" for="gender2n">
			female neutered
		  </label>
		</div>
	</div>

    <div class="col">
		<div class="form-check">
		  <input class="form-check-input" type="radio" name="gender" id="gender7n" value="4" <?php echo ($pet['gender'] == 4) ? 'checked' : ''; ?>>
		  <label class="form-check-label" for="gender7n">
			other
		  </label>
		</div>
	</div>

	</div>
  </div>

  <div class="form-group">
    <label for="breed">breed</label>
	<div class="form-row">
		<div class="col">
		<select name="breed" class="form-control" id="breeds">
			<?php foreach($breeds as $breed): ?>
				<option value="<?php echo $breed['id']; ?>" <?php echo ($breed['id'] == $pet['breed']) ? 'selected': ''; ?>><?php echo $breed['name']; ?></option>
			<?php endforeach; ?>
		</select>
		</div>
		<div class="col">
		<div class="input-group input-group-sm mb-2 mr-sm-2">
			<div class="input-group-prepend">
				<div class="input-group-text">or add</div>
			</div>
			<input name="breed_custom" type="text" class="form-control form-control-sm" placeholder="custom breed">
		</div>
		</div>
	 </div>
  </div>
<div class="row">
    <div class="col"> 
	  <div class="form-group">
		<label for="color">color</label>
		<input type="text" name="color" class="form-control" id="color" value="<?php echo (isset($pet['color'])) ? $pet['color']: '' ?>">
	  </div>
	</div>
    <div class="col"> 
	  <div class="form-group">
		<label for="weight">weight <a href="<?php echo base_url(); ?>pets/history_weight/<?php echo $pet['id'] ?>"><i class="fas fa-file-medical-alt"></i></a></label>
		<div class="input-group mb-3">
			<input type="text" class="form-control" name="weight" id="weight" aria-describedby="basic-addon2"  value="<?php echo (isset($pet['last_weight'])) ? $pet['last_weight']: '' ?>">
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
    <input type="text" name="vacbook" class="form-control" id="vacbook" value="<?php echo (isset($pet['nr_vac_book'])) ? $pet['nr_vac_book']: '' ?>">
  </div>
  <div class="form-group">
    <label for="chip">Chip</label>
    <input type="text" name="chip" class="form-control" id="chip" value="<?php echo (isset($pet['chip'])) ? $pet['chip']: '' ?>">
  </div>
  
  <h5>Trivia</h5>
  <hr>
  <div class="form-group">
    <label for="exampleFormControlTextarea1">Opmerkingen</label>
    <textarea class="form-control" name="msg" id="exampleFormControlTextarea1" rows="3"><?php echo (isset($pet['note'])) ? $pet['note']: '' ?></textarea>
  </div>
  
  <h5>Status</h5>
  <hr>
	<div class="form-group">
		<div class="form-check">
		<input class="form-check-input" name="dead" type="checkbox" value="1" id="gridCheck" <?php echo (isset($pet['death']) && $pet['death'] == 1) ? 'checked': ''; ?>>
			<label class="form-check-label" for="gridCheck">
			Dead
			</label>
		</div>
		<div class="form-check">
		<input class="form-check-input" name="lost" type="checkbox" value="1" id="gridCheck2" <?php echo (isset($pet['lost']) && $pet['lost'] == 1) ? 'checked': ''; ?>>
		  <label class="form-check-label" for="gridCheck2">
			Lost
		  </label>
		</div>
	</div> 
  <h5>Update</h5>
  <hr>
  <input type="hidden" name="owner" value="<?php echo $owner['id']; ?>">
  <button type="submit" name="submit" value="1" class="btn btn-primary">Submit</button>
</form>

			</div>
		</div>

	</div>
	
	<div class="col-lg-5 col-xl-2">
		<?php include "pets/fiche/block_full_client.php"; ?>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#breeds").select2();
});
</script>