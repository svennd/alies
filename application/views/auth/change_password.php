<div class="row">
		<div class="col-xl-8">
			<div class="card mb-4">
					<div class="card-header d-flex flex-row align-items-center justify-content-between">
						<?php echo $this->lang->line("change_password"); ?>
					</div>
					<div class="card-body">

			<?php if ($message): ?>
				<div class="alert alert-danger" role="alert">
					<?php echo $message; ?>
				</div>
			<?php endif; ?>
			<form action="<?php echo base_url('vet/change_password'); ?>" method="post" accept-charset="utf-8">
				<div class="mb-1 small"><?php echo $this->lang->line("old_password"); ?></div>
				<div class="form-group">
				  <input type="password" name="old" class="form-control form-control-user">
				</div>
				<div class="mb-1 small"><?php echo $this->lang->line("new_password"); ?></div>
				<div class="form-group">
				  <input type="password" name="new" class="form-control form-control-user">
				</div>
				<div class="mb-1 small"><?php echo $this->lang->line("confirm_password"); ?></div>
				<div class="form-group">
				  <input type="password" name="new_confirm" class="form-control form-control-user">
				</div>

				<?php echo form_input($user_id);?>
				<input type="submit" name="submit" value="<?php echo $this->lang->line("change"); ?>" class="btn btn-primary btn-user btn-block" />
			  </form>		
			</div>
		  </div>
			<div class="card mb-4 py-3 border-left-warning">
				<div class="card-body"><?php echo $this->lang->line("after_change_password"); ?></div>
			</div>
			</div>
</div>