<p><?php echo $this->lang->line('limit_explain'); ?></p>

<div class="list-group mb-3 shadow">
	<div class="list-group-item list-group-item-action">
		<div class="row align-items-center">
			<div class="col">
				<strong class="mb-0"><?php echo $this->lang->line('global_limit'); ?></strong>
				<p class="text-muted mb-0"><?php echo $this->lang->line('global_limit_explain'); ?></p>
			</div>
			<div class="col-auto">
				<div class="input-group">
					<input type="text" name="limit_stock" class="form-control" id="limit_stock" value="<?php echo (isset($product['limit_stock'])) ? $product['limit_stock']: '' ?>">
					<div class="input-group-append">
						<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php foreach ($stock_locations as $stock): 
	$local_limit_id = -1;
	$local_limit_value = 0;
	$local_color = $stock['color'];
	# super dirty but whateves
	if ($llimit)
	{
		foreach($llimit as $limit)
		{
			if ($stock['id'] == $limit['stock'])
			{
				$local_limit_id = $limit['id'];
				$local_limit_value = $limit['volume'];
				break;
			}
		}
	}
	?>
	<div class="list-group-item list-group-item-action">
		<div class="row align-items-center">
			<div class="col">
				<strong class="mb-0">
				<i class="fa-solid fa-fw fa-location-dot" style="color:<?php echo $local_color; ?>"></i><?php echo $stock['name']; ?></strong>
			</div>
			<div class="col-auto">
				<div class="input-group input-group-sm">
					<input type="text" name="limit[<?php echo $stock['id']; ?>][<?php echo $local_limit_id; ?>]" class="form-control" id="limits<?php echo $stock['id'] . $local_limit_id; ?>" value="<?php echo $local_limit_value; ?>">
					<div class="input-group-append">
						<span class="input-group-text" id="basic-addon2"><?php echo $product['unit_sell']; ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endforeach; ?>


</div>