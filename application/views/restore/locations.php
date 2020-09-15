<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Locations List</h6>
            </div>
            <div class="card-body">
			<?php if ($locations): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Location</th>
					<th>edit</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($locations as $loc): ?>
				<tr>
					<td><?php echo $loc['name']; ?></td>
					<td>
						<a href="<?php echo base_url(); ?>restore/locations/<?php echo $loc['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-trash-restore"></i> Restore</a>
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
  