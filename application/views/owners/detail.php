<!-- normal button : on small screens -->
<a href="<?php echo base_url('owners/invoices/' . $owner['id']); ?>" class="btn btn-outline-success mb-3 d-block d-sm-none d-md-none"><i class="fas fa-history fa-fw"></i><?php echo $this->lang->line('invoices'); ?></a>
<a href="<?php echo base_url('owners/edit/' . $owner['id']); ?>" class="btn btn-outline-info mb-3 d-block d-sm-none d-md-none"><i class="fas fa-user fa-fw"></i><?php echo $this->lang->line('edit_client'); ?></a>
				
<div class="row">
	<div class="col-lg-8 col-xl-10">

		<div class="card mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div>
					<a href="<?php echo base_url('/'); ?>"><?php echo $this->lang->line('client'); ?></a> / <a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / <?php echo $this->lang->line('overview'); ?>
					<small>(#<?php echo $owner['id']; ?>)</small>
				</div>
				<!-- hide on small screens -->
				<div class="dropdown no-arrow d-none d-sm-block">
					<a href="<?php echo base_url('owners/products/' . $owner['id']); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-history fa-fw"></i><?php echo $this->lang->line('products'); ?></a>
					<a href="<?php echo base_url('owners/invoices/' . $owner['id']); ?>" class="btn btn-outline-success btn-sm mr-3"><i class="fas fa-history fa-fw"></i><?php echo $this->lang->line('invoices'); ?></a>
					<a href="<?php echo base_url('owners/edit/' . $owner['id']); ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-user fa-fw"></i><?php echo $this->lang->line('edit_client'); ?></a>
				</div>
			</div>
			<div class="card-body">
			<?php if (isset($update) && $update) : ?>
					<div class="alert alert-success alert-dismissible fade show" role="alert">
						<?php echo $this->lang->line('updated_owner'); ?>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<?php endif; ?>
					<ul class="list-group">

					<?php if ($pets): ?>
					<?php
						foreach ($pets as $pet):
						if (is_null($pet['death_date']) && $pet['death'] || $pet['lost'])
						{
							continue;
						}
						if ($pet['death'])
						{
							$isLongerThanTwoWeeksAgo = (new DateTime($pet['death_date']) < (new DateTime())->sub(new DateInterval('P2W')));
							if($isLongerThanTwoWeeksAgo)
							{
								continue;
							}
						}
					?>
					<?php $age = timespan(strtotime($pet['birth']), time(), 1); ?>
						<li class="list-group-item">
							<div class="row">
								<div class="col-lg-12 col-sm-12 col-xl-2">
									<?php if($pet['death']): ?><i class="fa-solid fa-cross text-danger"></i><?php endif; ?>
									<a href="<?php echo base_url('pets/fiche/' . $pet['id']); ?>">
										<?php echo get_symbol($pet['type']); ?>
										<?php echo $pet['name']; ?>
									</a>
									<?php echo ($age < 30) ? '(' . $age . ')' : ''; ?>
									<hr class="mt-2 mb-3 d-xl-none" style="border-top: 1px solid rgba(0, 0, 0, 0.1);"/>
								</div>

								<div class="col-6 col-sm-6 col-md-3 col-lg-3 col-xl-1">
									#<?php echo $pet['id']; ?>
								</div>

								<div class="col-6 col-sm-6 col-md-3 col-lg-4 col-xl-2">
									<?php echo get_gender($pet['gender']); ?>
								</div>

								<div class="col-md-3 col-lg-3 col-xl-2">
									<?php if (isset($pet['breeds'])): ?>
										<?php echo $pet['breeds']['name']; ?>
									<?php endif; ?>
									<?php if (isset($pet['breeds2'])): ?>
										x <?php echo $pet['breeds2']['name']; ?>
									<?php endif; ?>
								</div>

								<?php if ($pet['last_weight']): ?>
									<div class="d-none d-sm-block col-md-2 col-lg-2 col-xl-1">
										<?php echo ($pet['last_weight'] != 0) ? '<a href="' . base_url() . 'pets/history_weight/' . $pet['id'] . '">' . $pet['last_weight'] . ' kg</a>' : ''; ?>
									</div>
								<?php endif; ?>

								<div class="col-lg-12 col-xl-4 d-none d-sm-block">
									<a class="btn btn-outline-success" href="<?php echo base_url(); ?>events/new_event/<?php echo $pet['id'] ?>"><i class="fas fa-user-md"></i> <?php echo $this->lang->line('new_consult'); ?></a>
									<a class="btn btn-outline-info" href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id'] ?>"><i class="fas fa-info-circle"></i> Fiche</a>
								</div>
								<div class="col-12 d-block d-sm-none d-md-none">
									<a class="btn btn-outline-success my-3" href="<?php echo base_url(); ?>events/new_event/<?php echo $pet['id'] ?>"><i class="fas fa-user-md"></i> <?php echo $this->lang->line('new_consult'); ?></a>									
									<a class="btn btn-outline-info ml-5" href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id'] ?>"><i class="fas fa-info-circle"></i> Fiche</a>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
					<?php endif; ?>
					<li class="list-group-item list-group-item-light">
						<p class="text-center">
							<a class="btn btn-outline-info" href="<?php echo base_url() . 'pets/add/' . $owner['id']; ?>"><i class="fas fa-plus"></i> <?php echo $this->lang->line('add_pet'); ?></a>
						</p>
					</li>
					</ul>
					<br/>
					<?php if ($pets): ?>
					<?php
						$total_dead = 0;
						$total_lost = 0;
						$dead_pet = array();
						$lost_pet = array();
						foreach ($pets as $pet):
							if ($pet['death'])
							{
								if (!is_null($pet['death_date']))
								{
									$isLongerThanTwoWeeksAgo = (new DateTime($pet['death_date']) < (new DateTime())->sub(new DateInterval('P2W')));
									if(!$isLongerThanTwoWeeksAgo)
									{
										continue;
									}
								}
								$total_dead++;
								$dead_pet[] = $pet;
							}
							elseif ($pet['lost'])
							{
								$total_lost++;
								$lost_pet[] = $pet;
							}
						endforeach;
					?>
						<?php if($total_dead + $total_lost != 0): ?>
							<div id="lost_or_passed" class="d-none d-sm-block">
								<div class="mb-3">
								<?php if($dead_pet): ?>
								<a class="btn btn-outline-info btn-sm" data-toggle="collapse" href="#passed" role="button" aria-expanded="false" aria-controls="collapseExample"><?php echo $this->lang->line('passed_away'); ?> (<?php echo $total_dead; ?>)</a>
								<?php endif; ?>

								<?php if($lost_pet): ?>
								<a class="btn btn-outline-info btn-sm" data-toggle="collapse" href="#gone" role="button" aria-expanded="false" aria-controls="collapseExample"><?php echo $this->lang->line('gone_or_lost'); ?> (<?php echo $total_lost; ?>)</a>
								<?php endif; ?>
								</div>

								<?php if($dead_pet): ?>
								<div class="collapse" id="passed" data-parent="#lost_or_passed">
								<div class="card card-body">
									<strong><?php echo $this->lang->line('passed_away'); ?></strong>
									<?php foreach ($dead_pet as $pet): ?>
										<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id'] ?>">
												<?php echo get_symbol($pet['type']); ?>
										<?php echo $pet['name']; ?>
										</a>
									<?php endforeach; ?>
								</div>
								</div>
								<?php endif; ?>

								<?php if($lost_pet): ?>
								<div class="collapse" id="gone" data-parent="#lost_or_passed">
								<div class="card card-body">
									<strong><?php echo $this->lang->line('gone_or_lost'); ?></strong>
									<?php foreach ($lost_pet as $pet): ?>
										<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id'] ?>">
												<?php echo get_symbol($pet['type']); ?>
										<?php echo $pet['name']; ?>
										</a>
									<?php endforeach; ?>
								</div>
								</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
			</div>
		</div>
	</div>
	
	<div class="col-md-2">
		<?php include "application/views/blocks/block_full_client.php"; ?>

		<?php if($open_bill): ?>
		<div class="alert alert-danger" role="alert">
		<?php echo $this->lang->line('open_invoices'); ?> :
			<ul>
		<?php foreach($open_bill as $bill): ?>
			<li><a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $bill['id']; ?>"><?php echo $bill['total_brut']; ?> &euro;</a></li>
		<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
		<!-- only for admin -->
		<?php if ($this->ion_auth->in_group("admin")): ?>
		<a class="btn btn-outline-danger d-none d-sm-block" href="<?php echo base_url() . 'debug/index/' . $owner['id']; ?>"><i class="fas fa-bug"></i> Debug</a>
		<?php endif; ?>
	</div>

</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#owners").show();
	$("#clients").addClass('active');
});
</script>
