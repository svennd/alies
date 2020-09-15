<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Procedures</h6>
            </div>
            <div class="card-body">
			<a href="#" class="btn btn-outline-success" id="add"><i class="fas fa-plus"></i> Add Procedure</a>
			<div id="add_form" style="display:none;">
				<form method="post" action="<?php echo base_url(); ?>admin/proc" class="form-inline">
					<input type="text" class="form-control mb-2 mr-sm-2" name="name" value="" />
					<div class="input-group mb-2 mr-sm-2">
						<input type="text" class="form-control" name="price">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro;</span>
						</div>
					</div>
					<div class="input-group mb-2 mr-sm-2">
						<select name="booking_code" class="form-control" id="booking_code">
							<?php foreach($booking as $t): ?>
								<option value="<?php echo $t['id']; ?>"><?php echo $t['code'] . ' ' . $t['category'] . ' ' . $t['btw']  . '%'; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<button type="submit" name="submit" value="add_proc" class="btn btn-primary mb-2">Add Procedure</button>
				</form>
			</div>
			<br/>
			<br/>
			<?php if ($proc): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Name</th>
					<th>Price</th>
					<th>Booking</th>
					<th>Options</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($proc as $pro): ?>
				<tr>
					<td><?php echo $pro['name']; ?></td>
					<td><?php echo $pro['price']; ?> &euro;</td>
					<td>
						<?php echo $pro['booking_code']['code']; ?><br>
						<small><?php echo $pro['booking_code']['category']; ?> <?php echo $pro['booking_code']['btw']; ?>%</small>
					</td>
					<td>
							<a href="<?php echo base_url(); ?>admin/proc_edit/<?php echo $pro['id']; ?>" class="edit btn btn-outline-success btn-sm btn-edit"><i class="fas fa-edit"></i></a>
							&nbsp;
							<a href="<?php echo base_url(); ?>admin/delete_proc/<?php echo $pro['id']; ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
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
  