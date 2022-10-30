<?php
# map this at global level
# todo
foreach ($locations as $l)
{
	$loc[$l['id']] = $l['name'];
}
?>
<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>products"><?php echo $this->lang->line('Products'); ?></a> /
		<?php echo (isset($product['name'])) ? $product['name']: '' ?>
	</div>

	<div class="card-body">

		<div class="row">
			<div class="col-sm-8">
				<h2><?php echo (isset($product['name'])) ? $product['name']: '' ?> <span class="badge badge-secondary"><?php echo (isset($product['type']['name'])) ? $product['type']['name']: '' ?></span></h2>
			</div>
			<div class="col-sm-4">
				<div class="row">
					<div class="col-sm-4">
						<?php if($local_limit > 0): ?>
							<?php if($local_limit <= $local_stock): ?>
								<div class="rounded" style="border:1px solid #9ad99e; padding:5px; background-color:#f3fef3;">
									<span style="color:#639d6d;"><i class="fas fa-circle"></i></span> <?php echo $this->lang->line('local_stock'); ?><br/>
									<span class="pl-4"><small><?php echo floatval($local_stock) . '/' . $local_limit . ' '. $product['unit_sell']; ?></small></span>
								</div>
							<?php else: ?>
								<div class="rounded" style="border:1px solid #d76207; padding:5px; background-color:#d7070708;">
									<span style="color:#d76207;"><i class="fas fa-circle"></i></span> <?php echo $this->lang->line('local_stock'); ?><br/>
									<span class="pl-4"><small><?php echo floatval($local_stock) . '/' . $local_limit . ' '. $product['unit_sell']; ?></small></span>
								</div>
							<?php endif; ?>
						<?php else: ?>
								<div class="rounded" style="border:1px solid #efefef; padding:5px;">
									<span style="color:#639d6d;"><i class="fas fa-circle"></i></span> <?php echo $this->lang->line('local_stock'); ?><br/>
									<span class="pl-4"><small><?php echo $local_stock . ' ' . $product['unit_sell']; ?></small></span>
								</div>
						<?php endif; ?>
					</div>
					<div class="col-sm-4">
						<?php if($product['limit_stock'] > 0): ?>
							<?php if($product['limit_stock'] <= $global_stock): ?>
								<div class="rounded" style="border:1px solid #9ad99e; padding:5px; background-color:#f3fef3;"><!-- green -->
									<span style="color:#639d6d;"><i class="fas fa-circle"></i></span> <?php echo $this->lang->line('global_stock'); ?><br/>
									<span class="pl-4"><small><?php echo floatval($global_stock) . '/' . floatval($product['limit_stock']) . ' '. $product['unit_sell']; ?></small></span>
								</div>
							<?php else: ?>
								<div class="rounded" style="border:1px solid #d76207; padding:5px; background-color:#d7070708;"><!-- red -->
									<span style="color:#d76207;"><i class="fas fa-circle"></i></span> <?php echo $this->lang->line('global_stock'); ?><br/>
									<span class="pl-4"><small><?php echo floatval($global_stock) . '/' . floatval($product['limit_stock']) . ' '. $product['unit_sell']; ?></small></span>
								</div>
							<?php endif; ?>
						<?php else: ?>
								<div class="rounded" style="border:1px solid #efefef; padding:5px;"><!-- grey -->
									<span style="color:#639d6d;"><i class="fas fa-circle"></i></span> <?php echo $this->lang->line('global_stock'); ?><br/>
									<span class="pl-4"><small><?php echo floatval($global_stock) . ' ' . $product['unit_sell']; ?></small></span>
								</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<div style="width:250px;">
		<small><?php echo $this->lang->line('consumer_price'); ?> : </small>
		<p class="lead ml-3" style="color:#fa591d;"><strong>
		<?php
			if ($product['prices'])
			{
				if (count($product['prices']) > 1)
				{
					echo '<a data-toggle="collapse" href="#collapse' . $product['id'] . '" role="button" style="color:#fa591d;" aria-expanded="false" aria-controls="collapse' . $product['id'] . '">' . $product['prices'][0]['price'] . '~' . $product['prices'][sizeof($product['prices']) - 1]['price']. '&euro;</a> / ' . $product['prices']['0']['volume'] . ' '. $product['unit_sell'];
					echo "<div class='collapse' id='collapse" . $product['id'] . "'><table class='table small'>";
					foreach ($product['prices'] as $price)
					{
						echo "<tr><td>". $price['volume'] ." ". $product['unit_sell']."</td><td>". $price['price'] ." &euro;</td><tr>";
					}
					echo "</table></div>";
				}
				else
				{
					echo $product['prices']['0']['price'] . " &euro; / " . $product['prices']['0']['volume'] . " ". $product['unit_sell'];
				}
			}
			else
			{
				echo $this->lang->line('no_price');
			}
		?>
		</p>
		</strong>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<div class="card shadow mb-4">
			<div class="card-header border-bottom">
			<ul class="nav nav-tabs card-header-tabs" id="mynavtab" role="tablist">
			  <li class="nav-item" role="presentation"><a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true">Info</a></li>
			  <li class="nav-item" role="presentation"><a class="nav-link" id="stocktabs-tab" data-toggle="tab" href="#stocktabs" role="tab" aria-controls="stocktabs" aria-selected="false">Stock</a></li>
			</ul>
			</div>
			<div class="card-body">
				<div class="tab-content" id="myTabContent">
					<div class="tab-pane fade active show" id="info" role="tabpanel" aria-labelledby="info-tab">
						<div class="row mt-3">
							<div class="col-sm-6">
								<h5><?php echo $this->lang->line('usage'); ?></h5>
								<table class="table">
								<tr>
									<td><?php echo $this->lang->line('one_month'); ?></td>
									<td><?php echo ($history_1m) ? floatval($history_1m['sum_vol']) : 0; ?> <?php echo (isset($product['unit_sell'])) ? $product['unit_sell']: '' ?></td>
								</tr>
								<tr>
									<td><?php echo $this->lang->line('six_months'); ?></td>
									<td><?php echo ($history_6m) ? floatval($history_6m['sum_vol']) : 0; ?> <?php echo (isset($product['unit_sell'])) ? $product['unit_sell']: '' ?></td>
								</tr>
								<tr>
									<td><?php echo $this->lang->line('one_year'); ?></td>
									<td><?php echo ($history_1y) ? floatval($history_1y['sum_vol']) : 0; ?> <?php echo (isset($product['unit_sell'])) ? $product['unit_sell']: '' ?></td>
								</tr>
								</table>

								<h5><?php echo $this->lang->line('comment'); ?></h5>
								<form action="<?php echo base_url(); ?>products/profile/<?php echo $product['id']; ?>" method="post" autocomplete="off">
								  <div class="form-group">
									<textarea class="form-control" name="message" id="message" rows="3"><?php echo (isset($product['comment'])) ? $product['comment']: '' ?></textarea>
								  </div>
								  <button type="submit" name="submit" value="update" class="btn btn-primary"><?php echo $this->lang->line('store'); ?></button>
								</form>

							</div>
							<div class="col-sm-6">
								<h5><?php echo $this->lang->line('product_details'); ?></h5>
								<table class="table">
								<tr>
									<td><?php echo $this->lang->line('producer_supplier'); ?></td>
									<td><?php echo (isset($product['producer'])) ? $product['producer']: '/' ?> - <?php echo (isset($product['supplier'])) ? $product['supplier']: '/' ?></td>
								</tr>
								<?php if (!empty($product['posologie'])) : ?>
								<tr>
									<td>Posologie</td>
									<td><?php echo (isset($product['posologie'])) ? $product['posologie']: '' ?></td>
								</tr>
								<?php endif; ?>
								<?php if (!empty($product['toedieningsweg'])) : ?>
								<tr>
									<td><?php echo $this->lang->line('administration'); ?></td>
									<td><?php echo (isset($product['toedieningsweg'])) ? $product['toedieningsweg']: '' ?></td>
								</tr>
								<?php endif; ?>
								<?php if (!empty($product['dead_volume'])) : ?>
								<tr>
									<td><?php echo $this->lang->line('dead_volume'); ?></td>
									<td><?php echo (isset($product['dead_volume']) && $product['dead_volume'] != 0) ? $product['dead_volume']: '' ?></td>
								</tr>
								<?php endif; ?>
								<tr>
									<td><?php echo $this->lang->line('sell_volume'); ?></td>
									<td><?php echo (isset($product['sell_volume'])) ? $product['sell_volume']: '' ?> <?php echo (isset($product['unit_sell'])) ? $product['unit_sell']: '' ?></td>
								</tr>
								<tr>
									<td><?php echo $this->lang->line('catalog_price'); ?></td>
									<td><?php echo (isset($product['buy_price'])) ? $product['buy_price']: '' ?> &euro; / <?php echo (isset($product['buy_volume'])) ? $product['buy_volume']: '' ?> <?php echo (isset($product['unit_buy'])) ? $product['unit_buy']: '' ?></td>
								</tr>
								<?php if (!empty($product['input_barcode'])) : ?>
								<tr>
									<td><?php echo $this->lang->line('input_barcode'); ?></td>
									<td><?php echo (isset($product['input_barcode'])) ? $product['input_barcode']: '' ?></td>
								</tr>
								<?php endif; ?>
								<tr>
									<td><?php echo $this->lang->line('booking'); ?></td>
									<td><?php echo (isset($product['booking_code']['category'])) ? $product['booking_code']['category'] . ' ' . $product['booking_code']['code'] . ' ' . $product['booking_code']['btw']: '' ?></td>
								</tr>
								<?php if ($product['delay'] != 0) : ?>
								<tr>
									<td><?php echo $this->lang->line('delay'); ?></td>
									<td><?php echo (isset($product['delay'])) ? $product['delay']: '' ?></td>
								</tr>
								<?php endif; ?>
								<?php if($product['vaccin']):  ?>
								<tr>
									<td><?php echo $this->lang->line('vaccin'); ?></td>
									<td><?php echo (isset($product['vaccin'])) ? $product['vaccin']: '' ?></td>
								</tr>
								<tr>
									<td><?php echo $this->lang->line('vaccin_freq'); ?></td>
									<td><?php echo (isset($product['vaccin_freq'])) ? $product['vaccin_freq']: '' ?></td>
								</tr>
								<?php endif; ?>
								</table>
							</div>
						</div>
						<hr />
						<small><?php echo $this->lang->line('product_entered'); ?> : <?php echo user_format_date($product['created_at'], $user->user_date); ?>,  <?php echo $this->lang->line('last_update'); ?> : <?php echo user_format_date($product['updated_at'], $user->user_date); ?></small>
					</div>
					<div class="tab-pane fade" id="stocktabs" role="tabpanel" aria-labelledby="stocktabs-tab">
						<?php if (isset($product['stock'])):

						 // check if local stock is empty
						 $local_stock_count = 0;
						 foreach($product['stock'] as $stock): if($user->current_location == $stock['location']): $local_stock_count++; endif; endforeach;
						?>
						<div class="row mt-3">
							<div class="col-sm-12">
								<h5><?php echo $this->lang->line('global_stock'); ?></h5>
								<table class="table">
								<tr>
									<td><?php echo $this->lang->line('eol'); ?></td>
									<td><?php echo $this->lang->line('lotnr'); ?></td>
									<td><?php echo $this->lang->line('volume'); ?></td>
									<td><?php echo $this->lang->line('location'); ?></td>
									<td><?php echo $this->lang->line('barcode'); ?></td>
									<td><?php echo $this->lang->line('added'); ?></td>
								</tr>
								<?php foreach($product['stock'] as $stock):  ?>
								<tr <?php echo (($user->current_location == $stock['location'])) ? 'class="table-success"' :''; ?>>
									<td><?php 
										echo 
										(is_null($stock['eol'])) ? 
										"???" : 
										(
												(strtotime($stock['eol']) < strtotime(date('Y-m-d'))) ? 
													'<span style="color:red;"> ' . date_format(date_create($stock['eol']), $user->user_date) . '</span>'
														: 
													date_format(date_create($stock['eol']), $user->user_date)
										)
										; ?></td>
									<td><?php echo $stock['lotnr']; ?></td>
									<td><?php echo $stock['volume']; ?></td>
									<td><?php echo $loc[$stock['location']]; ?></td>
									<td><?php echo $stock['barcode']; ?></td>
									<td><?php echo date_format(date_create($stock['created_at']), $user->user_date); ?></td>
								</tr>
								<?php endforeach; ?>
								</table>
							</div>
						</div>

						<?php else: ?>
							<?php echo $this->lang->line('no_global_stock'); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){

	$("#product_list").addClass('active');
	
});
</script>
