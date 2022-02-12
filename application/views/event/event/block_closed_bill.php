<div class="card shadow mb-4"> 
	<div class="card-header">Bill</div>
	<div class="card-body">
		<div class="list-group">
		<?php if($consumables): ?>
			<?php foreach($consumables as $prod) : ?>
				<p class="list-group-item list-group-item-action list-group-hack"><?php echo $prod['volume'] . ' ' . $prod['product']['unit_sell']  . ' ' . $prod['product']['name']; ?></p>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if($procedures_d): ?>
				<?php foreach($procedures_d as $proc) : ?>
					<p class="list-group-item list-group-item-action list-group-hack"><?php echo $proc['procedures']['name']; ?></p>
				<?php endforeach; ?>
		<?php endif; ?>
	</div>
		<br/>
		<a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $event_info['payment']; ?>" class="btn btn-outline-success"><i class="fas fa-arrow-right"></i> Show bill</a>
	</div>
</div>
