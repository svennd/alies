<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('locations'); ?></div>
				<div class="dropdown no-arrow">
					<a href="#" class="btn btn-outline-success btn-sm" id="add"><i class="fas fa-plus"></i> <?php echo $this->lang->line('add_location'); ?></a>
					<a href="<?php echo base_url(); ?>restore/locations" class="btn btn-outline-danger btn-sm"><i class="fas fa-fw fa-history"></i> Restore</a>
				</div>
			</div>
            <div class="card-body">
			
			<div id="add_form" style="display:none;">
				<form method="post" action="<?php echo base_url(); ?>admin/locations" class="form-inline">
					<input type="text" class="form-control mb-2 mr-sm-2" name="name" value="" />
					<button type="submit" name="submit" value="add_location" class="btn btn-primary mb-2"><?php echo $this->lang->line('add_location'); ?></button>
				</form>
			<br/>
			<br/>
			</div>
			<?php if ($locations): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('location'); ?></th>
					<th><?php echo $this->lang->line('edit'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($locations as $loc): ?>
				<tr>
					<td>
						<div id="name_<?php echo $loc['id']; ?>">	
							<?php echo $loc['name']; ?>
						</div>
						<div id="edit_<?php echo $loc['id']; ?>" style="display:none;">								
							<form method="post" action="<?php echo base_url(); ?>admin/locations" class="form-inline">
								<input type="text" class="form-control mb-2 mr-sm-2" name="name" value="<?php echo $loc['name']; ?>" />
								<input type="hidden" name="id" value="<?php echo $loc['id']; ?>" />
								<button type="submit" name="submit" value="update_location_name" class="btn btn-primary mb-2"><?php echo $this->lang->line('edit'); ?></button>
							</form>
						</div>
					</td>
					<td>
						<a href="#" id="<?php echo $loc['id']; ?>" class="edit btn btn-outline-success btn-sm"><i class="fas fa-edit"></i></a>
						&nbsp;
						<a href="<?php echo base_url(); ?>admin/delete_location/<?php echo $loc['id']; ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php endif; ?>
                </div>
		</div>

	</div>
      
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable();
	$("#admin").addClass('active');
	
	$("#add").on('click',function(){
		$("#add_form").show();
		$(this).hide();
	});
	
	$("#dataTable").on('click', '.edit', function(){
		var id = $(this).attr('id');
		$("#name_" + id).hide();
		$("#edit_" + id).show();
	});
});
</script>
  