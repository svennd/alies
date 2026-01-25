<style>
.depad_header th { padding: 0.15rem 0.75rem; }
.nav-borders .nav-link {
  color: #4e73df;
  border-bottom-width: 0.125rem;
  border-bottom-style: solid;
  border-bottom-color: transparent;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  padding-left: 0;
  padding-right: 0;
  margin-left: 1rem;
  margin-right: 1rem;
}
.nav-borders .nav-link.active {
  color: #1cc88a;
  border-bottom-color: #1cc88a;
}

.nav-borders.flex-column .nav-link {
  padding-top: 0;
  padding-bottom: 0;
  padding-right: 1rem;
  padding-left: 1rem;
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
  margin-right: 0;
  margin-left: 0;
  border-bottom: none;
  border-right-width: 0.125rem;
  border-right-style: solid;
  border-right-color: transparent;
}

.nav-borders.flex-column .nav-link:hover {
  color: #1cc88a;
}
</style>
<div class="row">
	<div class="col-lg-12 col-xl-10">

		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div class="d-none d-md-block">
					<a href="<?php echo base_url('owners/detail/' . $owner['id']); ?>"><?php echo $owner['last_name'] ?></a> /
					<a href="<?php echo base_url('pets/fiche/' . $pet['id']); ?>"><?php echo $pet['name'] ?></a> <small>(#<?php echo $pet['id']; ?>)</small> / Event
				</div>
				<div class="d-md-none">Event</div>
				<?php include "event/block_header_types.php"; ?>
			</div>
			<div class="card-body">
				<div class="d-none d-md-block">
					<nav class="nav nav-borders" id="headtabs">
						<a href="#index" class="nav-link" id="nav-home-tab" data-toggle="tab" data-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="false">
							<i class="fa-solid fa-receipt"></i> <?php echo $this->lang->line('check'); ?>
						</a>
						<a href="#report" class="nav-link <?php echo ($event_info['no_history'] == 1) ? "disabled":""; ?>" id="nav-report-tab" data-toggle="tab" data-target="#nav-report" type="button" role="tab" aria-controls="nav-report" aria-selected="true">
							<i class="fa-solid fa-notes-medical"></i> <?php echo $this->lang->line('report'); ?>
						</a>
					</nav>
					<hr class="mt-0 mb-3">
				</div>
				
				<div class="tab-content" id="nav-tabContent-heads">
					<div class="tab-pane active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
						<?php include "invoice_index.php"; ?>
					</div>
					<div class="tab-pane" id="nav-report" role="tabpanel" aria-labelledby="nav-report-tab">
						<?php include "event/block_report.php"; ?>
					</div>
				</div>
				<hr class="mt-0 mb-3">
				<?php if($consumables || $procedures_d): ?>
					<?php if($event_info['payment'] == 0) : ?>
						<a href="<?php echo base_url('invoice/bill/' . $owner['id'] . '/' . $event_id); ?>" class="btn btn-outline-success"><i class="fas fa-arrow-right"></i> <?php echo $this->lang->line('create_bill'); ?></a>
					<?php else: ?>
						<a href="<?php echo base_url('invoice/get_bill/' . $event_info['payment']); ?>" class="btn btn-outline-success"><i class="fas fa-arrow-right"></i> <?php echo $this->lang->line('show_bill'); ?></a>
					<?php endif; ?>
				<?php else: ?>
					<a id="generate_bill" href="<?php echo base_url('invoice/bill/' . $owner['id']); ?>" class="btn btn-outline-success d-none"><i class="fas fa-arrow-right"></i> <?php echo $this->lang->line('create_bill'); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="col-xl-2 d-none d-md-block">
		<?php include "event/block_client.php"; ?>
		<?php include "application/views/pets/fiche/block_other_pets.php"; ?>
		<?php include "application/views/pets/fiche/block_birth.php"; ?>
		<?php include "application/views/pets/fiche/block_nutrition.php"; ?>
		<?php include "application/views/pets/fiche/block_medication.php"; ?>
		<?php include "event/block_event_controller.php"; ?>
	</div>

</div>

<script>
	const URL_ADD_LINE 		= '<?php echo base_url('events/add_line/' . $event_id . '/'); ?>';
	const URL_PROC_OR_PROD 	= '<?php echo base_url('products/get_product_or_procedure'); ?>';
	const URL_DELETE_PROC 	= '<?php echo base_url('events/proc_remove/' . $event_id . '/'); ?>';
	const URL_DELETE_PROD 	= '<?php echo base_url('events/prod_remove/' . $event_id . '/'); ?>';
</script>
<script src="<?php echo javascript('assets/js/events.js'); ?>"></script>