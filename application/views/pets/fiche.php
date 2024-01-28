<style>
.test {
	background-color: #ffffff;
	box-shadow: 0 0 3px 0 rgba(20,27,202,.17);
	border-radius: 10px;
	padding:15px;
	height: 100%;
}
.notest {
    padding: 1.25rem;
}
</style>

<div class="row">
	<div class="col-lg-7 col-xl-10">
		<div class="row d-flex justify-content-between">
			<div class="col-lg-8 d-flex align-self-stretch">
				<?php include "fiche/pet_info.php"; ?>
			</div>
			<div class="col-lg-4 d-flex align-self-stretch">
				<?php include "fiche/vaccines.php"; ?>
			</div>
		</div>

		<?php include "fiche/block_history.php"; ?>

	</div>
	<div class="col-lg-5 col-xl-2">
		<a href="<?php echo base_url('events/new_event/'. $pet['id']); ?>" class="btn btn-success btn-icon-split btn-lg mb-3"><span class="icon text-white-50"><i class="fas fa-user-md"></i></span><span class="text"><?php echo $this->lang->line('new_consult'); ?></span></a>
		
		<?php include "application/views/blocks/block_full_client.php"; ?>
		<?php include "fiche/block_weight.php"; ?>
		<?php include "fiche/block_birth.php"; ?>
		<?php include "fiche/block_nutrition.php"; ?>
		<?php include "fiche/block_medication.php"; ?>
		<?php include "fiche/block_other_pets.php"; ?>
	</div>
</div>