<style>
.list-group-hack {
  padding: 0.5rem 1.25rem;
  margin-bottom: 0px;
}
</style>
<div class="row">
      <div class="col-lg-8 mb-4">

		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>products">Products</a> / Search Product
			</div>
            <div class="card-body">
				<form action="<?php echo base_url(); ?>products" method="post" autocomplete="off" class="form-inline">
					<label class="sr-only" for="name">product name</label>
					<input type="text" class="form-control mb-2 mr-sm-2" id="name" name="name" autocomplete="false" placeholder="<?php echo (isset($search_q)) ? $search_q : 'Product Name'; ?>">
					<button type="submit" name="submit" value="search_product" class="btn btn-primary mb-2">Search</button>
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


    <?php if ($product_types) : ?>

    <?php if (count($product_types) > 7) : ?>

        <div class="card shadow mb-4">
          <div class="card-header">Products</div>
                <div class="card-body">
            By category :
            <ul>
            <?php foreach($product_types as $type): ?>
              <li><a href="<?php echo base_url(); ?>products/product_list/<?php echo $type['id']; ?>"><?php echo $type['name']; ?></a> <?php echo (isset($type['products'])) ? '( ' . $type['products'][0]['counted_rows'] . ' )' : '';?></li>
            <?php endforeach; ?>
            </ul>
          </div>
        </div>

    <?php else: // not to many categories ?>

          <div class="card shadow mb-4">
            <div class="card-header border-bottom">
            <ul class="nav nav-tabs card-header-tabs" id="mynavtab-types" role="tablist">
              <?php $i = 0; foreach($product_types as $type): ?>

                <li class="nav-item" role="presentation">
                  <a class="nav-link <?php echo ($i == 0) ? 'active': ''; ?>" id="info-<?php echo $type['id']; ?>-tab" data-toggle="tab" href="#info-<?php echo $type['id']; ?>" role="tab" aria-controls="info-<?php echo $type['id']; ?>" aria-selected="true"><?php echo $type['name'] . ' (' . $type['products'][0]['counted_rows'] . ')'; ?></a>
                </li>
              <?php $i++; endforeach; ?>
            </ul>
            </div>
            <div class="card-body">
                <table id="table-info" class="table" >
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Stock</th>
                    <th>Stock</th>
                    <th>Stock</th>
                  </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  </table>
            </div>
          </div>

        <?php endif; ?>
  <?php else: ?>
      No categories defined.
  <?php endif; ?>

  </div>

      <div class="col-lg-4 mb-4">

        <div class="card shadow mb-4">
          <div class="card-header border-bottom">
          <ul class="nav nav-tabs card-header-tabs" id="mynavtab" role="tablist">
            <li class="nav-item" role="presentation"><a class="nav-link active" id="moveprod-tab" data-toggle="tab" href="#moveprod" role="tab" aria-controls="moveprod" aria-selected="true">Move products</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link" id="writeoff-tab" data-toggle="tab" href="#writeoff" role="tab" aria-controls="writeoff" aria-selected="false">Write off</a></li>
          </ul>
          </div>
          <div class="card-body">
            <div class="tab-content" id="myTabContent">

              <!-- move product -->
              <div class="tab-pane fade active show" id="moveprod" role="tabpanel" aria-labelledby="moveprod-tab">
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

              <div class="tab-pane fade" id="writeoff" role="tabpanel" aria-labelledby="writeoff-tab">
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

                    <input type="text" id="disabledTextInput" class="form-control" placeholder="<?php echo $current_location; ?>" readonly>
                    <input type="hidden" name="location" value="<?php echo $current_loc_id; ?>" />
          				  </div>
          				  <button type="submit" name="submit" value="writeoff" class="btn btn-danger">Remove</button>
          				</form>
              </div>

            </div>
          </div>
        </div>


          <div class="card shadow mb-4">
            <div class="card-header border-bottom">
            <ul class="nav nav-tabs card-header-tabs" id="mynavtab" role="tablist">
              <li class="nav-item" role="presentation"><a class="nav-link active" id="expire-tab" data-toggle="tab" href="#expire" role="tab" aria-controls="expire" aria-selected="true">Expiring</a></li>
              <li class="nav-item" role="presentation"><a class="nav-link" id="stocktabs-tab" data-toggle="tab" href="#stocktabs" role="tab" aria-controls="stocktabs" aria-selected="false">New Products</a></li>
              <li class="nav-item" role="presentation"><a class="nav-link" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true">Modified Products</a></li>
            </ul>
            </div>
            <div class="card-body">
              <div class="tab-content" id="myTabContent">

                <!-- Expired -->
                <div class="tab-pane fade active show" id="expire" role="tabpanel" aria-labelledby="expire-tab">

                  <?php if ($expired) : ?>
                  <div class="list-group">
                    <?php $i=0; foreach($expired as $exp_prod): $i++; if($i > 10) continue; ?>
                      <a href="<?php echo base_url(); ?>products/profile/<?php echo $exp_prod['products']['id']; ?>" class="list-group-item list-group-item-action list-group-hack">
                        <div class="d-flex w-100 justify-content-between">
                          <?php echo round($exp_prod['volume']) . ' ' . $exp_prod['products']['unit_sell'] ?>, <?php echo $exp_prod['products']['name']; ?>
                          <small>in <?php echo timespan(time(), strtotime($exp_prod['eol']. ' 01:01:01'), 1); ?></small>
                        </div>
                      </a>
                    <?php endforeach; ?>
                  </div>

                  <br />
                  <a href="<?php echo base_url(); ?>stock/expired_stock" class="btn btn-sm btn-outline-warning">Details (<?php echo count($expired); ?>)</a>
                  <?php else: ?>
                    Nothing expiring
                  <?php endif; ?>
                </div>

                <!-- Modified products -->
                <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="info-tab">
                  <?php if ($last_modified) : ?>
                    <div class="list-group">
                      <?php foreach($last_modified as $mod): ?>
                        <a href="<?php echo base_url(); ?>products/profile/<?php echo $mod['id']; ?>" class="list-group-item list-group-item-action list-group-hack">
                          <div class="d-flex w-100 justify-content-between">
                            <?php echo $mod['name']; ?>
                            <small><?php echo timespan(time(), strtotime($mod['updated_at']. ' 01:01:01'), 1); ?> ago</small>
                          </div>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    No Updates.
                  <?php endif; ?>
                </div>

                <!-- New products -->
                <div class="tab-pane fade" id="stocktabs" role="tabpanel" aria-labelledby="stocktabs-tab">
                  <?php if ($last_created) : ?>
                    <div class="list-group">
                      <?php foreach($last_created as $mod): ?>
                        <a href="<?php echo base_url(); ?>products/profile/<?php echo $mod['id']; ?>" class="list-group-item list-group-item-action list-group-hack">
                          <div class="d-flex w-100 justify-content-between">
                            <?php echo $mod['name']; ?>
                            <small><?php echo timespan(time(), strtotime($mod['created_at']. ' 01:01:01'), 1); ?> ago</small>
                          </div>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    No created.
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

	</div>

</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#product_list").addClass('active');

  var requestUrl = "<?php echo base_url(); ?>products/a_pid_by_type/1";

  var table = $("#table-info").DataTable({
    ajax: requestUrl,
    "pageLength": 50,
    "lengthMenu": [[50, 100, -1], [50, 100, "All"]],
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
      // console.log(element_id);
      // subsequent ajax call, with button click:
      requestUrl = "<?php echo base_url(); ?>products/a_pid_by_type/" + element_id;
      table.ajax.url( requestUrl ).load();
  });

});
</script>
