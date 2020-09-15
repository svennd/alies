<!-- Page Heading -->
<h1 class="h3 mb-4 text-gray-800">Settings</h1>

<div class="row">
<div class="col-lg-6">
	<div class="card shadow mb-4">
			<div class="card-header py-3">
			  <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
			</div>
			<div class="card-body">
			<?php if ($message): ?>
				<div class="alert alert-danger" role="alert">
					<?php echo $message; ?>
				</div>
			<?php endif; ?>
			<form action="<?php echo base_url(); ?>vet/change_password" method="post" accept-charset="utf-8">
				<div class="mb-1 small">Old Password</div>
				<div class="form-group">
				  <input type="password" name="old" class="form-control form-control-user">
				</div>
				<div class="mb-1 small">New Password</div>
				<div class="form-group">
				  <input type="password" name="new" class="form-control form-control-user">
				</div>
				<div class="mb-1 small">Confirm news Password</div>
				<div class="form-group">
				  <input type="password" name="new_confirm" class="form-control form-control-user">
				</div>

				<?php echo form_input($user_id);?>
				<input type="submit" name="submit" value="Change" class="btn btn-primary btn-user btn-block" />
			  </form>		
			</div>
		  </div>
		<div class="card mb-4 py-3 border-left-warning">
			<div class="card-body">
			 After changing the password, you will be logged out automatically; This is normal.
			</div>
		  </div>
	  </div>
  </div>