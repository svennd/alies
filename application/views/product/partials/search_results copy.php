<?php if (!empty($results)): ?>
	<div class="list-group shadow-sm">
		<?php foreach ($results as $result): ?>
			<?php $is_product = $result['type'] === 'product'; ?>
			<div class="list-group-item py-2 px-3">
				<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
					<div class="pr-md-3">
						<div class="d-flex flex-wrap align-items-center mb-1">
							<span class="badge badge-<?php echo $is_product ? 'primary' : 'success'; ?> mr-2">
								<?php echo ucfirst($result['type']); ?>
							</span>
							<div class="font-weight-bold product-search-result-title mb-0">
								<?php if ($is_product): ?>
									<a href="<?php echo $result['profile_url']; ?>" class="text-primary">
										<?php echo html_escape($result['name']); ?>
									</a>
								<?php else: ?>
									<span class="text-gray-900"><?php echo html_escape($result['name']); ?></span>
								<?php endif; ?>
							</div>
							<?php if ($is_product && !$result['sellable']): ?><i class='fa-solid fa-xmark' style='color: red;'></i><?php endif; ?>
						</div>
						<div class="text-muted product-search-stock">
							<?php if ($is_product): ?>
								L <strong><?php echo html_escape($result['local_stock']); ?></strong>
								<span class="mx-1">/</span>
								G <strong><?php echo html_escape($result['global_stock']); ?></strong>
								<?php if (!empty($result['unit_sell'])): ?>
									<span class="ml-1"><?php echo html_escape($result['unit_sell']); ?></span>
								<?php endif; ?>
							<?php else: ?>
								Price <strong><?php echo number_format((float) $result['price'], 2, ',', '.'); ?> &euro;</strong>
							<?php endif; ?>
						</div>
					</div>
					<?php if ($is_admin): ?>
						<div class="product-search-actions">
							<a href="<?php echo $result['edit_url']; ?>" class="btn btn-outline-primary btn-sm">Edit</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php elseif (isset($results)): ?>
	<div class="product-search-empty">
		No products or procedures matched this search.
	</div>
<?php endif; ?>
