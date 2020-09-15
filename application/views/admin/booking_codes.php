<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Booking codes</h6>
            </div>
            <div class="card-body">
			<a href="#" class="btn btn-outline-success" id="add"><i class="fas fa-plus"></i> Add Booking code</a>
			<div id="add_form" style="display:none;">
				<form method="post" action="<?php echo base_url(); ?>admin/booking" class="form-inline">
					<input type="text" class="form-control mb-2 mr-sm-2" name="category" placeholder="category" value="" />
					<input type="text" class="form-control mb-2 mr-sm-2" name="code" placeholder="code" value="" />
					<div class="input-group mb-2 mr-sm-2">
						<input type="text" class="form-control" name="btw" placeholder="btw" value="" />
						<div class="input-group-append">
						<span class="input-group-text" id="basic-addon2">%</span>
						</div>
					</div>
					<button type="submit" name="submit" value="add_booking_code" class="btn btn-primary mb-2">Add</button>
				</form>
			</div>
			<br/>
			<br/>
			<?php if ($booking): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Category</th>
					<th>Code</th>
					<th>btw</th>
					<th>option</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($booking as $book): ?>
				<tr>
					<td><?php echo $book['category']; ?></td>
					<td><?php echo $book['code']; ?></td>
					<td><?php echo $book['btw']; ?> %</td>
					<td>
						<a href="<?php echo base_url(); ?>admin/booking_rm/<?php echo $book['id']; ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
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
	$("#adminbooking").addClass('active');
	
	$("#add").on('click',function(){
		$("#add_form").show();
		$(this).hide();
	});
});
</script>
  