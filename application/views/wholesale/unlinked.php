<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> /
				<a href="<?php echo base_url('wholesale/index'); ?>"><?php echo $this->lang->line('wholesale'); ?></a> /
				Not linked (<?php echo count($products); ?>)
			</div>
			<div class="card-body">
				<?php if ($products): ?>
					<div class="table-responsive">
						<table class="table table-sm table-hover" id="dataTable">
							<thead>
								<tr>
									<th>Description</th>
									<th>Vendor id</th>
									<th>Distributor</th>
									<th>Bruto</th>
									<th>Last update</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($products as $product): ?>
									<tr>
										<td>
											<a href="<?php echo base_url('wholesale/get_history/' . $product['id']); ?>">
												<?php echo html_escape($product['description']); ?>
											</a>
										</td>
										<td><?php echo html_escape($product['vendor_id']); ?></td>
										<td><?php echo html_escape($product['distributor']); ?></td>
										<td><?php echo html_escape($product['bruto']); ?></td>
										<td><?php echo ($product['updated_at']) ? user_format_date($product['updated_at'], $user->user_date) : '-'; ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="alert alert-light border mb-0">
						All wholesale products are linked.
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');

	$("#dataTable").DataTable();
});
</script>
