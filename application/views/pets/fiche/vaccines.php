<div class="card shadow mb-4" style="width:100%;">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div><?php echo $this->lang->line('vaccines'); ?>
		</div>
	</div>
	<div class="card-body">
		
<?php if($vaccines): ?>
	<table class="table table-sm">
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
			<td><?php echo ($vac['max_rappel'] <= (new DateTime())->modify('+3 month')) ?
								'<span class="text-danger">' . user_format_date( $vac['max_rappel'], $user->user_date) . '</span>'
								:
								user_format_date( $vac['max_rappel'], $user->user_date)
								; ?></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<?php echo $this->lang->line('no_vaccines'); ?>
<?php endif; ?>
	<div class="text-center pt-3">
		<a href="<?php echo base_url('vaccine/fiche/' . $pet['id']); ?>" class="btn btn-outline-primary"><i class="fa-solid fa-syringe fa-fw"></i> <?php echo $this->lang->line('title_vaccines'); ?></a>					
	</div>
</div>
</div>