<?php if ($procedures_d) : ?>
<?php foreach ($procedures_d as $done):?>
	<tr style="background-color: <?php echo ($done['price'] == 0) ? "#fff0ed" : "#edfff8" ?>;">
		<td><?php echo $done['procedures']['name']; ?></td>
		<td>&nbsp;</td>
		<td><?php echo $done['procedures']['price']; $total += $done['price']; ?></td>
		<td>
			<form action="<?php echo base_url(); ?>events/proc_edit/<?php echo $event_id; ?>" id="proc<?php echo $done['id'] ?>" method="post" autocomplete="off" class="form-inline">
				<div class="input-group" style="width:175px;">
					<input type="text" name="amount" value="<?php echo $done['amount']; ?>" class="form-control" id="amount<?php echo $done['id']; ?>" <?php echo ($event_state == STATUS_CLOSED) ? 'disabled' : ''; ?>>
					<div class="input-group-append">
						<span class="input-group-text">st</span>
					</div>
				</div>
			<input type="hidden" name="id" value="<?php echo $done['id']; ?>"/>
			</form>	
		</td>
		<td><?php echo $done['btw']; ?>%</td>
		<td>
			<?php echo $done['price']; ?><br/>
			<small><?php echo $done['net_price']; ?> ex. vat</small>
		</td>
		<td>	
		<?php if ($event_state != STATUS_CLOSED): ?>						
			<button type="submit" name="submit" form="proc<?php echo $done['id'] ?>" value="edit_proc" class="btn btn-outline-success btn-sm"><i class="fas fa-save"></i></button>	
			<a href="<?php echo base_url(); ?>events/proc_remove/<?php echo $event_id; ?>/<?php echo $done['id'] ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
		<?php endif; ?>
		</td>
	</tr>

<?php endforeach; ?>
<?php endif; ?>