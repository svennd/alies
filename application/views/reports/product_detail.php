<?php 
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
			<?php  if($product_info): $total_volume = 0; ?>
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
						<td <?php echo ($stock['stock_state'] == STOCK_ERROR) ? 'class="bg-warning text-dark"': ''; ?>><?php echo stock_state($stock['stock_state']); ?></td>
						<td><?php echo $stock['stock_volume']; ?> <?php $total_volume += $stock['stock_volume']; ?></td>
						<td><?php echo $stock['stock_lotnr']; ?></td>
						<td><?php echo $stock['stock_eol']; ?></td>
						<td><?php echo $stock['stock_in_price']; ?></td>
						<td><?php echo $lookup_loc[$stock['stock_location']]; ?></td>
						<td><?php echo $stock['stock_updated_at']; ?></td>
						<td><?php echo $stock['stock_created_at']; ?></td>
					</tr>
				<?php endforeach; ?>
					<tr>
						<td>&nbsp;</td>
						<td class="bg-secondary text-white">total :</td>
						<td class="bg-secondary text-white"><?php echo $total_volume; ?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
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
						<td>date</td>
						<td>volume</td>
						<td>stock barcode</td>
						<td>consult</td>
					</tr>
				<?php foreach ($eprod as $prod): ?>
					<tr>
						<td><?php echo user_format_date($prod['event']['created_at'], $user->user_date); ?></td>
						<td><?php echo $prod['volume']; ?></td>
						<td><?php echo $prod['barcode']; ?>
						<td><a href="<?php echo base_url('events/event/' . $prod['event']['id']); ?>">consult</a></td>
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
  