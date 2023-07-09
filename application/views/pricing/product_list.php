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
					<th>Name</th>
					<th>Sell Price</th>
					<th>Modify</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($products as $product): ?>
				<tr>
					<td>
						<?php echo $product['name']; ?>
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
											<tr>";
								}
								echo "</table></div>";
							}
							else
							{
								echo $product['prices']['0']['price'] . "&euro;";
							}
						}
					?>
					</td>
					<td>
						<a href="<?php echo base_url(); ?>pricing/prod/<?php echo $product['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-edit"></i></a>
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
