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
		<a href="<?php echo base_url(); ?>products">Products</a> /
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
									<span style="color:#d76207;"><i class="fas fa-circle"></i></span> Local Stock<br/>
									<span class="pl-4"><small><?php echo floatval($local_stock) . '/' . $local_limit . ' '. $product['unit_sell']; ?></small></span>
								</div>
							<?php else: ?>
								<div class="rounded" style="border:1px solid #d76207; padding:5px; background-color:#d7070708;">
									<span style="color:#d76207;"><i class="fas fa-circle"></i></span> Local Stock<br/>
									<span class="pl-4"><small><?php echo floatval($local_stock) . '/' . $local_limit . ' '. $product['unit_sell']; ?></small></span>
								</div>
							<?php endif; ?>
						<?php else: ?>
								<div class="rounded" style="border:1px solid #efefef; padding:5px;">
									<span style="color:#639d6d;"><i class="fas fa-circle"></i></span> Local Stock<br/>
									<span class="pl-4"><small><?php echo $local_stock . ' ' . $product['unit_sell']; ?></small></span>
								</div>
						<?php endif; ?>
					</div>
					<div class="col-sm-4">
						<?php if($product['limit_stock'] > 0): ?>
							<?php if($product['limit_stock'] <= $global_stock): ?>
								<div class="rounded" style="border:1px solid #9ad99e; padding:5px; background-color:#f3fef3;"><!-- green -->
									<span style="color:#639d6d;"><i class="fas fa-circle"></i></span> Global Stock <br/>
									<span class="pl-4"><small><?php echo floatval($global_stock) . '/' . floatval($product['limit_stock']) . ' '. $product['unit_sell']; ?></small></span>
								</div>
							<?php else: ?>
								<div class="rounded" style="border:1px solid #d76207; padding:5px; background-color:#d7070708;"><!-- red -->
									<span style="color:#d76207;"><i class="fas fa-circle"></i></span> Global Stock<br/>
									<span class="pl-4"><small><?php echo floatval($global_stock) . '/' . floatval($product['limit_stock']) . ' '. $product['unit_sell']; ?></small></span>
								</div>
							<?php endif; ?>
						<?php else: ?>
								<div class="rounded" style="border:1px solid #efefef; padding:5px;"><!-- grey -->
									<span style="color:#639d6d;"><i class="fas fa-circle"></i></span> Global Stock<br/>
									<span class="pl-4"><small><?php echo floatval($global_stock) . ' ' . $product['unit_sell']; ?></small></span>
								</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<div style="width:250px;">
		<small>Consumer price : </small>
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
				echo "no price set";
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
								<h5>Usage</h5>
								<table class="table">
								<tr>
									<td>1 month</td>
									<td><?php echo ($history_1m) ? floatval($history_1m['sum_vol']) : 0; ?> <?php echo (isset($product['unit_sell'])) ? $product['unit_sell']: '' ?></td>
								</tr>
								<tr>
									<td>6 month</td>
									<td><?php echo ($history_6m) ? floatval($history_6m['sum_vol']) : 0; ?> <?php echo (isset($product['unit_sell'])) ? $product['unit_sell']: '' ?></td>
								</tr>
								<tr>
									<td>1 year</td>
									<td><?php echo ($history_1y) ? floatval($history_1y['sum_vol']) : 0; ?> <?php echo (isset($product['unit_sell'])) ? $product['unit_sell']: '' ?></td>
								</tr>
								</table>

								<h5>Comment</h5>
								<form action="<?php echo base_url(); ?>products/profile/<?php echo $product['id']; ?>" method="post" autocomplete="off">
								  <div class="form-group">
									<textarea class="form-control" name="message" id="message" rows="6"><?php echo (isset($product['comment'])) ? $product['comment']: '' ?></textarea>
								  </div>
								  <button type="submit" name="submit" value="update" class="btn btn-primary">Update</button>
								</form>

							</div>
							<div class="col-sm-6">
								<h5>Product Details</h5>
								<table class="table">
								<tr>
									<td>Producer - Supply</td>
									<td><?php echo (isset($product['producer'])) ? $product['producer']: '/' ?> - <?php echo (isset($product['supplier'])) ? $product['supplier']: '/' ?></td>
								</tr>
								<?php if (!empty($product['posologie'])) : ?>
								<tr>
									<td>posologie</td>
									<td><?php echo (isset($product['posologie'])) ? $product['posologie']: '' ?></td>
								</tr>
								<?php endif; ?>
								<?php if (!empty($product['toedieningsweg'])) : ?>
								<tr>
									<td>toedieningsweg</td>
									<td><?php echo (isset($product['toedieningsweg'])) ? $product['toedieningsweg']: '' ?></td>
								</tr>
								<?php endif; ?>
								<?php if (!empty($product['toedieningsweg'])) : ?>
								<tr>
									<td>Dead Volume</td>
									<td><?php echo (isset($product['dead_volume']) && $product['dead_volume'] != 0) ? $product['dead_volume']: '' ?></td>
								</tr>
								<?php endif; ?>
								<tr>
									<td>sell_volume</td>
									<td><?php echo (isset($product['sell_volume'])) ? $product['sell_volume']: '' ?> <?php echo (isset($product['unit_sell'])) ? $product['unit_sell']: '' ?></td>
								</tr>
								<tr>
									<td>Catalog price</td>
									<td><?php echo (isset($product['buy_price'])) ? $product['buy_price']: '' ?> &euro; / <?php echo (isset($product['buy_volume'])) ? $product['buy_volume']: '' ?> <?php echo (isset($product['unit_buy'])) ? $product['unit_buy']: '' ?></td>
								</tr>
								<?php if (!empty($product['input_barcode'])) : ?>
								<tr>
									<td>input_barcode</td>
									<td><?php echo (isset($product['input_barcode'])) ? $product['input_barcode']: '' ?></td>
								</tr>
								<?php endif; ?>
								<tr>
									<td>Booking</td>
									<td><?php echo (isset($product['booking_code']['category'])) ? $product['booking_code']['category'] . ' ' . $product['booking_code']['code'] . ' ' . $product['booking_code']['btw']: '' ?></td>
								</tr>
								<?php if ($product['delay'] != 0) : ?>
								<tr>
									<td>delay</td>
									<td><?php echo (isset($product['delay'])) ? $product['delay']: '' ?></td>
								</tr>
								<?php endif; ?>
								<?php if($product['vaccin']):  ?>
								<tr>
									<td>vaccin</td>
									<td><?php echo (isset($product['vaccin'])) ? $product['vaccin']: '' ?></td>
								</tr>
								<tr>
									<td>vaccin_freq</td>
									<td><?php echo (isset($product['vaccin_freq'])) ? $product['vaccin_freq']: '' ?></td>
								</tr>
								<?php endif; ?>
								</table>
							</div>
						</div>
						<hr />
						<small>Product entered : <?php echo user_format_date($product['created_at'], $user->user_date); ?>, last edit : <?php echo user_format_date($product['updated_at'], $user->user_date); ?></small>
					</div>
					<div class="tab-pane fade" id="stocktabs" role="tabpanel" aria-labelledby="stocktabs-tab">
						<?php if (isset($product['stock'])):

						 // check if local stock is empty
						 $local_stock_count = 0;
						 foreach($product['stock'] as $stock): if($user->current_location == $stock['location']): $local_stock_count++; endif; endforeach;
						?>
						<div class="row mt-3">
							<div class="col-sm-12">
								<h5>Local Stock</h5>
								<?php if($local_stock_count > 0): ?>
								<table class="table">
								<tr>
									<td>End of Life</td>
									<td>Lotnr</td>
									<td>Volume</td>
									<td>Barcode</td>
									<td>Entered</td>
								</tr>
								<?php foreach($product['stock'] as $stock): if($user->current_location == $stock['location']): ?>
								<tr>
									<td><?php echo date_format(date_create($stock['eol']), $user->user_date); ?></td>
									<td><?php echo $stock['lotnr']; ?></td>
									<td><?php echo $stock['volume']; ?></td>
									<td><?php echo $stock['barcode']; ?></td>
									<td><?php echo date_format(date_create($stock['created_at']), $user->user_date); ?></td>
								</tr>
								<?php endif; ?>
								<?php endforeach; ?>
								</table>
								<?php else : ?>
									No local stock.
								<?php endif; ?>
								<br/>
								<br/>
								<br/>
							</div>
							<div class="col-sm-12">
								<h5>Global Stock</h5>
								<table class="table">
								<table class="table">
								<tr>
									<td>End of Life</td>
									<td>Lotnr</td>
									<td>Volume</td>
									<td>Location</td>
									<td>Barcode</td>
									<td>Entered</td>
								</tr>
								<?php foreach($product['stock'] as $stock): if($user->current_location != $stock['location']): ?>
								<tr>
									<td><?php echo date_format(date_create($stock['eol']), $user->user_date); ?></td>
									<td><?php echo $stock['lotnr']; ?></td>
									<td><?php echo $stock['volume']; ?></td>
									<td><?php echo $loc[$stock['location']];; ?></td>
									<td><?php echo $stock['barcode']; ?></td>
									<td><?php echo date_format(date_create($stock['created_at']), $user->user_date); ?></td>
								</tr>
								<?php endif; ?>
								<?php endforeach; ?>
								</table>
							</div>
						</div>

						<?php else: ?>
							no stock<br/>
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
	$('#message').trumbowyg({

    btns: [
        ['strong', 'em', 'fontsize'],
        ['link'],
        ['orderedList'],
        ['removeformat'],
    ],

    plugins: {
        fontsize: {
            sizeList: [
                '14px',
                '18px',
                '22px'
            ],
            allowCustomSize: false
        }
    }
});
});
</script>
