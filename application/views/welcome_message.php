<?php if(!is_bool($update_to_version)) : ?>
<div class="alert alert-success" role="alert">Upgraded database schema to version <?php echo $update_to_version; ?></div>
<?php endif; ?>

<!-- normal button : on small screens -->
<a href="<?php echo base_url('owners/add'); ?>" class="btn btn-success mb-3 d-block d-sm-none d-md-none"><i class="fa-solid fa-fw fa-user-plus"></i> <?php echo $this->lang->line('New_client'); ?></a>

<div class="row">
	<div class="col-lg-12">
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
					<div class="col-lg-3 d-none d-sm-block ">
						<div class="d-flex justify-content-end mb-3 mr-5">
							<a href="<?php echo base_url('owners/add'); ?>" class="btn btn-success btn-lg px-4 shadow-sm bounceit"><i class="fa-solid fa-user-plus me-2"></i> <?php echo $this->lang->line('New_client'); ?></a>
						</div>
						<img class="img-fluid" src="<?php echo base_url('assets/img/people_search.png'); ?>">
					</div>
				</div>
				</form>
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