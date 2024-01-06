<?php if ($consumables) : ?>
	<?php foreach ($consumables as $product): ?>
		<tr style="background-color: <?php echo ($product['price_brut'] == 0) ? "#fff0ed" : "#edfff8" ?>;">
			<td>
				<?php echo $product['volume'] . ' ' . $product['product']['unit_sell'] . ' ' . $product['product']['name']; ?>
			</td>
			<td>
				<form action="<?php echo base_url('events/edit_unit_price/' . $product['id'] . '/' . $event_info['id']); ?>" id="form<?php echo $product['id']; ?>" method="post" autocomplete="off" class="form-inline">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
						<?php if(count($product['prices']) > 1): ?>
							<a data-toggle="collapse" href="#collapse<?php echo $product['id']; ?>" role="button" aria-expanded="false" aria-controls="collapse<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-angles-down"></i></a>
						<?php else: ?>
							<a data-toggle="collapse" href="#" role="button" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-angles-down"></i></a>
						<?php endif; ?>
						</div>
						<input type="text" name="unit_price" value="<?php echo $product['unit_price']; ?>" class="form-control" id="volume<?php echo $product['id']; ?>" <?php echo ($event_info['status'] == STATUS_CLOSED) ? 'disabled' : ''; ?>>
						<div class="input-group-append">
							<span class="input-group-text">&euro;</span>
						</div>
						<div class="input-group-append">
							<button type="submit" name="submit" value="submit" class="btn btn-outline-success"><i class="fas fa-save"></i></button>
						</div>
					</div>
					<input type="hidden" name="price_ori_net" value="<?php echo ($product['price_ori_net']) ? $product['price_ori_net'] : $product['price_net']; ?>"/>
					<input type="hidden" name="btw" value="<?php echo $product['btw']; ?>"/>
					<input type="hidden" name="volume" value="<?php echo $product['volume']; ?>"/>
					<input type="hidden" name="reason" id="reason<?php echo $product['id']; ?>" value="MANUAL"/>
					<input type="hidden" name="type" value="<?php echo PRODUCT; ?>"/>
				</form>
				<?php 
					if (count($product['prices']) > 1)
					{
						echo "<div class='collapse' id='collapse" . $product['id'] . "'>
								<table class='table table-sm small'>
									<thead>
										<tr>
											<th>" . $this->lang->line('volume') . "</th>
											<th>" . $this->lang->line('Unit_price') . "</th>
											<th>" . $this->lang->line('apply') . "</th>
										</tr>
									</thead>
								";
						foreach ($product['prices'] as $price)
						{
							echo "<tr>
										<td>". $price['volume'] ." ". $product['product']['unit_sell']."</td>
										<td>". $price['price'] ." &euro;</td>
										<td>
										<a href='#' class='send btn btn-sm btn-outline-success' data-id='". $product["id"] ."' data-float-value='". $price['price'] ."' >". number_format($price['price']*$product['volume'], 2) ." &euro; <i class='fa-solid fa-arrow-right'></i></a>
										</td>
								</tr>";
						}
						echo "</table></div>";
					}
				?>
				</td>
			<td>
				<?php echo ($product['price_ori_net']) ? '<span class="crossed-out">' . $product['price_ori_net'] . '</span>' : $product['price_net']; ?>
				<?php if ($product['price_ori_net']): ?><small><br/>~<?php echo number_format($product['price_ori_net']/$product['volume'], 2) ?> / <?php echo $product['product']['unit_sell']; ?></small><?php endif; ?>
			</td>
			<td><?php echo $product['price_net']; ?></td>
	
			<td><?php echo show_difference($product['price_net'], $product['price_ori_net']); ?></td>
				<td><?php echo round($product['price_brut'],2); ?>
					<?php $total += $product['price_brut']; ?>
					<?php $total_ex += $product['price_net']; ?>
				</td>
		</tr>
	<?php endforeach; ?>
<?php endif; ?>
