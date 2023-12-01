<?php if ($consumables) : ?>
	<?php foreach ($consumables as $product): ?>
		<tr style="background-color: <?php echo ($product['price_brut'] == 0) ? "#fff0ed" : "#edfff8" ?>;">
			<td>
				<?php echo $product['volume'] . ' ' . $product['product']['unit_sell'] . ' ' . $product['product']['name']; ?>
			</td>
			<td>
				<?php 
					if (count($product['prices']) > 1)
					{
						echo  $product['unit_price'] . '  <a data-toggle="collapse" href="#collapse' . $product['id'] . '" role="button" aria-expanded="false" aria-controls="collapse' . $product['id'] . '" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-angles-down"></i></a>';
						echo "<div class='collapse' id='collapse" . $product['id'] . "'>
								<table class='table table-sm small'>
									<thead>
										<tr>
											<th>Volume</th>
											<th>Unit Price</th>
											<th>Apply</th>
										</tr>
									</thead>
								";
						foreach ($product['prices'] as $price)
						{
							echo "<tr>
										<td>". $price['volume'] ." ". $product['product']['unit_sell']."</td>
										<td>". $price['price'] ." &euro;</td>
										<td>
										<a href='#' class='send btn btn-sm btn-outline-success' data-id='". $product["id"] ."' data-float-value='". $price['price']*$product['volume'] ."' >". $price['price']*$product['volume'] ." &euro; <i class='fa-solid fa-arrow-right'></i></a>
										</td>
								</tr>";
						}
						echo "</table></div>";
					}
					else
					{
						echo $product['prices']['0']['price'];
					}
				?>
				</td>
			<td><?php echo ($product['price_ori_net'] != 0) ? $product['price_ori_net'] : $product['price_net']; ?></td>
			
			<td>
				<form action="<?php echo base_url('events/edit_price/' . $event_info['id']); ?>" id="form<?php echo $product['id']; ?>" method="post" autocomplete="off" class="form-inline">
				
					<div class="input-group input-group-sm">
						<input type="text" name="price" value="<?php echo $product['price_net']; ?>" class="form-control" id="volume<?php echo $product['id']; ?>" <?php echo ($event_info['status'] == STATUS_CLOSED) ? 'disabled' : ''; ?>>
						  <div class="input-group-append">
							<span class="input-group-text">&euro;</span>
						  </div>
						  <div class="input-group-append">
							 <button type="submit" name="submit" value="store_prod_price" class="btn btn-outline-success"><i class="fas fa-save"></i></button>
						  </div>
					</div>
					<input type="hidden" name="price_ori_net" value="<?php echo ($product['price_ori_net'] != 0) ? $product['price_ori_net'] : $product['price_net']; ?>"/>
					<input type="hidden" name="btw" value="<?php echo $product['btw']; ?>"/>
					<input type="hidden" name="event_product_id" value="<?php echo $product['id']; ?>"/>
					<input type="hidden" name="pid" value="<?php echo $product['product_id']; ?>"/>
					<input type="hidden" name="reason" id="reason<?php echo $product['id']; ?>" value="MANUAL"/>
				</form>
			</td>			
			<td><?php echo show_difference($product['price_net'], $product['price_ori_net']); ?></td>
				<td><?php echo round($product['price_brut'],2); ?>
					<?php $total += $product['price_brut']; ?>
					<?php $total_ex += $product['price_net']; ?>
				</td>
		</tr>
	<?php endforeach; ?>
<?php endif; ?>
