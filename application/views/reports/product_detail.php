<?php 

function stock_state($stock_state) {
	switch ($stock_state) {
		case 0:
			return "check";
		case 1:
			return "in_use";
		case 2:
			return "history";
		case 3:
			return "error";
	}
}
foreach ($locations as $loc)
{
	$lookup_loc[$loc['id']] = $loc['name'];
}
?>
<div class="row">
      <div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header"><a href="<?php echo base_url(); ?>reports">Reports</a> / <a href="<?php echo base_url(); ?>reports/products">Products</a> / <?php echo (isset($product_info[0]) && $product_info[0]['name']) ? $product_info[0]['name'] : ''; ?> / Stock</div>
            <div class="card-body">
			<?php  if($product_info): ?>
				<table class="table">
					<tr>
						<td>barcode</td>
						<td>state</td>
						<td>volume</td>
						<td>lotnr</td>
						<td>eol</td>
						<td>in_price</td>
						<td>location</td>
						<td>updated_at</td>
						<td>created_at</td>
					</tr>
				<?php foreach ($product_info as $key => $stock): ?>
					<tr>
						<td><?php echo $stock['stock_barcode']; ?></td>
						<td><?php echo stock_state($stock['stock_state']); ?></td>
						<td><?php echo $stock['stock_volume']; ?></td>
						<td><?php echo $stock['stock_lotnr']; ?></td>
						<td><?php echo $stock['stock_eol']; ?></td>
						<td><?php echo $stock['stock_in_price']; ?></td>
						<td><?php echo $lookup_loc[$stock['stock_location']]; ?></td>
						<td><?php echo $stock['stock_updated_at']; ?></td>
						<td><?php echo $stock['stock_created_at']; ?></td>
					</tr>
				<?php endforeach; ?>
				</table>
			<?php endif; ?>
			</div>
		</div>
		
		<div class="card shadow mb-4">
			<div class="card-header"><a href="<?php echo base_url(); ?>reports">Reports</a> / <?php echo (isset($product_info[0]) && $product_info[0]['name']) ? $product_info[0]['name'] : ''; ?> / Use</div>
			<div class="card-body">
			<?php if($eprod): ?>
				<table class="table">
					<tr>
						<td>volume</td>
						<td>stock barcode</td>
						<td>event_id</td>
						<td>vet</td>
					</tr>
				<?php foreach ($eprod as $prod): ?>
					<tr>
						<td><?php echo $prod['volume']; ?></td>
						<td><?php echo $prod['barcode']; ?></td>
						<td><?php echo $prod['event']['id']; ?></td>
						<td><?php echo $prod['event']['vet']; ?></td>
					</tr>
				<?php endforeach; ?>
				</table>
			<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
});
</script>
  