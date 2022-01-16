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
              <li class="nav-item" role="presentation"><a class="nav-link active" id="expire-tab" data-toggle="tab" href="#expire" role="tab" aria-controls="expire" aria-selected="true">Expiring</a></li>
              <li class="nav-item" role="presentation"><a class="nav-link" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true">Modified Products</a></li>
              <li class="nav-item" role="presentation"><a class="nav-link" id="stocktabs-tab" data-toggle="tab" href="#stocktabs" role="tab" aria-controls="stocktabs" aria-selected="false">New Products</a></li>
            </ul>
            </div>
            <div class="card-body">
              <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade active show" id="expire" role="tabpanel" aria-labelledby="expire-tab">
                  <?php if ($expired) : ?>
                  <ul>
                    <?php foreach($expired as $exp_prod): ?>
                    <li><a href="<?php echo base_url(); ?>products/profile/<?php echo $exp_prod['products']['id']; ?>"><?php echo $exp_prod['products']['name']; ?></a>
                      <small>(<?php echo timespan(time(), strtotime($exp_prod['eol']. ' 01:01:01'), 1); ?>, <?php echo round($exp_prod['volume']) . ' ' . $exp_prod['products']['unit_sell'] ?>)</small></li>
                    <?php endforeach; ?>
                  </ul>
                  <?php else: ?>
                    Nothing expiring
                  <?php endif; ?>
                </div>
                <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="info-tab">
                  <?php if ($last_modified) : ?>
                  <ul>
                    <?php foreach($last_modified as $mod): ?>
                    <li><a href="<?php echo base_url(); ?>products/profile/<?php echo $mod['id']; ?>"><?php echo $mod['name']; ?></a> <small>(<?php echo timespan(strtotime($mod['updated_at']), time(), 1); ?> Ago)</small></li>
                    <?php endforeach; ?>
                  </ul>
                  <?php else: ?>
                    No Updates.
                  <?php endif; ?>
                </div>
                <div class="tab-pane fade" id="stocktabs" role="tabpanel" aria-labelledby="stocktabs-tab">
                  <?php if ($last_created) : ?>
                  <ul>
                    <?php foreach($last_created as $mod): ?>
                    <li><a href="<?php echo base_url(); ?>products/profile/<?php echo $mod['id']; ?>"><?php echo $mod['name']; ?></a> <small>(<?php echo timespan(strtotime($mod['created_at']), time(), 1); ?> Ago)</small></li>
                    <?php endforeach; ?>
                  </ul>
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
