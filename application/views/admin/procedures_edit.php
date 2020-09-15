<div class="row">
	<div class="col-lg-12 mb-4">
	  <div class="card shadow mb-4">
			<div class="card-header py-3">
			  <h6 class="m-0 font-weight-bold text-primary">Procedures</h6>
			</div>
			<div class="card-body">
				<form method="post" action="<?php echo base_url(); ?>admin/proc">
					<input type="text" class="form-control mb-2 mr-sm-2" name="name" value="<?php echo $proc['name']; ?>" />
					<div class="input-group mb-2 mr-sm-2">
						<input type="text" class="form-control" name="price" value="<?php echo $proc['price']; ?>">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro;</span>
						</div>
					</div>
					<div class="input-group mb-2 mr-sm-2">
						<select name="booking_code" class="form-control" id="booking_code">
							<?php foreach($booking as $t): ?>
								<option value="<?php echo $t['id']; ?>" <?php echo ($t['id'] == $proc['booking_code']['id']) ? "selected='selected'":"";?>><?php echo $t['code'] . ' ' . $t['category'] . ' ' . $t['btw']  . '%'; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<input type="hidden" name="id" value="<?php echo $proc['id'] ?>">
					<button type="submit" name="submit" value="edit_proc" class="btn btn-primary mb-2">Edit Procedure</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#adminproc").addClass('active');
	
	$("#add").on('click',function(){
		$("#add_form").show();
		$(this).hide();
	});
});
</script>
  