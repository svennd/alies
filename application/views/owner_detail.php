<div class="row">

	<div class="col-lg-8 col-xl-10">

		<div class="card mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>owners/search">Client</a> / <a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / overview
				<small>(#<?php echo $owner['id']; ?>)</small>
			</div>
			<div class="card-body">
				<a href="<?php echo base_url(); ?>owners/invoices/<?php echo $owner['id']; ?>" class="btn btn-success btn-icon-split"><span class="icon text-white-50"><i class="fas fa-history"></i></span><span class="text">Invoices</span></a>
				<a href="<?php echo base_url(); ?>owners/edit/<?php echo $owner['id']; ?>" class="btn btn-info btn-icon-split"><span class="icon text-white-50"><i class="fas fa-user"></i></span><span class="text">Edit Client</span></a>
			</div>
		</div>

		<div class="card">
			<div class="card-header">Pets</div>
				<div class="card-body">
					<?php if (isset($update) && $update) : ?>
					<div class="alert alert-success alert-dismissible fade show" role="alert">
						Info updated!
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<?php endif; ?>
					<ul class="list-group">

					<?php if ($pets): ?>
					<?php
						foreach ($pets as $pet):
						if ($pet['death'])
						{
							continue;
						}
						if ($pet['lost'])
						{
							continue;
						}
					?>
					<?php $age = timespan(strtotime($pet['birth']), time(), 1); ?>
						<li class="list-group-item">
							<div class="row">
								<div class="col-lg-12 col-sm-12 col-xl-2">
									<?php echo get_symbol($pet['type']); ?>
									<?php echo $pet['name']; ?>
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
								</div>

								<?php if ($pet['last_weight']): ?>
									<div class="d-none d-sm-block col-md-2 col-lg-2 col-xl-1">
										<?php echo ($pet['last_weight'] != 0) ? '<a href="' . base_url() . 'pets/history_weight/' . $pet['id'] . '">' . $pet['last_weight'] . ' kg</a>' : ''; ?>
									</div>
								<?php endif; ?>

								<div class="col-lg-12 col-xl-4 my-1">
									<div class="btn-group">
										<a class="btn btn-outline-success" href="<?php echo base_url(); ?>events/new_event/<?php echo $pet['id'] ?>"><i class="fas fa-user-md"></i> New Consult</a>
										<a class="btn btn-outline-info" href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id'] ?>"><i class="fas fa-info-circle"></i> Fiche</a>
									</div>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
					<?php endif; ?>
					<li class="list-group-item list-group-item-light">
						<p class="text-center">
								<a class="btn btn-outline-info" href="<?php echo base_url() . 'pets/add/' . $owner['id']; ?>"><i class="fas fa-plus"></i> Add Pet</a>
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
							<div id="lost_or_passed">
								<div class="mb-3">
								<?php if($dead_pet): ?>
								<a class="btn btn-outline-info btn-sm" data-toggle="collapse" href="#passed" role="button" aria-expanded="false" aria-controls="collapseExample">Passed Away (<?php echo $total_dead; ?>)</a>
								<?php endif; ?>

								<?php if($lost_pet): ?>
								<a class="btn btn-outline-info btn-sm" data-toggle="collapse" href="#gone" role="button" aria-expanded="false" aria-controls="collapseExample">Gone / Lost (<?php echo $total_lost; ?>)</a>
								<?php endif; ?>
								</div>

								<?php if($dead_pet): ?>
								<div class="collapse" id="passed" data-parent="#lost_or_passed">
								<div class="card card-body">
									<strong>Passed away</strong>
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
									<strong>Gone / lost</strong>
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
	
	<div class="col-md-6 col-lg-4 col-xl-2">
		<?php include "blocks/block_full_client.php"; ?>

		<?php if($open_bill): ?>
		<div class="alert alert-danger" role="alert">
		Still open invoices :
			<ul>
		<?php foreach($open_bill as $bill): ?>
			<li><a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $bill['id']; ?>"><?php echo $bill['amount']; ?> &euro;</a></li>
		<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
		<a class="btn btn-outline-danger" href="<?php echo base_url() . 'debug/index/' . $owner['id']; ?>"><i class="fas fa-bug"></i> Debug</a>
	</div>

</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#owners").show();
	$("#clients").addClass('active');
});
</script>
