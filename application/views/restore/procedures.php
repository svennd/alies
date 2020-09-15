<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Procedures</h6>
            </div>
            <div class="card-body">
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
					<?php if (isset($pro['booking_code']['code'])) : ?>
						<?php echo $pro['booking_code']['code']; ?><br>
						<small><?php echo $pro['booking_code']['category']; ?> <?php echo $pro['booking_code']['btw']; ?>%</small>
					<?php endif; ?>
					</td>
					<td>
						<a href="<?php echo base_url(); ?>restore/procedures/<?php echo $pro['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-trash-restore"></i> Restore</a>
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
	$("#backup").addClass('active');	
});
</script>
  