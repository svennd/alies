<?php if ($procedures_d) : ?>
<?php foreach ($procedures_d as $done):?>
	<tr style="background-color: <?php echo ($done['price_brut'] == 0) ? "#fff0ed" : "#edfff8" ?>;">
		<td><?php echo $done['volume'] . ' '. $done['procedures']['name']; ?></td>
		<td><?php echo $done['procedures']['price']; ?></td>
		<td><?php echo ($done['price_ori_net'] != 0) ? $done['price_ori_net'] : $done['price_net']; ?></td>
		<td>
			<form action="<?php echo base_url('events/edit_price/' . $event_info['id']); ?>" id="form<?php echo $done['id']; ?>" method="post" autocomplete="off" class="form-inline">
			
				<div class="input-group input-group-sm">
					<input type="text" name="price" value="<?php echo $done['price_net']; ?>" class="form-control" id="volume<?php echo $done['id']; ?>" <?php echo ($event_info['status'] == STATUS_CLOSED) ? 'disabled' : ''; ?>>
					  <div class="input-group-append">
						<span class="input-group-text">&euro;</span>
					  </div>
					  <div class="input-group-append">
						 <button type="submit" name="submit" value="store_proc_price" class="btn btn-outline-success"><i class="fas fa-save"></i></button>
					  </div>
				</div>
				<input type="hidden" name="price_ori_net" value="<?php echo ($done['price_ori_net'] != 0) ? $done['price_ori_net'] : $done['price_net']; ?>"/>
				<input type="hidden" name="btw" value="<?php echo $done['btw']; ?>"/>
				<input type="hidden" name="event_proc_id" value="<?php echo $done['id']; ?>"/>
				<input type="hidden" name="pid" value="<?php echo $done['procedures']['id']; ?>"/>
				<input type="hidden" name="reason" id="reason<?php echo $done['id']; ?>" value="MANUAL"/>
			</form>
		</td>
		<td><?php echo show_difference($done['price_net'], $done['price_ori_net']); ?></td>
		<td>
			<?php echo round($done['price_brut'],2); ?>
			<?php $total += $done['price_brut']; ?>
			<?php $total_ex += $done['price_net']; ?>
		</td>
		
	</tr>

<?php endforeach; ?>
<?php endif; ?>