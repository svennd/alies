<div class="row">
	<div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
		<div class="card-header">
			<a href="<?php echo base_url('pets/fiche/' . $pet_id); ?>"><?php echo $pet['name']; ?></a> / 
			<a href="<?php echo base_url('vaccine/fiche/' . $pet_id); ?>"><?php echo $this->lang->line('title_vaccines'); ?></a> /
			<?php echo $this->lang->line('add'); ?></div>
            <div class="card-body">
                <form action="<?php echo base_url('vaccine/add_martian_vaccine/' . $pet_id); ?>" method="post" autocomplete="off">
				<table class="table table-sm">
					<tr>
						<td><?php echo $this->lang->line('vaccines'); ?></td>
						<td>
							<input type="text" class="form-control" id="vaccine" name="vaccine" value="" required>
						</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('injection'); ?></td>
						<td>
							<input type="date" class="form-control" id="created_at" name="created_at" value="" required>
						</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('rappel_date'); ?></td>
						<td>
							<input type="date" class="form-control" id="date_redo" name="date_redo" value="">
						</td>
					</tr>
					<tr>
						<td><?php echo $this->lang->line('rappel_date'); ?></td>
						<td>
							<div class="custom-control custom-switch">
								<input type="checkbox" class="custom-control-input" id="no_rappel" name="no_rappel">
								<label class="custom-control-label" for="no_rappel"><?php echo $this->lang->line('enable'); ?></label>
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>
							<div class="d-flex justify-content-between">
								<button type="submit" name="submit" value="1" class="btn btn-primary btn-sm"><?php echo $this->lang->line('add'); ?></button>
							</div>
						</td>
					</tr>
				</table>
                </form>

				<div class="alert alert-warning" role="alert"><?php echo $this->lang->line('vaccine_not_from_stock'); ?></div>
			</div>
	  </div>
	</div>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
});
</script>
