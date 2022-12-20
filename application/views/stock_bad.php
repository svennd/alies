<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				Stock / Expired stock
			</div>
      <div class="card-body">
			<p>This table shows products that are expired (up to 360d) or are going to expire soon (90d).
        <span class="p-1 bg-danger text-white">Expiring soon (30d)</span>, <span class="p-1 bg-secondary text-white">Expired</span>.<br/></p>
			<?php if ($stock_gone_bad): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Product</th>
					<th>EOL</th>
					<th>EOL (user)</th>
					<th>Lotnr</th>
					<th>Volume</th>
					<th>Location</th>
					<th>Barcode</th>
          <th>Options</h>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($stock_gone_bad as $expire):
				?>
				<tr>
					<td><?php echo $expire['products']['name']; ?></td>
					<td><?php echo $expire['eol']; ?></td>
					<td><?php echo date_format(date_create($expire['eol']), $user->user_date); ?></td>
					<td><?php echo $expire['lotnr']; ?></td>
					<td><?php echo $expire['volume']; ?> <?php echo $expire['products']['unit_buy']; ?></td>
					<td><?php echo $expire['stock_locations']['name']; ?></td>
					<td><?php echo $expire['barcode']; ?></td>
          <td>
            <?php
              $date = new DateTime($expire['eol']);
              $now = new DateTime();

              if($date < $now):
            ?>
            <form action="<?php echo base_url(); ?>stock/write_off" method="post" autocomplete="off">
    					<input type="hidden" name="volume" value="<?php echo $expire['volume']; ?>">
    					<input type="hidden" name="product_id" value="<?php echo $expire['products']['id']; ?>">
    					<input type="hidden" name="location" value="<?php echo $expire['stock_locations']['id']; ?>">
    					<input type="hidden" name="barcode" value="<?php echo $expire['barcode']; ?>">
    				  <button type="submit" name="submit" value="write_off_q" class="btn btn-primary">Write off</button>
    				</form>
            <?php endif; ?>
          </td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php endif; ?>
      </div>
		</div>

	</div>

</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){

	$("#product_list").addClass('active');

	const current_date = new Date();

	$("#dataTable").DataTable({
			"order": [[ 1, "desc" ]],
			"createdRow": function( row, data, dataIndex){
				var product_date = new Date(data[1]);
				date_diff = (current_date - product_date);
				if( date_diff > 0){
					$(row).addClass("table-secondary");
				/* 30 days */
				} else if (date_diff > -(1000*60*60*24*30)) {
					$(row).addClass("table-danger");
				}
			}
	});
});
</script>
