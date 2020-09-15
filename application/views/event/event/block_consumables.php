<?php if ($consumables) : ?>
	<?php foreach ($consumables as $product): ?>
		<tr style="background-color: <?php echo ($product['price'] == 0) ? "#fff0ed" : "#edfff8" ?>;">
			<td>
				<?php echo $product['product']['name']; ?>
			</td>
			<td>
			<?php if (!empty($product['barcode'])) : ?>
				<small><?php echo $product['barcode']; ?><br/>eol:<?php echo $product['stock']['eol']; ?> lot:<?php echo $product['stock']['lotnr']; ?></small>
			<?php else: ?>
				<small>-</small>
			<?php endif; ?>
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
			<td>
			<form action="<?php echo base_url(); ?>events/prod_edit/<?php echo $event_id; ?>" id="form<?php echo $product['id']; ?>" method="post" autocomplete="off" class="form-inline">
			
				<div class="input-group" style="width:175px;">
					<input type="text" name="volume" value="<?php if($product['volume'] != 0) { echo $product['volume']; }; ?>" class="form-control <?php if($product['volume'] == 0) { echo "is-invalid"; } ?>" id="volume<?php echo $product['id']; ?>" <?php echo ($event_state == STATUS_CLOSED) ? 'disabled' : ''; ?>>
					  <div class="input-group-append">
						<span class="input-group-text"><?php echo $product['product']['unit_sell']; ?></span>
					  </div>
				</div>
				
				<input type="hidden" name="event_product_id" value="<?php echo $product['id']; ?>"/>
				<input type="hidden" name="pid" value="<?php echo $product['product_id']; ?>"/>
				<input type="hidden" name="btw" value="<?php echo $product['btw']; ?>"/>
			</form>
			
			</td>
			<td>
				<?php echo (!empty($product['btw'])) ? $product['btw'] : $product['product']['btw_sell']; ?>%
			</td>
			
				<td><?php echo $product['price']; ?><br/>
					<small><?php echo $product['net_price']; ?> ex. vat</small>
					<?php $total += $product['price']; ?>
				</td>		
			<td>
				<?php if ($event_state != STATUS_CLOSED): ?>
					<button type="submit" name="submit" value="edit_prod" form="form<?php echo $product['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-save"></i></button>
					<a href="<?php echo base_url(); ?>events/prod_remove/<?php echo $event_id; ?>/<?php echo $product['id'] ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
				<?php endif; ?>
			</td>
		</tr>
		<?php if($product['product']['vaccin']): ?>
		<tr>
			<td colspan="7">
				<form action="<?php echo base_url(); ?>events/edit_vaccin/<?php echo $event_id; ?>/<?php echo $product['vaccine']['id']; ?>" id="form_vaccin_<?php echo $product['id']; ?>" method="post" autocomplete="off" class="form-inline">
				  <div class="form-group mb-2">
					<span class="fa-stack" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-level-up-alt fa-rotate-90 fa-stack-1x"></i>
					</span>		
				  </div>
				  <div class="form-group mb-2 mx-3">
					<label for="redo<?php echo $product['vaccine']['id']; ?>" class="sr-only">Redo</label>
					<input type="date" name="redo" class="form-control" value="<?php echo $product['vaccine']['redo']; ?>" id="redo<?php echo $product['vaccine']['id']; ?>">
				  </div>
				<?php if ($event_state != STATUS_CLOSED): ?>
				  <button type="submit" class="btn btn-success mb-2 btn-sm">Change Expiry</button>
				<?php endif; ?>
				</form>
			</td>
		</tr>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>