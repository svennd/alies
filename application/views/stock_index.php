<div class="row">
  <div class="col-lg-8 mb-4">
      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / List
			</div>
            <div class="card-body">
			<p>Locations :
				<?php foreach ($locations as $loc): ?>
					<a href="<?php echo base_url(); ?>stock/<?php echo $loc['id'] ?>" class="btn <?php echo ($loc['id'] == $filter) ? 'btn-outline-success' : 'btn-outline-primary';?>"><?php echo $loc['name']; ?></a>
				<?php endforeach; ?>
					<a href="<?php echo base_url(); ?>stock/all" class="btn  <?php echo ($filter == 'all') ? 'btn-outline-success' : 'btn-outline-primary';?>">All</a>
			</p>
			<hr/>
			<br/>
			<?php if ($products): ?>
				<?php if($filter == "all"): ?>
					<table class="table" id="dataTable">
					<thead>
					<tr>
						<th>Product</th>
						<th>volume</th>
						<th>stock locations</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($products as $product): ?>
					<tr>
						<td><?php echo $product['name']; ?></td>
						<td><?php echo $product['total_volume']; ?> <?php echo $product['unit_sell']; ?></td>
						<td><a href="<?php echo base_url(); ?>stock/stock_detail/<?php echo $product['product_id']; ?>"><?php echo $product['total_stock_locations']; ?></a></td>
					</tr>
					<?php endforeach; ?>
					</tbody>
					</table>
				<?php else : ?>
					<table class="table" id="dataTable">
					<thead>
					<tr>
						<th>Product</th>
						<th>eol</th>
						<th>lotnr</th>
						<th>volume</th>
						<th>barcode</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($products as $product): ?>
					<tr>
						<td><a href="<?php echo base_url('stock/stock_detail/' . $product['products']['id']); ?>"><?php echo $product['products']['name']; ?></td>
						<td><?php echo user_format_date($product['eol'], $user->user_date); ?></td>
						<td><?php echo $product['lotnr']; ?></td>
						<td><?php echo $product['volume']; ?> <?php echo $product['products']['unit_sell']; ?></td>
						<td><?php echo $product['barcode']; ?></td>
					</tr>
					<?php endforeach; ?>
					</tbody>
					</table>
			<?php endif; ?>
			<?php else: ?>
				No products in this view.
			<?php endif; ?>
                </div>
		</div>

	</div>
  <div class="col-lg-4 mb-4">
	<a href="<?php echo base_url(); ?>stock/add_stock" class="btn btn-success btn-lg mb-3"><i class="fas fa-shopping-cart"></i> Add Stock</a>
	<a href="<?php echo base_url(); ?>stock/limit" class="btn btn-warning btn-lg mb-3"><i class="fas fa-exclamation-triangle"></i> Shortages</a>
  <?php if ($this->ion_auth->in_group("admin")): ?>
	<a href="<?php echo base_url(); ?>stock/stock_limit" class="btn btn-info btn-lg mb-3"><i class="fas fa-tasks"></i> Local Limits</a>
  <?php endif; ?>
	<a href="<?php echo base_url(); ?>stock/expired_stock" class="btn btn-danger btn-lg mb-3"> <i class="fas fa-prescription-bottle"></i> Expired Stock</a>

	<div class="card shadow mb-4">
		<div class="card-header">
			<a href="<?php echo base_url(); ?>stock">Stock</a> / Move stock
		</div>
		<div class="card-body">
			<?php if($success == 1): ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					Product(s) moved !
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					  </button>
				</div>
			<?php endif; ?>
			<form action="<?php echo base_url(); ?>stock/move_stock" method="post" autocomplete="off">
			<div class="form-group">
				<label for="barcodes">Add product(s) by barcode :</label>
				<textarea class="form-control" name="barcodes" aria-describedby="barcodesHelp"  id="barcodes" rows="3"></textarea>
				<small id="barcodesHelp" class="form-text text-muted">One line per product</small>
			</div>

		<div class="form-row">
			<div class="col mb-3">
				<label for="disabledTextInput">From Location</label>
				<input type="text" id="disabledTextInput" class="form-control" placeholder="<?php echo $current_location; ?>" readonly>
			</div>
			<div class="col mb-3">
				<label for="exampleFormControlInput1">to Location</label>
				<select name="location" class="form-control" id="location">
					<?php foreach($locations as $location): ?>
						<?php if ($location['name'] == $current_location) { continue; } ?>
						<option value="<?php echo $location['id']; ?>"><?php echo $location['name']; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>


			  <button type="submit" name="submit" value="barcode" class="btn btn-primary">Move</button>
			</form>
		</div>
	</div>

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / Write off
			</div>
            <div class="card-body">
			<?php if($success == 2): ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					Products removed from stock !
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					  </button>
				</div>
			<?php endif; ?>
				<?php if(isset($warnings) && count($warnings) > 0): ?>
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
					<strong>Holy guacamole!</strong> We have some issue :
					<ul>
					  <?php foreach($warnings as $w): ?>
						<li><?php echo $w; ?></li>
					  <?php	endforeach; ?>
					</ul>
					  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					  </button>
				</div>
				<?php endif; ?>
				<form action="<?php echo base_url(); ?>stock/write_off" method="post" autocomplete="off">
				<div class="form-group">
					<label for="barcodes">Write off product by barcode :</label>
					<input type="text" id="product_barcode" name="barcode" class="form-control">
				</div>
				  <div class="form-group">
					<label for="exampleFormControlInput1">from Stock Location</label>
					<select name="location" class="form-control" id="location">
						<?php foreach($locations as $location): ?>
							<option value="<?php echo $location['id']; ?>"><?php echo $location['name']; ?></option>
						<?php endforeach; ?>
					</select>
				  </div>
				  <button type="submit" name="submit" value="writeoff" class="btn btn-danger">Remove</button>
				</form>
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
});
</script>
