<div class="row">
      <div class="col-lg-9 mb-4">

		<div class="card shadow mb-4">
    <div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><?php echo $this->lang->line('search_product'); ?></div>
				<div class="dropdown no-arrow">
	  			<?php if ($this->ion_auth->in_group("admin")): ?>
					  <a href="<?php echo base_url(); ?>products/new" class="btn btn-outline-success btn-sm"><i class="fas fa-fw fa-plus"></i><?php echo $this->lang->line('new_product'); ?></a>
				  <?php endif; ?>
				</div>		
			</div>
            <div class="card-body">
				<form action="<?php echo base_url(); ?>products" method="post" autocomplete="off" class="form-inline">
					<label class="sr-only" for="name"><?php echo $this->lang->line('product_sheet'); ?></label>
					<input type="text" class="form-control mb-2 mr-sm-2" id="name" name="name" autocomplete="false" placeholder="<?php echo (isset($search_q)) ? $search_q : $this->lang->line('product_sheet'); ?>">
					<button type="submit" name="submit" value="search_product" class="btn btn-primary mb-2"><?php echo $this->lang->line('title_search'); ?></button>
				</form>
			<?php if ($search): ?>
			<ul>
				<?php foreach($search as $sear): ?>
					<li><a href="<?php echo base_url(); ?>products/profile/<?php echo $sear['id']; ?>"><?php echo $sear['name']; ?></a></li>
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
			<p>
				<?php foreach ($locations as $loc): ?>
					<a href="<?php echo base_url(); ?>products/index/<?php echo $loc['id'] ?>" class="btn <?php echo ($loc['id'] == $clocation) ? 'btn-outline-success' : 'btn-outline-primary'; ?> btn-sm"><?php echo $loc['name']; ?></a>
				<?php endforeach; ?>
					<a href="<?php echo base_url(); ?>products/index/all" class="btn <?php echo ($clocation == 'all') ? 'btn-outline-success' : 'btn-outline-primary'; ?> btn-sm"><?php echo $this->lang->line('search_all'); ?></a>
			</p>

			<?php if($success == 2): ?>
				<div class="alert alert-success alert-dismissible fade show" style="width:450px;" role="alert">
					<?php echo $this->lang->line('products_remove_stock'); ?>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			<?php endif; ?>
			<hr/>
			<br/>
			<?php if ($products): ?>
				<?php if($clocation == "all"): ?>
					<table class="table" id="dataTable">
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
					<table class="table" id="dataTable">
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
						<td data-sort="<?php echo ($product['eol']) ? strtotime($product['eol']) : time(); ?>"><?php echo user_format_date($product['eol'], $user->user_date); ?></td>
						<td><?php echo $product['lotnr']; ?></td>
						<td><?php echo $product['volume']; ?> <?php echo $product['unit_sell']; ?></td>
						<td><?php echo $product['type']; ?></td>
						<td><?php echo $product['barcode']; ?></td>
						<td>
							<button type="submit" name="submit" type="button" class="btn btn-success btn-sm move_product" 
										id="<?php echo $product['product_id']; ?>" 
										data-id="<?php echo $product['product_id']; ?>"
										data-name="<?php echo $product['product_name']; ?>"
										data-lotnr="<?php echo $product['lotnr']; ?>"
										data-barcode="<?php echo $product['barcode']; ?>"
							><?php echo $this->lang->line('move'); ?></button>
							<a href="<?php echo base_url('stock/write_off/' . $product['barcode']. '/' . $clocation); ?>" class="btn btn-danger btn-sm ml-3"><i class="fas fa-ban"></i></a>
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
      
      <div class="card shadow mb-4" id="move_stock_tab" style="display:none;">
			<form action="<?php echo base_url(); ?>stock/move_stock" method="post" autocomplete="off">
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
							<select name="to_location" class="form-control" id="location">
								<?php foreach($locations as $location): ?>
									<?php if ($location['id'] == $clocation) { continue; } ?>
									<option value="<?php echo $location['id']; ?>"><?php echo $location['name']; ?></option>
								<?php endforeach; ?>
							</select>

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
document.addEventListener("DOMContentLoaded", function(){
	$("#product_list").addClass('active');

  var requestUrl = "<?php echo base_url(); ?>products/a_pid_by_type/1";

  var table = $("#table-info").DataTable({
    ajax: requestUrl,
    "columnDefs": [
      { "targets": 0, "data": null, "render": function ( data, type, row ) {
        return "<a href='<?php echo base_url(); ?>products/profile/" + row[0] + "'>" + row[1] + "</a>";
        }
      },
      { "targets": 1, "data": null, "render": function ( data, type, row ) {
        var result = row[3] +" "+ row[2];
        return result;
        }
      },
      { "visible": false, "targets": [2,3]},
    ],

    "order": [[ 3, "desc" ]]
  });


  $('a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
      var element_id = this.id.split("-")[1]; // info-id-tab
      requestUrl = "<?php echo base_url(); ?>products/a_pid_by_type/" + element_id;
      table.ajax.url( requestUrl ).load();
  });

$("#dataTable").DataTable({
	responsive: true
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
