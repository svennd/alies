<div class="card shadow mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div><a href="<?php echo base_url('products'); ?>">Stock</a> / Errors</div>
	</div>
	<div class="card-body">
        <?php if($stock): ?>
            <table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Product</th>
					<th>Volume</th>
					<th>Details</th>
					<th>Info</th>
					<th>Last Update</th>
					<th>Options</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($stock as $stk): ?>
				<tr>
					<td><a href="<?php echo base_url('stock/stock_detail/' . $stk['product_id']. '/show_all'); ?>"><?php echo $stk['product_name']; ?></a></td>
					<td><?php echo $stk['volume']; ?> <?php echo $stk['unit_sell']; ?></td>
					<td>
                        <p class="small">
                        <?php echo $stk['st_location']; ?><br/>
                        <?php echo $stk['lotnr']; ?><br/>
                        <?php echo $stk['eol']; ?>
                        </p>
                    </td>
					<td><?php echo ($stk['info']) ? $stk['info'] : "-"; ?></td>
					<td data-sort="<?php echo ($stk['updated_at']) ? strtotime($stk['updated_at']) : 0; ?>"><?php echo ($stk['updated_at']) ?  date_format(date_create($stk['updated_at']), $user->user_date) : ""; ?></td>
					<td>
                        <a href="<?php echo base_url('stock/clear_error/'. $stk['id']); ?>" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-broom fa-fw"></i> Clear</a>
                        <a href="<?php echo base_url('logs/product/'. $stk['product_id']); ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-receipt"></i> Transactions</a>
                        <a href="<?php echo base_url('stock/edit/'. $stk['id']); ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i> Edit</a>
                    </td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
        <?php endif;?>
    </div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#product_list").addClass('active');

	$("#dataTable").DataTable();
});
</script>