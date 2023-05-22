<div class="card shadow mb-4">
	<div class="card-header"><a href="<?php echo base_url(); ?>vaccine/fiche/<?php echo $pet['id']; ?>"><?php echo $this->lang->line('title_vaccines'); ?></a></div>
	<div class="card-body">
	<?php if($vaccines): ?>
		<table class="table">
		  <thead>
			<tr>
			  <th><?php echo $this->lang->line('vaccines'); ?></th>
			  <th><?php echo $this->lang->line('injection'); ?></th>
			  <th><?php echo $this->lang->line('rappel_date'); ?></th>
			</tr>
		  </thead>
		  <tbody>
			<?php foreach($vaccines as $vac):?>
			<tr>
			  <td><?php echo $vac['name']; ?></td>
			  <td><?php echo user_format_date( $vac['max_injection'], $user->user_date); ?></td>
			  <td><?php echo (!$vac['min_no_rappel']) ? user_format_date( $vac['max_rappel'], $user->user_date) : '---'; ?></td>
			</tr>
			<?php endforeach; ?>
		  </tbody>
		</table>
	<?php else: ?>
		<?php echo $this->lang->line('no_vaccines'); ?>
	<?php endif; ?>
	</div>
</div>
