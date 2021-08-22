<div class="row">
      <div class="col-lg-12 mb-4">

		    <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>products">Products</a> / List
			</div>
            <div class="card-body">
			<br/>
			<?php if ($products): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Name</th>
					<th>Price</th>
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
						else
						{
							if (count($product['prices']) > 1)
							{
								echo '<a data-toggle="collapse" href="#collapse' . $product['id'] . '" role="button" aria-expanded="false" aria-controls="collapse' . $product['id'] . '">' . $product['prices'][0]['price'] . '~' . $product['prices'][sizeof($product['prices']) - 1]['price']. '&euro;</a> / ' . $product['prices']['0']['volume'] . ' '. $product['unit_sell'];
								echo "<div class='collapse' id='collapse" . $product['id'] . "'><table class='small'>";
								foreach ($product['prices'] as $price)
								{
									echo "<tr><td>". $price['volume'] ." ". $product['unit_sell']."</td><td>". $price['price'] ."&euro;</td><tr>";
								}
								echo "</table></div>";
							}
							else
							{
								echo $product['prices']['0']['price'] . "&euro; / " . $product['prices']['0']['volume'] . " ". $product['unit_sell'];
							}
						}
					?>
					</td>

					
					<td>
						<a href="<?php echo base_url(); ?>products/product_price/<?php echo $product['id']; ?>"><i class="fas fa-edit"></i></a>
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
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
	$("#prd").show();
	$("#products").addClass('active');
	$("#product_list").addClass('active');
});
</script>
  