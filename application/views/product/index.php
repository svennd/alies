<div class="row">
      <div class="col-lg-12 mb-4">

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
						<div class="shadow rounded product-search-shell">
						  <div class="form-group has-search mb-0">
							<span class="fa fa-search form-control-feedback"></span>
							 <div class="input-group">
								<input type="text" class="form-control product-search-input" id="product_live_search" name="search_query" placeholder="Search products or procedures" value="<?php echo html_escape($search_q); ?>" aria-describedby="productSearchStatus">
								<div class="input-group-append">
								  <button class="btn btn-primary" type="submit">
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
				<div class="product-search-panel">
					<div id="product_live_results" class="product-search-results">
						<?php
							if (strlen($search_q) >= 2) {
								$this->load->view('product/partials/search_results', array(
									'results' => $search_results,
									'is_admin' => $is_admin,
								));
							}
						?>
					</div>
				</div>

			</div>
		</div>

  <!-- STOCK -->
  <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div>Stock</div>
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url('limits/' . (is_numeric((int)$curlocation) ? 'local/' . $curlocation: 'global')); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-exclamation-triangle"></i> <?php echo $this->lang->line('shortage'); ?></a>
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
				<?php if($curlocation == "all"): ?>
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
						<th><?php echo $this->lang->line('last_update'); ?></th>
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
						<td><?php echo user_format_date(
							is_null($product['updated_at']) ? $product['created_at'] : $product['updated_at'], 
							$user->user_date); ?></td>
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
</div>

<script type="text/javascript">
const URL_STOCK_LOCATION = '<?php echo base_url('products/index/'); ?>';
const PRODUCT_SEARCH_URL = '<?php echo base_url('products/search_catalog'); ?>';
const BUTTON_LOCATIONS = [
			<?php foreach ($locations as $loc): ?>
            { 
				text:'<?php if ($loc['id'] == $user_location): ?><i class="fa-solid fa-location-dot"></i> <?php endif; ?><?php echo $loc['name']; ?>', 
				className:'btn <?php echo ($loc['id'] == $curlocation) ? 'btn-outline-success' : 'btn-outline-primary'; ?> btn-sm',
				action: function ( e, dt, button, config ) {
					window.location = URL_STOCK_LOCATION + '<?php echo $loc['id']; ?>';
				}     
			},
			<?php endforeach; ?>
            { 
				text:'<?php echo $this->lang->line('search_all'); ?>', 
				className:'btn <?php echo ("all" == $curlocation) ? 'btn-outline-success' : 'btn-outline-primary'; ?> btn-sm',
				action: function ( e, dt, button, config ) {
					window.location = URL_STOCK_LOCATION + 'all';
				}     
			}
	];
document.addEventListener("DOMContentLoaded", function(){
	$("#product_list").addClass('active');
	const $searchInput = $("#product_live_search");
	const $searchResults = $("#product_live_results");
	const $searchStatus = $("#productSearchStatus");
	let pendingSearch = null;
	let searchTimer = null;

	function setSearchStatus(message) {
		$searchStatus.text(message);
	}

	function runLiveSearch(query) {
		if (pendingSearch) {
			pendingSearch.abort();
		}

		pendingSearch = $.ajax({
			url: PRODUCT_SEARCH_URL,
			method: "GET",
			dataType: "json",
			data: { q: query }
		}).done(function(response) {
			$searchResults.html(response.html);
			setSearchStatus(response.count + " result" + (response.count === 1 ? "" : "s") + " for \"" + query + "\"");
		}).fail(function(xhr, status) {
			if (status === "abort") {
				return;
			}

			$searchResults.html('<div class="product-search-empty">Search failed. Please try again.</div>');
			setSearchStatus("Search failed.");
		});
	}

	$searchInput.on("input", function() {
		const query = $(this).val().trim();

		window.clearTimeout(searchTimer);

		if (query.length < 2) {
			if (pendingSearch) {
				pendingSearch.abort();
			}

			$searchResults.empty();
			setSearchStatus(query.length === 0 ? "Type at least 2 characters to search." : "Keep typing to search.");
			return;
		}

		setSearchStatus("Searching...");
		searchTimer = window.setTimeout(function() {
			runLiveSearch(query);
		}, 220);
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
	order : [[6, 'asc']]
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

});
</script>
