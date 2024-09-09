<?php if(!is_bool($update_to_version)) : ?>
<div class="alert alert-success" role="alert">Upgraded database schema to version <?php echo $update_to_version; ?></div>
<?php endif; ?>

<style>
.card-waves .card-body {
    background-image: url("<?php echo base_url('assets/img/bg-waves-2.svg'); ?>");
	width: 100%;
}
</style>

<!-- normal button : on small screens -->
<a href="<?php echo base_url('owners/add'); ?>" class="btn btn-success mb-3 d-block d-sm-none d-md-none"><i class="fa-solid fa-fw fa-user-plus"></i> <?php echo $this->lang->line('New_client'); ?></a>

<div class="row">
	<div class="col-lg-9">
		<div class="card card-waves shadow h-100">
			<div class="card-body">
				<form action="<?php echo base_url('search'); ?>" method="get" autocomplete="off">
				<div class="row align-items-center justify-content-between px-3">
					<div class="col-lg-8">
						<h3 class="text-primary"><?php echo $this->lang->line('title_search'); ?></h3>
						
						<div class="d-none d-sm-block">
							<p class="lead mb-4"><?php echo $this->lang->line('search_help'); ?></p>
						</div>
						<div class="shadow rounded">
						  <div class="form-group has-search">
							<span class="fa fa-search form-control-feedback"></span>
							 <div class="input-group">
								<input type="text" class="form-control" name="search_query" placeholder="search" value="" id="client_search">
								<div class="input-group-append">
								  <button class="btn btn-primary" type="submit" type="button">
									<div class="d-none d-sm-block"><?php echo $this->lang->line('title_search'); ?></div>
									<div class="d-block d-sm-none d-md-none">S</div>
								  </button>
								</div>
							</div>
						  </div>
						</div>
					</div>
					<div class="col-lg-3">
						<img class="img-fluid" src="<?php echo base_url('assets/img/people_search.png'); ?>">
					</div>
				</div>
				</form>
			</div>
		</div>					
	</div>
	<!-- large button : on large screens -->
	<div class="col-lg-3">
		<a href="<?php echo base_url('owners/add'); ?>" class="btn btn-success btn-lg mb-3 d-none d-sm-block bounceit"><i class="fa-solid fa-fw fa-user-plus"></i> <?php echo $this->lang->line('New_client'); ?></a>
			<div class="card shadow">
				<div class="card-body">
					<?php foreach($vets as $vet): if ($vet['id'] == $this->user->id) { continue; } ?>
					<div class="d-flex align-items-center justify-content-between mb-4">
						<div class="d-flex align-items-center flex-shrink-0 mr-3">
							<div class="avatar avatar-<?php echo get_online_status($vet['last_login']); ?> mr-3 bg-gray-200">
							<img class="rounded-circle img-fluid" src="<?php echo base_url('assets/public/' .  ((!empty($vet['image']) && is_readable('assets/public/' . $vet['image'])) ? $vet['image'] : 'unknown.jpg')); ?>">
							</div>
							<div class="d-flex flex-column font-weight-bold">
								<a class="text-dark line-height-normal mb-1" href="<?php echo base_url('vet/pub/' . $vet['id']); ?>"><?php echo substr($vet['first_name'], 0, 1); ?>. <?php echo $vet['last_name']; ?></a>
								<div class="small text-muted line-height-normal"><?php echo $this->lang->line('vet'); ?></div>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#home").addClass('active');
	$("#client_search").focus();
});
</script>