<?php if ($procedures_d) : ?>
<?php foreach ($procedures_d as $done):?>
	<tr style="background-color: <?php echo ($done['price'] == 0) ? "#fff0ed" : "#edfff8" ?>;">
		<td><?php echo $done['amount']; ?> x <?php echo $done['procedures']['name']; ?></td>
		<td><?php echo $done['procedures']['price']; ?></td>
		<td><?php echo ($done['calc_net_price'] != 0) ? $done['calc_net_price'] : $done['net_price']; ?></td>
		<td>
			<form action="<?php echo base_url(); ?>events/edit_price/<?php echo $event_info['id']; ?>" id="form<?php echo $done['id']; ?>" method="post" autocomplete="off" class="form-inline">
			
				<div class="input-group">
					<input type="text" name="price" value="<?php echo $done['net_price']; ?>" class="form-control" id="volume<?php echo $done['id']; ?>" <?php echo ($event_info['status'] == STATUS_CLOSED) ? 'disabled' : ''; ?>>
					  <div class="input-group-append">
						<span class="input-group-text">&euro;</span>
					  </div>
					  <div class="input-group-append">
						 <button type="submit" name="submit" value="store_proc_price" class="btn btn-outline-success"><i class="fas fa-save"></i></button>
					  </div>
				</div>
				<input type="hidden" name="ori_net_price" value="<?php echo ($done['calc_net_price'] != 0) ? $done['calc_net_price'] : $done['net_price']; ?>"/>
				<input type="hidden" name="btw" value="<?php echo $done['btw']; ?>"/>
				<input type="hidden" name="event_proc_id" value="<?php echo $done['id']; ?>"/>
				<input type="hidden" name="pid" value="<?php echo $done['procedures']['id']; ?>"/>
			</form>
		</td>
		<td>
		<?php
			if ($done['calc_net_price'] != 0)
			{
				
				$change = round((($done['net_price']-$done['calc_net_price'])/$done['calc_net_price'])*100);
				echo ($change < 0) ? 
								'<span style="color:red;">' . $change . '%</span>' 
							: 
								'<span style="color:green;">' . $change . '%</span>';
			}
		?>
		</td>
		<td>
			<?php echo $done['price']; ?>
			<?php $total += $done['price']; ?>
		</td>
		
	</tr>

<?php endforeach; ?>
<?php endif; ?>