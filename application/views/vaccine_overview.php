<div class="row">
	<div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
		<div class="card-header">
			<a href="<?php echo base_url(); ?>owners/detail/<?php echo $pet_info['owners']['id']; ?>"><?php echo $pet_info['owners']['last_name'] ?></a> / 
			<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet_info['id']; ?>"><?php echo $pet_info['name'] ?></a> / vaccines
		</div>
            <div class="card-body">
			<?php if($vaccines): ?>
			<table class="table">
			  <thead>
				<tr>
				  <th>Vaccine</th>
				  <th>Date Expire</th>
				  <th>Event</th>
				  <th>Location</th>
				  <th>Vet</th>
				  <th>Created</th>
				</tr>
			  </thead>
			  <tbody>
				<?php foreach($vaccines as $vac): ?>
				<tr>
				  <td><?php echo $vac['product']['name']; ?></td>
				  <td><?php echo $vac['redo']; ?></td>
				  <td><a href="<?php echo base_url(); ?>events/event/<?php echo $vac['event_id']; ?>"><?php echo $vac['event_id']; ?></a></td>
				  <td><?php echo $vac['location']['name']; ?></td>
				  <td><?php echo $vac['vets']['first_name']; ?></td>
				  <td><?php echo $vac['created_at']; ?></td>
				</tr>
				<?php endforeach; ?>
			  </tbody>
			</table>
			<?php endif; ?>
			</div>
	  </div>
	</div>
</div>