<?php if ($procedures_d) : ?>
<?php foreach ($procedures_d as $done): ?>
	<tr style="background-color: <?php echo ($done['price'] == 0) ? "#fff0ed" : "#edfff8" ?>;">
		<td class="d-none d-xl-block">
			<?php echo $done['procedures']['name']; ?></td>
		<td>
			<form action="<?php echo base_url(); ?>events/proc_edit/<?php echo $event_id; ?>" id="proc<?php echo $done['id'] ?>" method="post" autocomplete="off" class="form-inline">
				<div class="input-group input-group-sm" style="width:125px;">
					<input type="text" name="amount" value="<?php echo $done['amount']; ?>" class="form-control" id="amount<?php echo $done['id']; ?>" disabled>
					<div class="input-group-append">
						<span class="input-group-text">st</span>
					</div>
				</div>
			<input type="hidden" name="id" value="<?php echo $done['id']; ?>"/>
			</form>
		</td>
		<td>&nbsp;</td>
		<td><?php echo $done['btw']; ?>%</td>
		<td><?php echo round($done['price'],2); ?>  &euro;<?php $total += $done['price']; ?></td>
		<td>
			<small><?php echo round($done['net_price'],2); $total_excl += $done['net_price']; ?> &euro;</small>
		</td>
		<td>	
		<?php if ($event_state != STATUS_CLOSED): ?>						
			<!-- <button type="submit" name="submit" form="proc<?php echo $done['id'] ?>" value="edit_proc" class="btn btn-outline-success btn-sm"><i class="fas fa-save"></i></button>	 -->
			<a href="<?php echo base_url(); ?>events/proc_remove/<?php echo $event_id; ?>/<?php echo $done['id'] ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
		<?php endif; ?>
		</td>
	</tr>

<?php endforeach; ?>
<?php endif; ?>