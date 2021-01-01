<div class="card shadow mb-4">
	<div class="card-header py-3">
	  <h6 class="m-0 font-weight-bold text-primary">Users</h6>
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
						<?php echo anchor("auth/edit_group/".$group->id, $group->name) ;?><br />
					<?php endforeach?>
				</td>
				<td>
					<?php echo ($user['active']) ? anchor("auth/deactivate/". $user['id'], '<i class="fas fa-lock"></i>') : anchor("auth/activate/". $user['id'], '<i class="fas fa-lock-open"></i>');?>
					&nbsp;
					<a href="<?php echo base_url(). 'member/edit_user/' . $user['id']; ?>"><i class="fas fa-edit"></i></a>
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