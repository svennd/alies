<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('wholesale'); ?>
			</div>
			<div class="card-body">
				<div class="row align-items-stretch mb-4">
					<div class="col-lg-8 mb-3 mb-lg-0">
						<div class="border rounded h-100 p-4 bg-light">
							<h4 class="mb-2"><?php echo $this->lang->line('wholesale'); ?></h4>
							<p class="text-muted mb-4">Search by description, internal product, vendor id, CNK, VHB or distributor.</p>
							<form action="<?php echo base_url('wholesale/index'); ?>" method="get" autocomplete="off">
								<div class="input-group">
									<input
										type="text"
										name="search"
										class="form-control"
										placeholder="Search wholesale..."
										value="<?php echo html_escape($search); ?>"
										autofocus
									>
									<div class="input-group-append">
										<button class="btn btn-primary" type="submit">Search</button>
									</div>
								</div>
							</form>
						</div>
					</div>
					<div class="col-lg-2 col-md-6 mb-3 mb-lg-0">
						<div class="border rounded h-100 p-4">
							<div class="text-xs text-uppercase text-muted mb-2">Wholesale records</div>
							<div class="h2 mb-0"><?php echo number_format((int) $wholesale_count); ?></div>
						</div>
					</div>
					<div class="col-lg-2 col-md-6">
						<div class="border rounded h-100 p-4">
							<div class="text-xs text-uppercase text-muted mb-2">Not linked</div>
							<div class="font-weight-bold mb-2">
								<a href="<?php echo base_url('wholesale/unlinked'); ?>"><?php echo number_format((int) $unlinked_count); ?></a>
							</div>
							<small class="text-muted d-block">Last update</small>
							<div class="font-weight-bold"><?php echo ($last_update) ? user_format_date($last_update, $user->user_date) : '-'; ?></div>
						</div>
					</div>
				</div>

				<?php if ($search !== ''): ?>
					<div class="d-flex justify-content-between align-items-center mb-3">
						<h5 class="mb-0">Results for "<?php echo html_escape($search); ?>"</h5>
						<span class="text-muted"><?php echo count($products); ?> result(s)</span>
					</div>

					<?php if ($products): ?>
						<div class="table-responsive">
							<table class="table table-sm table-hover">
								<thead>
									<tr>
										<th>Description</th>
										<th>Internal product</th>
										<th>Vendor id</th>
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
											<td>
												<?php if (!empty($product['product_id'])): ?>
													<a href="<?php echo base_url('products/profile/' . $product['product_id']); ?>">
														<?php echo html_escape($product['product_name']); ?>
													</a>
												<?php else: ?>
													<span class="text-muted">-</span>
												<?php endif; ?>
											</td>
											<td><?php echo html_escape($product['vendor_id']); ?></td>
											<td><?php echo html_escape($product['bruto']); ?></td>
											<td><?php echo ($product['updated_at']) ? user_format_date($product['updated_at'], $user->user_date) : '-'; ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php else: ?>
						<div class="alert alert-light border mb-0">
							No wholesale records found for "<?php echo html_escape($search); ?>".
						</div>
					<?php endif; ?>
				<?php else: ?>
					<div class="row">
						<div class="col-lg-12">
							<div class="border rounded h-100 p-3">
								<div class="d-flex justify-content-between align-items-center mb-3">
									<h5 class="mb-0">Last 5 products</h5>
									<small class="text-muted">Recent updates</small>
								</div>
								<?php if ($recent_products): ?>
									<div class="list-group list-group-flush">
										<?php foreach ($recent_products as $product): ?>
											<a href="<?php echo base_url('wholesale/get_history/' . $product['id']); ?>" class="list-group-item list-group-item-action px-0">
												<div class="font-weight-bold"><?php echo html_escape($product['description']); ?></div>
												<small class="text-muted">
													<?php echo !empty($product['product_name']) ? html_escape($product['product_name']) . ' | ' : ''; ?>
													<?php echo ($product['updated_at']) ? user_format_date($product['updated_at'], $user->user_date) : '-'; ?>
												</small>
											</a>
										<?php endforeach; ?>
									</div>
								<?php else: ?>
									<div class="text-muted">No recent products found.</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
});
</script>
