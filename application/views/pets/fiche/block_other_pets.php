<?php if( is_array($other_pets) && count($other_pets) > 1):?>
<div class="card border-left-success shadow py-1 mb-3">
	<div class="card-body">
		<div class="row no-gutters align-items-center">
			<div class="col mr-2">
				<div class="text-xs font-weight-bold text-uppercase mb-1"><?php echo $this->lang->line('other_pets'); ?></div>
				<div class="text-gray-800">
				<ul>
				<?php foreach($other_pets as $p): if($p['id'] == $pet['id']) { continue; }?>
				
				<li><a href="<?php echo base_url(); ?>pets/fiche/<?php echo $p['id']; ?>"><?php echo $p['name']; ?></a></li>
				<?php endforeach; ?>
				</ul>
				</div>
			</div>
			<div class="col-auto"><i class="fas fa-paw fa-2x text-gray-300"></i></div>
		</div>
	</div>
</div>
<?php endif; ?>