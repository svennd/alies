	<p>
		    <div class="card shadow mb-4">

			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url(); ?>products">Products</a> / List</div>
				<?php if ($this->ion_auth->in_group("admin")): ?>
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url(); ?>products/new" class="btn btn-outline-success btn-sm"><i class="fas fa-fw fa-plus"></i> new product</a>
				</div>
				<?php endif; ?>
			</div>

            <div class="card-body">
			<p>Type :
				<?php foreach ($types as $type): ?>
					<a href="<?php echo base_url(); ?>products/product_list/<?php echo $type['id'] ?>" class="btn btn-outline-primary"><?php echo $type['name']; ?></a>
				<?php endforeach; ?>
				<a href="<?php echo base_url(); ?>products/product_list/other" class="btn btn-outline-primary">Other</a>
			</p>
			<hr/>
			<br/>
			<?php if ($products): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Name</th>
					<th>Supply chain</th>
					<th>Aankoop</th>
					<th>Price</th>
					<th>Booking</th>
					<th>Type</th>
					<th>Modify</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($products as $product): ?>
				<tr>
					<td>
						<?php echo $product['name']; ?>
						<?php if ($product['name'] != $product['short_name']) : ?>
						<br/>
						<small><?php echo $product['short_name']; ?></small>
						<?php endif; ?>
					</td>
					<td>
						<small>
						<?php if(!empty($product['supplier'])): ?>
						S : <?php echo $product['supplier']; ?><br/>
						<?php endif; ?>
						<?php if(!empty($product['producer'])): ?>
						P : <?php echo $product['producer']; ?>
						<?php endif; ?>
						</small>
					</td>
					<td><?php echo $product['buy_volume']; ?> <?php echo $product['unit_buy']; ?> / &euro; <?php echo $product['buy_price']; ?></td>
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
						<small>
						b : <?php echo $product['btw_buy']; ?> %<br>
						s : <?php echo $product['btw_sell']; ?> %
						<?php echo $product['booking_code']['category']; ?>
					</small></td>
					<td><?php echo (isset($product['type']['name'])) ? $product['type']['name'] : 'Other'; ?></td>
					<td>
						<a href="<?php echo base_url(); ?>products/product/<?php echo $product['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-edit"></i></a>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php endif; ?>
                </div>
		</div>
	</p>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});

		$("#prd").show();
		$("#products").addClass('active');
		$("#product_list").addClass('active');
});
</script>
