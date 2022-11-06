<div class="row">
	<div class="col-lg-7 col-xl-10">
		<?php include "fiche/block_history.php"; ?>

		<div class="row my-3">
			<div class="col-lg-6">
				<?php include "fiche/pet_info.php"; ?>
			</div>
			<div class="col-lg-6">
				<?php include "fiche/vaccines.php"; ?>
			</div>
		</div>
	</div>
	<div class="col-lg-5 col-xl-2">
		<a href="<?php echo base_url(); ?>events/new_event/<?php echo $pet['id']; ?>" class="btn btn-success btn-icon-split btn-lg mb-3"><span class="icon text-white-50"><i class="fas fa-user-md"></i></span><span class="text"><?php echo $this->lang->line('new_consult'); ?></span></a>
		<?php include "fiche/block_client.php"; ?>
		<?php include "fiche/block_weight.php"; ?>
		<?php include "fiche/block_birth.php"; ?>
		<?php include "fiche/block_nutrition.php"; ?>
		<?php include "fiche/block_other_pets.php"; ?>
	</div>
</div>
