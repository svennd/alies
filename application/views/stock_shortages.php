<?php
# map this at global level
# todo
foreach ($locations as $l)
{
	$loc[$l['id']] = $l['name'];
}
?>
<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / Shortages (Global)
			</div>
            <div class="card-body">
			<br/>
			<?php if ($global_stock): ?>
			
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Product</th>
					<th>Limit</th>
					<th>Stock (global)</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($global_stock as $product): ?>
				<tr>
					<td><a href="<?php echo base_url(); ?>products/product/<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a></td>
					<td><?php echo $product['limit_stock']; ?> <?php echo $product['unit_sell']; ?></td>
					<td><?php echo $product['in_stock']; ?> <?php echo $product['unit_sell']; ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No shortages
			<?php endif; ?>
                </div>
		</div>

	</div>
	
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / Shortages (Local)
			</div>
            <div class="card-body">
			<br/>
			<?php if ($local_stock): ?>
			
				<table class="table" id="dataTable2">
				<thead>
				<tr>
					<th>Product</th>
					<th>Location</th>
					<th>Limit</th>
					<th>Stock (local)</th>
					<th>Stock (global)</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($local_stock as $product): ?>
				<tr>
					<td><?php echo $product['name']; ?></td>
					<td><?php echo $loc[$product['location']]; ?></td>
					<td><?php echo $product['required_volume']; ?> <?php echo $product['unit_sell']; ?></td>
					<td><?php echo (is_null($product['available_volume'])) ? "0" : $product['available_volume']; ?> <?php echo $product['unit_sell']; ?></td>
					<td><a href="<?php echo base_url() . 'stock/stock_detail/' . $product['product_detail'];?>"><?php echo $product['all_volume']; ?> <?php echo $product['unit_sell']; ?></a></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No shortages
			<?php endif; ?>
                </div>
		</div>

	</div>
      
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#products").addClass('active');
	$("#stock").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
	$("#dataTable2").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
});
</script>
  