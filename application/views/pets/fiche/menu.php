<div class="card mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / 
		<?php echo $pet['name'] ?> <small>(#<?php echo $pet['id']; ?>)</small>
	</div>
	<!-- hide on small screens -->
	<div class="card-body d-none d-sm-block">
		<a href="<?php echo base_url(); ?>events/new_event/<?php echo $pet['id']; ?>" class="btn btn-success btn-icon-split"><span class="icon text-white-50"><i class="fas fa-user-md"></i></span><span class="text"><?php echo $this->lang->line('new_consult'); ?></span></a>
		
		<a href="<?php echo base_url(); ?>tooth/fiche/<?php echo $pet['id']; ?>" class="btn btn-primary btn-icon-split ml-5"><span class="icon text-white-50"><i class="fas fa-tooth"></i></span><span class="text"><?php echo $this->lang->line('tooth'); ?></span></a>
	<a href="<?php echo base_url(); ?>pets/change_owner/<?php echo $pet['id']; ?>" class="btn btn-danger btn-icon-split ml-5"><span class="icon text-white-50"><i class="fas fa-exchange-alt"></i></span><span class="text"><?php echo $this->lang->line('change_owner'); ?></span></a>
		
		<a href="<?php echo base_url(); ?>pets/export/<?php echo $pet['id']; ?>" class="btn btn-warning btn-icon-split ml-5"><span class="icon text-white-50"><i class="fas fa-file-export"></i></span><span class="text"><?php echo $this->lang->line('export'); ?></span></a>
		
	</div>
</div>

<!-- phone only links -->
<a href="<?php echo base_url(); ?>events/new_event/<?php echo $pet['id']; ?>" class="btn btn-success mb-3 d-block d-sm-none d-md-none"><i class="fas fa-user-md"></i> <?php echo $this->lang->line('consult'); ?></a>
<a href="<?php echo base_url(); ?>tooth/fiche/<?php echo $pet['id']; ?>" class="btn btn-primary mb-3 d-block d-sm-none d-md-none"><i class="fas fa-tooth"></i> <?php echo $this->lang->line('tooth'); ?></a>
<a href="<?php echo base_url(); ?>pets/change_owner/<?php echo $pet['id']; ?>" class="btn btn-danger mb-3 d-block d-sm-none d-md-none"><i class="fas fa-exchange-alt"></i> <?php echo $this->lang->line('export'); ?></a>