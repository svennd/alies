<div class="card shadow mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		
		<div><a href="<?php echo base_url('stock/all'); ?>">Stock</a> / <?php echo $product['name']; ?> / detail</div>
		<div class="dropdown no-arrow">
			<?php if ($this->ion_auth->in_group("admin")): ?>
				<?php if($show_all): ?>
					<a href="<?php echo base_url('stock/stock_detail/' . $product['id']); ?>"class="btn btn-outline-info btn-sm">Normal</a>
				<?php else: ?>
					<a href="<?php echo base_url('stock/stock_detail/' . $product['id'] . '/show_all'); ?>"class="btn btn-outline-info btn-sm">History</a>
				<?php endif; ?>
				<a href="<?php echo base_url('logs/product/' . $product['id']); ?>"class="btn btn-outline-danger btn-sm"><i class="fas fa-exchange-alt"></i> Transaction log</a>
			<?php endif; ?>
		</div>
	</div>
	<div class="card-body">
	<?php if ($stock_detail): ?>
		<table class="table" id="dataTable">
		<thead>
		<tr>
			<th>Volume</th>
			<th>Lotnr</th>
			<th>EOL</th>
			<th>In Price</th>
			<th>Barcode</th>
			<th>Location</th>
			<th>State</th>
			<?php if ($this->ion_auth->in_group("admin")): ?>
			<th>Option</th>
			<?php endif; ?>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($stock_detail as $detail): ?>
		<?php $change = round((($detail['in_price']-$detail['products']['buy_price'])/$detail['products']['buy_price'])*100); ?>
		<tr>
			<td><?php echo $detail['volume']; ?> <?php echo $detail['products']['unit_sell']; ?></td>
			<td><?php echo $detail['lotnr']; ?></td>
			<td><?php echo user_format_date($detail['eol'], $user->user_date); ?></td>
			<td><?php echo $detail['in_price']; ?> &euro; (<?php echo ($change > 0) ? '<span style="color:red;">+' . $change : '<span style="color:green;">' . $change; ?>%</span>)</td>
			<td><?php echo $detail['barcode']; ?></td>
			<td><?php echo $detail['stock_locations']['name']; ?></td>
			<td><?php echo stock_state($detail['state']); ?></td>
			<?php if ($this->ion_auth->in_group("admin")): ?>
			<td><a href="<?php echo base_url('stock/edit/' . $detail['id']); ?>" class="btn btn-outline-success">edit</a></td>
			<?php endif; ?>
		</tr>
		<?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th class="bg-secondary text-white">Total:</th>
                <th colspan="<?php echo ($this->ion_auth->in_group("admin")) ? '7' : '6';?>">&nbsp;</th>
            </tr>
        </tfoot>
		</table>
			
		<?php else: ?>
			no stock found
		<?php endif; ?>
		</div>
</div>

<div class="card shadow mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div><a href="<?php echo base_url(); ?>stock/all">Stock</a> / <?php echo $product['name']; ?> / usage last 6m</div>
		<div class="dropdown no-arrow">
			<a href="<?php echo base_url('reports/usage/' . $product['id']); ?>" class="btn btn-outline-info btn-sm">Details</a>
		</div>
	</div>
	<div class="card-body">
	<?php if ($stock_usage): ?>

		<table class="table" id="dataTable">
		<thead>
		<tr>
			<th>Volume</th>
			<th>Month</th>
			<th>Year</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($stock_usage as $usage): ?>
		<tr>
			<td><?php echo $usage['volume']; ?> <?php echo $product['unit_sell']; ?></td>
			<td><?php echo $usage['month']; ?></td>
			<td><?php echo $usage['year']; ?></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
	<?php else: ?>
		no usage known.
	<?php endif; ?>
		</div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#products").addClass('active');
	$("#stock").addClass('active');
	$("#dataTable").DataTable(
		{
			"pageLength": 50, 
			"lengthMenu": [[50, 100, -1], [50, 100, "All"]],
			footerCallback: function (row, data, start, end, display) {
            var api = this.api();
 
            // Remove the formatting to get integer data for summation
            var intVal = function (i) {
                return typeof i === 'string' ? i.replace(/[<?php echo $detail['products']['unit_sell']; ?>,]/g, '') * 1 : typeof i === 'number' ? i : 0;
            };
 
            // Total over all pages
            total = api
                .column(0)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
 
            // Total over this page
            pageTotal = api
                .column(0, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
 
            // Update footer
            $(api.column(0).footer()).html(pageTotal + '<?php echo $detail['products']['unit_sell']; ?>' + ' (' + total + ' <?php echo $detail['products']['unit_sell']; ?>)' );
       		},
		});
});
</script>
