<div class="card shadow mb-4">
	<div class="card-header">Bill</div>
	<div class="card-body">
			<ul>
		<?php if($consumables): ?>
			<?php foreach($consumables as $prod) : ?>
				<li><?php echo $prod['volume'] . ' ' . $prod['product']['unit_sell']  . ' ' . $prod['product']['name']; ?></li>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if($procedures_d): ?>
			<?php foreach($procedures_d as $proc) : ?>
				<li><?php echo $proc['amount'] . ' ' . $proc['procedures']['name']; ?></li>
			<?php endforeach; ?>
		<?php endif; ?>
		</ul>
		<a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $event_info['payment']; ?>" class="btn btn-outline-success"><i class="fas fa-arrow-right"></i> Show bill</a>
	</div>
</div>