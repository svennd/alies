<div class="card mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / 
		<?php echo $pet['name'] ?> <small>(#<?php echo $pet['id']; ?>)</small>
	</div>
	<div class="card-body">
		<a href="<?php echo base_url(); ?>events/new_event/<?php echo $pet['id']; ?>" class="btn btn-primary btn-icon-split"><span class="icon text-white-50"><i class="fas fa-user-md"></i></span><span class="text">New Consult</span></a>
		
		<a href="<?php echo base_url(); ?>vaccine/fiche/<?php echo $pet['id']; ?>" class="btn btn-success btn-icon-split ml-5"><span class="icon text-white-50"><i class="fas fa-syringe"></i></span><span class="text">Vaccination</span></a>	
		<a href="<?php echo base_url(); ?>pets/history/<?php echo $pet['id']; ?>" class="btn btn-success btn-icon-split"><span class="icon text-white-50"><i class="fas fa-file-medical-alt"></i></span><span class="text">History</span></a>
		<a href="<?php echo base_url(); ?>tooth/fiche/<?php echo $pet['id']; ?>" class="btn btn-success btn-icon-split"><span class="icon text-white-50"><i class="fas fa-tooth"></i></span><span class="text">Tooth</span></a>
		
		
		<a href="<?php echo base_url(); ?>pets/edit/<?php echo $pet['id']; ?>" class="btn btn-info btn-icon-split ml-5"><span class="icon text-white-50"><i class="fas fa-paw"></i></span><span class="text">Edit Pet</span></a>
		<a href="<?php echo base_url(); ?>pets/change_owner/<?php echo $pet['id']; ?>" class="btn btn-danger btn-icon-split ml-5"><span class="icon text-white-50"><i class="fas fa-exchange-alt"></i></span><span class="text">Change Owner</span></a>
		
	</div>
</div>