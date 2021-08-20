<div class="row">

	<div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>owners/detail/<?php echo $pet_info['owners']['id']; ?>"><?php echo $pet_info['owners']['last_name'] ?></a> / 
				<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet_info['id']; ?>"><?php echo $pet_info['name'] ?></a> / 
				<a href="<?php echo base_url(); ?>tooth/fiche/<?php echo $pet_info['id']; ?>">teeth</a> / 
				history
			</div>
            <div class="card-body">
				<?php if ($history): ?> 
					<table class="table">
					<tr>
						<th>Vet</th>
						<th>Location</th>
						<th>Message</th>
						<th>Date</th>
					</tr>
				<?php foreach($history as $his): ?>
					<tr>
						<td><?php echo $his['vet']['first_name']; ?></td>
						<td><?php echo $his['location']['name']; ?></td>
						<td>
							<a href="#collapseExample<?php echo $his['id']; ?>" data-toggle="collapse" aria-expanded="false" aria-controls="collapseExample<?php echo $his['id']; ?>">
								<?php echo substr($his['msg'], 0, 15); ?>
							</a>
							<div class="collapse" id="collapseExample<?php echo $his['id']; ?>">
								<?php echo $his['msg']; ?>
							</div>
							</td>
						<td><?php echo timespan(strtotime($his['created_at']), time(), 1); ?></td>
					</tr>
				<?php endforeach; ?>
					</table>
				<?php else: ?>
					No history
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
