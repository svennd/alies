<div class="row">
	<div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
		<div class="card-header"><a href="<?php echo base_url('pets/fiche/' . $vac['pet']['id']); ?>"><?php echo $vac['pet']['name']; ?></a> / Edit Vaccine</div>
            <div class="card-body">
                <form action="<?php echo base_url('vaccine/update/' . $vac['id']); ?>" method="post" autocomplete="off">
				<table class="table table-sm">
					<tr>
						<td><?php echo $this->lang->line('vaccines'); ?></td>
						<td><?php echo $vac['product']['name']; ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('injection'); ?></td>
						<td><?php echo user_format_date($vac['created_at'], $user->user_date); ?></td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('rappel_date'); ?></td>
						<td>
							<input type="date" class="form-control" id="date_redo" name="date_redo" value="<?php echo $vac['redo']; ?>">
						</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('rappel_date'); ?></td>
						<td>
							<div class="custom-control custom-switch">
								<input type="checkbox" class="custom-control-input" id="no_rappel" name="no_rappel" <?php echo ($vac['no_rappel']) ? 'checked': '';?>>
								<label class="custom-control-label" for="no_rappel"><?php echo $this->lang->line('disable'); ?></label>
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><button type="submit" name="submit" value="1" class="btn btn-primary btn-sm"><?php echo $this->lang->line('edit'); ?></button></td>
					</tr>
				</table>
                </form>
			</div>
	  </div>
	</div>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#clients").addClass('active');
});
</script>
