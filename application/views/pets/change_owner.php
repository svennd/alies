<div class="row">
	<div class="col-lg-7 col-xl-10">
		<div class="card mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / 
				<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id'] ?>"><?php echo $pet['name'] ?></a> <small>(#<?php echo $pet['id']; ?>)</small> / Change owner
			</div>
			<div class="card-body">
			<table class="table">
			<tr>
				<th>Current owner</th>
				<th>New owner</th>
			</tr>
			<tr>
				<td>
					<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>" class="<?php echo ($owner['debts']) ? 'text-danger' : 'text-primary'; ?>"><?php echo $owner['last_name']. "&nbsp;" . $owner['first_name']; ?></a>
					<br><?php echo $owner['street'] . ' ' . $owner['nr']. ' ' .  $owner['city']; ?><br>
					<?php if ($owner['telephone']) : ?><abbr title="Phone">P:</abbr> <?php echo $owner['telephone']; ?><br/><?php endif; ?>
					<?php if ($owner['mobile']) : ?><abbr title="Mobile">M:</abbr> <?php echo $owner['mobile']; ?><br/><?php endif; ?>
				</td>
				<td>
					<?php if ( $new_owner ) : ?>
						<a href="<?php echo base_url(); ?>owners/detail/<?php echo $new_owner['id']; ?>" class="<?php echo ($new_owner['debts']) ? 'text-danger' : 'text-primary'; ?>"><?php echo $new_owner['last_name']. "&nbsp;" . $new_owner['first_name']; ?></a>
						<br><?php echo $new_owner['street'] . ' ' . $new_owner['nr']. ' ' .  $new_owner['city']; ?><br>
						<?php if ($new_owner['telephone']) : ?><abbr title="Phone">P:</abbr> <?php echo $new_owner['telephone']; ?><br/><?php endif; ?>
						<?php if ($new_owner['mobile']) : ?><abbr title="Mobile">M:</abbr> <?php echo $new_owner['mobile']; ?><br/><?php endif; ?>
					<?php endif; ?>
				</td>
			</tr>
			</table>
			<?php if ( $new_owner ) : ?>
				<a href="<?php echo base_url(); ?>/pets/change_owner_complete/<?php echo $pet['id']; ?>/<?php echo $new_owner['id']; ?>" class="btn btn-outline-danger">Complete transfer</a>
			<?php endif; ?>
			</div>
		</div>
	
	<?php if ( !$new_owner ) : ?>
	<div class="row">
		<div class="col-lg-6 mb-4">
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
					<a class="nav-link <?php echo (isset($client)) ? 'active' : ''; ?>" id="clientid-tab" data-toggle="tab" href="#clientid" role="tab" aria-controls="clientid" aria-selected="false">Client ID</a>
				  </li>
				</ul>
				<div class="tab-content" id="myTabContent">
				  <div class="tab-pane fade <?php echo (isset($name) || (!isset($name) && !isset($street) && !isset($phone) && !isset($client))) ? 'show active' : ''; ?>" id="name" role="tabpanel" aria-labelledby="name-tab">
							<form action="<?php echo base_url(); ?>pets/change_owner/<?php echo $pet['id']; ?>" method="post" autocomplete="off" class="form-inline my-3">
								<label class="sr-only" for="last_name">Last Name</label>
								<input type="text" class="form-control mb-2 mr-sm-2 <?php echo (isset($name)) ? "is-valid" : "" ?>" id="name" name="name" autocomplete="false" placeholder="<?php echo (isset($name)) ? $name : "Last Name" ?>">
								<button type="submit" name="submit" value="name" class="btn btn-primary mb-2">Search</button>
							</form>
				  </div>
				  <div class="tab-pane fade <?php echo (isset($street)) ? 'show active' : ''; ?>" id="street" role="tabpanel" aria-labelledby="street-tab">
									<form action="<?php echo base_url(); ?>pets/change_owner/<?php echo $pet['id']; ?>" method="post" autocomplete="off" class="form-inline my-3">
										<label class="sr-only" for="last_name">Street</label>
										<input type="text" class="form-control mb-2 mr-sm-2 <?php echo (isset($street)) ? "is-valid" : "" ?>" id="street" name="street" autocomplete="false" placeholder="<?php echo (isset($street)) ? $street : "Street" ?>">
										<button type="submit" name="submit" value="street" class="btn btn-primary mb-2">Search</button>
									</form>
				  </div>
				  <div class="tab-pane fade <?php echo (isset($client)) ? 'show active' : ''; ?>" id="clientid" role="tabpanel" aria-labelledby="clientid-tab">
									<form action="<?php echo base_url(); ?>pets/change_owner/<?php echo $pet['id']; ?>" method="post" autocomplete="off" class="form-inline my-3">
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
	<?php endif; ?>
	
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
						<?php echo $res['last_bill']; ?>
					</td>
					<td>
						<a class="btn btn-outline-secondary" href="<?php echo base_url() . 'pets/change_owner/' . $pet['id'] . '/' . $res['id']; ?>"><i class="fas fa-exchange-alt"></i> Change</a>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<div class="col-lg-5 col-xl-2">
		<h3>Current Owner</h3>
		<?php include "fiche/block_client.php"; ?>
	</div>
</div>
