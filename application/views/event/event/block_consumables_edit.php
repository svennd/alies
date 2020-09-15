<?php if ($consumables) : ?>
	<?php foreach ($consumables as $product): ?>
		<tr style="background-color: <?php echo ($product['price'] == 0) ? "#fff0ed" : "#edfff8" ?>;">
			<td>
				<?php echo $product['volume'] . ' ' . $product['product']['unit_sell']; ?> <?php echo $product['product']['name']; ?>
			</td>
			<td>
				<?php 
					if (count($product['prices']) > 1)
					{
						echo '<a data-toggle="collapse" href="#collapse' . $product['id'] . '" role="button" aria-expanded="false" aria-controls="collapse' . $product['id'] . '">' . $product['prices'][0]['price'] . '~' . $product['prices'][sizeof($product['prices']) - 1]['price']. '&euro;</a> / ' . $product['prices']['0']['volume'] . ' '. $product['product']['unit_sell'];
						echo "<div class='collapse' id='collapse" . $product['id'] . "'><table class='small'>";
						foreach ($product['prices'] as $price)
						{
							echo "<tr><td>". $price['volume'] ." ". $product['product']['unit_sell']."</td><td>". $price['price'] ."&euro;</td><tr>";
						}
						echo "</table></div>";
					}
					else
					{
						echo $product['prices']['0']['price'] . "&euro; / " . $product['prices']['0']['volume'] . " ". $product['product']['unit_sell'];
					}
				?>
				</td>
			<td><?php echo ($product['calc_net_price'] != 0) ? $product['calc_net_price'] : $product['net_price']; ?></td>
			
			<td>
				<form action="<?php echo base_url(); ?>events/edit_price/<?php echo $event_info['id']; ?>" id="form<?php echo $product['id']; ?>" method="post" autocomplete="off" class="form-inline">
				
					<div class="input-group">
						<input type="text" name="price" value="<?php echo $product['net_price']; ?>" class="form-control" id="volume<?php echo $product['id']; ?>" <?php echo ($event_info['status'] == STATUS_CLOSED) ? 'disabled' : ''; ?>>
						  <div class="input-group-append">
							<span class="input-group-text">&euro;</span>
						  </div>
						  <div class="input-group-append">
							 <button type="submit" name="submit" value="store_prod_price" class="btn btn-outline-success"><i class="fas fa-save"></i></button>
						  </div>
					</div>
					<input type="hidden" name="ori_net_price" value="<?php echo ($product['calc_net_price'] != 0) ? $product['calc_net_price'] : $product['net_price']; ?>"/>
					<input type="hidden" name="btw" value="<?php echo $product['btw']; ?>"/>
					<input type="hidden" name="event_product_id" value="<?php echo $product['id']; ?>"/>
					<input type="hidden" name="pid" value="<?php echo $product['product_id']; ?>"/>
				</form>
			</td>			
			<td>
			<?php
				if ($product['calc_net_price'] != 0)
				{
					
					$change = round((($product['net_price']-$product['calc_net_price'])/$product['calc_net_price'])*100);
					echo ($change < 0) ? 
									'<span style="color:red;">' . $change . '%</span>' 
								: 
									'<span style="color:green;">' . $change . '%</span>';
				}
			?>
			</td>
				<td><?php echo $product['price']; ?>
					<?php $total += $product['price']; ?>
				</td>
		</tr>
	<?php endforeach; ?>
<?php endif; ?>