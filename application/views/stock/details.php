<div class="card shadow mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		
		<div><a href="<?php echo base_url('products'); ?>"><?php echo $this->lang->line('products'); ?></a> / <a href="<?php echo base_url('products/profile/' . $product['id']); ?>"><?php echo $product['name']; ?></a> / detail</div>
		<div class="dropdown no-arrow">
			<?php if ($this->ion_auth->in_group("admin")): ?>
				<?php if($show_all): ?>
					<a href="<?php echo base_url('stock/stock_detail/' . $product['id']); ?>"class="btn btn-outline-info btn-sm">Normal</a>
				<?php else: ?>
					<a href="<?php echo base_url('stock/stock_detail/' . $product['id'] . '/show_all'); ?>"class="btn btn-outline-info btn-sm">History</a>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
	<div class="card-body">
	<?php if ($stock_detail): ?>
		<table class="table table-sm" id="dataTable">
		<thead>
			<tr>
				<th><?php echo $this->lang->line('volume'); ?></th>
				<th><?php echo $this->lang->line('lotnr'); ?></th>
				<th><?php echo $this->lang->line('eol'); ?></th>
				<?php if ($this->ion_auth->in_group("admin")): ?>
					<th><?php echo $this->lang->line('price_dayprice'); ?></th>
				<?php endif; ?>
				<th><?php echo $this->lang->line('location'); ?></th>
				<th><?php echo $this->lang->line('state'); ?></th>
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
			<td data-sort="<?php echo ($detail['eol']) ? strtotime($detail['eol']) : "0"; ?>"><?php echo ($detail['eol']) ? user_format_date($detail['eol'], $user->user_date) : ''; ?></td>
			<?php if ($this->ion_auth->in_group("admin")): ?>
				<td><?php echo $detail['in_price']; ?> &euro; (<?php echo ($change > 0) ? '<span style="color:red;">+' . $change : '<span style="color:green;">' . $change; ?>%</span>)</td>
			<?php endif; ?>
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
                <th colspan="<?php echo ($this->ion_auth->in_group("admin")) ? '6' : '4';?>">&nbsp;</th>
            </tr>
        </tfoot>
		</table>
			
		<?php else: ?>
			no stock found
		<?php endif; ?>
		</div>
</div>

<script type="text/javascript">

const UNIT_SELL = '<?php echo $product['unit_sell']; ?>';

document.addEventListener("DOMContentLoaded", function(){
	$("#product_list").addClass('active');

	// also used in product/profile.php
	$("#dataTable").DataTable(
	{
			footerCallback: function (row, data, start, end, display) {
            var api = this.api();
 
            // Remove the formatting to get integer data for summation
			var intVal = function (i) {
				return typeof i === 'number' ? i : parseFloat(i.replace(/[^\d.-]/g, '')) || 0;
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
            $(api.column(0).footer()).html(Math.round(pageTotal*100)/100 + ' ' +  UNIT_SELL + ' (total : ' + Math.round(total*100)/100 + ' ' + UNIT_SELL + ' )' );
       		},
		});

	// $("#dataTable").DataTable(
	// {
	// 	footerCallback: function (row, data, start, end, display) {
	// 	var api = this.api();

	// 	// Remove the formatting to get integer data for summation
	// 	var intVal = function (i) {
	// 		return typeof i === 'string' ? i.replace(/[<?php echo $detail['products']['unit_sell']; ?>,]/g, '') * 1 : typeof i === 'number' ? i : 0;
	// 	};

	// 	// Total over all pages
	// 	total = api
	// 		.column(0)
	// 		.data()
	// 		.reduce(function (a, b) {
	// 			return intVal(a) + intVal(b);
	// 		}, 0);

	// 	// Total over this page
	// 	pageTotal = api
	// 		.column(0, { page: 'current' })
	// 		.data()
	// 		.reduce(function (a, b) {
	// 			return intVal(a) + intVal(b);
	// 		}, 0);

	// 	// Update footer
	// 	$(api.column(0).footer()).html(Math.round(pageTotal*100)/100 + ' <?php echo $detail['products']['unit_sell']; ?>' + ' (' + Math.round(total*100)/100 + ' <?php echo $detail['products']['unit_sell']; ?>)' );
	// 	},
	// });
});
</script>
