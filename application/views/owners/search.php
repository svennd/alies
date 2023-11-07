<style>
.card-waves .card-body {
    background-image: url("<?php echo base_url(); ?>/assets/img/bg-waves.svg");
}
.card-waves .card-body, .card-angles .card-body {
    background-size: 100% auto;
    background-repeat: no-repeat;
    background-position: center bottom;
}
.nav-borders .nav-link.active {
    color: #0061f2;
    border-bottom-color: #0061f2;
}
.nav-borders .nav-link:hover {
    border-bottom-color: #aebdd4;
}
.nav-borders .nav-link {
    color: #69707a;
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
</style>

<!-- normal button : on small screens -->
<a href="<?php echo base_url(); ?>owners/add" class="btn btn-success mb-3 d-block d-sm-none d-md-none"><i class="fas fa-user"></i> <?php echo $this->lang->line('New_client'); ?></a>

<div class="row">
	
	<div class="col-lg-10">
		<div class="card card-waves shadow mb-4">
			<div class="card-body px-3 pt-3 pb-0">
				<form action="<?php echo base_url(); ?>search" method="post" autocomplete="off">
				<div class="row align-items-center justify-content-between px-3">
					<div class="col-lg-8">
						<?php if(!isset($query)): ?>
						<h3 class="text-primary"><a href="<?php echo base_url('search'); ?>"><?php echo $this->lang->line('title_search'); ?></a></h3>
						
						<div class="d-none d-sm-block">
							<p class="lead mb-4"><?php echo $this->lang->line('search_help'); ?></p>
						</div>
						<?php endif; ?>
						<div class="shadow rounded">
						  <div class="form-group has-search">
							<span class="fa fa-search form-control-feedback"></span>
							 <div class="input-group">
								<input type="text" class="form-control <?php echo (isset($query)) ? 'is-valid' :''?>" name="search_query" placeholder="search" value="<?php echo (isset($query)) ? $query :''?>">
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
					<?php if(!isset($query)): ?>
					<div class="col-lg-3">
						<img class="img-fluid" src="<?php echo base_url(); ?>assets/img/people_search.png">
					</div>
					<?php endif; ?>
				</div>
				</form>
				<?php if (isset($query)) : ?>
					<nav class="nav nav-borders">
						<?php 
							$results = array(
													array($last_name, $this->lang->line('last_name'), 'last_name'), 
													array($first_name, $this->lang->line('first_name'), 'first_name'), 
													array($street, $this->lang->line('street'), 'street'), 
													array($pets, $this->lang->line('pets'), 'pets'), 
													array($phone, $this->lang->line('phone'), 'phone'),
													array($breeds, $this->lang->line('breed'), 'breed'),
													);
							$active_switch = false;
							$total_count = 0;
							foreach ($results as $key => $value) 
							{
								$current_count = count($value[0]);
								$total_count += $current_count;
								
								$current_key = $key + 1; // 0 --> all
								
								if (!$current_count) {continue;}
								
								if ($user->search_config == $current_key)
								{
									$active_switch = true;
									echo '<a href="#" class="nav-link filter_type active" id="' . $value[2] . '">' . $value[1] . ' <span class="badge badge-primary">' . $current_count . '</span></a>';
								}
								else
								{
									echo '<a href="#" class="nav-link filter_type" id="' . $value[2] . '">' . $value[1] . ' <span class="badge badge-primary">' . $current_count . '</span></a>';
								}
							}
							echo '<a href="#" class="nav-link ' . ((!$active_switch) ? "active" : "") .' reset" id="reset_filter">' . $this->lang->line('search_all') . ' <span class="badge badge-primary">' . $total_count . '</span></a>';
						?>
					</nav>
				<?php endif; ?>
			</div>
		</div>					
	</div>	
	<!-- large button : on large screens -->
	<div class="col-lg-2 mb-4">
		<a href="<?php echo base_url(); ?>owners/add" class="btn btn-success btn-lg mb-3 d-none d-sm-block"><i class="fas fa-user"></i> <?php echo $this->lang->line('New_client'); ?></a>
		<?php if (!isset($query) && $this->ion_auth->in_group("admin")): ?>
		<div class="card border-danger my-3">
			<div class="card-header text-danger">Client Export (admin)</div>
			<div class="card-body">
				<a href="<?php echo base_url(); ?>export/clients/90" class="btn btn-success btn-sm" download><i class="fas fa-file-export"></i> <?php echo $this->lang->line('export_client_90d'); ?></a>
				<a href="<?php echo base_url(); ?>export/clients" class="btn btn-primary btn-sm ml-3" download><i class="fas fa-file-export"></i> <?php echo $this->lang->line('export_client_all'); ?></a><br/>
				<?php echo $this->lang->line('export_clients'); ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>

<div class="row">

	<div class="col-lg-12">
	<?php if (isset($query)): ?>
		<div class="card shadow mb-4">
			<div class="card-header"><?php echo $this->lang->line('search_client'); ?></div>
			<div class="card-body">
		<table class="table table-bordered table-hover display dt-responsive nowrap" width="100%" id="dataTable">
		<thead>
		<tr>
			<th>Q</th>
			<th><?php echo $this->lang->line('last_name'); ?></th>
			<th><?php echo $this->lang->line('first_name'); ?></th>
			<th><?php echo $this->lang->line('adress'); ?></th>
			<th>nr</th>
			<th><?php echo $this->lang->line('city'); ?></th>
			<?php if (count($phone)): ?>
			<th><?php echo $this->lang->line('phone'); ?></th>
			<?php endif; ?>
			<th><?php echo $this->lang->line('last_visit'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($last_name as $res): ?>
		<tr class="<?php if(!$res['last_bill'] || $res['disabled']): ?>table-secondary<?php endif; ?>">
			<td>last_name</td>
			<td>
				<a href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>" class="text-nowrap">
					<?php echo $res['last_name']; ?>
				</a>
			</td>
			<td><?php echo $res['first_name']; ?></td>
			<td><?php echo $res['street']; ?></td>
			<td><?php echo $res['nr']; ?></td>
			<td><?php echo $res['city']; ?></td>
			<?php if (count($phone)): ?>
			<td>
				<?php echo (!empty($res['telephone'])) ? $res['telephone'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['mobile'])) ? $res['mobile'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['phone2'])) ? $res['phone2'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['phone3'])) ? $res['phone3'] . '<br/>' : ''; ?>
			</td>
			<?php endif; ?>
			<td><?php echo $res['last_bill']; ?></td>
		</tr>
		<?php endforeach; ?>
		<?php foreach ($first_name as $res): ?>
		<tr>
			<td>first_name</td>
			<td>
				<a href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>" class="text-nowrap">
					<?php echo $res['last_name']; ?>
				</a>
			</td>
			<td><?php echo $res['first_name']; ?></td>
			<td><?php echo $res['street']; ?></td>
			<td><?php echo $res['nr']; ?></td>
			<td><?php echo $res['city']; ?></td>
			<?php if (count($phone)): ?>
			<td>
				<?php echo (!empty($res['telephone'])) ? $res['telephone'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['mobile'])) ? $res['mobile'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['phone2'])) ? $res['phone2'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['phone3'])) ? $res['phone3'] . '<br/>' : ''; ?>
			</td>
			<?php endif; ?>
			<td><?php echo $res['last_bill']; ?></td>
		</tr>
		<?php endforeach; ?>
		<?php foreach ($street as $res): ?>
		<tr>
			<td>street</td>
			<td>
				<a href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>" class="text-nowrap">
					<?php echo $res['last_name']; ?>
				</a>
			</td>
			<td><?php echo $res['first_name']; ?></td>
			<td><?php echo $res['street']; ?></td>
			<td><?php echo $res['nr']; ?></td>
			<td><?php echo $res['city']; ?></td>
			<?php if (count($phone)): ?>
			<td>
				<?php echo (!empty($res['telephone'])) ? $res['telephone'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['mobile'])) ? $res['mobile'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['phone2'])) ? $res['phone2'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['phone3'])) ? $res['phone3'] . '<br/>' : ''; ?>
			</td>
			<?php endif; ?>
			<td><?php echo $res['last_bill']; ?></td>
		</tr>
		<?php endforeach; ?>
		<?php foreach ($pets as $res): ?>
		<tr>
			<td>pets</td>
			<td>
				<a href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>" class="text-nowrap">
					<?php echo $res['last_name']; ?>
				</a>
				<small>(<i class="fas fa-fw fa-dog"></i><?php echo $res['name'] ?>)</small>
			</td>
			<td><?php echo $res['first_name']; ?></td>
			<td><?php echo $res['street']; ?></td>
			<td><?php echo $res['nr']; ?></td>
			<td><?php echo $res['city']; ?></td>
			<?php if (count($phone)): ?>
			<td>
				<?php echo (!empty($res['telephone'])) ? $res['telephone'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['mobile'])) ? $res['mobile'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['phone2'])) ? $res['phone2'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['phone3'])) ? $res['phone3'] . '<br/>' : ''; ?>
			</td>
			<?php endif; ?>
			<td><?php echo $res['last_bill']; ?></td>
		</tr>
		<?php endforeach; ?>
		<?php foreach ($phone as $res): ?>
		<tr>
			<td>phone</td>
			<td>
				<a href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>" class="text-nowrap">
					<?php echo $res['last_name']; ?>
				</a>
			</td>
			<td><?php echo $res['first_name']; ?></td>
			<td><?php echo $res['street']; ?></td>
			<td><?php echo $res['nr']; ?></td>
			<td><?php echo $res['city']; ?></td>
			<?php if (count($phone)): ?>
			<td>
				<?php echo (!empty($res['telephone'])) ? $res['telephone'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['mobile'])) ? $res['mobile'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['phone2'])) ? $res['phone2'] . '<br/>' : ''; ?>
				<?php echo (!empty($res['phone3'])) ? $res['phone3'] . '<br/>' : ''; ?>
			</td>
			<?php endif; ?>
			<td><?php echo $res['last_bill']; ?></td>
		</tr>
		<?php endforeach; ?>
		<?php foreach ($breeds as $breed): ?>
		<tr>
			<td>breeds</td>
			<td>
				<a href="<?php echo base_url() . 'owners/detail/' . $breed['id']; ?>" class="text-nowrap">
					<?php echo $breed['last_name']; ?>
				</a>
				<small>(<i class="fas fa-fw fa-dog"></i><?php echo $breed['name'] ?>, <strong><?php echo $breed['breed']; ?></strong>)</small>
			</td>
			<td><?php echo $breed['first_name']; ?></td>
			<td><?php echo $breed['street']; ?></td>
			<td><?php echo $breed['nr']; ?></td>
			<td><?php echo $breed['city']; ?></td>
			<?php if (count($phone)): ?>
			<td>
				<?php echo (!empty($breed['telephone'])) ? $breed['telephone'] . '<br/>' : ''; ?>
				<?php echo (!empty($breed['mobile'])) ? $breed['mobile'] . '<br/>' : ''; ?>
				<?php echo (!empty($breed['phone2'])) ? $breed['phone2'] . '<br/>' : ''; ?>
				<?php echo (!empty($breed['phone3'])) ? $breed['phone3'] . '<br/>' : ''; ?>
			</td>
			<?php endif; ?>
			<td><?php echo $breed['last_bill']; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
	</table>
		<?php endif; ?>
		</div>
		</div>
	</div>				
</div>				

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#clients").addClass('active');
	
	var dt = $("#dataTable").DataTable({
		"responsive": {
        	"details": {
            "type": 'column',
            "target": 'tr'
        }
  	  },
	  "columnDefs": [
		{ "responsivePriority": 1, "targets": 1 },
		{ "type": "date", "targets": 5 },
		{ "targets": [ 0 ], "visible": false }
	  ]
	});
	dt
    .order( [ 6, 'desc' ] )
    .draw();	
	
	// initial filter if required
	if($('.filter_type.active').attr('id')) {
		dt
		.column(0)
        .search($('.filter_type.active').attr('id'))
        .draw();
	}
	
    $('.filter_type').on('click', function() {
	  // remove color from filter
	  $('#reset_filter').removeClass('active');
	  
	  // reset all colors
	  $('.filter_type').removeClass('active');
	  
	  // add success to current filter
	  $(this).removeClass('btn-info').addClass('active')
	  
      dt
		.column(0)
        .search($(this).attr('id'))
        .draw();
    });

    $('.reset').on('click', function() {
		
	  // reset all colors
	  $('.filter_type').removeClass('active');
	  
	  // color reset
	  $(this).addClass('active');
		
      dt
		.column(0)
        .search('')
        .draw();
    });
});
</script>
