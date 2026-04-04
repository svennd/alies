<?php if (!empty($results)): ?>
	<table class="table table-sm my-3">
	<?php foreach ($results as $result): ?>
		<?php $is_product = $result['type'] === 'product'; ?>
			<tr>
				<td>
					<?php if ($is_product): ?>
						<a href="<?php echo $result['profile_url']; ?>"><?php echo html_escape($result['name']); ?></a>
					<?php else: ?>
						<?php echo html_escape($result['name']); ?>
					<?php endif; ?>
					<span class="ml-2">
						<?php if ($is_product && $result['is_antibiotic']): ?><i class='fa-solid fa-bacteria' style='color: rgb(202, 112, 85);'></i><?php endif; ?>
						<?php if ($is_product && $result['vaccin']): ?><i class='fa-solid fa-syringe' style='color: rgb(114, 89, 73);'></i><?php endif; ?>
						<?php if ($is_product && !$result['sellable']): ?><i class='fa-solid fa-xmark' style='color: red;'></i><?php endif; ?>
					</span>
				</td>
				<td>
					<div class="text-muted product-search-stock">
						<?php if ($is_product && ($result['local_stock'] + $result['global_stock']) > 0): ?>
							<?php echo html_escape($result['local_stock']); ?> / <?php echo html_escape($result['global_stock']); ?> 
							<?php if (!empty($result['unit_sell'])): ?><?php echo html_escape($result['unit_sell']); ?><?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
				</td>
				<td>
				<?php if ($is_admin): ?><a href="<?php echo $result['edit_url']; ?>" class="btn btn-outline-primary btn-sm">Edit</a><?php endif; ?>
				</td>
	<?php endforeach; ?>
	</table>
<?php elseif (isset($results)): ?>
	<div class="product-search-empty">
		No products or procedures matched this search.
	</div>
<?php endif; ?>
