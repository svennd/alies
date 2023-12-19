<?php if ($procedures_d) : ?>
<?php foreach ($procedures_d as $done): ?>
	<tr style="background-color: <?php echo ($done['price_brut'] == 0) ? "#fff0ed" : "#edfff8" ?>;">
		<td>
			<span class="d-md-none"><?php echo floatval($done['volume']); ?>x </span>
			<?php echo $done['procedures']['name']; ?></td>
		<td class="d-none d-sm-table-cell">
			<form action="<?php echo base_url(); ?>events/proc_edit/<?php echo $event_id; ?>" id="proc<?php echo $done['id'] ?>" method="post" autocomplete="off" class="form-inline">
				<div class="input-group input-group-sm" style="width:125px;">
					<input type="text" name="volume" value="<?php echo $done['volume']; ?>" class="form-control" id="volume<?php echo $done['id']; ?>" disabled>
					<div class="input-group-append">
						<span class="input-group-text">st</span>
					</div>
				</div>
			<input type="hidden" name="id" value="<?php echo $done['id']; ?>"/>
			</form>
		</td>
		<td class="d-none d-sm-table-cell">&nbsp;</td>
		<td class="d-none d-sm-table-cell"><?php echo $done['btw']; ?>%</td>
		<td><?php echo floatval(round($done['price_brut'],2)); ?>  &euro;<?php $total += $done['price_brut']; ?>
			<span class="d-md-none float-right">
			<?php if ($event_state != STATUS_CLOSED): ?>						
				<a href="<?php echo base_url('events/proc_remove/' . $event_id .'/'. $done['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
			<?php endif; ?>
			</span>
		</td>
		<td class="d-none d-sm-table-cell">
			<small><?php echo floatval($done['price_net']); $total_excl += $done['price_net']; ?> &euro;</small>
		</td>
		<td class="d-none d-sm-table-cell">	
		<?php if ($event_state != STATUS_CLOSED): ?>						
			<a href="<?php echo base_url('events/proc_remove/' . $event_id .'/'. $done['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
		<?php endif; ?>
		</td>
	</tr>

<?php endforeach; ?>
<?php endif; ?>