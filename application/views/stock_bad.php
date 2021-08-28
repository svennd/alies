<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / Expired stock
			</div>
            <div class="card-body">
			<p>This table shows products that are expired (up to 360d) or are going to expire soon (90d). Background red are <30d, background grey are expired.<br/></p>
			<?php if ($stock_gone_bad): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Product</th>
					<th>EOL</th>
					<th>Lotnr</th>
					<th>Volume</th>
					<th>Location</th>
					<th>barcode</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($stock_gone_bad as $expire): 
				?>
				<tr>
					<td><?php echo $expire['products']['name']; ?></td>
					<td><?php echo $expire['eol']; ?></td>
					<td><?php echo $expire['lotnr']; ?></td>
					<td><?php echo $expire['volume']; ?> <?php echo $expire['products']['unit_buy']; ?></td>
					<td><?php echo $expire['stock_locations']['name']; ?></td>
					<td><?php echo $expire['barcode']; ?></td>
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
	$("#prd").show();
	$("#products").addClass('active');
	$("#stock").addClass('active');	
	
	const current_date = new Date();
	
	$("#dataTable").DataTable({
			"pageLength": 50, 
			"lengthMenu": [[50, 100, -1], [50, 100, "All"]],
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
  