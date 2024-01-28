<div class="row">
      <div class="col-lg-9 mb-4">

		<div class="card shadow mb-4">
    <div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><?php echo $this->lang->line('search_product'); ?></div>
				<div class="dropdown no-arrow">
	  			<?php if ($this->ion_auth->in_group("admin")): ?>
					  <a href="<?php echo base_url('products/new'); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-fw fa-plus"></i><?php echo $this->lang->line('new_product'); ?></a>
				  <?php endif; ?>
				</div>		
			</div>
			<div class="card-body">
				<form action="<?php echo base_url('products'); ?>" method="get" autocomplete="off">
				<div class="row align-items-center justify-content-between px-1">
					<div class="col-lg-8">
						<div class="d-none d-sm-block">
							<p class="lead mb-4"><?php echo $this->lang->line('search_product_help'); ?></p>
						</div>
						<div class="shadow rounded">
						  <div class="form-group has-search">
							<span class="fa fa-search form-control-feedback"></span>
							 <div class="input-group">
								<input type="text" class="form-control" name="search_query" placeholder="search" value="">
								<div class="input-group-append">
								  <button class="btn btn-primary" type="submit" type="button">
									<div class="d-none d-sm-block"><?php echo $this->lang->line('title_search'); ?></div>
									<div class="d-block d-sm-none d-md-none">S</div>
								  </button>
									<a href="<?php echo base_url('products/product_list'); ?>" class="btn btn-success"><i class="fas fa-list"></i> <?php echo $this->lang->line('list_products'); ?></a>
								</div>
							</div>
						  </div>
						</div>
					</div>
					<div class="col-lg-3">
						<img class="img-fluid" src="<?php echo base_url('assets/img/product_search.png'); ?>">
					</div>
				</div>
				</form>
			<?php if ($search_product): ?>
			<ul>
				<?php foreach($search_product as $sear): ?>
					<li><a href="<?php echo base_url(); ?>products/profile/<?php echo $sear['id']; ?>"><?php echo $sear['name']; ?></a></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
			<?php if ($search_procedure): ?>
			<ul>
				<?php foreach($search_procedure as $sear): ?>
					<li><?php echo $sear['name']; ?> (procedure)</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>

			</div>
		</div>

  <!-- STOCK -->
  <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div>Stock</div>
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url('limits/' . (is_numeric($clocation) ? 'local/' . $clocation: 'global')); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-exclamation-triangle"></i> <?php echo $this->lang->line('shortage'); ?></a>
					<a href="<?php echo base_url('stock/expired_stock'); ?>" class="btn btn-outline-danger btn-sm"> <i class="fas fa-prescription-bottle"></i> <?php echo $this->lang->line('expired'); ?> (<?php echo $expired; ?>)</a>
				</div>
			</div>
            <div class="card-body">

			<?php if($success == 2): ?>
				<div class="alert alert-success alert-dismissible fade show" style="width:450px;" role="alert">
					<?php echo $this->lang->line('products_remove_stock'); ?>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			<?php endif; ?>
			<?php if ($products): ?>
				<?php if($clocation == "all"): ?>
					<table class="table table-sm" id="full_stock">
					<thead>
					<tr>
						<th><?php echo $this->lang->line('Products'); ?></th>
						<th><?php echo $this->lang->line('volume'); ?></th>
						<th><?php echo $this->lang->line('type'); ?></th>
						<th><?php echo $this->lang->line('location'); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($products as $product): ?>
					<tr>
						<td><a href="<?php echo base_url('products/profile/' . $product['product_id']) ?>"><?php echo $product['name']; ?></td>
						<td><?php echo $product['total_volume']; ?> <?php echo $product['unit_sell']; ?></td>
						<td><?php echo $product['type']; ?></td>
						<td><a href="<?php echo base_url(); ?>stock/stock_detail/<?php echo $product['product_id']; ?>"><?php echo $product['total_stock_locations']; ?></a></td>
					</tr>
					<?php endforeach; ?>
					</tbody>
					</table>
				<?php else : ?>
					<table class="table table-sm" id="dataTable">
					<thead>
					<tr>
						<th data-priority="1"><?php echo $this->lang->line('Products'); ?></th>
						<th><?php echo $this->lang->line('eol'); ?></th>
						<th><?php echo $this->lang->line('lotnr'); ?></th>
						<th><?php echo $this->lang->line('volume'); ?></th>
						<th><?php echo $this->lang->line('type'); ?></th>
						<th><?php echo $this->lang->line('barcode'); ?></th>
						<th data-priority="2"><?php echo $this->lang->line('options'); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($products as $product): ?>
					<tr>
						<td><a href="<?php echo base_url('products/profile/' . $product['product_id']); ?>"><?php echo $product['product_name']; ?></td>
						<td data-sort="<?php echo ($product['eol'] && $product['eol'] != "0000-00-00") ? strtotime($product['eol']) : time() + (60*60*24*7*52*5); ?>">
									<?php 
										echo 
												(strtotime($product['eol']) < strtotime(date('Y-m-d'))) ? 
													'<span style="color:tomato;"> ' . user_format_date($product['eol'], $user->user_date) . '</span>'
														: 
														user_format_date($product['eol'], $user->user_date)
										; ?>
						</td>
						<td><?php echo $product['lotnr']; ?></td>
						<td><?php echo $product['volume']; ?> <?php echo $product['unit_sell']; ?></td>
						<td><?php echo $product['type']; ?></td>
						<td><?php echo $product['barcode']; ?></td>
						<td>
							<?php if($clocation != $this->user->current_location): ?>
								<button type="submit" name="submit" type="button" class="btn btn-success btn-sm move_product" 
											id="<?php echo $product['product_id']; ?>" 
											data-id="<?php echo $product['product_id']; ?>"
											data-name="<?php echo $product['product_name']; ?>"
											data-lotnr="<?php echo $product['lotnr']; ?>"
											data-barcode="<?php echo $product['barcode']; ?>"
								><?php echo $this->lang->line('move'); ?></button>
							<?php else: ?>
								&nbsp;
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
					</tbody>
					</table>
			<?php endif; ?>
			<?php else: ?>
				<?php echo $this->lang->line('no_products_in_view'); ?>
			<?php endif; ?>
                </div>
		</div>

  <!-- END STOCK -->
  </div>

      <div class="col-lg-3 mb-4">

      <a href="<?php echo base_url(); ?>stock/add_stock" class="btn btn-success btn-lg"><i class="fas fa-shopping-cart"></i> <?php echo $this->lang->line('add_stock'); ?></a> <br/><br/>
      <div class="card shadow mb-4 position-fixed" id="move_stock_tab" style="display:none;">
			<form action="<?php echo base_url('stock/move_stock'); ?>" method="post" autocomplete="off">
				<div class="card-header text-success">
					<i class="fas fa-shipping-fast fa-bounce"></i> <?php echo $this->lang->line('move_stock'); ?>
				</div>
				<div class="card-body">
					<table class="table table-sm" id="product_table">
					</table>
					
					<div class="form-row">
						<div class="col mb-3">
							<label for="disabledTextInput"><?php echo $this->lang->line('from_location'); ?></label>
							<input type="text" id="disabledTextInput" class="form-control" placeholder="<?php foreach($locations as $location): ?><?php echo ($location['id'] == $clocation) ? $location['name'] : ''; ?><?php endforeach; ?>" readonly>
						</div>
						<div class="col mb-3">
							<label for="exampleFormControlInput1"><?php echo $this->lang->line('to_location'); ?></label>
							<input type="text" id="disabledTextInput" class="form-control" placeholder="<?php foreach($locations as $location): ?><?php echo ($location['id'] == $this->user->current_location) ? $location['name'] : ''; ?><?php endforeach; ?>" readonly>
							<input type="hidden" name="to_location" value="<?php echo $this->user->current_location; ?>" />
						</div>
					</div>
					<input type="hidden" name="barcodes" id="barcodes" value="" />
					<input type="hidden" name="from_location" id="from_location" value="<?php echo $clocation; ?>" />
					<button type="submit" name="submit" value="barcode" class="btn btn-primary"><?php echo $this->lang->line('move'); ?></button>
				</div>
			</form>
		</div>


	</div>

</div>

<script type="text/javascript">
const URL_REQ 	= '<?php echo base_url('products/a_pid_by_type/'); ?>';
const URL_STOCK_LOCATION = '<?php echo base_url('products/index/'); ?>';
const BUTTON_LOCATIONS = [
			<?php foreach ($locations as $loc): ?>
            { 
				text:'<?php if ($loc['id'] == $user_location): ?><i class="fa-solid fa-location-dot"></i> <?php endif; ?><?php echo $loc['name']; ?>', 
				className:'btn <?php echo ($loc['id'] == $clocation) ? 'btn-outline-success' : 'btn-outline-primary'; ?> btn-sm',
				action: function ( e, dt, button, config ) {
					window.location = URL_STOCK_LOCATION + '<?php echo $loc['id']; ?>';
				}     
			},
			<?php endforeach; ?>
            { 
				text:'<?php echo $this->lang->line('search_all'); ?>', 
				className:'btn <?php echo ("all" == $clocation) ? 'btn-outline-success' : 'btn-outline-primary'; ?> btn-sm',
				action: function ( e, dt, button, config ) {
					window.location = URL_STOCK_LOCATION + 'all';
				}     
			}
	];
document.addEventListener("DOMContentLoaded", function(){
	$("#product_list").addClass('active');

	$('a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
		var element_id = this.id.split("-")[1]; // info-id-tab
		var requestUrl = URL_REQ + element_id;
		table.ajax.url( requestUrl ).load();
	});

// main table
$("#dataTable").DataTable({
	responsive: true,
	scrollY:        '45vh',
    deferRender:    true,
    scroller:       true,
	dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
	buttons: BUTTON_LOCATIONS,
	"columnDefs": [
		{ "visible": false, "targets": [5]}
	],
	"order": [[1, 'asc']]
});

// if selected "ALL" -> less columns
$("#full_stock").DataTable({ 
	responsive: true,
	scrollY:        '45vh',
    deferRender:    true,
    scroller:       true,
	dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
	buttons: BUTTON_LOCATIONS
});

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
