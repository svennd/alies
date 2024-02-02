<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div>
					<a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / 
					<a href="<?php echo base_url('wholesale/index'); ?>"><?php echo $this->lang->line('wholesale'); ?></a>
				</div>
			</div>
            <div class="card-body">
			<?php if($deliveries): ?>
						<table class="table table-sm" id="deliveries">
						<thead>
						<tr>
							<th>delivery_date</th>
							<th>delivery_nr</th>
							<th>order_date</th>
							<th>bruto_price</th>
							<th>netto_price</th>
							<th>Product</th>
							<th>amount</th>
							<th>lotnr</th>
							<th>due_date</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($deliveries as $d):?>
						<tr>
							<td data-sort="<?php echo strtotime($d['delivery_date']); ?>"><?php echo user_format_date($d['delivery_date'], $user->user_date); ?></td>
							<td><?php echo $d['delivery_nr']; ?></td>
							<td><?php echo $d['order_date']; ?></td>
							<td><?php echo $d['bruto_price']; ?></td>
							<td><?php echo $d['netto_price']; ?></td>
							<td><?php echo (isset($d['wholesale'])) ? "<a href='" . base_url('wholesale/get_history/'. $d['wholesale']['id']) . "''>" . $d['wholesale']['description'] : ""; ?></td>
							<td><?php echo $d['amount']; ?></td>
							<td><?php echo $d['lotnr']; ?></td>
							<td data-sort="<?php echo strtotime($d['due_date']); ?>">
							<?php 
										echo 
												(strtotime($d['due_date']) < strtotime(date('Y-m-d'))) ? 
													'<span style="color:tomato;"> ' . user_format_date($d['due_date'], $user->user_date) . '</span>'
														: 
														user_format_date($d['due_date'], $user->user_date)
										; ?>		
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
	$("#deliveries").DataTable({
		scrollY:        "65vh",
		deferRender:    true,
		scroller:       true,
		"order": [[ 0, "desc" ]]
	});
	$("#admin").addClass('active');
});
</script>
  