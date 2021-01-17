<?php if(!is_bool($update_to_version)) : ?>
<div class="alert alert-success" role="alert">Upgraded database schema to version <?php echo $update_to_version; ?></div>
<?php endif; ?>


<div class="row">
	<div class="col-xl-3 col-md-5 mb-4">
		<?php if ($local_stock && count($local_stock) > 0): ?>
		<div class="card border-left-danger shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Stock Shortages (Local)</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($local_stock); ?> product(s)</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-cart-arrow-down fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
		<?php else: ?>
		<div class="card border-left-success shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-success text-uppercase mb-1">Stock Shortages (Local)</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800">OK</div>
					</div>
					<div class="col-auto">
						<i class="far fa-smile-beam fa-2x text-gray-600"></i>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
	
	<?php if (count($global_stock) > 0): ?>
	<div class="col-xl-3 col-md-5 mb-4">
		<div class="card border-left-danger shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Stock Shortages (Global)</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($global_stock); ?> product(s)</div>
					</div>
					<div class="col-auto">
						<i class="fas fa-cart-plus fa-2x text-gray-300"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
	
	<?php if ($bad_products && count($bad_products) > 0): ?>
	<div class="col-xl-3 col-md-5 mb-4">
		<div class="card border-left-info shadow h-100 py-2">
			<div class="card-body">
				<div class="row no-gutters align-items-center">
					<div class="col mr-2">
						<div class="text-xs font-weight-bold text-info text-uppercase mb-1">Expiring Stock (Local)</div>
						<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($bad_products); ?> product(s)</div>
					</div>
					<div class="col-auto">
						<span class="fa-stack ">
						  <i class="fas fa-prescription-bottle fa-stack-1x"></i>
						  <i class="fas fa-ban fa-stack-2x fa-2x" style="color:Tomato"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>

<div class="row">

	<div class="col-lg-8">
		<div class="card mb-4">
			<div class="card-header">
				Welcome
			</div>
			<div class="card-body">
				<img src="<?php echo base_url(); ?>assets/img/welcome_cat.png" class="img-fluid" />
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-lg-4">
		<div class="card mb-4">
			<div class="card-header">
				Vets
			</div>
			<div class="card-body">
 				<?php foreach($vets as $vet): ?>
				<div class="d-flex align-items-center justify-content-between mb-4">
					<div class="d-flex align-items-center flex-shrink-0 mr-3">
					<div class="avatar avatar-xl mr-3 bg-gray-200">
						<img class="rounded-circle" style="height: 2.5rem;width: 2.5rem;" src="<?php echo base_url(); ?>assets/public/<?php echo (!empty($vet['image'])) ? $vet['image'] : 'unknown.jpg'; ?>">
					</div>
						<div class="d-flex flex-column font-weight-bold">
						<a class="text-dark line-height-normal mb-1" href=""><?php echo $vet['first_name']; ?> <?php echo $vet['last_name']; ?></a>
							<div class="small text-muted line-height-normal">Last login <?php echo timespan($vet['last_login'], time(), 1); ?></div>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>

<?php if ($current_location == "none"): ?>
Please select your location :<br/><br/>
<div class="row">
	<?php foreach($locations as $l) : ?>
	
  <div class="col-sm-3"><a class="btn btn-warning" href="<?php echo base_url() . '/welcome/change_location/' . $l['id']; ?>"><?php echo $l['name'] ?></a></div>
	<?php endforeach; ?>
</div>
<?php endif; ?>