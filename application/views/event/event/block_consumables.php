<?php if ($consumables) : ?>
	<?php foreach ($consumables as $product): ?>
		<tr style="background-color: <?php echo ($product['price_brut'] == 0) ? "#fff0ed" : "#edfff8" ?>;">
			<td>
				<span class="d-md-none"><?php echo floatval($product['volume']); ?>x </span>
				<?php echo $product['product']['name']; ?>
			</td>
			<td class="d-none d-sm-table-cell">
			<form action="<?php echo base_url('events/prod_edit/' . $event_id); ?>" id="form<?php echo $product['id']; ?>" method="post" autocomplete="off" class="form-inline">
			
				<div class="input-group input-group-sm" style="width:125px;">
					<input type="text" name="volume" value="<?php if($product['volume'] != 0) { echo $product['volume']; }; ?>" class="form-control <?php if($product['volume'] == 0) { echo "is-invalid"; } ?>" id="volume<?php echo $product['id']; ?>" <?php echo ($event_state == STATUS_CLOSED) ? 'disabled' : ''; ?>>
					  <div class="input-group-append">
						<span class="input-group-text"><?php echo $product['product']['unit_sell']; ?></span>
					  </div>
				</div>
				
				<input type="hidden" name="event_product_id" value="<?php echo $product['id']; ?>"/>
				<input type="hidden" name="pid" value="<?php echo $product['product_id']; ?>"/>
				<input type="hidden" name="btw" value="<?php echo $product['btw']; ?>"/>
				<?php if ($event_state != STATUS_CLOSED): ?>
				&nbsp;<button type="submit" name="submit" value="edit_prod" form="form<?php echo $product['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-sync-alt"></i></button>
				<?php endif; ?>
			</form>
			
			</td>
			<td class="d-none d-sm-table-cell">
			<?php if (!empty($product['stock'])) : ?>
				<small>eol: <?php echo user_format_date($product['stock']['eol'], $user->user_date); ?> lot: <?php echo $product['stock']['lotnr']; ?></small>
			<?php else: ?>
				<small>-</small>
			<?php endif; ?>
			</td>
			<td class="d-none d-sm-table-cell"><?php echo (!empty($product['btw'])) ? $product['btw'] : $product['product']['btw_sell']; ?>%</td>
			
			<td><?php echo floatval(round($product['price_brut'], 2)); ?> &euro; 
				<span class="d-md-none float-right">
				<?php if ($event_state != STATUS_CLOSED): ?>
					<a href="<?php echo base_url('events/prod_remove/' . $event_id . '/' . $product['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
				<?php endif; ?>
				</span>
				<?php $total += $product['price_brut']; ?>
				<?php $total_excl += $product['price_net']; ?>
			</td>
			<td class="d-none d-sm-table-cell">
				<small><?php echo round($product['price_net'],2); ?> &euro;</small>
			</td>		
			<td class="d-none d-sm-table-cell">
				<?php if ($event_state != STATUS_CLOSED): ?>
					<a href="<?php echo base_url('events/prod_remove/' . $event_id . '/' . $product['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
				<?php endif; ?>
			</td>
		</tr>
		<?php if($product['product']['vaccin']): ?>
		<tr>
			<td colspan="7">
				<form action="<?php echo base_url('events/edit_vaccin/' . $event_id . '/' . $product['vaccine']['id']); ?>" id="form_vaccin_<?php echo $product['id']; ?>" method="post" autocomplete="off" class="form-inline">
				  <div class="form-group">
					<span class="fa-stack <?php if($product['vaccine']['no_rappel']): ?>text-danger<?php else: ?>text-success<?php endif; ?>" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-level-up-alt fa-rotate-90 fa-stack-1x"></i>
					</span>
				  </div>
				  <div class="form-group">
					<label for="redo<?php echo $product['vaccine']['id']; ?>" class="sr-only">Redo</label>
					<input type="date" name="redo" class="form-control form-control-sm" value="<?php echo $product['vaccine']['redo']; ?>" id="redo<?php echo $product['vaccine']['id']; ?>">
				  </div>
				  <div class="btn-group ml-4" role="group" aria-label="Basic example">
					<button type="submit" class="btn btn-success btn-sm"><i class="fas fa-wrench"></i> <?php echo $this->lang->line('reminder'); ?></button>
					<button type="submit" name="disable" value="1" class="btn btn-danger btn-sm"><i class="far fa-bell-slash"></i> <?php echo $this->lang->line('disable'); ?></button>
				</div>
				<div class="ml-5" id="msg_redo<?php echo $product['vaccine']['id']; ?>">&nbsp;</div>
				</form>
			</td>
		</tr>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>