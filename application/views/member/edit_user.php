<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>member">Users</a> /
		Edit User / <?php echo (isset($user_edit->username)) ? $user_edit->username :  $user_edit->id; ?>
	</div>
	
	<div class="card-body">
<?php if($update): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
	<p class="mb-0">Updated !</p>
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<?php endif; ?>

<?php if($warning): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
	<p class="mb-0">warning : <br/><?php echo $warning; ?></p>
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<?php endif; ?>

	<form action="<?php echo base_url() ?>member/edit_user/<?php echo $user_edit->id; ?>" method="post" accept-charset="utf-8">
	<h5>Login info</h5>
	<hr>
	<div class="form-group">
		<label for="email">Email</label>
		<input type="mail" name="email" class="form-control" id="email" value="<?php echo $user_edit->email; ?>" disabled>
	</div>	 
	 <div class="form-row">
		<div class="form-group col-md-6">
			<label for="password">Password</label>
			<input type="password" name="password" class="form-control" id="password" autocomplete='new-password'>
		</div>
		<div class="form-group col-md-6">
		  <label for="password_confirm">Confirm Password:</label>
		  <input type="password" name="password_confirm" class="form-control" id="password_confirm" autocomplete='new-password'>
		</div>
	  </div>
	  <br/>
	<h5>Personal info</h5>
	<hr>
	  <div class="form-row">
		<div class="form-group col-md-6">
		  <label for="first_name">First Name</label>
		  <input type="text" name="first_name" class="form-control" id="first_name" value="<?php echo $user_edit->first_name; ?>">
		</div>
		<div class="form-group col-md-6">
		  <label for="last_name">Last Name</label>
		  <input type="text" name="last_name" class="form-control" id="last_name" value="<?php echo $user_edit->last_name; ?>">
		</div>
	  </div>
		<div class="form-group">
			<label for="phone">Phone</label>
			<input type="text" name="phone" class="form-control" id="phone" value="<?php echo $user_edit->phone; ?>">
		</div>
	  <br/>
	<h5>Permissions</h5>
	<hr>
	
	  <fieldset class="form-group">
    <div class="row">
      <legend class="col-form-label col-sm-2 pt-0">Groups</legend>
      <div class="col-sm-10">
	   <?php foreach ($groups_edit as $group):?>
        <div class="form-check">
			<input type="checkbox" class="form-check-input" name="groups[]" id="<?php echo $group['name']; ?>" value="<?php echo $group['id']; ?>"
			<?php foreach($current_groups_edit as $cgroup) : ?>
				<?php if ($cgroup->id == $group['id']): ?>checked="checked"<?php endif; ?>
			<?php endforeach; ?>
			>
			<label class="form-check-label" for="<?php echo $group['name']; ?>"><?php echo $group['description']; ?></label>
        </div>
       <?php endforeach?>
      </div>
    </div>
  </fieldset>		
  
<br/>
	  <button type="submit" name="submit" value="edit_user" class="btn btn-primary">Edit user</button>
	</form>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
	$("#usermanagement").show();
	$("#usermgm").addClass('active');
	$("#userlist").addClass('active');
});
</script>