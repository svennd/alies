
<fieldset class="border max bg-light">
	<legend class="text-center topfields">Bill</legend>
	<div class="list-group px-2">
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
</fieldset>