<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>stock/all">Stock</a> / <?php echo $stock_detail[0]['products']['name']; ?> / detail
	</div>
	<div class="card-body">
	<?php if ($stock_detail): ?>

		<table class="table" id="dataTable">
		<thead>
		<tr>
			<th>Product</th>
			<th>Volume</th>
			<th>Lotnr</th>
			<th>EOL</th>
			<th>In Price</th>
			<th>Barcode</th>
			<th>Location</th>
			<!-- <th>State</th> -->
		</tr>
		</thead>
		<tbody>
		<?php foreach ($stock_detail as $detail): ?>
		<?php $change = round((($detail['in_price']-$detail['products']['buy_price'])/$detail['products']['buy_price'])*100); ?>
		<tr>
			<td><?php echo $detail['products']['name']; ?></td>
			<td><?php echo $detail['volume']; ?> <?php echo $detail['products']['unit_sell']; ?></td>
			<td><?php echo $detail['lotnr']; ?></td>
			<td><?php echo user_format_date($detail['eol'], $user->user_date); ?></td>
			<td><?php echo $detail['in_price']; ?> &euro; (<?php echo ($change > 0) ? '<span style="color:red;">+' . $change : '<span style="color:green;">' . $change; ?>%</span>)</td>
			<td><?php echo $detail['barcode']; ?></td>
			<td><?php echo $detail['stock_locations']['name']; ?></td>
			<!-- <td><?php echo $detail['state']; ?></td> -->
		</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
		<a href="<?php echo base_url('stock/stock_detail/' . $stock_detail[0]['products']['id'] . '/show_all'); ?>" class="btn btn-sm btn-info">show history</a>
	<?php else: ?>
		no entries
	<?php endif; ?>
		</div>
</div>

<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>stock/all">Stock</a> / <?php echo $stock_detail[0]['products']['name']; ?> / usage last 6m
	</div>
	<div class="card-body">
	<?php if ($stock_usage): ?>

		<table class="table" id="dataTable">
		<thead>
		<tr>
			<th>Volume</th>
			<th>Month</th>
			<th>Year</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($stock_usage as $usage): ?>
		<tr>
			<td><?php echo $usage['volume']; ?> <?php echo $detail['products']['unit_sell']; ?></td>
			<td><?php echo $usage['month']; ?></td>
			<td><?php echo $usage['year']; ?></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
	<?php else: ?>
		no usage known.
	<?php endif; ?>
		</div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#products").addClass('active');
	$("#stock").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
});
</script>
