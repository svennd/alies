<div class="row">
	<div class="col-lg-12 mb-4">
			<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>owners/detail/<?php echo $pet_info['owners']['id']; ?>"><?php echo $pet_info['owners']['last_name'] ?></a> / 
				<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet_info['id']; ?>"><?php echo $pet_info['name'] ?></a> / tooth
			</div>
			<div class="card-body">
				<?php if($pet_info['type'] == DOG): ?>
					<?php include "tooth/dog.php"; ?>
				<?php elseif($pet_info['type'] == CAT): ?>
					<?php include "tooth/cat.php"; ?>
				<?php elseif($pet_info['type'] == HORSE):?>
					<?php include "tooth/horse.php"; ?>
				<?php else : ?>
					No tooth records known;
				<?php endif; ?>
				<br/>
				<br/>
				<br/>
			<div class="mb-2">
				<div class="row">
					<div class="col-lg-8">
						<input type="color" id="head" name="head" value="#fff947">
						<label for="head">Paint color</label>
						
					</div>
					<div class="col-lg-4">
						<div class="alert alert-success" id="tooth_update_alert" style="display:none;" role="alert">test</div>
					</div>

				</div>
			</div>
			</div>
		</div>
	</div>
	<div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
			<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  Tooth Report
                  <div class="dropdown no-arrow">
                    <a  href="<?php echo base_url(); ?>tooth/history/<?php echo $pet_id; ?>" role="button" id="dropdownMenuLink">
                      <i class="fas fa-history"></i>
                    </a>
                  </div>
			</div>
			<div class="card-body">
			<?php if ($update_text) : ?>
			<div class="alert alert-success alert-dismissible fade show" role="alert">Updated!<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
			<?php endif; ?>
				<form action="<?php echo base_url(); ?>tooth/store/<?php echo $pet_id; ?>" method="post" autocomplete="off">  
				  <div class="form-group">
					<label for="message">Info</label>
					<textarea class="form-control" name="message" id="message" rows="6"><?php echo ($tooth_msg['msg']) ? $tooth_msg['msg'] : ''; ?></textarea>
				  </div>
				  <button type="submit" name="submit" value="update" class="btn btn-primary">Update</button>
				</form>

			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
	<?php if ($pet_info['tooths']) : ?>
	<?php foreach ($pet_info['tooths'] as $tooth): ?>
	$("#path_tooth_<?php echo $tooth['tooth']; ?>").css("fill", "<?php echo $tooth['tooth_status']; ?>");
	<?php endforeach; ?>
	<?php endif; ?>
	var color_tooth = "yellow";

	$("#head").change(function() {
		color_tooth = this.value;
	});
	
	$(".tooth").click(function() {
		$("#path_tooth_" + this.id).css("fill", color_tooth);
		
		$.ajax({
		 url:'<?php echo base_url(); ?>tooth/update/<?php echo $pet_id; ?>',
		 method: 'post',
		 data: {tooth: this.id, color: color_tooth},
		 dataType: 'json',
		 success: function(response){
			 console.log("Updated tooth #" + response.tooth + " to color " + response.color)
				$("#tooth_update_alert").html("Updated tooth <b>#" + response.tooth + "</b> to color <span style='background-color:"+ response.color +"'>" + response.color + "</span>");
				$("#tooth_update_alert").show();
			},
		});
   
	});
});
</script>
