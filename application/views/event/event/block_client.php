<div class="card 
		<?php echo ($owner['debts']) ? "border-left-danger" : (($owner['low_budget']) ? "border-left-warning" : "border-left-success"); ?> 
		shadow py-1 mb-3 d-none d-xl-block">
	<div class="card-body">
		<div class="row no-gutters align-items-center">
			<div class="col mr-2">
				<div class="font-weight-bold <?php echo ($owner['debts']) ? 'text-danger' : 'text-primary'; ?> text-uppercase mb-1"><?php echo $owner['last_name'] ?></div>
				<div class="mb-0 text-gray-800">
					<?php echo $owner['street'] . ' ' . $owner['nr']. '<br/>' .  $owner['zip']. ' ' .  $owner['city']; ?><br>
					<?php if ($owner['telephone']) : ?><?php echo print_phone($owner['telephone']); ?><br/><?php endif; ?>
					<?php if ($owner['mobile']) : ?><?php echo print_phone($owner['mobile']); ?><br/><?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>


<?php if (!empty($owner['msg'])): ?>
	<div class="card bg-warning text-white">
		<div class="card-body">
		<div class="text-white"><?php echo empty($owner['msg']) ? "?" : $owner['msg']; ?></div>
		</div>
	</div>
	<br/>
<?php endif; ?>