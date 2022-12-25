<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
	  <div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('booking_codes'); ?> / Restore</div>
			</div>
            <div class="card-body">
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
						<a href="<?php echo base_url(); ?>restore/booking/<?php echo $book['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-trash-restore"></i> Restore</a>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No removed booking codes;
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
	
});
</script>
  