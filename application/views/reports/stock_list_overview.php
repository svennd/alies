<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Stock product list</h6>
            </div>
            <div class="card-body">
			This generates a list with all products in the stock. (that are active, state=2)
			<br/>
			<?php if ($locations): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Location</th>
					<th>Download</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($locations as $loc): ?>
				<tr>
					<td><?php echo $loc['name']; ?></td>
					<td><a href="<?php echo base_url(); ?>reports/stock_list/<?php echo $loc['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv"></i> Download</a></td>
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
});
</script>
  