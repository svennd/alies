<div class="card shadow mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
	<div>Users</div>
		<div class="dropdown no-arrow">
			<a href="<?php echo base_url(); ?>member/create_user" class="btn btn-outline-success btn-sm" id="add"><i class="fas fa-plus"></i> Add user</a>
		</div>
	</div>
	<div class="card-body">
	  <div class="table-responsive">
		<table class="table table-bordered" id="dataTable" width="100%">
		 <thead>
		<tr>
			<th>Name</th>
			<th>Mail</th>
			<th>Groups</th>
			<th>Actions</th>
		</tr>
		 </thead>
		  <tbody>
			<?php foreach ($users as $user):?>
			<tr>
				<td><?php echo $user['first_name'];?> <?php echo $user['last_name'];?></td>
				<td><?php echo $user['email'];?></td>
				<td>
					<?php foreach ($user['groups'] as $group):?>
						<!-- <?php echo anchor("auth/edit_group/".$group->id, $group->name) ;?><br /> -->
						<?php echo $group->description;?><br />
					<?php endforeach?>
				</td>
				<td>
					<!-- <?php echo ($user['active']) ? anchor("auth/deactivate/". $user['id'], '<i class="fas fa-fw fa-lock"></i>', 'class="btn btn-outline-danger btn-sm"') : anchor("auth/activate/". $user['id'], '<i class="fas fa-fw fa-lock-open"></i>', 'class="btn btn-outline-info btn-sm"');?>
					&nbsp; -->
					<a href="<?php echo base_url(). 'member/edit_user/' . $user['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-edit"></i></a>
				</td>
			</tr>
			<?php endforeach;?>
		  </tbody>
		</table>
	  </div>

	</div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable();
	$("#usermanagement").show();
	$("#usermgm").addClass('active');
	$("#userlist").addClass('active');
});
</script>