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

<div class="row">
	<div class="col-lg-10">
		<div class="card card-waves shadow mb-4">
			<div class="card-body px-3 pt-3 pb-0">
				<form action="<?php echo base_url(); ?>search" method="post" autocomplete="off">
				<div class="row align-items-center justify-content-between px-3">
					<div class="col-lg-8">
						<h3 class="text-primary"><a href="<?php echo base_url('search'); ?>">Search</a></h3>
						<p class="lead mb-4">Search the database using (first) name, street, phone, pet id, pet chip nr, pet name.</p>
						<div class="shadow rounded">
						  <div class="form-group has-search">
							<span class="fa fa-search form-control-feedback"></span>
							 <div class="input-group">
								<input type="text" class="form-control <?php echo (isset($query)) ? 'is-valid' :''?>" name="search_query" placeholder="search" value="<?php echo (isset($query)) ? $query :''?>">
								<div class="input-group-append">
								  <button class="btn btn-primary" type="button">
									Search
								  </button>
								</div>
							</div>
						  </div>
						</div>
					</div>
					<div class="<?php echo (isset($query))? 'col-lg-1' : 'col-lg-3' ?>"><img class="img-fluid" src="<?php echo base_url(); ?>assets/img/people_search.png"></div>
				</div>
				</form>
				<?php if (isset($query)) : ?>
					<nav class="nav nav-borders">
					  <?php if(count($last_name)): ?><a href="#" class="nav-link filter_type" id="last_name">Last Name <?php echo (count($last_name)) ? '<span class="badge badge-primary">' . count($last_name) . '</span>' : ''; ?></a><?php endif; ?>
					  <?php if(count($first_name)): ?><a href="#" class="nav-link filter_type" id="first_name">First Name <?php echo (count($first_name)) ? '<span class="badge badge-primary">' . count($first_name) . '</span>' : ''; ?></a><?php endif; ?>
					  <?php if(count($street)): ?><a href="#" class="nav-link filter_type" id="street">Street <?php echo (count($street)) ? '<span class="badge badge-primary">' . count($street) . '</span>' : ''; ?></a><?php endif; ?>
					  <?php if(count($pets)): ?><a href="#" class="nav-link filter_type" id="pets">Pets <?php echo (count($pets)) ? '<span class="badge badge-primary">' . count($pets) . '</span>' : ''; ?></a><?php endif; ?>
					  <?php if(count($phone)): ?><a href="#" class="nav-link filter_type" id="phone">Phone <?php echo (count($phone)) ? '<span class="badge badge-primary">' . count($phone) . '</span>' : ''; ?></a><?php endif; ?>
					  <a href="#" class="nav-link active reset" id="reset_filter">All <span class="badge badge-primary"><?php echo count($last_name) + count($first_name) + count($pets) + count($street) + count($phone); ?></span></a>
					</nav>
				<?php endif; ?>
			</div>
		</div>					
	</div>	
	<div class="col-lg-2 mb-4">
		<a href="<?php echo base_url(); ?>owners/add" class="btn btn-success btn-lg mb-3"><i class="fas fa-user"></i> New Client</a>
	</div>	
</div>

<div class="row">

	<div class="col-lg-12">
	
<?php if (isset($query)): ?>
		<div class="card shadow mb-4">
			<div class="card-header">Results</div>
			<div class="card-body">
<table class="table table-bordered table-hover" id="dataTable">
		<thead>
		<tr>
			<th>search_query</th>
			<th>Last Name</th>
			<th>First Name</th>
			<th>Adress</th>
			<th>nr</th>
			<th>City</th>
			<?php if (count($phone)): ?>
			<th>Phone</th>
			<?php endif; ?>
			<th>Last Visit</th>
			<th>option</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($last_name as $res): ?>
		<tr>
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
			<td>
				<a class="btn btn-outline-secondary btn-sm" href="<?php echo base_url() . 'owners/edit/' . $res['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
				<a class="btn btn-outline-success btn-sm" href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>"><i class="fas fa-user"></i> Client</a>
			</td>
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
			<td>
				<a class="btn btn-outline-secondary btn-sm" href="<?php echo base_url() . 'owners/edit/' . $res['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
				<a class="btn btn-outline-success btn-sm" href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>"><i class="fas fa-user"></i> Client</a>
			</td>
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
			<td>
				<a class="btn btn-outline-secondary btn-sm" href="<?php echo base_url() . 'owners/edit/' . $res['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
				<a class="btn btn-outline-success btn-sm" href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>"><i class="fas fa-user"></i> Client</a>
			</td>
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
			<td>
				<a class="btn btn-outline-secondary btn-sm" href="<?php echo base_url() . 'owners/edit/' . $res['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
				<a class="btn btn-outline-success btn-sm" href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>"><i class="fas fa-user"></i> Client</a>
			</td>
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
			<td>
				<a class="btn btn-outline-secondary btn-sm" href="<?php echo base_url() . 'owners/edit/' . $res['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
				<a class="btn btn-outline-success btn-sm" href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>"><i class="fas fa-user"></i> Client</a>
			</td>
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
	
	var dt = $("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]],
	  "columnDefs": [
		{ "type": "date", "targets": 5 },
		{ "targets": [ 0 ], "visible": false }
	  ]
	});
	dt
    .order( [ 0, 'asc' ] )
    .draw();	

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