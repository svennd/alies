<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('booking_codes'); ?></div>
				<div class="dropdown no-arrow">
					<a href="#" class="btn btn-outline-success btn-sm" id="add"><i class="fas fa-plus"></i> Add Booking code</a>
					<a href="<?php echo base_url(); ?>restore/booking" class="btn btn-outline-danger btn-sm"><i class="fas fa-fw fa-history"></i> Restore</a>
				</div>
			</div>
            <div class="card-body">
			
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
			<br/>
			<br/>
			</div>
			<?php if ($booking): ?>
				<table class="table table-sm" id="dataTable">
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
					<td>
						<?php echo $book['category']; ?>
						<small><br/>
							<?php if (isset($book['products'][0]['counted_rows']) && (int)$book['products'][0]['counted_rows'] > 0): ?>
								<a href="<?php echo base_url("products/search_by_booking/" . $book['id']); ?>"><span class="badge badge-success"><?php echo $book['products'][0]['counted_rows']; ?> products</span></a>
							<?php endif; ?>
							<?php if (isset($book['procedures'][0]['counted_rows']) && (int)$book['procedures'][0]['counted_rows'] > 0): ?>
								<a href="<?php echo base_url("products/search_by_booking/" . $book['id']); ?>"><span class="badge badge-primary"><?php echo $book['procedures'][0]['counted_rows']; ?> procedures</span></a>
							<?php endif; ?>
						</td>
					<td><?php echo $book['code']; ?></td>
					<td><?php echo $book['btw']; ?> %</td>
					<td>
						<a href="<?php echo base_url('admin/booking_rm/'. $book['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
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
});
</script>
  