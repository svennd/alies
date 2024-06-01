<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
	 		<?php include 'header.php'; ?>
            <div class="card-body">
			<div class="tab-content" id="myTabContent">
				<div class="tab-pane fade active show" id="global" role="tabpanel" aria-labelledby="global-tab">
				<br/>
				<?php if ($global_stock): ?>
				
					<table class="table table-sm" id="dataTable">
					<thead>
					<tr>
						<th>Product</th>
						<th><?php echo $this->lang->line('global_stock'); ?> / <?php echo $this->lang->line('limit'); ?></th>
						<th><?php echo $this->lang->line('global_use'); ?> (30d, 90d)</th>
						<th>Prio</th>
						<?php if ($this->ion_auth->in_group("admin")): ?>
						<th>Order</th>
						<th>wsid</th>
						<th>wsname</th>
						<?php endif; ?>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($global_stock as $product): ?>
					<?php
						$all_volume = intval($product['all_volume']);
						# use
						$global_30d = (is_null($product['global_use_30d'])) ? 0 : $product['global_use_30d'];
						$global_90d = (is_null($product['global_use_90d'])) ? 0 : $product['global_use_90d'];
						# default
						$prio = 1;

						if ($global_30d == 0 && $global_90d == 0)
						{
							# no use
							$prio -= 2;
						}
						# we have enough stock
						# to survive 90d
						elseif ($global_90d < $all_volume)
						{
							$prio -= 2;
						}
						# enough for 30d
						elseif ($global_30d < $all_volume)
						{
							$prio -= 1;
						}
						
						# small volume products
						# and have transactions
						if($global_90d != 0 && ($global_30d/$global_90d) >= 0.5)
						{
							$prio += 1;
						}
					?>
					<tr <?php echo ($prio > 1) ? 'class="table-warning"' : ''; ?>>
						<td><a href="<?php echo base_url(); ?>products/profile/<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a></td>
						<td><?php echo $all_volume; ?> / <?php echo $product['limit_stock']; ?> <?php echo $product['unit_sell']; ?></td>
						<td>
							<?php if ($this->ion_auth->in_group("admin")): ?><a href="<?php echo base_url('reports/usage/' . $product['id']); ?>"><?php endif; ?>
							<?php echo intval($global_30d); ?> / <?php echo intval($global_90d); ?> <?php echo $product['unit_sell']; ?>
							<?php if ($this->ion_auth->in_group("admin")): ?></a><?php endif; ?>
						</td>
						<td><?php echo $prio; ?></td>
						<?php if ($this->ion_auth->in_group("admin")): ?>
						<td>
							<a href="<?php echo base_url('products/set_backorder/' . $product['id']); ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-cart-shopping"></i></a>
						</td>
						<td><?php echo $product['wsid']; ?></td>
						<td><?php echo $product['wsname']; ?></td>
						<?php endif; ?>
					</tr>
					<?php endforeach; ?>
					</tbody>
					</table>
				<?php else: ?>
					<?php echo $this->lang->line('no_shortage'); ?>
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
	$("#dataTable").DataTable({
		"order": [[ 3, "desc" ]],
		<?php if ($this->ion_auth->in_group("admin")): ?>
		dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
		buttons: [
            { extend:'excel', text:'<i class="fas fa-file-export"></i> Excel', className:'btn btn-outline-success btn-sm', exportOptions: {columns: [0,1,2,5,6]}},
            { extend:'pdf', text:'<i class="far fa-file-pdf"></i> PDF', className:'btn btn-outline-success btn-sm', exportOptions: {columns: [0,1,2,5,6]}}
        ],
		"columnDefs": [
			{ "visible": false, "targets": 5 },
			{ "visible": false, "targets": 6 }
		]
		<?php endif; ?>
	});
});
</script>
  