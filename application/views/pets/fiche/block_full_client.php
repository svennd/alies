<div class="card 
		<?php echo ($owner['debts']) ? "border-left-danger" : (($owner['low_budget']) ? "border-left-warning" : "border-left-success"); ?> 
		shadow py-1 mb-3">
	<div class="card-body">
		<div class="row no-gutters align-items-center">
			<div class="col mr-2">
				<div class="font-weight-bold <?php echo ($owner['debts']) ? 'text-danger' : 'text-primary'; ?> text-uppercase mb-1">
					<a href="<?php echo base_url(); ?>owners/edit/<?php echo $owner['id']; ?>" class="<?php echo ($owner['debts']) ? 'text-danger' : 'text-primary'; ?>"><?php echo $owner['last_name']. " &nbsp;" . $owner['first_name']; ?></a>
				</div>
				<div class="mb-0 text-gray-800">
					<?php echo $owner['street'] . ' ' . $owner['nr']. ' ' .  $owner['zip']. ' ' .  $owner['city']; ?><br>
					<?php if ($owner['telephone']) : ?><abbr title="Phone">P:</abbr> <?php echo $owner['telephone']; ?><br/><?php endif; ?>
					<?php if ($owner['mobile']) : ?><abbr title="Mobile">M:</abbr> <?php echo $owner['mobile']; ?><br/><?php endif; ?>
					<?php if ($owner['mail']) : ?><a href="mailto:<?php echo $owner['mail']; ?>"><?php echo $owner['mail']; ?></a><br/><?php endif; ?>
					<?php if ($owner['btw_nr']) : ?><br/><?php echo $owner['btw_nr']; ?><br/><?php endif; ?>
				
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

<?php if ($owner['invoice_addr']) : ?>
<div class="card border-left-primary shadow py-1 mb-3">
	<div class="card-body">
		<div class="row no-gutters align-items-center">
			<div class="col mr-2">
				<div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php echo $this->lang->line('Invoice'); ?></div>
				<div class="mb-0 text-gray-800">
					<?php if ($owner['invoice_contact']) : ?><b><?php echo $owner['invoice_contact']; ?></b><br/><?php endif; ?>
					<?php if ($owner['invoice_addr']) : ?><?php echo $owner['invoice_addr']; ?><br/><?php endif; ?>
					<?php if ($owner['invoice_tel']) : ?><abbr title="Phone">P:</abbr> <?php echo $owner['invoice_tel']; ?><br/><?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>