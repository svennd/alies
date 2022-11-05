<div class="row">
  <div class="col-lg-10 mb-4">
      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url(); ?>stock">Stock</a> / List</div>
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url('limits/local/' . $filter); ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-exclamation-triangle"></i> Local short</a>
					<a href="<?php echo base_url('limits/global'); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-exclamation-circle"></i> Global short</a>
					<a href="<?php echo base_url('stock/expired_stock'); ?>" class="btn btn-outline-danger btn-sm"> <i class="fas fa-prescription-bottle"></i> Expired</a>
				</div>
			</div>
            <div class="card-body">
			<p>Locations :
				<?php foreach ($locations as $loc): ?>
					<a href="<?php echo base_url(); ?>stock/<?php echo $loc['id'] ?>" class="btn <?php echo ($loc['id'] == $filter) ? 'btn-outline-success' : 'btn-outline-primary';?>"><?php echo $loc['name']; ?></a>
				<?php endforeach; ?>
					<a href="<?php echo base_url(); ?>stock/all" class="btn  <?php echo ($filter == 'all') ? 'btn-outline-success' : 'btn-outline-primary';?>">All</a>
			</p>

			<?php if($success == 2): ?>
				<div class="alert alert-success alert-dismissible fade show" style="width:450px;" role="alert">
					Products removed from stock !
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			<?php endif; ?>
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
						<th>option</th>
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
						<td>
							<a href="<?php echo base_url('stock/write_off/0/' . $product['barcode']. '/' . $filter); ?>" class="btn btn-danger btn-sm mr-3"><i class="fas fa-ban"></i></a>
							<button type="submit" name="submit" type="button" class="btn btn-success btn-sm move_product" 
										id="<?php echo $product['products']['id']; ?>" 
										data-id="<?php echo $product['products']['id']; ?>"
										data-name="<?php echo $product['products']['name']; ?>"
										data-lotnr="<?php echo $product['lotnr']; ?>"
										data-barcode="<?php echo $product['barcode']; ?>"
							>Move</button></td>
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
  <div class="col-lg-2 mb-4">
	<a href="<?php echo base_url(); ?>stock/add_stock" class="btn btn-success btn-lg"><i class="fas fa-shopping-cart"></i> Add Stock</a>

		<div class="card shadow mb-4" id="move_stock_tab" style="display:none;">
			<form action="<?php echo base_url(); ?>stock/move_stock" method="post" autocomplete="off">
				<div class="card-header text-success">
					<i class="fas fa-shipping-fast fa-bounce"></i> Move stock
				</div>
				<div class="card-body">
					<table class="table table-sm" id="product_table">
					</table>
					
					<div class="form-row">
						<div class="col mb-3">
							<label for="disabledTextInput">From Location</label>
							<input type="text" id="disabledTextInput" class="form-control" placeholder="<?php foreach($locations as $location): ?><?php echo ($location['id'] == $filter) ? $location['name'] : ''; ?><?php endforeach; ?>" readonly>
						</div>
						<div class="col mb-3">
							<label for="exampleFormControlInput1">to Location</label>
							<select name="to_location" class="form-control" id="location">
								<?php foreach($locations as $location): ?>
									<?php if ($location['id'] == $filter) { continue; } ?>
									<option value="<?php echo $location['id']; ?>"><?php echo $location['name']; ?></option>
								<?php endforeach; ?>
							</select>

						</div>
					</div>
					<input type="hidden" name="barcodes" id="barcodes" value="" />
					<input type="hidden" name="from_location" id="from_location" value="<?php echo $filter; ?>" />
					<button type="submit" name="submit" value="barcode" class="btn btn-primary">Move</button>
				</div>
			</form>
		</div>

	</div>

</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#products").addClass('active');
	$("#stock").addClass('active');

	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});

	const move_products = [];
	$("#dataTable").on('click','.move_product', function() {
		// show move screen
		$("#move_stock_tab").show();

		let product = {
				id:$(this).data("id"), 
				name:$(this).data("name"), 
				barcode:$(this).data("barcode"), 
				lotnr:$(this).data("lotnr"), 
			};
		move_products.push(product);

		console.log(move_products);

		let html_product = '<tr><th>Name</th><th>LotNR</th></tr>';
		let input = '';
		for (s of move_products) {
			html_product += '<tr><td>' + s.name + '(' + s.barcode  + ')</td><td>' + s.lotnr + '</td></tr>';
			input += s.barcode + ','
		}
		$("#product_table").html(html_product);
		$("#barcodes").val(input);

		$(this).hide();
	});
});
</script>