<div class="row">
      <div class="col-lg-12 mb-4">

		    <div class="card shadow mb-4">
			<div class="card-header">Producten</div>
            <div class="card-body">
			<br/>
			<?php if ($products): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('name'); ?></th>
					<th><?php echo $this->lang->line('catalog_price'); ?></th>
					<th><?php echo $this->lang->line('price_alies'); ?></th>
					<th><?php echo $this->lang->line('price_sale'); ?></th>
					<th><?php echo $this->lang->line('margin'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($products as $product): ?>
				<tr>
					<td><a href="<?php echo base_url('pricing/prod/' . $product['id']); ?>"><?php echo $product['name']; ?></a></td>
					<!-- <td><?php echo $product['prices']['0']['volume'] . " ". $product['unit_sell']; ?></td> -->
					<td><?php echo (isset($product['wholesale']) && isset($product['wholesale']['bruto'])) ? $product['wholesale']['bruto']. " &euro;" : '---' ; ?></td>
					<td><?php echo $product['buy_price']. " &euro;"; ?></td>
					<td>
					<?php
						if (!isset($product['prices']))
						{
							echo "<span style='color:red;'><b>no price</b></span>";
						}
						elseif(!$product['sellable'])
						{
							echo "---";
						}
						else
						{
							if (count($product['prices']) > 1)
							{
								echo '<a data-toggle="collapse" href="#collapse' . $product['id'] . '" role="button" aria-expanded="false" aria-controls="collapse' . $product['id'] . '">' . $product['prices'][0]['price'] . '~' . $product['prices'][sizeof($product['prices']) - 1]['price']. '&euro;</a>';
								echo "<div class='collapse' id='collapse" . $product['id'] . "'><table class='small'>";
								foreach ($product['prices'] as $price)
								{
									$error = 0;
									if ($product['buy_price'] == 0) { $product['buy_price'] = 1; $error = 1;}
									$unit_price = ($product['buy_price']/$product['buy_volume']);
									$change = round((($unit_price-$price['price'])/$unit_price)*100*-1);
									echo "<tr>
												<td>". $price['volume'] ." ". $product['unit_sell']."</td>
												<td>". $price['price'] ."&euro;</td>
												<td>". (($change > 0) ? '<span style="color:green;">+' . $change : '<span style="color:red;">' . $change) ."% ". (($error) ? "error in data":"") . "</td>

										<tr>";
								}
								echo "</table></div>";
							}
							else
							{
								echo $product['prices']['0']['price'] . " &euro;";
							}
						}
					?>
					</td>


					<td>
						<?php
							if (!isset($product['prices']))
							{
								echo "<span style='color:red;'><b>no price</b></span>";
							}
							elseif(!$product['sellable'])
							{
								echo "---";
							}
							else
							{
								if (count($product['prices']) > 1)
								{
									$unit_price = ($product['buy_price']/$product['buy_volume']);
									$first_change = round((($unit_price-$product['prices'][0]['price'])/$unit_price)*100*-1);
									$last_change = round((($unit_price-$product['prices'][sizeof($product['prices']) - 1]['price'])/$unit_price)*100*-1);
									echo (($first_change > 0) ? '<span style="color:green;">+' . $first_change : '<span style="color:red;">' . $first_change) . ' ~ ' . (($last_change > 0) ? '<span style="color:green;">+' . $last_change : '<span style="color:red;">' . $last_change) . '%';
								}
								else
								{
									if ($product['prices']['0']['price'] == 0 || $product['buy_price'] == 0)
									{
										echo "---";
									}
									else
									{
										$unit_price = ($product['buy_price']/$product['buy_volume']);
										$change = round((($unit_price-$product['prices'][0]['price'])/$unit_price)*100*-1);
										echo  (($change > 0) ? '<span style="color:green;">+' . $change : '<span style="color:red;">' . $change) . "%";
									}
								}
							}
						?>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php endif; ?>
                </div>
		</div>

	</div>

</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable();

	$("#pricingmg").show();
	$("#pricing").addClass('active');
	$("#prod_list").addClass('active');
});
</script>
