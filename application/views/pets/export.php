<div class="row">
    <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> /
				<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet_info['id']; ?>"><?php echo $pet_info['name'] ?></a> <small>(#<?php echo $pet_info['id']; ?>)</small> /
				Export
			</div>
            <div class="card-body">
			<p>Please select the events to export.</p>
			<form action="<?php echo base_url(); ?>pets/export/<?php echo $pet_info['id']; ?>" method="post" autocomplete="off">
      <?php if($pet_history): ?>
  		<table class="table table-sm">
			  <thead class="thead-light">
				<tr>
				  <th scope="col">#</th>
				  <th scope="col">Date</th>
				  <th scope="col">Title</th>
				  <th scope="col">Location</th>
				  <th scope="col">Vet</th>
				  <th scope="col">Add in export</th>
				</tr>
			  </thead>
			<?php  $i = 1; foreach ($pet_history as $his):

				if (isset($history_to_take) && !in_array($his['id'], $history_to_take)) { continue; }
				?>
				<tr>
					<td>#<?php echo $i++; ?></td>
					<td><?php echo $his['created_at']; ?></td>
					<td><?php echo $his['title']; ?></td>
					<td><?php echo $his['location']['name']; ?></td>
					<td><?php echo $his['vet']['first_name']. ' ' . $his['vet']['last_name']; ?></td>
					<td>
						<div class="input-group mb-2 mr-sm-2">
							<div class="input-group-prepend">
								<div class="input-group-text">
									<input type="checkbox" name="history_to_take[]" value="<?php echo $his['id']; ?>">
								</div>
							</div>
							<input type="button" class="form-control ana" name="show" id="ana_<?php echo $i; ?>" value="show">
						</div>
					</td>
				</tr>
				<tr id="ana_<?php echo $i; ?>_text" style="display:none;">
					<td colspan="6" style="border-bottom:1px solid black;"><?php echo nl2br($his['anamnese']); ?></td>
				</tr>
			<?php endforeach; ?>
			</table>
				<button type="submit" name="submit" value="1" class="btn btn-primary">Export PDF</button>
      <?php else: ?>
        no events on this pet.
      <?php endif; ?>
			</form>
			</div>
		</div>
	</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function(){
	$(".ana").click(function(){
		console.log(this.id);
		$("#" + this.id + "_text").toggle();
	});
});
</script>
