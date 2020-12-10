
<div class="row">
	<div class="col-lg-12 mb-4">
	
	<div class="row">
		<div class="col-lg-12 mb-4">
			<div class="card mb-4">
				<div class="card-header">
					<a href="<?php echo base_url(); ?>owners/search">Client</a> / Search
				</div>
				<div class="card-body">
				
				<ul class="nav nav-tabs" id="myTab" role="tablist">
				  <li class="nav-item">
					<a class="nav-link <?php echo (isset($name) || (!isset($name) && !isset($street) && !isset($phone) && !isset($client))) ? 'active' : ''; ?>" id="name-tab" data-toggle="tab" href="#name" role="tab" aria-controls="name" aria-selected="true">Name</a>
				  </li>
				  <li class="nav-item">
					<a class="nav-link <?php echo (isset($street)) ? 'active' : ''; ?>" id="street-tab" data-toggle="tab" href="#street" role="tab" aria-controls="street" aria-selected="false">Street</a>
				  </li>
				  <li class="nav-item">
					<a class="nav-link <?php echo (isset($phone)) ? 'active' : ''; ?>" id="phone-tab" data-toggle="tab" href="#phone" role="tab" aria-controls="phone" aria-selected="false">Phone</a>
				  </li>
				  <li class="nav-item">
					<a class="nav-link <?php echo (isset($client)) ? 'active' : ''; ?>" id="clientid-tab" data-toggle="tab" href="#clientid" role="tab" aria-controls="clientid" aria-selected="false">Client ID</a>
				  </li>
				</ul>
				<div class="tab-content" id="myTabContent">
				  <div class="tab-pane fade <?php echo (isset($name) || (!isset($name) && !isset($street) && !isset($phone) && !isset($client))) ? 'show active' : ''; ?>" id="name" role="tabpanel" aria-labelledby="name-tab">
							<form action="<?php echo base_url(); ?>owners/search" method="post" autocomplete="off" class="form-inline my-3">
								<label class="sr-only" for="last_name">Last Name</label>
								<input type="text" class="form-control mb-2 mr-sm-2 <?php echo (isset($name)) ? "is-valid" : "" ?>" id="name" name="name" autocomplete="false" placeholder="<?php echo (isset($name)) ? $name : "Last Name" ?>">
								<button type="submit" name="submit" value="name" class="btn btn-primary mb-2">Search</button>
							</form>
				  </div>
				  <div class="tab-pane fade <?php echo (isset($street)) ? 'show active' : ''; ?>" id="street" role="tabpanel" aria-labelledby="street-tab">
									<form action="<?php echo base_url(); ?>owners/search" method="post" autocomplete="off" class="form-inline my-3">
										<label class="sr-only" for="last_name">Street</label>
										<input type="text" class="form-control mb-2 mr-sm-2 <?php echo (isset($street)) ? "is-valid" : "" ?>" id="street" name="street" autocomplete="false" placeholder="<?php echo (isset($street)) ? $street : "Street" ?>">
										<button type="submit" name="submit" value="street" class="btn btn-primary mb-2">Search</button>
									</form>
				  </div>
				  <div class="tab-pane fade <?php echo (isset($phone)) ? 'show active' : ''; ?>" id="phone" role="tabpanel" aria-labelledby="phone-tab">
									<form action="<?php echo base_url(); ?>owners/search" method="post" autocomplete="off" class="form-inline my-3">
										<label class="sr-only" for="last_name">Phone</label>
										<input type="text" class="form-control mb-2 mr-sm-2 <?php echo (isset($phone)) ? "is-valid" : "" ?>" id="phone" name="phone" autocomplete="false" placeholder="<?php echo (isset($phone)) ? $phone : "Phone" ?>">
										<button type="submit" name="submit" value="phone" class="btn btn-primary mb-2">Search</button>
									</form>
				  </div>
				  <div class="tab-pane fade <?php echo (isset($client)) ? 'show active' : ''; ?>" id="clientid" role="tabpanel" aria-labelledby="clientid-tab">
									<form action="<?php echo base_url(); ?>owners/search" method="post" autocomplete="off" class="form-inline my-3">
										<label class="sr-only" for="last_name">Phone</label>
										<input type="text" class="form-control mb-2 mr-sm-2 <?php echo (isset($client)) ? "is-valid" : "" ?>" id="client" name="client" autocomplete="false" placeholder="<?php echo (isset($client)) ? $client : "client id" ?>">
										<button type="submit" name="submit" value="client" class="btn btn-primary mb-2">Search</button>
									</form>
				  </div>
				</div>


				</div>
			</div>
		</div>
	</div>
	
	<?php if (isset($result) && $result): ?>
	<div class="card mb-4">
		<div class="card-header">Search results</div>
		<div class="card-body">
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Last Name</th>
					<th>First Name</th>
					<th>Adress</th>
					<th>City</th>
					<th>Pet(s)</th>
					<th>Last Visit</th>
					<th>option</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($result as $res): ?>
				<tr>
					<td>
						<a href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>" class="text-nowrap">
							<?php echo $res['last_name']; ?>
						</a>
					</td>
					<td><?php echo $res['first_name']; ?></td>
					<td>
					<div class="row">
						<div class="col">
						<?php echo $res['street']; ?>
						</div>
						<div class="col">
						<?php echo $res['nr']; ?>
						</div>
					</div>
					</td>
					<td>
						<?php echo $res['city']; ?>
					</td>
					<td>
						<?php
						if (isset($res['pets']))
						{
							if ($res['pets'])
							{
								if (count($res['pets']) == 1)
								{
									echo $res['pets']['0']['name'];
								}
								else									
								{
									// we don't do all
									// echo count($res['pets']);
									echo "2+";
								}
							}
						}
						?>
					</td>
					<td>
						<?php echo $res['last_bill']; ?>
					</td>
					<td>
						<a class="btn btn-outline-secondary" href="<?php echo base_url() . 'owners/edit/' . $res['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
						<a class="btn btn-outline-secondary" href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>"><i class="fas fa-user"></i> Client</a>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#owners").show();
	$("#clients").addClass('active');
	$("#client_search").addClass('active');
	var dt = $("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]],
	  "columnDefs": [
		{ "type": "date", "targets": 5 }
	  ]
	});
	dt
    .order( [ 5, 'desc' ] )
    .draw();
});
</script>