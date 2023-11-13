<div class="row">
	<div class="col-xl-12">
		<div class="card mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / 
				<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id'] ?>"><?php echo $pet['name'] ?></a> <small>(#<?php echo $pet['id']; ?>)</small> / <?php echo $this->lang->line('change_owner'); ?>
			</div>
			<div class="card-body">
			<table class="table">
			<tr>
				<th><?php echo $this->lang->line('current_owner'); ?></th>
				<th><?php echo $this->lang->line('new_owner'); ?></th>
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
					<a href="<?php echo base_url(); ?>owners/search"><?php echo $this->lang->line('client'); ?></a> / <?php echo $this->lang->line('title_search'); ?>
				</div>
				<div class="card-body">
				
				<ul class="nav nav-tabs" id="myTab" role="tablist">
				  <li class="nav-item">
					<a class="nav-link <?php echo (isset($name) || (!isset($name) && !isset($street) && !isset($phone) && !isset($client))) ? 'active' : ''; ?>" id="name-tab" data-toggle="tab" href="#name" role="tab" aria-controls="name" aria-selected="true"><?php echo $this->lang->line('name'); ?></a>
				  </li>
				  <li class="nav-item">
					<a class="nav-link <?php echo (isset($client)) ? 'active' : ''; ?>" id="clientid-tab" data-toggle="tab" href="#clientid" role="tab" aria-controls="clientid" aria-selected="false"><?php echo $this->lang->line('client_id'); ?></a>
				  </li>
				</ul>
				<div class="tab-content" id="myTabContent">
				  <div class="tab-pane fade <?php echo (isset($name) || (!isset($name) && !isset($street) && !isset($phone) && !isset($client))) ? 'show active' : ''; ?>" id="name" role="tabpanel" aria-labelledby="name-tab">
							<form action="<?php echo base_url(); ?>pets/change_owner/<?php echo $pet['id']; ?>" method="post" autocomplete="off" class="form-inline my-3">
								<label class="sr-only" for="last_name">Last Name</label>
								<input type="text" class="form-control mb-2 mr-sm-2 <?php echo (isset($name)) ? "is-valid" : "" ?>" id="name" name="name" autocomplete="false" placeholder="<?php echo (isset($name)) ? $name : "Last Name" ?>">
								<button type="submit" name="submit" value="name" class="btn btn-primary mb-2"><?php echo $this->lang->line('title_search'); ?></button>
							</form>
				  </div>
				  <div class="tab-pane fade <?php echo (isset($client)) ? 'show active' : ''; ?>" id="clientid" role="tabpanel" aria-labelledby="clientid-tab">
									<form action="<?php echo base_url(); ?>pets/change_owner/<?php echo $pet['id']; ?>" method="post" autocomplete="off" class="form-inline my-3">
										<label class="sr-only" for="last_name">Client id</label>
										<input type="text" class="form-control mb-2 mr-sm-2 <?php echo (isset($client)) ? "is-valid" : "" ?>" id="client" name="client" autocomplete="false" placeholder="<?php echo (isset($client)) ? $client : "client id" ?>">
										<button type="submit" name="submit" value="client" class="btn btn-primary mb-2"><?php echo $this->lang->line('title_search'); ?></button>
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
					<th><?php echo $this->lang->line('client_id'); ?></th>
					<th><?php echo $this->lang->line('last_name'); ?></th>
					<th><?php echo $this->lang->line('first_name'); ?></th>
					<th><?php echo $this->lang->line('adress'); ?></th>
					<th><?php echo $this->lang->line('city'); ?></th>
					<th><?php echo $this->lang->line('last_visit'); ?></th>
					<th><?php echo $this->lang->line('options'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($result as $res): ?>
				<tr>
					<td><?php echo $res['id']; ?></td>
					<td><?php echo $res['last_name']; ?></td>
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
						<a class="btn btn-outline-danger btn-sm" href="<?php echo base_url() . 'pets/change_owner/' . $pet['id'] . '/' . $res['id']; ?>"><i class="fas fa-exchange-alt"></i> <?php echo $this->lang->line('change'); ?></a>
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
	$("#clients").addClass('active');

	$("#dataTable").DataTable();
});
</script>