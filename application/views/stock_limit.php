<?php
# map this at global level
# todo
foreach ($locations as $l)
{
	$loc[$l['id']] = $l['name'];
}
?>
<div class="row">
      <div class="col-lg-4 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / Add Stock Limit
			</div>
			<div class="card-body">
				<form action="<?php echo base_url(); ?>stock/stock_limit" method="post" autocomplete="off">
				<div class="form-group">
					<label for="product_id">product</label>
					<select name="product_id" class="form-control" id="product_name">
						<?php foreach($products as $product): ?>
							<option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
						<?php endforeach; ?>
					</select>
				  </div>
				  <div class="form-group">
					<label for="volume">Min. Volume</label>
					<input type="text" name="volume" class="form-control" id="volume">
				  </div>
				  <div class="form-group">
					<label for="location">Location</label>
					<select name="location" class="form-control" id="location">
						<?php foreach($locations as $location): ?>
							<option value="<?php echo $location['id']; ?>"><?php echo $location['name']; ?></option>
						<?php endforeach; ?>
					</select>
				  </div>
				  <button type="submit" name="submit" value="add" class="btn btn-primary">Add</button>
				</form>
			</div>
		</div>

	</div>
      
	<div class="col-lg-8 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / Stock Limit
			</div>
            <div class="card-body">
			<?php if ($stock_limit): ?>
			
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Product</th>
					<th>Location</th>
					<th>Limit</th>
					<th>Option</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($stock_limit as $limit): ?>
				<tr>
					<td><?php echo $limit['products']['name']; ?></td>
					<td><?php echo $loc[$limit['stock']]; ?></td>
					<td><?php echo $limit['volume']; ?></td>
					<td><a href="<?php echo base_url(); ?>stock/stock_limit_rm/<?php echo $limit['id']; ?>" class="btn btn-primary"><i class="fas fa-trash-alt"></i></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No limits on products
			<?php endif; ?>
                </div>
		</div>

	</div>
      
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#product_name").select2();
	$("#products").addClass('active');
	$("#stock").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
});
</script>
  