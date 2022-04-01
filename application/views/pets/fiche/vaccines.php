<div class="card shadow mb-4">
	<div class="card-header"><a href="<?php echo base_url(); ?>vaccine/fiche/<?php echo $pet['id']; ?>">Vaccinations</a></div>
	<div class="card-body">
	<?php if($vaccines): ?>
		<table class="table">
		  <thead>
			<tr>
			  <th>Vaccine</th>
			  <th>Date Expire</th>
			</tr>
		  </thead>
		  <tbody>
			<?php foreach($vaccines as $vac):?>
			<tr>
			  <td><?php echo $vac['name']; ?></td>
			  <td><?php echo user_format_date( $vac['max_redo'], $user->user_date); ?></td>
			</tr>
			<?php endforeach; ?>
		  </tbody>
		</table>
	<?php else: ?>
		No known vaccines
	<?php endif; ?>
	</div>
</div>
