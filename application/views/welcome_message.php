<?php if(!is_bool($update_to_version)) : ?>
<div class="alert alert-success" role="alert">Upgraded database schema to version <?php echo $update_to_version; ?></div>
<?php endif; ?>

<div class="row">
	<div class="col-xl-8 col-lg-8">

		<div class="card mb-4">
			<div class="card-header">
				Welcome
			</div>
			<div class="card-body">

<div class="list-group">

<?php if ($local_stock && count($local_stock) > 0): ?>
  <a href="<?php echo base_url(); ?>stock/limit" class="list-group-item list-group-item-action list-group-hack">
    <div class="d-flex w-100 justify-content-between">
		<h5 class="mb-1">Local Stock Shortages</h5>
		<small class="text-muted"></small>
    </div>
    <p class="mb-1 text-danger"><?php echo count($local_stock); ?> product(s) are below required amount.</p>
  </a>
<?php endif; ?>

<?php if (count($global_stock) > 0): ?>
  <a href="<?php echo base_url(); ?>stock/limit" class="list-group-item list-group-item-action list-group-hack">
    <div class="d-flex w-100 justify-content-between">
		<h5 class="mb-1">Global Stock Shortages</h5>
		<small class="text-muted"></small>
    </div>
    <p class="mb-1 text-danger"><?php echo count($global_stock); ?> product(s) are below required amount.</p>
  </a>
<?php endif; ?>

<?php if ($bad_products && count($bad_products) > 0): ?>
  <a href="<?php echo base_url(); ?>stock/expired_stock" class="list-group-item list-group-item-action list-group-hack">
    <div class="d-flex w-100 justify-content-between">
		<h5 class="mb-1">Expiring Local Stock</h5>
		<small class="text-muted"></small>
    </div>
    <p class="mb-1 text-danger"><?php echo count($bad_products); ?> product(s) are about to expire.</p>
  </a>
<?php endif; ?>
</div>

				<div class="row">
					<div class="col-lg-6">
						<img src="<?php echo base_url(); ?>assets/img/cat_0<?php echo date('N') % 6; ?>.png" class="img-fluid" />
					</div>
					<div class="col-lg-6">
						<br/>
						<br/>
						<h5>Bad joke of the day</h5>
						<?php
							$var =  $this->lang->line('bad_joke_of_the_day');
							echo "<b>". $var[0] ."</b><br>" . $var[1];
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-4 col-lg-4">
		<div class="card mb-4">
			<div class="card-header">
				Vets
			</div>
			<div class="card-body">
				<?php foreach($vets as $vet): ?>
				<div class="d-flex align-items-center justify-content-between mb-4">
					<div class="d-flex align-items-center flex-shrink-0 mr-3">
						<div class="avatar avatar-xl mr-3 bg-gray-200">
							<img class="rounded img-fluid" style="max-width: 5rem;" src="<?php echo base_url(); ?>assets/public/<?php echo (!empty($vet['image']) && is_readable('assets/public/' . $vet['image'])) ? $vet['image'] : 'unknown.jpg'; ?>">
						</div>
						<div class="d-flex flex-column font-weight-bold">
						<a class="text-dark line-height-normal mb-1" href="<?php echo base_url(); ?>vet/pub/<?php echo $vet['id']; ?>"><?php echo $vet['first_name']; ?> <?php echo $vet['last_name']; ?></a>
							<div class="small text-muted line-height-normal">Last login <?php echo timespan($vet['last_login'], time(), 1); ?></div>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
<!-- row -->
</div>

<?php if ($current_location == "none"): ?>
Please select your location :<br/><br/>
<div class="row">
	<?php foreach($locations as $l) : ?>

  <div class="col-sm-3"><a class="btn btn-warning" href="<?php echo base_url() . '/welcome/change_location/' . $l['id']; ?>"><?php echo $l['name'] ?></a></div>
	<?php endforeach; ?>
</div>
<?php endif; ?>
