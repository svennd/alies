<div class="row">
	<div class="col-lg-12">
		<div class="card mb-4">
			<div class="card-header">
				Vet / <?php echo $profile['first_name'] . " " . $profile['last_name']; ?>
			</div>
			<div class="card-body">
			<div class="row">
			<div class="col">
				<img class="img-profile rounded" src="<?php echo base_url() . 'assets/public/' . (!empty($profile['image']) ? $profile['image'] : 'unknown.jpg' ) ; ?>" />
			</div>
			<div class="col">
			<table class="table">
				<tr>
					<td>Email</td>
					<td><?php echo $profile['email']; ?></td>
				</tr>
				<tr>
					<td>Phone</td>
					<td><?php echo $profile['phone']; ?></td>
				</tr>
				<tr>
					<td>Last update</td>
					<td><?php echo $profile['updated_at']; ?></td>
				</tr>
				<tr>
					<td>Last login</td>
					<td><?php echo $profile['last_login']; ?></td>
				</tr>
				<tr>
					<td>Sidebar</td>
					<td><svg width="20" height="20"><rect width="20" height="20" style="fill:<?php echo $profile['sidebar'] ?>;stroke-width:1;stroke:rgb(0,0,0)" /></svg></td>
				</tr>
			</table>
			</div>
			</div>
			</div>
		</div>
	</div>
</div>